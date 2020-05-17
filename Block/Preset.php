<?php

namespace Swissup\ProLabels\Block;

use Magento\Framework\View\Element\AbstractBlock;

class Preset extends AbstractBlock
{
    /**
     * Get prolable presets
     *
     * @return array
     */
    public function getProlabels()
    {
        $prolabels = (array)$this->getData('prolabel');
        ksort($prolabels, SORT_NUMERIC);
        return array_values($prolabels);
    }
}
