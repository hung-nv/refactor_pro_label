<?php

namespace Swissup\ProLabels\Model\Renderer;

use Magento\Framework\DataObject;

class Amp
{
    /**
     * Render labels.
     *
     * @param  DataObject $labels
     * @param  array      $skipPostions
     * @return string
     */
    public function render(
        DataObject $labels,
        array $skipPostions = ['content']
    ) {
        $html = '';
        $labelsData = $labels->getLabelsData();
        foreach ($labelsData as $data) {
            if (in_array($data['position'], $skipPostions)) {
                continue;
            }

            $class = $data['position'];
            if ($class !== 'content') {
                $class .= ' absolute';
            } else {
                $class .= ' prolabels';
            }

            $html .= "<div class=\"{$class}\">";
            foreach ($data['items'] as $label) {
                $label = new DataObject($label);
                $labelHtml = $this->getLabelHtml(
                    "prolabel {$label->getCssClass()}",
                    $label->getText()
                );
                $html .= $this->replaceVariables(
                    $labelHtml,
                    $labels->getPredefinedVariables(),
                    $label->getRoundMethod(),
                    $label->getRoundValue()
                );
            }

            $html .= "</div>";
        }

        return $html;
    }

    /**
     * Replace variables with its values in $html.
     *
     * @param  string  $html
     * @param  array   $vars
     * @param  string  $round_method
     * @param  integer $round_value
     * @return string
     */
    protected function replaceVariables(
        $html,
        $vars,
        $round_method = '',
        $round_value = 1
    ) {
        $round_value = (float)$round_value ? (float)$round_value : 1;
        foreach ($vars as $var => $value) {
            $value = is_float($value) && $round_method
                ? call_user_func($round_method, $value / $round_value)
                : $value;
            $html = str_replace($var, $value, $html);
        }

        return $html;
    }

    /**
     * @param  string $css_class
     * @param  string $text
     * @return string
     */
    protected function getLabelHtml($css_class, $text)
    {
        return "<span class=\"{$css_class}\">"
            . "<span class=\"prolabel__inner\">"
                . "<span class=\"prolabel__wrapper\">"
                    . "<span class=\"prolabel__content\">{$text}</span>"
                . "</span>"
            . "</span>"
        . "</span>";
    }
}
