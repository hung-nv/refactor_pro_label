<?php

namespace Swissup\ProLabels\Api\Data;

interface LabelInterface
{
    CONST LABEL_ID = 'label_id';
    CONST TITLE = 'title';
    CONST STORE_ID = 'store_id';
    CONST STATUS = 'status';
    CONST CUSTOMER_GROUPS = 'customer_groups';
    CONST CONDITIONS_SERIALIZED = 'conditions_serialized';
    CONST PRODUCT_POSITION = 'product_position';
    CONST PRODUCT_IMAGE = 'product_image';
    CONST PRODUCT_CUSTOM_STYLE = 'product_custom_style';
    CONST PRODUCT_TEXT = 'product_text';
    CONST PRODUCT_CUSTOM_URL = 'product_custom_url';
    CONST PRODUCT_ROUND_METHOD = 'product_round_method';
    CONST PRODUCT_ROUND_VALUE = 'product_round_value';
    CONST CATEGORY_POSITION = 'category_position';
    CONST CATEGORY_IMAGE = 'category_image';
    CONST CATEGORY_CUSTOM_STYLE = 'category_custom_style';
    CONST CATEGORY_TEXT = 'category_text';
    CONST CATEGORY_CUSTOM_URL = 'category_custom_url';
    CONST CATEGORY_ROUND_METHOD = 'category_round_method';
    CONST CATEGORY_ROUND_VALUE = 'category_round_value';

    /**
     * Get label_id
     *
     * return int
     */
    public function getLabelId();

    /**
     * Get title
     *
     * return string
     */
    public function getTitle();

    /**
     * Get stores
     *
     * return string
     */
    public function getStoreId();

    /**
     * Get status
     *
     * return int
     */
    public function getStatus();

    /**
     * Get customer_groups
     *
     * return int
     */
    public function getCustomerGroups();

    /**
     * Get condition_data
     *
     * return string
     */
    public function getConditionsSerialized();

    /**
     * Get product_position
     *
     * return string
     */
    public function getProductPosition();

    /**
     * Get product_image
     *
     * return string
     */
    public function getProductImage();

    /**
     * Get product_custom_style
     *
     * return string
     */
    public function getProductCustomStyle();

    /**
     * Get product_text
     *
     * return string
     */
    public function getProductText();

    /**
     * Get product_custom_url
     *
     * return string
     */
    public function getProductCustomUrl();

    /**
     * Get product_round_method
     *
     * return string
     */
    public function getProductRoundMethod();

    /**
     * Get product_round_value
     *
     * return string
     */
    public function getProductRoundValue();

    /**
     * Get category_position
     *
     * return string
     */
    public function getCategoryPosition();

    /**
     * Get category_image
     *
     * return string
     */
    public function getCategoryImage();

    /**
     * Get category_custom_style
     *
     * return string
     */
    public function getCategoryCustomStyle();

    /**
     * Get category_text
     *
     * return string
     */
    public function getCategoryText();

    /**
     * Get category_custom_url
     *
     * return string
     */
    public function getCategoryCustomUrl();

    /**
     * Get category_round_method
     *
     * return string
     */
    public function getCategoryRoundMethod();

    /**
     * Get category_round_value
     *
     * return string
     */
    public function getCategoryRoundValue();

    /**
     * Set label_id
     *
     * @param int $labelId
     * return \SWISSUP\Prolabels\Api\Data\LabelInterface
     */
    public function setLabelId($labelId);

    /**
     * Set title
     *
     * @param string $title
     * return \SWISSUP\Prolabels\Api\Data\LabelInterface
     */
    public function setTitle($title);

    /**
     * Set store id
     *
     * @param string $storeId
     * return \SWISSUP\Prolabels\Api\Data\LabelInterface
     */
    public function setStoreId($storeId);

    /**
     * Set status
     *
     * @param int $status
     * return \SWISSUP\Prolabels\Api\Data\LabelInterface
     */
    public function setStatus($status);


    /**
     * Set Customer Groups
     *
     * @param int $customerGroups
     * return \SWISSUP\Prolabels\Api\Data\LabelInterface
     */
    public function setCustomerGroups($customerGroups);

    /**
     * Set conditions_serialized
     *
     * @param string $conditionsSerialized
     * return \SWISSUP\Prolabels\Api\Data\LabelInterface
     */
    public function setConditionsSerialized($conditionsSerialized);

    /**
     * Set product_position
     *
     * @param string $productPosition
     * return \SWISSUP\Prolabels\Api\Data\LabelInterface
     */
    public function setProductPosition($productPosition);

    /**
     * Set product_image
     *
     * @param string $productImage
     * return \SWISSUP\Prolabels\Api\Data\LabelInterface
     */
    public function setProductImage($productImage);

    /**
     * Set product_custom_style
     *
     * @param string $productCustomStyle
     * return \SWISSUP\Prolabels\Api\Data\LabelInterface
     */
    public function setProductCustomStyle($productCustomStyle);

    /**
     * Set product_text
     *
     * @param string $productText
     * return \SWISSUP\Prolabels\Api\Data\LabelInterface
     */
    public function setProductText($productText);

    /**
     * Set product_custom_url
     *
     * @param string $productCustomUrl
     * return \SWISSUP\Prolabels\Api\Data\LabelInterface
     */
    public function setProductCustomUrl($productCustomUrl);

    /**
     * Set product_round_method
     *
     * @param string $productRoundMethod
     * return \SWISSUP\Prolabels\Api\Data\LabelInterface
     */
    public function setProductRoundMethod($productRoundMethod);

    /**
     * Set product_round_value
     *
     * @param string $productRoundValue
     * return \SWISSUP\Prolabels\Api\Data\LabelInterface
     */
    public function setProductRoundValue($productRoundValue);

    /**
     * Set category_position
     *
     * @param string $categoryPosition
     * return \SWISSUP\Prolabels\Api\Data\LabelInterface
     */
    public function setCategoryPosition($categoryPosition);

    /**
     * Set category_image
     *
     * @param string $categoryImage
     * return \SWISSUP\Prolabels\Api\Data\LabelInterface
     */
    public function setCategoryImage($categoryImage);

    /**
     * Set category_custom_style
     *
     * @param string $categoryCustomStyle
     * return \SWISSUP\Prolabels\Api\Data\LabelInterface
     */
    public function setCategoryCustomStyle($categoryCustomStyle);

    /**
     * Set category_text
     *
     * @param string $categoryText
     * return \SWISSUP\Prolabels\Api\Data\LabelInterface
     */
    public function setCategoryText($categoryText);

    /**
     * Set category_custom_url
     *
     * @param string $categoryCustomUrl
     * return \SWISSUP\Prolabels\Api\Data\LabelInterface
     */
    public function setCategoryCustomUrl($categoryCustomUrl);

    /**
     * Set category_round_method
     *
     * @param string $categoryRoundMethod
     * return \SWISSUP\Prolabels\Api\Data\LabelInterface
     */
    public function setCategoryRoundMethod($categoryRoundMethod);

    /**
     * Set category_round_value
     *
     * @param string $categoryRoundValue
     * return \SWISSUP\Prolabels\Api\Data\LabelInterface
     */
    public function setCategoryRoundValue($categoryRoundValue);

}
