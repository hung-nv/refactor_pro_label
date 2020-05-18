<?php
namespace Swissup\ProLabels\Controller\Adminhtml\Label;

use Magento\Backend\App\Action\Context;

class Apply extends \Magento\Backend\App\Action
{
    const PAGE_SIZE = 500;
    /**
     * Json encoder
     *
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $json_encoder;
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;
    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;

    /**
     * @var \Swissup\ProLabels\Model\LabelFactory
     */
    protected $label_factory;

    /**
     * @param Context                                   $context
     * @param \Magento\Framework\Json\EncoderInterface  $jsonEncoder
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Swissup\ProLabels\Model\LabelFactory     $labelFactory
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\App\ResourceConnection $resource,
        \Swissup\ProLabels\Model\LabelFactory $labelFactory
    ) {
        $this->json_encoder = $jsonEncoder;
        $this->resource = $resource;
        $this->connection = $resource->getConnection();
        $this->label_factory = $labelFactory;
        parent::__construct($context);
    }
    /**
     * Index orders action
     *
     */
    public function execute()
    {
        $indexing_labels = array();
        $label_id = $this->getRequest()->getParam('label_id');
        $label_model = $this->label_factory->create();
        $session = $this->_session;
        if (!$session->hasData("swissup_labels_init")) {
            if ($label_id) {
                $indexing_labels[] = $label_id;
                $label_model->load($label_id);
                $this->connection->delete(
                    $this->resource->getTableName('swissup_prolabels_index'),
                    ['label_id=?' => $label_model->getId()]
                );
            } else {
                //indexing all labels
                $labels_collection = $label_model->getCollection();
                $labels_collection->addFieldToFilter('status', 1);
                $indexing_labels = $labels_collection->getAllIds();
                $this->connection->delete(
                    $this->resource->getTableName('swissup_prolabels_index')
                );
            }

            if (count($indexing_labels) == 0) {
                $this->messageManager->addNotice(__('We couldn\'t find any labels'));
                return $this->getResponse()->setBody(
                    $this->json_encoder->encode(array(
                        'finished'  => true
                    ))
                );
            }

            $session->setData("swissup_labels", $indexing_labels);
            $session->setData("swissup_labels_success", []);
            $session->setData("swissup_label_new", 1);
            $session->setData("swissup_labels_init", 1);
        }

        if ($session->getData("swissup_label_new")) {
            // prepare to reindex new label
            $productIds = $label_model->prepareProductsToIndexing();
            $session->setData("swissup_label_product_count", count($productIds));
            $session->setData("swissup_label_product_apply", 0);
            $session->setData("swissup_label_step", 0);
            $session->setData("swissup_label_new", 0);

            $percent = 100 * (int)$session->getData("swissup_label_product_apply") / (int)$session->getData("swissup_label_product_count");
            $response_loader_text = count($session->getData("swissup_labels_success")) + 1
                . ' of ' . count($session->getData("swissup_labels")) . ' - ' . $percent . '%';
            $this->getResponse()->setBody(
                $this->json_encoder->encode(array(
                    'finished'  => false,
                    'loaderText' => $response_loader_text
                ))
            );
        } else {
            $not_applyed_label_ids = array_diff(
                $session->getData("swissup_labels"),
                $session->getData("swissup_labels_success")
            );
            $label_id = reset($not_applyed_label_ids);
            $label_model->load($label_id);
            $productsForIndexing = $label_model->getItemsToReindex(self::PAGE_SIZE, $session->getData("swissup_label_step"));
            if (count($productsForIndexing) > 0) {
                $productCountForIndexing = count($productsForIndexing);
                $reindexedProductCount = $productCountForIndexing + (int)$session->getData("swissup_label_product_apply");
                $session->setData("swissup_label_product_apply", $reindexedProductCount);
                $applyedProducts = $label_model->getMatchingProductIds($productsForIndexing);

                if (count($applyedProducts) > 0) {
                    $this->connection->insertMultiple(
                        $this->resource->getTableName('swissup_prolabels_index'), $applyedProducts);
                }
                $prevStep = (int)$session->getData("swissup_label_step");
                $nextStep = $prevStep + 1;
                $session->setData("swissup_label_step", $nextStep);

                $percent = 100 * (int)$session->getData("swissup_label_product_apply") / (int)$session->getData("swissup_label_product_count");
                $response_loader_text = count($session->getData("swissup_labels_success")) + 1
                    . ' of ' . count($session->getData("swissup_labels")) . ' - ' . (int)$percent . '%';
                return $this->getResponse()->setBody(
                    $this->json_encoder->encode(array(
                        'finished'  => false,
                        'loaderText' => $response_loader_text
                    ))
                );
            } else {
                // finish aplly label
                $percent = 100 * (int)$session->getData("swissup_label_product_apply") / (int)$session->getData("swissup_label_product_count");
                $response_loader_text = count($session->getData("swissup_labels_success")) + 1
                    . ' of ' . count($session->getData("swissup_labels")) . ' - ' . (int)$percent . '%';
                $successLabels = $session->getData("swissup_labels_success");
                $successLabels[] = $label_model->getId();
                $session->setData("swissup_labels_success", $successLabels);
                $not_applyed_label_ids = array_diff(
                    $session->getData("swissup_labels"),
                    $session->getData("swissup_labels_success")
                );
                if (count($not_applyed_label_ids) > 0) {
                    $session->setData("swissup_label_new", 1);
                    return $this->getResponse()->setBody(
                        $this->json_encoder->encode(array(
                            'finished'  => false,
                            'loaderText' => $response_loader_text
                        ))
                    );
                } else {
                    //all labels are applyed
                    $success_count = count($session->getData("swissup_labels_success"));
                    $session->unsetData("swissup_labels_init");
                    $session->unsetData("swissup_label_product_apply");
                    $session->unsetData("swissup_labels");
                    $session->unsetData("swissup_label_product_count");
                    $session->unsetData("swissup_labels_success");
                    $session->unsetData("swissup_label_step");
                    if ($success_count > 1) {
                        $this->messageManager->addSuccess(__('Labels have been applied.'));
                    } else {
                        $this->messageManager->addSuccess(__('Label has been applied.'));
                    }
                    return $this->getResponse()->setBody(
                        $this->json_encoder->encode(array(
                            'finished'  => true
                        ))
                    );
                }
            }
        }
    }
}
