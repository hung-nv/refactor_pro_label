<?php

namespace Swissup\ProLabels\Block\System\Config\Form\Field\Presets\Product;

use Swissup\ProLabels\Block\System\Config\Form\Field\PresetsAbstract;

class NewLabel extends PresetsAbstract
{
    /**
     * {@inheritdoc}
     */
    public function getPresetsUrl()
    {
        return $this->getUrl(
                'prolabels/presets',
                [
                    'type' => 'new'
                ]
            );
    }
}
