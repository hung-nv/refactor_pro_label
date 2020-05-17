<?php

namespace Swissup\ProLabels\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

/**
 * ProLabels Abstract Label Helper
 *
 * @author     Swissup Team <core@magentocommerce.com>
 */
class AbstractLabel extends AbstractHelper
{
    /**
     * @var \Magento\CatalogInventory\Api\StockStateInterface
     */
    protected $_stockState;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

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
        $this->_localeDate    = $localeDate;
        $this->_priceCurrency = $priceCurrency;
        $this->_storeManager  = $storeManager;
        $this->_stockState    = $_stockState;
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
    public function getStockQty(\Magento\Catalog\Model\Product $product)
    {
        $simpleQty = [];
        if ('grouped' === $product->getTypeId()) {
            /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $simpleProducts */
            $childIds = $product->getTypeInstance()->getAssociatedProducts($product);
            foreach ($childIds as $simpleProduct) {
                $simpleQty[] = $this->_stockState->getStockQty($simpleProduct->getId());
            }

            $quantity = min($simpleQty);
        } elseif ('bundle' === $product->getTypeId()) {
            $optionIds = $product->getTypeInstance()->getOptionsIds($product);
            /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $simpleProducts */
            $simpleProducts = $product->getTypeInstance()->getSelectionsCollection($optionIds, $product);
            foreach ($simpleProducts as $simpleProduct) {
                $simpleQty[] = $this->_stockState->getStockQty($simpleProduct->getId());
            }

            $quantity = min($simpleQty);
        } elseif ('configurable' === $product->getTypeId()) {
            $simpleProducts = $product->getTypeInstance()->getUsedProducts($product);
            foreach ($simpleProducts as $simpleProduct) {
                $simpleQty[] = $this->_stockState->getStockQty($simpleProduct->getId());
            }

            $quantity = min($simpleQty);
        } else {
            $quantity = $this->_stockState->getStockQty($product->getId());
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
        $store           = $this->_storeManager->getStore()->getId();
        $specialNewsFrom = $product->getNewsFromDate();
        $specialNewsTo   = $product->getNewsToDate();
        if ($specialNewsFrom ||  $specialNewsTo) {
            return $this->_localeDate->isScopeDateInInterval($store, $specialNewsFrom, $specialNewsTo);
        }

        return false;
    }

    public function get_upload_image_label($imagePath, $mode)
    {
        $baseMediaUrl = $this->_storeManager
            ->getStore()
            ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        return $baseMediaUrl . "prolabels/{$mode}/" . $imagePath;
    }
}
