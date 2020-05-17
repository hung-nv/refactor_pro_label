<?php

namespace Swissup\ProLabels\Model;

use Magento\Framework\App\ObjectManager;

class LabelsModifier
{
    /**
     * @var array
     */
    protected $styles = [];

    /**
     * @var array
     */
    protected $positions = [];

    /**
     * @var \Magento\Framework\App\Helper\AbstractHelper|null
     */
    private $imageHelper = null;

    /**
     * @return \Magento\Framework\App\Helper\AbstractHelper|null
     */
    private function getImageHelper()
    {
        if (is_null($this->imageHelper)) {
            if (class_exists('\Swissup\Amp\Helper\Image')) {
                $this->imageHelper = ObjectManager::getInstance()->get(
                    '\Swissup\Amp\Helper\Image'
                );
            } else {
                $this->imageHelper = false;
            }
        }

        return $this->imageHelper;
    }

    /**
     * Modify labels. Use CSS classes instead of inline css.
     *
     * @param  \Magento\Framework\DataObject $labels
     */
    public function modify(\Magento\Framework\DataObject $labels)
    {
        $labelsData = $labels->getLabelsData();
        foreach ($labelsData as &$data) {
            if (!isset($data['items'])) {
                continue;
            }

            // Collect labels positions
            if (isset($data['position'])) {
                $this->addPosition($data['position']);
            }

            foreach ($data['items'] as &$label) {
                $inlineCss = '';
                // collect label CSS for class.
                if (!empty($label['image'])) {
                    $inlineCss .= $this->getBackgroundCss($label['image']);
                    $label['image'] = '';
                }

                if (!empty($label['custom'])) {
                    $inlineCss .= $label['custom'];
                    $label['custom'] = '';
                }

                // Get class name for collected label CSS.
                if ($inlineCss) {
                    $label['css_class'] = $this->getCssClass($inlineCss);
                }

                // Replace inline CSS in label text with class(es).
                if (!empty($label['text'])) {
                    $label['text'] = $this->replaceInlineCSS($label['text']);
                }
            }
        }

        $labels->setData('labels_data', $labelsData);
    }

    /**
     * Get css collected during labels data modification.
     *
     * @return string
     */
    public function getCollectedStyles()
    {
        $css = '';
        foreach ($this->styles as $class => $values) {
            $css .= '.' . $class .'.' . $class . '{' . $values . '}';
        }

        return $css;
    }

    /**
     * Find inline css in HTML string (first occurence).
     *
     * @param  string $html
     * @return string
     */
    public function findInlineCss($html){
        // insired by https://stackoverflow.com/a/5518159
        preg_match('/(<[^>]+) style=".*?"/i', $html, $matches);
        if (!$matches) {
            preg_match('/(<[^>]+) style=\'.*?\'/i', $html, $matches);
        }

        if (!$matches) {
            return '';
        }

        // + 1 is for quote character
        $css = substr($matches[0], strlen($matches[1]) + strlen(' style=') + 1);
        $css = substr($css, 0, -1);

        return $css;
    }

    /**
     * Replace inline css string in HTMl with respective CSS class.
     *
     * @param  string $html
     * @return string
     */
    private function replaceInlineCSS($html)
    {
        $i = 0;
        $css = $this->findInlineCss($html);
        while ($css && $i++ <= 20) {
            $class = $this->getCssClass($css);
            $replaced = 0;
            $html = str_replace("style=\"{$css}\"", "class=\"{$class}\"", $html, $replaced);
            if ($replaced < 1) {
                // nothing was replaced; assume it is style='...'
                $html = str_replace("style='{$css}'", "class=\"{$class}\"", $html);
            }

            $css = $this->findInlineCss($html);
        }

        return $html;
    }

    /**
     * Get respective class for css.
     *
     * @param  string $css
     * @param  string $prefix
     * @return string
     */
    private function getCssClass($css, $prefix = 'spl-')
    {
        if (!in_array($css, $this->styles)) {
            $this->styles[$prefix . count($this->styles)] = $css;
        }

        return array_search($css, $this->styles);
    }

    /**
     * @param string $position
     */
    private function addPosition($position)
    {
        if (!in_array($position, $this->positions)) {
            $this->positions[] = $position;
        }
    }

    /**
     * Get positions collected during labels data modifications.
     *
     * @return array
     */
    public function getCollectedPositions()
    {
        return $this->positions;
    }

    /**
     * Generate css for background image.
     *
     * @param  string $image
     * @return string
     */
    private function getBackgroundCss($image)
    {
        $helper = $this->getImageHelper();

        return $helper
            ? "background-image:url({$image});"
                ."width:{$helper->getWidth($image)}px;"
                ."height:{$helper->getHeight($image)}px;"
            : '';
    }
}
