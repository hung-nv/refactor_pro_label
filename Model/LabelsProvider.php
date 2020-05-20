<?php

namespace Swissup\ProLabels\Model;

use Magento\Framework\Api\SimpleDataObjectConverter as Converter;
use Magento\Catalog\Model\Product;

class LabelsProvider
{
    /**
     * @var \Swissup\ProLabels\Helper\ProductLabelsHelper
     */
    protected $system_labels;

    /**
     * @var \Swissup\ProLabels\Model\Label
     */
    protected $label_model;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $store_manager;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customer_session;

    /**
     * @var \Magento\Framework\DataObject
     */
    protected $collection;

    /**
     * @var \Magento\Framework\DataObject\Factory
     */
    protected $data_object_factory;

    /**
     * @param \Swissup\ProLabels\Helper\ProductLabelsHelper           $systemLabels
     * @param \Swissup\ProLabels\Model\Label                    $labelModel
     * @param \Magento\Store\Model\StoreManagerInterface        $storeManager
     * @param \Magento\Customer\Model\Session                   $customerSession
     * @param \Magento\CatalogInventory\Api\StockStateInterface $stockState
     * @param \Magento\Framework\DataObject\Factory             $dataObjectFactory
     * @param LabelsModifier                                    $modifier
     */
    public function __construct(
        \Swissup\ProLabels\Helper\ProductLabelsHelper $systemLabels,
        \Swissup\ProLabels\Model\Label $labelModel,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\DataObject\Factory $dataObjectFactory
    ) {
        $this->system_labels = $systemLabels;
        $this->label_model = $labelModel;
        $this->store_manager = $storeManager;
        $this->customer_session = $customerSession;
        $this->data_object_factory = $dataObjectFactory;
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
            $labels = $this->data_object_factory->create(
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
        if ($onSale = $this->system_labels->getOnSaleLabel($product, $mode)) {
            $labels[$onSale->getPosition()][] = $onSale;
        }

        if ($isNew = $this->system_labels->getIsNewLabel($product, $mode)) {
            $labels[$isNew->getPosition()][] = $isNew;
        }

        if ($inStock = $this->system_labels->getStockLabel($product, $mode)) {
            $labels[$inStock->getPosition()][] = $inStock;
        }

        if ($outOfStock = $this->system_labels->getOutOfStockLabel($product, $mode)) {
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
        $labelIds = $this->label_model->getProductLabels($product->getId());
        if (count($labelIds) == 0) {
            return $this;
        }

        $collection = $this->label_model
            ->getCollection()
            ->addFieldToFilter('label_id', $labelIds)
            ->addFieldToFilter('status', 1);
        $customerGroupId = $this->customer_session->getCustomerGroupId();
        $storeId = $this->store_manager->getStore()->getId();
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
            $labelData = $this->system_labels->getLabelOutputObject($labelConfig, $product, $mode);

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
        return $this->system_labels->get_upload_image_label($image, $mode);
    }

    /**
     * Get Grouped Product Discount Value
     *
     * @param  Product $product
     * @return float
     */
    protected function getGroupedProductDiscountPersent(Product $product)
    {
        return $this->get_max_result_discount_persent($product);
    }

    protected function getGroupedProductDiscountAmount($product)
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $simpleProducts */
        $max_result_discount_amount = $this->get_max_result_discount_amount($product);

        return $max_result_discount_amount;
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
        return $this->get_discount_percent_value($product);
    }

    /**
     * #discount_amount# placeholder
     *
     * @param  Product $product
     * @return float
     */
    public function getDiscountAmountValue(Product $product)
    {
        return $this->get_discount_value($product);
    }

    /**
     * #special_price# placeholder
     *
     * @param  Product $product
     * @return float
     */
    public function getSpecialPriceValue(Product $product)
    {
        return $this->system_labels->getSpecialPrice($product);;
    }

    /**
     * #price# placeholder
     *
     * @param  Product $product
     * @return float
     */
    public function getPriceValue(Product $product)
    {
        return $this->system_labels->getRegularPrice($product);
    }

    /**
     * #final_price# placeholder
     *
     * @param  Product $product
     * @return float
     */
    public function getFinalPriceValue(Product $product)
    {
        return $this->system_labels->getFinalPrice($product);
    }

    /**
     * #stock_item# placeholder
     *
     * @param  Product $product
     * @return float|string
     */
    public function getStockItemValue(Product $product)
    {
        return $this->system_labels->get_stock_qty($product);
    }

    private function get_discount_value($product) {
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

    private function get_discount_percent_value($product) {
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

    private function get_max_result_discount_amount($product) {
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

    private function get_max_result_discount_persent($product) {
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
}
