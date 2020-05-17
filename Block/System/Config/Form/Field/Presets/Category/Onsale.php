<?php

namespace Swissup\ProLabels\Block\System\Config\Form\Field\Presets\Category;

use Swissup\ProLabels\Block\System\Config\Form\Field\PresetsAbstract;

class Onsale extends PresetsAbstract
{
    /**
     * {@inheritdoc}
     */
    public function getPresetsUrl()
    {
        return $this->getUrl(
                'prolabels/presets',
                [
                    'mode' => 'category',
                    'type' => 'on_sale'
                ]
            );
    }
}
