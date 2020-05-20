<?php

namespace Swissup\ProLabels\Setup;

use Magento\Framework\DB\DataConverter\SerializedToJson;
use Magento\Framework\DB\AggregatedFieldDataConverter;
use Magento\Framework\DB\FieldToConvert;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Swissup\ProLabels\Model\ResourceModel\Label as LabelResource;

class UpgradeData implements \Magento\Framework\Setup\UpgradeDataInterface
{
    /**
     * @var ProductMetadataInterface
     */
    private $magento_metadata;

    /**
     * @var LabelResource
     */
    private $label_resource;

    /**
     * @var AggregatedFieldDataConverter
     */
    private $aggregated_field_converter;

    /**
     * @param ProductMetadataInterface     $productMetadata
     * @param AggregatedFieldDataConverter $aggregatedFieldConverter
     * @param LabelResource                $labelResource
     */
    public function __construct(
        ProductMetadataInterface $productMetadata,
        AggregatedFieldDataConverter $aggregatedFieldConverter,
        LabelResource $labelResource
    ) {
        $this->magento_metadata = $productMetadata;
        $this->aggregated_field_converter = $aggregatedFieldConverter;
        $this->label_resource = $labelResource;
    }

    /**
     * @inheritdoc
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.2.0', '<')
            && version_compare($this->magento_metadata->getVersion(), '2.2.0', '>=')
        ) {
            $this->convertSerializedDataToJson($setup);
        }

        $setup->endSetup();
    }

    /**
     * Convert metadata from serialized to JSON format:
     *
     * @param ModuleDataSetupInterface $setup
     * @return void
     */
    public function convertSerializedDataToJson($setup)
    {
        $dataConvert = [
          new FieldToConvert(
            SerializedToJson::class,
            $this->label_resource->getMainTable(),
            $this->label_resource->getIdFieldName(),
            'conditions_serialized'
          ),
          new FieldToConvert(
            SerializedToJson::class,
            $this->label_resource->getMainTable(),
            $this->label_resource->getIdFieldName(),
            'store_id'
          ),
          new FieldToConvert(
            SerializedToJson::class,
            $this->label_resource->getMainTable(),
            $this->label_resource->getIdFieldName(),
            'customer_groups'
          ),
        ];

        $this->aggregated_field_converter->convert(
            $dataConvert,
            $setup->getConnection()
        );
    }
}
