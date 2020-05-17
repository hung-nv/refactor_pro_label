<?php
namespace Swissup\ProLabels\Controller\Adminhtml\Label;

use Magento\Backend\App\Action;

class Save extends Action
{
    /**
     * Array of image uploaders for product label and category label
     *
     * @var array
     */
    protected $imageUploader;

    /**
     * @var \Magento\Framework\App\Request\DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var \Swissup\ProLabels\Model\LabelFactory
     */
    protected $labelFactory;

    /**
     * @param Action\Context                                        $context
     * @param \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
     * @param \Swissup\ProLabels\Model\LabelFactory                 $labelFactory
     * @param array                                                 $imageUploader
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor,
        \Swissup\ProLabels\Model\LabelFactory $labelFactory,
        $imageUploader = []
    ) {
        $this->dataPersistor = $dataPersistor;
        $this->labelFactory = $labelFactory;
        $this->imageUploader = $imageUploader;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Swissup_ProLabels::save');
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $this->dataPersistor->set('prolabels_label', $data);
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            /** @var \Swissup\ProLabels\Model\Label $model */
            $model = $this->labelFactory->create();
            $id = $this->getRequest()->getParam('label_id');
            if ($id) {
                $model->load($id);
            }

            if (isset($data['rule'])) {
                $data['conditions'] = $data['rule']['conditions'];
                unset($data['rule']);
            }

            $model->loadPost($data);
            /*
             ** Label Images Upload
             */
            foreach ($this->imageUploader as $mode => $imageUploader) {
                $imageName = '';
                if (isset($data["{$mode}_image"])
                    && is_array($data["{$mode}_image"])
                ) {
                    $imageName = isset($data["{$mode}_image"][0]['name'])
                        ? $data["{$mode}_image"][0]['name']
                        : '';
                    if (isset($data["{$mode}_image"][0]['tmp_name'])) {
                        try {
                            $imageUploader->moveFileFromTmp($imageName);
                        } catch (\Exception $e) {
                            //
                        }
                    }
                }
                $model->setData("{$mode}_image", $imageName);
            }

            $model->setCustomerGroups($data['customer_groups']);
            $model->setStoreId($data['store_id']);
            try {
                $model->save();
                $this->messageManager->addSuccess(__('Label has been saved.'));
                $this->dataPersistor->clear('prolabels_label');
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath(
                        '*/*/edit',
                        [
                            'label_id' => $model->getId(),
                            '_current' => true
                        ]
                    );
                }

                return $resultRedirect->setPath('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $this->messageManager->addException(
                    $e,
                    __('Something went wrong while saving the label.')
                );
            }

            return $resultRedirect->setPath(
                '*/*/edit',
                [
                    'label_id' => $this->getRequest()->getParam('label_id')
                ]
            );
        }
        return $resultRedirect->setPath('*/*/');
    }
}
