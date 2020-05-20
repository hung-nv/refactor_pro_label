<?php
namespace Swissup\ProLabels\Block\Product;

use Magento\Framework\View\Element\Template;
use Magento\Customer\Model\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Catalog\Api\Data\ProductInterface;

class Labels extends Template
{
    /**
     * @var \Swissup\ProLabels\Model\Label
     */
    protected $label_model;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $http_context;

    /**
     * @var \Swissup\ProLabels\Helper\ProductLabelsHelper
     */
    protected $system_labels;

    /**
     * @param Template\Context                        $context
     * @param \Magento\Framework\Registry             $registry
     * @param \Swissup\ProLabels\Model\Label          $labelModel
     * @param \Magento\Framework\App\Http\Context     $httpContext
     * @param \Swissup\ProLabels\Helper\ProductLabelsHelper $systemLabels
     * @param array                                   $data
     */
    public function __construct(
        Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Swissup\ProLabels\Model\Label $labelModel,
        \Magento\Framework\App\Http\Context $httpContext,
        \Swissup\ProLabels\Helper\ProductLabelsHelper $systemLabels,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->label_model = $labelModel;
        $this->http_context = $httpContext;
        $this->system_labels = $systemLabels;
        parent::__construct($context, $data);
    }

    /**
     * Initialize block's cache
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->addData(
            [
                'cache_lifetime' => 86400,
                'cache_tags' => [\Magento\Catalog\Model\Product::CACHE_TAG]
            ]
        );
    }
    /**
     * Get Key pieces for caching block content
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        $product = $this->getCurrentProduct();
        $label_ids = $this->label_model->getProductLabels($product->getId());

        $can_show_stock_label = (bool)$this->system_labels->getStockLabel($product, 'product');

        return [
            'PROLABELS_LABELS',
            $this->_storeManager->getStore()->getId(),
            $this->http_context->getValue(Context::CONTEXT_GROUP),
            'template' => $this->getTemplate(),
            'name' => $this->getNameInLayout(),
            implode(',', $label_ids),
            'product' => $product->getId(),
            'show_stock_label' => $can_show_stock_label
                ? $this->system_labels->get_stock_qty($product) // left in stock value
                : 0                                          // stock label disabled
        ];
    }

    public function getBaseImageWrapConfig()
    {
        return $this->_scopeConfig->getValue(
            'prolabels/general/base',
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getContentWrapConfig()
    {
        return $this->_scopeConfig->getValue(
            'prolabels/general/content',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return ProductInterface
     */
    public function getCurrentProduct()
    {
        return $this->registry->registry('product');
    }
}
