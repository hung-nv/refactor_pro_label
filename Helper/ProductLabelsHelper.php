<?php

namespace Swissup\ProLabels\Helper;

use Swissup\ProLabels\Helper\AbstractLabelHelper;
use Magento\Store\Model\ScopeInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Pricing\Price;

/**
 * System Labels - on sale, new, in stock, out stock
 */
class ProductLabelsHelper extends AbstractLabelHelper
{
    /**
     * @return Get On Sale Label Data
     */
    public function getOnSaleLabel($product, $mode)
    {
        $isOnSaleConfig = $this->scopeConfig->getValue("prolabels/on_sale/{$mode}", ScopeInterface::SCOPE_STORE);
        if (!$isOnSaleConfig["active"]
            || !$this->isOnSale($product)
        ) {
            return false;
        }

        return $this->getLabelOutputObject($isOnSaleConfig);
    }

    /**
     * @return Get Is New Label Data
     */
    public function getIsNewLabel($product, $mode)
    {
        $isInSaleConfig = $this->scopeConfig->getValue("prolabels/is_new/{$mode}", ScopeInterface::SCOPE_STORE);
        if (!$isInSaleConfig["active"]
            || !$this->is_new_product($product)
        ) {
            return false;
        }

        return $this->getLabelOutputObject($isInSaleConfig);
    }

    /**
     * @return Get Stock Label Data
     */
    public function getStockLabel($product, $mode)
    {
        $stockConfig = $this->scopeConfig->getValue("prolabels/in_stock/{$mode}", ScopeInterface::SCOPE_STORE);
        $isAvailable = $product->isAvailable();
        $qty = $this->get_stock_qty($product);

        if (!$stockConfig["active"]
            || !$isAvailable
            || $qty <= 0
            || $qty >= $stockConfig['stock_lower']
        ) {
            return false;
        }

        return $this->getLabelOutputObject($stockConfig);
    }

    /**
     * @return Get Out Of Stock Label Data
     */
    public function getOutOfStockLabel($product, $mode)
    {
        $isAvailable = $product->isAvailable();
        $stockConfig = $this->scopeConfig->getValue("prolabels/out_stock/{$mode}", ScopeInterface::SCOPE_STORE);
        if (!$stockConfig["active"]
            || $isAvailable
        ) {
            return false;
        }

        return $this->getLabelOutputObject($stockConfig);
    }

    /**
     * Check If Product Has Discount
     *
     * @param $product \Magento\Catalog\Model\Product
     * @return
     */
    public function isOnSale($product)
    {
        if ('bundle' === $product->getTypeId()) {
            if ((int)$product->getSpecialPrice()) {
                $specialPriceFrom = $product->getSpecialFromDate();
                $specialPriceTo = $product->getSpecialToDate();
                $store = $this->storeManager->getStore()->getId();
                return $this->localeDate->isScopeDateInInterval(
                    $store,
                    $specialPriceFrom,
                    $specialPriceTo
                );
            }
        } elseif ('grouped' === $product->getTypeId()) {
            /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $simpleProductIds */
            $simpleProductIds = $product->getTypeInstance()->getAssociatedProducts($product);
            foreach($simpleProductIds as $simpleProduct) {
                if ($this->getFinalPrice($simpleProduct) < $this->getRegularPrice($simpleProduct)) {
                    return true;
                }
            }
        } else {
            $finalPrice = $this->getFinalPrice($product);
            $regularPrice = $this->getRegularPrice($product);
            return $finalPrice < $regularPrice;
        }

        return false;
    }

    /**
     * Improved method to get regular price of product
     *
     * @param  ProductInterface $product
     * @return float
     */
    public function getRegularPrice(ProductInterface $product)
    {
        $priceInfo = $product->getPriceInfo();
        $price = $priceInfo->getPrice(Price\RegularPrice::PRICE_CODE)
            ->getAmount()
            ->getValue();
        if ('configurable' === $product->getTypeId()) {
            // Inspired by \Magento\Catalog\Pricing\Price\RegularPrice
            // FIX. Configurable product dosn't apply convert and round.
            $priceInCurrentCurrency = $this->_priceCurrency->convertAndRound($price);
            $price = $priceInCurrentCurrency ? (float)$priceInCurrentCurrency : 0;
        }

        return $price;
    }

    /**
     * Get final price of product
     *
     * @param  ProductInterface $product
     * @return float
     */
    public function getFinalPrice(ProductInterface $product)
    {
        $finalPrice = $product->getPriceInfo()->getPrice(Price\FinalPrice::PRICE_CODE);
        if ('bundle' === $product->getTypeId()) {
            return $finalPrice->getMinimalPrice()->getValue();
        }

        if ('configurable' === $product->getTypeId()) {
            // Inspired by \Magento\Catalog\Pricing\Price\RegularPrice
            // FIX. Configurable product dosn't apply convert and round.
            $priceInCurrentCurrency = $this->_priceCurrency->convertAndRound(
                $finalPrice->getAmount()->getValue()
            );

            return $priceInCurrentCurrency ? (float)$priceInCurrentCurrency : 0;
        }

        return $finalPrice->getAmount()->getValue();
    }

    /**
     * Get special price of product
     *
     * @param  ProductInterface $product
     * @return float
     */
    public function getSpecialPrice(ProductInterface $product)
    {
        $specialPrice = $product->getPriceInfo()->getPrice(Price\SpecialPrice::PRICE_CODE);
        return $specialPrice->getAmount()->getValue();
    }
}
