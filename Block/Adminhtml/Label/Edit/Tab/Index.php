<?php
namespace Swissup\ProLabels\Block\Adminhtml\Label\Edit\Tab;

use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory as SetCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;

class Index extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @param ProductCollectionFactory                               $productCollectionFactory
     * @param SetCollectionFactory                                   $setsFactory
     * @param \Magento\Catalog\Model\ProductFactory                  $productFactory
     * @param \Magento\Catalog\Model\Product\Type                    $productType
     * @param \Magento\Catalog\Model\Product\Attribute\Source\Status $productStatus
     * @param \Magento\Catalog\Model\Product\Visibility              $productVisibility
     * @param \Swissup\ProLabels\Model\LabelFactory                  $labelFactory
     * @param \Magento\Backend\Block\Template\Context                $context
     * @param \Magento\Backend\Helper\Data                           $backendHelper
     * @param array                                                  $data
     */
    public function __construct(
        ProductCollectionFactory $productCollectionFactory,
        SetCollectionFactory $setsFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\Product\Type $productType,
        \Magento\Catalog\Model\Product\Attribute\Source\Status $productStatus,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        \Swissup\ProLabels\Model\LabelFactory $labelFactory,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = []
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->setsFactory = $setsFactory;
        $this->productFactory = $productFactory;
        $this->productType = $productType;
        $this->productStatus = $productStatus;
        $this->productVisibility = $productVisibility;
        $this->labelFactory = $labelFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('label_indexed_grid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('asc');
        $this->setUseAjax(true);
    }

    /**
     * Apply various selection filters to prepare products grid collection.
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        $labelModel = $this->labelFactory->create();
        $labelId = $this->getRequest()->getParam('label_id');
        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToSelect('*')
            ->addFieldToFilter(
                'entity_id',
                [
                    'in' => $labelModel->getIndexedProducts($labelId)
                ]
            );

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'entity_id',
            [
                'header' => __('ID'),
                'sortable' => true,
                'index' => 'entity_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );

        $this->addColumn(
            'name',
            [
                'header' => __('Name'),
                'index' => 'name',
                'header_css_class' => 'col-name',
                'column_css_class' => 'col-name'
            ]
        );

        $this->addColumn(
            'type',
            [
                'header' => __('Type'),
                'index' => 'type_id',
                'type' => 'options',
                'options' => $this->productType->getOptionArray(),
                'header_css_class' => 'col-type',
                'column_css_class' => 'col-type'
            ]
        );

        $sets = $this->setsFactory->create()->setEntityTypeFilter(
            $this->productFactory->create()->getResource()->getTypeId()
        )->load()->toOptionHash();

        $this->addColumn(
            'set_name',
            [
                'header' => __('Attribute Set'),
                'index' => 'attribute_set_id',
                'type' => 'options',
                'options' => $sets,
                'header_css_class' => 'col-attr-name',
                'column_css_class' => 'col-attr-name'
            ]
        );

        $this->addColumn(
            'status',
            [
                'header' => __('Status'),
                'index' => 'status',
                'type' => 'options',
                'options' => $this->productStatus->getOptionArray(),
                'header_css_class' => 'col-status',
                'column_css_class' => 'col-status'
            ]
        );

        $this->addColumn(
            'visibility',
            [
                'header' => __('Visibility'),
                'index' => 'visibility',
                'type' => 'options',
                'options' => $this->productVisibility->getOptionArray(),
                'header_css_class' => 'col-visibility',
                'column_css_class' => 'col-visibility'
            ]
        );

        $this->addColumn(
            'sku',
            [
                'header' => __('SKU'),
                'index' => 'sku',
                'header_css_class' => 'col-sku',
                'column_css_class' => 'col-sku'
            ]
        );

        $this->addColumn(
            'price',
            [
                'header' => __('Price'),
                'type' => 'currency',
                'currency_code' => (string)$this->_scopeConfig->getValue(
                    \Magento\Directory\Model\Currency::XML_PATH_CURRENCY_BASE,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                ),
                'index' => 'price',
                'header_css_class' => 'col-price',
                'column_css_class' => 'col-price'
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * Retrieve the Url for a specified product row
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return '#';
    }

    /**
     * {@inheritdoc}
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/indexed', ['_current' => true]);
    }
}
