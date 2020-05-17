<?php
namespace Swissup\ProLabels\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Upgrade the ProLabels module DB scheme
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            $setup->getConnection()->changeColumn(
                $setup->getTable('swissup_prolabels_label'),
                'is_active',
                'status',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'nullable' => false,
                    'default'  => 1,
                    'comment' => 'Label Status'
                ]
            );
        }

        if (version_compare($context->getVersion(), '1.1.0', '<')) {
            // drop unused columns
            $columnNames = [
                'product_image_width',
                'product_image_height',
                'category_image_width',
                'category_image_height'
            ];
            foreach ($columnNames as $columnName) {
                $setup->getConnection()->dropColumn(
                    $setup->getTable('swissup_prolabels_label'),
                    $columnName
                );
            }

            // extend column length
            $columnNames = [
                'product_image' => 'Product Image',
                'product_custom_style' => 'Product Custom Style',
                'product_text' => 'Product Text',
                'category_image' => 'Category Image',
                'category_custom_style' => 'Category Custom Style',
                'category_text' => 'Category Text'
            ];
            foreach ($columnNames as $columnName => $columnTitle) {
                $setup->getConnection()->modifyColumn(
                    $setup->getTable('swissup_prolabels_label'),
                    $columnName,
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'nullable' => false,
                        'comment' => $columnTitle
                    ]
                );
            }
        }

        $setup->endSetup();
    }
}
