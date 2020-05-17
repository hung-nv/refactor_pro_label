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
    protected $resultPageFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Swissup_ProLabels::prolabels_labels');
        $resultPage->addBreadcrumb(__('ProLabels'), __('ProLabels'));
        $resultPage->addBreadcrumb(__('Product Labels'), __('Product Labels'));
        $resultPage->getConfig()->getTitle()->prepend(__('Product Labels'));

        return $resultPage;
    }
}
