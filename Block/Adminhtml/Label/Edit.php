<?php

namespace Swissup\ProLabels\Block\Adminhtml\Label;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Initialize label edit block
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'label_id';
        $this->_blockGroup = 'Swissup_ProLabels';
        $this->_controller = 'adminhtml_label';

        parent::_construct();

        if ($this->_isAllowedAction('Swissup_ProLabels::save')) {
            $this->buttonList->update('save', 'label', __('Save Label'));
            $this->buttonList->add(
                'saveandcontinue',
                [
                    'label' => __('Save and Continue Edit'),
                    'class' => 'save',
                    'data_attribute' => [
                        'mage-init' => [
                            'button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form'],
                        ],
                    ]
                ],
                -100
            );
            if ($this->_coreRegistry->registry('prolabel')->getId()) {
                $this->buttonList->add(
                    'apply',
                    [
                        'class' => 'apply',
                        'label' => __('Apply')
                    ]
                );
            }
        } else {
            $this->buttonList->remove('save');
        }

        if ($this->_isAllowedAction('Swissup_ProLabels::delete')) {
            $this->buttonList->update('delete', 'label', __('Delete Label'));
        } else {
            $this->buttonList->remove('delete');
        }
    }

    /**
     * Retrieve text for header element depending on loaded post
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        if ($this->_coreRegistry->registry('prolabel')->getId()) {
            return __("Edit Label '%1'", $this->escapeHtml($this->_coreRegistry->registry('prolabel')->getTitle()));
        } else {
            return __('New Label');
        }
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }

    /**
     * Getter of url for "Save and Continue" button
     * tab_id will be replaced by desired by JS later
     *
     * @return string
     */
    protected function _getSaveAndContinueUrl()
    {
        return $this->getUrl('*/*/save', ['_current' => true, 'back' => 'edit', 'active_tab' => '']);
    }
}
