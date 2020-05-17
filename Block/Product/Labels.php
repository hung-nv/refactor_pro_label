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
    protected $labelModel;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @var \Swissup\ProLabels\Helper\ProductLabels
     */
    protected $systemLabels;

    /**
     * @param Template\Context                        $context
     * @param \Magento\Framework\Registry             $registry
     * @param \Swissup\ProLabels\Model\Label          $labelModel
     * @param \Magento\Framework\App\Http\Context     $httpContext
     * @param \Swissup\ProLabels\Helper\ProductLabels $systemLabels
     * @param array                                   $data
     */
    public function __construct(
        Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Swissup\ProLabels\Model\Label $labelModel,
        \Magento\Framework\App\Http\Context $httpContext,
        \Swissup\ProLabels\Helper\ProductLabels $systemLabels,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->labelModel = $labelModel;
        $this->httpContext = $httpContext;
        $this->systemLabels = $systemLabels;
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
        $labelIds = $this->labelModel->getProductLabels($product->getId());
        $canShowStockLabel = (bool)$this->systemLabels->getStockLabel($product, 'product');
        return [
            'PROLABELS_LABELS',
            $this->_storeManager->getStore()->getId(),
            $this->httpContext->getValue(Context::CONTEXT_GROUP),
            'template' => $this->getTemplate(),
            'name' => $this->getNameInLayout(),
            implode(',', $labelIds),
            'product' => $product->getId(),
            'show_stock_label' => $canShowStockLabel
                ? $this->systemLabels->getStockQty($product) // left in stock value
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
