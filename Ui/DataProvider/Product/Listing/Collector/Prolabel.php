<?php

namespace Swissup\ProLabels\Ui\DataProvider\Product\Listing\Collector;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductRenderExtensionFactory;
use Magento\Catalog\Api\Data\ProductRenderInterface;
use Magento\Catalog\Ui\DataProvider\Product\ProductRenderCollectorInterface;

/**
 * Collect enough for rendering prolabel in recently viewed widget.
 */
class Prolabel implements ProductRenderCollectorInterface
{
    /** Review html key */
    const KEY = "swissup_prolabel";

    /**
     * @var ProductRenderExtensionFactory
     */
    private $productRenderExtensionFactory;

    /**
     * @var \Swissup\ProLabels\Helper\Catalog
     */
    private $helper;

    /**
     * @param ProductRenderExtensionFactory     $productRenderExtensionFactory
     * @param \Swissup\ProLabels\Helper\Catalog $helper
     */
    public function __construct(
        ProductRenderExtensionFactory $productRenderExtensionFactory,
        \Swissup\ProLabels\Helper\Catalog $helper
    ) {
        $this->productRenderExtensionFactory = $productRenderExtensionFactory;
        $this->helper = $helper;
    }

    /**
     * @inheritdoc
     */
    public function collect(
        ProductInterface $product,
        ProductRenderInterface $productRender
    ) {
        $labels = $this->helper->getLabels($product, 'category');
        if (!$labels || !$labels->getLabelsData()) {
            return;
        }

        $labelsJson = $this->helper->jsonEncode(
            $this->helper->getJsWidgetOptions($labels)
        );
        $extensionAttributes = $productRender->getExtensionAttributes();
        if (!$extensionAttributes) {
            $extensionAttributes = $this->productRenderExtensionFactory->create();
        }

        $extensionAttributes->setSwissupProlabel($labelsJson);
        $productRender->setExtensionAttributes($extensionAttributes);
    }
}
