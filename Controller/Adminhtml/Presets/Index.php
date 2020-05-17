<?php

namespace Swissup\ProLabels\Controller\Adminhtml\Presets;

use Magento\Framework\Controller\ResultFactory;

class Index extends \Magento\Backend\App\Action
{
    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $mode = $this->_request->getParam('mode', 'product');
        $this->_view->loadLayout("prolabels_presets_{$mode}");
        $block = $this->_view->getLayout()->getBlock('prolabel.presets');
        return $this->resultFactory
            ->create(ResultFactory::TYPE_JSON)
            ->setData([
                'labels' => $block->getProlabels()
            ]);
    }
}
