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
    protected $label_factory;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $result_page_factory;

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
        $this->label_factory = $labelFactory;
        $this->result_page_factory = $resultPageFactory;
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
        $resultPage = $this->result_page_factory->create();
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
        $model = $this->label_factory->create();
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

        $result_page = $this->set_result_page($id, $model);

        return $result_page;
    }

    /**
     * {inherit}
     */
    private function set_result_page($id, $model) {
        /** @var \Magento\Backend\Model\View\Result\Page $result_page */
        $result_page = $this->_initAction();
        $result_page->addBreadcrumb(
          $id ? __('Edit "%1"', $model->getTitle()) : __('New Label'),
          $id ? __('Edit "%1"', $model->getTitle()) : __('New Label')
        );
        $result_page->getConfig()->getTitle()->prepend(__('ProLabels'));
        $result_page->getConfig()->getTitle()
          ->prepend(
            $model->getId()
              ? __('Edit "%1"', $model->getTitle())
              : __('New Label')
          );

        return $result_page;
    }
}
