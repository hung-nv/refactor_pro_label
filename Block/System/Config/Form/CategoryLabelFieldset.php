<?php

namespace Swissup\ProLabels\Block\System\Config\Form;

class CategoryLabelFieldset extends ProductLabelFieldset
{
    /**
     * Build array for data-mage-init
     *
     * @return array
     */
    protected function getMageInitArray()
    {
        $mageInit = parent::getMageInitArray();
        $mageInit['Swissup_ProLabels/js/preview']['template'] =
            'Swissup_ProLabels/preview/category-labels';
        return $mageInit;
    }
}
