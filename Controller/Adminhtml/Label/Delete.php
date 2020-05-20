<?php
namespace Swissup\ProLabels\Controller\Adminhtml\Label;

class Delete extends \Magento\Backend\App\Action
{
    /**
     * @var \Swissup\ProLabels\Model\LabelFactory
     */
    protected $label_factory;

    /**
     * @param \Swissup\ProLabels\Model\LabelFactory $labelFactory
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Swissup\ProLabels\Model\LabelFactory $labelFactory,
        \Magento\Backend\App\Action\Context $context
    ) {
        $this->label_factory = $labelFactory;
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
        /** @var \Magento\Backend\Model\View\Result\Redirect $result_redirect */
        $result_redirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('label_id');

        if ($id) {
            return $this->run_execute($id, $result_redirect);
        }

        $this->messageManager->addError(__('Can\'t find a label to delete.'));
        
        return $result_redirect->setPath('*/*/');
    }

    private function run_execute($id, $result_redirect) {
        try {
            $model = $this->label_factory->create();
            $model->load($id);
            $model->delete();
            $this->messageManager->addSuccess(__('Label was deleted.'));
            return $result_redirect->setPath('*/*/');
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            return $result_redirect->setPath('*/*/edit', ['label_id' => $id]);
        }
    }
}
