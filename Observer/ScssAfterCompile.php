<?php

namespace Swissup\ProLabels\Observer;

class ScssAfterCompile implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @param \Swissup\ProLabels\Model\LabelsModifier $labelsModifier
     */
    public function __construct(
        \Swissup\ProLabels\Model\LabelsModifier $labelsModifier
    ) {
        $this->labelsModifier = $labelsModifier;
    }

    /**
     * @param  \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $prolabelsStyles = $this->labelsModifier->getCollectedStyles();
        $positions = $this->labelsModifier->getCollectedPositions();
        if (!$prolabelsStyles && !$positions) {
            return;
        }

        $block = $observer->getBlock();
        $transport = $observer->getTransport();
        $styles = $transport->getStyles();
        $styles .= $block->getData('prolabels_styles/_required');
        $classes = array_merge(['absolute'], $positions);
        foreach ($classes as $class) {
            $styles .= $block->getData('prolabels_styles/_dynamic/_prefix') . ' '
                . '.' . $class
                . '{' . $block->getData("prolabels_styles/_dynamic/{$class}") . '}';
        }

        $styles .= $prolabelsStyles;
        $transport->setStyles($styles);
    }
}
