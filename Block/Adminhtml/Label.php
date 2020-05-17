<?php
namespace Swissup\ProLabels\Block\Adminhtml;

/**
 * ProLabels label content block
 */
class Label extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_blockGroup     = 'Swissup_ProLabels';
        $this->_controller     = 'adminhtml_label';
        $this->_headerText     = __('Product Labels');
        $this->_addButtonLabel = __('Add New Label');
        parent::_construct();
    }
}
