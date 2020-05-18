<?php

namespace Swissup\ProLabels\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Swissup\ProLabels\interfaces\AbstractLabelInterface;

/**
 * ProLabels Abstract Label Helper
 *
 * @author     Swissup Team <core@magentocommerce.com>
 */
class AbstractLabelHelper extends AbstractHelper implements AbstractLabelInterface
{
    /**
     * @var \Magento\CatalogInventory\Api\StockStateInterface
     */
    protected $stockState;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $localeDate;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param \Magento\Framework\App\Helper\Context                $context
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface    $priceCurrency
     * @param \Magento\Store\Model\StoreManagerInterface           $storeManager
     * @param \Magento\CatalogInventory\Api\StockStateInterface    $_stockState
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\CatalogInventory\Api\StockStateInterface $_stockState
    ) {
        $this->localeDate    = $localeDate;
        $this->_priceCurrency = $priceCurrency;
        $this->storeManager  = $storeManager;
        $this->stockState    = $_stockState;
        parent::__construct($context);
    }

    /**
     * @param array $config
     * @return \Magento\Framework\DataObject
     */
    public function getLabelOutputObject($config)
    {
        $configObject = new \Magento\Framework\DataObject($config);
        $labelData = new \Magento\Framework\DataObject(
            [
                'position'   => $configObject->getPosition(),
                'text'       => $configObject->getText(),
                'image'      => $configObject->getImage(),
                'custom'     => $configObject->getCustom(),
                'custom_url' => $configObject->getCustomUrl(),
                'round_method' => $configObject->getRoundMethod(),
                'round_value' => $configObject->getRoundValue()
            ]
        );
        return $labelData;
    }

    /**
     * @param  \Magento\Catalog\Model\Product $product
     * @return float
     */
    public function get_stock_qty(\Magento\Catalog\Model\Product $product)
    {
        $simple_qty = [];
        if ('grouped' === $product->getTypeId()) {
            /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $simple_products */
            $child_ids = $product->getTypeInstance()->getAssociatedProducts($product);
            foreach ($child_ids as $simple_product) {
                $simple_qty[] = $this->stockState->getStockQty($simple_product->getId());
            }

            $quantity = min($simple_qty);
        } elseif ('bundle' === $product->getTypeId()) {
            $option_ids = $product->getTypeInstance()->getOptionsIds($product);
            /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $simpleProducts */
            $simple_products = $product->getTypeInstance()->getSelectionsCollection($option_ids, $product);
            foreach ($simple_products as $simple_product) {
                $simple_qty[] = $this->stockState->getStockQty($simple_product->getId());
            }

            $quantity = min($simple_qty);
        } elseif ('configurable' === $product->getTypeId()) {
            $simple_products = $product->getTypeInstance()->getUsedProducts($product);
            foreach ($simple_products as $simple_product) {
                $simple_qty[] = $this->stockState->getStockQty($simple_product->getId());
            }

            $quantity = min($simple_qty);
        } else {
            $quantity = $this->stockState->getStockQty($product->getId());
        }

        return $quantity;
    }

    /**
     * Check If Product Is New
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     */
    public function is_new_product(\Magento\Catalog\Model\Product $product)
    {
        $store           = $this->storeManager->getStore();
        $id_store = $store->getId();
        $special_news_from = $product->getNewsFromDate();
        $special_news_to   = $product->getNewsToDate();
        if ($special_news_from ||  $special_news_to) {
            return $this->localeDate->isScopeDateInInterval($id_store, $special_news_from, $special_news_to);
        }

        return false;
    }

    /**
     * {inherit}
     */
    public function get_upload_image_label($image_path, $instance)
    {
        $store = $this->storeManager->getStore();
        $base_url = $store->get∫BaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        return $base_url . "prolabels/{$instance}/" . $image_path;
    }
}
