<?php

namespace Swissup\ProLabels\Plugin\Block\Product;

class ImageBuilder
{
    protected $product_id;

    /**
     * @param \Swissup\ProLabels\Model\LabelsProvider $labelsProvider
     */
    public function __construct(
        \Swissup\ProLabels\Model\LabelsProvider $labelsProvider
    ) {
        $this->labelsProvider = $labelsProvider;
    }

    /**
     * @param  \Magento\Catalog\Block\Product\ImageBuilder $subject
     * @param  \Magento\Catalog\Model\Product              $product
     * @return null
     */
    public function beforeSetProduct(
        \Magento\Catalog\Block\Product\ImageBuilder $subject,
        \Magento\Catalog\Model\Product $product
    ) {
        $this->product_id = $product->getId();
        $this->labelsProvider->initialize($product, 'category');
        return null;
    }

    /**
     * @param  \Magento\Catalog\Block\Product\ImageBuilder $subject
     * @param  \Magento\Catalog\Block\Product\Image        $result
     * @return \Magento\Catalog\Block\Product\Image
     */
    public function afterCreate(
        \Magento\Catalog\Block\Product\ImageBuilder $subject,
        \Magento\Catalog\Block\Product\Image $result,
        \Magento\Catalog\Model\Product $product = null
    ) {
        if (!$result->hasProductId()) {
            $result->setProductId($this->product_id);
        }

        $this->init_category_by_product($product);

        return $result;
    }

    private function init_category_by_product($product) {
        if ($product) {
            $this->labelsProvider->initialize($product, 'category');
        }
    }
}
