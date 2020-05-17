<?php
namespace Swissup\ProLabels\Controller\Adminhtml\Label;

class Enable extends \Magento\Backend\App\Action
{
    /**
     * @var \Swissup\ProLabels\Model\LabelFactory
     */
    protected $labelFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(
        \Swissup\ProLabels\Model\LabelFactory $labelFactory,
        \Magento\Backend\App\Action\Context $context
    ) {
        $this->labelFactory = $labelFactory;
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
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('label_id');
        if ($id) {
            try {
                $model = $this->labelFactory->create();
                $model->load($id);
                $model->setStatus($this->getStatusCode());
                $model->save();
                $this->addSuccessMessage($model);
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $resultRedirect->setPath(
                    '*/*/edit',
                    [
                        'label_id' => $id
                    ]
                );
            }
        }

        $this->messageManager->addError(__('Can\'t find a label.'));
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return 1;
    }

    /**
     * Add success message about status change
     */
    protected function addSuccessMessage(
        \Swissup\ProLabels\Model\Label $label
    ) {
        $this->messageManager->addSuccess(
            __('Label "%1" was enabled.', $label->getTitle())
        );
    }
}
