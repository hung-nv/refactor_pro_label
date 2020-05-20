<?php
/**
 * Plugin for block Magento\Catalog\Block\Product\Image
 */
namespace Swissup\ProLabels\Plugin\Block\Product;

class Image
{
    /**
     * @var \Swissup\ProLabels\Helper\Data
     */
    private $helper;

    /**
     * @param \Swissup\ProLabels\Helper\Data $helper
     */
    public function __construct(
        \Swissup\ProLabels\Helper\CatalogHelpler $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Catalog\Block\Product\Image $subject
     * @param string $result
     * @return string
     */
    public function afterToHtml(
        \Magento\Catalog\Block\Product\Image $subject,
        $result
    ) {
        $return = $result . $this->helper->toHtmlProductLabels($subject->getProductId());

        return $return;
    }
}
