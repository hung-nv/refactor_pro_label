<?php
namespace Swissup\ProLabels\Controller\Adminhtml\Label;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Swissup_ProLabels::prolabels';

    /**
     * @var PageFactory
     */
    protected $result_page_factory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->result_page_factory = $resultPageFactory;
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->run_execute();

        return $resultPage;
    }

    /**
     * {inherit}
     */
    private function run_execute() {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->result_page_factory->create();
        $resultPage->setActiveMenu('Swissup_ProLabels::prolabels_labels');
        $resultPage->addBreadcrumb(__('ProLabels'), __('ProLabels'));
        $resultPage->addBreadcrumb(__('Product Labels'), __('Product Labels'));
        $resultPage->getConfig()->getTitle()->prepend(__('Product Labels'));

        return $resultPage;
    }
}
