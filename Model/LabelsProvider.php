<?php

namespace Swissup\ProLabels\Model;

use Magento\Framework\Api\SimpleDataObjectConverter as Converter;
use Magento\Catalog\Model\Product;

class LabelsProvider
{
    /**
     * @var \Swissup\ProLabels\Helper\ProductLabels
     */
    protected $systemLabels;

    /**
     * @var \Swissup\ProLabels\Model\Label
     */
    protected $labelModel;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\DataObject
     */
    protected $collection;

    /**
     * @var \Magento\Framework\DataObject\Factory
     */
    protected $dataObjectFactory;

    /**
     * @param \Swissup\ProLabels\Helper\ProductLabels           $systemLabels
     * @param \Swissup\ProLabels\Model\Label                    $labelModel
     * @param \Magento\Store\Model\StoreManagerInterface        $storeManager
     * @param \Magento\Customer\Model\Session                   $customerSession
     * @param \Magento\CatalogInventory\Api\StockStateInterface $stockState
     * @param \Magento\Framework\DataObject\Factory             $dataObjectFactory
     * @param LabelsModifier                                    $modifier
     */
    public function __construct(
        \Swissup\ProLabels\Helper\ProductLabels $systemLabels,
        \Swissup\ProLabels\Model\Label $labelModel,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\DataObject\Factory $dataObjectFactory
    ) {
        $this->systemLabels = $systemLabels;
        $this->labelModel = $labelModel;
        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->collection = $dataObjectFactory->create();
    }

    /**
     * Get initilized labels for product and mode
     *
     * @param  int    $productId
     * @param  string $mode
     * @return \Magento\Framework\DataObject
     */
    public function getLabels($productId, $mode)
    {
        return $this->collection->getData("{$productId}::{$mode}");
    }

    /**
     * Initilize labels for product in $mode
     * @param  Product                        $product
     * @param  string                         $mode
     * @return \Magento\Framework\DataObject
     */
    public function initialize(Product $product, $mode)
    {
        $labels = $this->getLabels($product->getId(), $mode);
        if (!$labels) {
            $labels = [];
            $this->initSystemLabels($labels, $product, $mode);
            $this->initManualLabels($labels, $product, $mode);
            $labels = $this->dataObjectFactory->create(
                [
                    'labels_data' => $this->prepareLabelsData($labels, $mode),
                    'predefined_variables' => $this->preparePredefinedVariables($labels, $product)
                ]
            );
            $this->collection->setData("{$product->getId()}::{$mode}", $labels);
        }

        return $labels;
    }

    /**
     * @param  array  $labels
     * @param  string $mode
     * @return array
     */
    protected function prepareLabelsData($labels, $mode)
    {
        $labelsData = [];

        foreach ($labels as $position => $labels) {
            $items = [];
            foreach ($labels as $label) {
                $data = $label->getData();
                unset($data['position']); // no need in this value
                // get label image URL
                $data['image'] = $label->getImage()
                    ? $this->getLabelImage($label->getImage(), $mode)
                    : null;
                $data['custom'] = $label->getCustom()
                    ? preg_replace("/\s+/", " ", $label->getCustom())
                    : null;

                $items[] = array_filter($data); // remove empty values
            }

            if (!empty($items)) {
                $labelsData[] = ['position' => $position, 'items' => $items];
            }
        }

        return $labelsData;
    }

    /**
     * @param  array   $labels
     * @param  Product $product
     * @return array
     */
    protected function preparePredefinedVariables($labels, Product $product)
    {
        $text = '';
        $predefinedVars = [];
        foreach ($labels as $labels) {
            foreach ($labels as $label) {
                $text .= $label->getText();
            }
        }

        // Remove all hex colors from label text.
        // Because text `<b style="color: #bbb">label #attr:sku#</b>` is too
        // triky to find predefined variables there.
        $text = preg_replace('/#([a-f0-9]{3}){1,2}\b/i', '', $text);
        // find all predefined variables
        preg_match_all('/#.+?#/', $text, $placeholders);
        foreach (array_unique($placeholders[0]) as $placeholder) {
            if (strpos($placeholder, '#attr:') !== false) {
                $attributeCode = str_replace(['#attr:', '#'], '', $placeholder);
                $attribute = $product
                    ->getResource()
                    ->getAttribute($attributeCode);
                if ($attribute) {
                    $predefinedVars[$placeholder] = $attribute
                        ->getFrontend()
                        ->getValue($product);
                }
            } else {
                $methodName = Converter::snakeCaseToUpperCamelCase(
                    str_replace('#', '', 'get' . $placeholder . 'Value')
                );
                $callback = [$this, $methodName];
                if (is_callable($callback)) {
                    $predefinedVars[$placeholder] = call_user_func(
                        $callback,
                        $product
                    );
                }
            }
        }

        return $predefinedVars;
    }

    /**
     * @param  array   &$labels
     * @param  Product $product
     * @param  string  $mode
     * @return $this
     */
    protected function initSystemLabels(&$labels, Product $product, $mode)
    {
        if ($onSale = $this->systemLabels->getOnSaleLabel($product, $mode)) {
            $labels[$onSale->getPosition()][] = $onSale;
        }

        if ($isNew = $this->systemLabels->getIsNewLabel($product, $mode)) {
            $labels[$isNew->getPosition()][] = $isNew;
        }

        if ($inStock = $this->systemLabels->getStockLabel($product, $mode)) {
            $labels[$inStock->getPosition()][] = $inStock;
        }

        if ($outOfStock = $this->systemLabels->getOutOfStockLabel($product, $mode)) {
            $labels[$outOfStock->getPosition()][] = $outOfStock;
        }

        return $this;
    }

    /**
     * @param  array   &$labels
     * @param  Product $product
     * @param  string  $mode
     * @return $this
     */
    protected function initManualLabels(&$labels, Product $product, $mode)
    {
        $labelIds = $this->labelModel->getProductLabels($product->getId());
        if (count($labelIds) == 0) {
            return $this;
        }

        $collection = $this->labelModel
            ->getCollection()
            ->addFieldToFilter('label_id', $labelIds)
            ->addFieldToFilter('status', 1);
        $customerGroupId = $this->customerSession->getCustomerGroupId();
        $storeId = $this->storeManager->getStore()->getId();
        foreach ($collection as $label) {
            $labelStores = $label->getStoreId();
            if (!in_array('0', $labelStores)) {
                if (!in_array($storeId, $labelStores)) { continue; }
            }
            if (!in_array($customerGroupId, $label->getCustomerGroups())) {
                continue;
            }
            $labelConfig = [
                'position' => $label->getData($mode . '_position'),
                'text' => $label->getData($mode . '_text'),
                'custom' => $label->getData($mode . '_custom_style'),
                'custom_url' => $label->getData($mode . '_custom_url'),
                'round_method' => $label->getData($mode . '_round_method'),
                'round_value' => $label->getData($mode . '_round_value'),
                'image' => $label->getData($mode . '_image')
            ];
            $labelData = $this->systemLabels->getLabelOutputObject($labelConfig, $product, $mode);

            if (!$labelData->getText()
                && !$labelData->getCustom()
                && !$labelData->getImage()
            ) {
                continue;
            }

            $labels[$labelData->getPosition()][] = $labelData;
        }

        return $this;
    }

    /**
     * Get prolabels image URL
     *
     * @param  string $image
     * @param  string $mode
     * @return string
     */
    public function getLabelImage($image, $mode = 'product')
    {
        return $this->systemLabels->get_upload_image_label($image, $mode);
    }

    /**
     * Get Grouped Product Discount Value
     *
     * @param  Product $product
     * @return float
     */
    protected function getGroupedProductDiscountPersent(Product $product)
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $simpleProducts */
        $simpleProducts = $product
            ->getTypeInstance()
            ->getAssociatedProducts($product);
        $maxResult = 0;
        foreach ($simpleProducts as $simpleProduct) {
            $price = $this->getPriceValue($simpleProduct);
            $calculatedPrice = $this->getFinalPriceValue($simpleProduct);
            $result = 100 - ($calculatedPrice * 100 / $price);
            if ($price > $calculatedPrice) {
                if ($result > $maxResult) {
                    $maxResult = $result;
                }
            }
        }

        return $maxResult;
    }

    protected function getGroupedProductDiscountAmount($product)
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $simpleProducts */
        $simpleProducts = $product
            ->getTypeInstance()
            ->getAssociatedProducts($product);
        $maxResult = 0;
        foreach ($simpleProducts as $simpleProduct) {
            $price = $this->getPriceValue($simpleProduct);
            $calculatedPrice = $this->getFinalPriceValue($simpleProduct);
            $result = $price - $calculatedPrice;
            if ($price > $calculatedPrice) {
                if ($result > $maxResult) {
                    $maxResult = $result;
                }
            }
        }

        return $maxResult;
    }

    /**
     * PLACEHOLDERS METHODS
     */

    /**
     * #discount_percent# placeholder
     *
     * @param  Product $product
     * @return float
     */
    public function getDiscountPercentValue(Product $product)
    {
        if ('grouped' === $product->getTypeId()) {
            $discountValue = $this->getGroupedProductDiscountPersent($product);
        } elseif ('bundle' === $product->getTypeId()) {
            $discountValue = $product->getSpecialPrice();
        } else {
            $finalPrice = $this->getFinalPriceValue($product);
            $regularPrice = $this->getPriceValue($product);
            $discountValue = (1 - $finalPrice / $regularPrice) * 100;
        }

        return $discountValue;
    }

    /**
     * #discount_amount# placeholder
     *
     * @param  Product $product
     * @return float
     */
    public function getDiscountAmountValue(Product $product)
    {
        if ('grouped' === $product->getTypeId()) {
            $discountValue = $this->getGroupedProductDiscountAmount($product);
        } elseif ('bundle' === $product->getTypeId()) {
            $price = $product->getPriceModel()->getTotalPrices($product);
            $discountValue = (int)$product->getSpecialPrice();
            $fullPrice = $discountValue
                ? ($price[1] * 100) / $discountValue
                : $price[1];
            $discountValue = $fullPrice - $price[1];
        } else {
            $finalPrice = $this->getFinalPriceValue($product);
            $regularPrice = $this->getPriceValue($product);
            $discountValue = $regularPrice - $finalPrice;
        }

        return $discountValue;
    }

    /**
     * #special_price# placeholder
     *
     * @param  Product $product
     * @return float
     */
    public function getSpecialPriceValue(Product $product)
    {
        return $this->systemLabels->getSpecialPrice($product);;
    }

    /**
     * #price# placeholder
     *
     * @param  Product $product
     * @return float
     */
    public function getPriceValue(Product $product)
    {
        return $this->systemLabels->getRegularPrice($product);
    }

    /**
     * #final_price# placeholder
     *
     * @param  Product $product
     * @return float
     */
    public function getFinalPriceValue(Product $product)
    {
        return $this->systemLabels->getFinalPrice($product);
    }

    /**
     * #stock_item# placeholder
     *
     * @param  Product $product
     * @return float|string
     */
    public function getStockItemValue(Product $product)
    {
        return $this->systemLabels->getStockQty($product);
    }
}
