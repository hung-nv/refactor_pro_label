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
    private $magentoMetadata;

    /**
     * @var LabelResource
     */
    private $labelResource;

    /**
     * @var AggregatedFieldDataConverter
     */
    private $aggregatedFieldConverter;

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
        $this->magentoMetadata = $productMetadata;
        $this->aggregatedFieldConverter = $aggregatedFieldConverter;
        $this->labelResource = $labelResource;
    }

    /**
     * @inheritdoc
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.2.0', '<')
            && version_compare($this->magentoMetadata->getVersion(), '2.2.0', '>=')
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
        $this->aggregatedFieldConverter->convert(
            [
                new FieldToConvert(
                    SerializedToJson::class,
                    $this->labelResource->getMainTable(),
                    $this->labelResource->getIdFieldName(),
                    'conditions_serialized'
                ),
                new FieldToConvert(
                    SerializedToJson::class,
                    $this->labelResource->getMainTable(),
                    $this->labelResource->getIdFieldName(),
                    'store_id'
                ),
                new FieldToConvert(
                    SerializedToJson::class,
                    $this->labelResource->getMainTable(),
                    $this->labelResource->getIdFieldName(),
                    'customer_groups'
                ),
            ],
            $setup->getConnection()
        );
    }
}
