<?php
/**
 * Copyright © 2015 Swissup. All rights reserved.
 */
namespace Swissup\ProLabels\Model\Config\Source;

class RoundMethod implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $result = [
            ['value' => "round", 'label' => __('Math')],
            ['value' => "ceil", 'label' => __('Ceil')],
            ['value' => "floor", 'label' => __('Floor')]
        ];
        return $result;
    }
}
