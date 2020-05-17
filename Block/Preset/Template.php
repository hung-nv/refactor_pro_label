<?php

namespace Swissup\ProLabels\Block\Preset;

class Template extends \Magento\Framework\View\Element\Template
{
    /**
     * Render preset template
     *
     * @return string
     */
    public function render()
    {
        return $this->_toHtml();
    }
}
