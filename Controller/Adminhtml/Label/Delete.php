<?php
namespace Swissup\ProLabels\Controller\Adminhtml\Label;

class Delete extends \Magento\Backend\App\Action
{
    /**
     * @var \Swissup\ProLabels\Model\LabelFactory
     */
    protected $labelFactory;

    /**
     * @param \Swissup\ProLabels\Model\LabelFactory $labelFactory
     * @param \Magento\Backend\App\Action\Context $context
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
        return $this->_authorization->isAllowed('Swissup_ProLabels::delete');
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
                $model->delete();
                $this->messageManager->addSuccess(__('Label was deleted.'));
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['label_id' => $id]);
            }
        }
        $this->messageManager->addError(__('Can\'t find a label to delete.'));
        return $resultRedirect->setPath('*/*/');
    }
}
