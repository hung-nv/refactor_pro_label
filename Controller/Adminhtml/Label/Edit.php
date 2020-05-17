<?php

namespace Swissup\ProLabels\Controller\Adminhtml\Label;

use Magento\Backend\App\Action;

class Edit extends Action
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Swissup\ProLabels\Model\LabelFactory
     */
    protected $labelFactory;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Action\Context                             $context
     * @param \Swissup\ProLabels\Model\LabelFactory      $labelFactory
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry                $registry
     */
    public function __construct(
        Action\Context $context,
        \Swissup\ProLabels\Model\LabelFactory $labelFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry
    ) {
        $this->labelFactory = $labelFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->registry = $registry;
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
     * Init actions
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function _initAction()
    {
        // load layout, set active menu and breadcrumbs
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Swissup_ProLabels::prolabels')
            ->addBreadcrumb(__('ProLabels'), __('ProLabels'))
            ->addBreadcrumb(__('Manage Labels'), __('Manage Labels'));
        return $resultPage;
    }

    /**
     * Edit Blog post
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('label_id');
        $model = $this->labelFactory->create();
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This label no longer exists.'));
                /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();

                return $resultRedirect->setPath('*/*/');
            }
        }

        $this->registry->register('prolabel', $model);

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_initAction();
        $resultPage->addBreadcrumb(
            $id ? __('Edit "%1"', $model->getTitle()) : __('New Label'),
            $id ? __('Edit "%1"', $model->getTitle()) : __('New Label')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('ProLabels'));
        $resultPage->getConfig()->getTitle()
            ->prepend(
                $model->getId()
                    ? __('Edit "%1"', $model->getTitle())
                    : __('New Label')
            );

        return $resultPage;
    }
}
