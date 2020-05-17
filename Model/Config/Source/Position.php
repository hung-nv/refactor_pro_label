<?php
/**
 * Copyright © 2015 Swissup. All rights reserved.
 */
namespace Swissup\ProLabels\Model\Config\Source;

class Position implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $result = [
            ['value' => "top-left", 'label' => __('Top-Left')],
            ['value' => "top-center", 'label' => __('Top-Center')],
            ['value' => "top-right", 'label' => __('Top-Right')],
            ['value' => "middle-left", 'label' => __('Middle-Left')],
            ['value' => "middle-center", 'label' => __('Middle-Center')],
            ['value' => "middle-right", 'label' => __('Middle-Right')],
            ['value' => "bottom-left", 'label' => __('Bottom-Left')],
            ['value' => "bottom-center", 'label' => __('Bottom-Center')],
            ['value' => "bottom-right", 'label' => __('Bottom-Right')],
            ['value' => "content", 'label' => __('Content')]
        ];
        return $result;
    }
}
