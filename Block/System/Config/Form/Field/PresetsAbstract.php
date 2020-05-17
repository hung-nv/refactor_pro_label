<?php

namespace Swissup\ProLabels\Block\System\Config\Form\Field;

abstract class PresetsAbstract extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * {@inheritdoc}
     */
    protected function _getElementHtml(
        \Magento\Framework\Data\Form\Element\AbstractElement $element
    ) {
        // add button to start preview
        $button = $this->getLayout()
            ->createBlock('Magento\Backend\Block\Widget\Button')
            ->setDisabled($element->getDisabled())
            ->setLabel(__('Load and Select'));

        $mageInit = json_encode([
            'Swissup_ProLabels/js/label-presets' => [
                'url' => $this->getPresetsUrl()
            ]
        ]);

        $togglerMageInit = json_encode([
            'Swissup_ProLabels/js/options-toggler' => []
        ]);

        return '<div class="prolabels-presets" data-mage-init=\'' . $mageInit . '\'>'
            . $button->toHtml()
            . '</div>'
            . '<div class="options-toggler" data-mage-init=\'' . $togglerMageInit . '\'><a href="#" data-show="Show advanced options" data-hide="Hide advanced options" title="Check advanced options to change text, color or upload image for label"></a></div>';
    }

    /**
     * Get URL to load labels presets
     *
     * @return string
     */
    abstract public function getPresetsUrl();
}
