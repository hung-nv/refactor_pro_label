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
    protected $jsonEncoder;
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
    protected $labelFactory;

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
        $this->jsonEncoder = $jsonEncoder;
        $this->resource = $resource;
        $this->connection = $resource->getConnection();
        $this->labelFactory = $labelFactory;
        parent::__construct($context);
    }
    /**
     * Index orders action
     *
     */
    public function execute()
    {
        $indexingLabels = array();
        $labelId = $this->getRequest()->getParam('label_id');
        $labelModel = $this->labelFactory->create();
        $session = $this->_session;
        if (!$session->hasData("swissup_labels_init")) {
            if ($labelId) {
                $indexingLabels[] = $labelId;
                $labelModel->load($labelId);
                $this->connection->delete(
                    $this->resource->getTableName('swissup_prolabels_index'),
                    ['label_id=?' => $labelModel->getId()]
                );
            } else {
                //indexing all labels
                $labelsCollection = $labelModel->getCollection();
                $labelsCollection->addFieldToFilter('status', 1);
                $indexingLabels = $labelsCollection->getAllIds();
                $this->connection->delete(
                    $this->resource->getTableName('swissup_prolabels_index')
                );
            }

            if (count($indexingLabels) == 0) {
                $this->messageManager->addNotice(__('We couldn\'t find any labels'));
                return $this->getResponse()->setBody(
                    $this->jsonEncoder->encode(array(
                        'finished'  => true
                    ))
                );
            }

            $session->setData("swissup_labels", $indexingLabels);
            $session->setData("swissup_labels_success", []);
            $session->setData("swissup_label_new", 1);
            $session->setData("swissup_labels_init", 1);
        }

        if ($session->getData("swissup_label_new")) {
            // prepare to reindex new label
            $productIds = $labelModel->prepareProductsToIndexing();
            $session->setData("swissup_label_product_count", count($productIds));
            $session->setData("swissup_label_product_apply", 0);
            $session->setData("swissup_label_step", 0);
            $session->setData("swissup_label_new", 0);

            $percent = 100 * (int)$session->getData("swissup_label_product_apply") / (int)$session->getData("swissup_label_product_count");
            $responseLoaderText = count($session->getData("swissup_labels_success")) + 1
                . ' of ' . count($session->getData("swissup_labels")) . ' - ' . $percent . '%';
            $this->getResponse()->setBody(
                $this->jsonEncoder->encode(array(
                    'finished'  => false,
                    'loaderText' => $responseLoaderText
                ))
            );
        } else {
            $notApplyedLabelIds = array_diff(
                $session->getData("swissup_labels"),
                $session->getData("swissup_labels_success")
            );
            $labelId = reset($notApplyedLabelIds);
            $labelModel->load($labelId);
            $productsForIndexing = $labelModel->getItemsToReindex(self::PAGE_SIZE, $session->getData("swissup_label_step"));
            if (count($productsForIndexing) > 0) {
                $productCountForIndexing = count($productsForIndexing);
                $reindexedProductCount = $productCountForIndexing + (int)$session->getData("swissup_label_product_apply");
                $session->setData("swissup_label_product_apply", $reindexedProductCount);
                $applyedProducts = $labelModel->getMatchingProductIds($productsForIndexing);

                if (count($applyedProducts) > 0) {
                    $this->connection->insertMultiple(
                        $this->resource->getTableName('swissup_prolabels_index'), $applyedProducts);
                }
                $prevStep = (int)$session->getData("swissup_label_step");
                $nextStep = $prevStep + 1;
                $session->setData("swissup_label_step", $nextStep);

                $percent = 100 * (int)$session->getData("swissup_label_product_apply") / (int)$session->getData("swissup_label_product_count");
                $responseLoaderText = count($session->getData("swissup_labels_success")) + 1
                    . ' of ' . count($session->getData("swissup_labels")) . ' - ' . (int)$percent . '%';
                return $this->getResponse()->setBody(
                    $this->jsonEncoder->encode(array(
                        'finished'  => false,
                        'loaderText' => $responseLoaderText
                    ))
                );
            } else {
                // finish aplly label
                $percent = 100 * (int)$session->getData("swissup_label_product_apply") / (int)$session->getData("swissup_label_product_count");
                $responseLoaderText = count($session->getData("swissup_labels_success")) + 1
                    . ' of ' . count($session->getData("swissup_labels")) . ' - ' . (int)$percent . '%';
                $successLabels = $session->getData("swissup_labels_success");
                $successLabels[] = $labelModel->getId();
                $session->setData("swissup_labels_success", $successLabels);
                $notApplyedLabelIds = array_diff(
                    $session->getData("swissup_labels"),
                    $session->getData("swissup_labels_success")
                );
                if (count($notApplyedLabelIds) > 0) {
                    $session->setData("swissup_label_new", 1);
                    return $this->getResponse()->setBody(
                        $this->jsonEncoder->encode(array(
                            'finished'  => false,
                            'loaderText' => $responseLoaderText
                        ))
                    );
                } else {
                    //all labels are applyed
                    $successCount = count($session->getData("swissup_labels_success"));
                    $session->unsetData("swissup_labels_init");
                    $session->unsetData("swissup_label_product_apply");
                    $session->unsetData("swissup_labels");
                    $session->unsetData("swissup_label_product_count");
                    $session->unsetData("swissup_labels_success");
                    $session->unsetData("swissup_label_step");
                    if ($successCount > 1) {
                        $this->messageManager->addSuccess(__('Labels have been applied.'));
                    } else {
                        $this->messageManager->addSuccess(__('Label has been applied.'));
                    }
                    return $this->getResponse()->setBody(
                        $this->jsonEncoder->encode(array(
                            'finished'  => true
                        ))
                    );
                }
            }
        }
    }
}
