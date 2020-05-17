<?php
namespace Swissup\ProLabels\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Adapter\AdapterInterface;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * Installs DB schema for a module
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        /**
         * Create table 'swissup_prolabels_label'
         */
         $table = $installer->getConnection()
             ->newTable($installer->getTable('swissup_prolabels_label'))
             ->addColumn('label_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, array(
                 'identity' => true,
                 'unsigned' => true,
                 'nullable'  => false,
                 'primary'   => true,
             ), 'LabelID')
             ->addColumn('title', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, array(
                 'nullable'  => false,
             ), 'Title')
             ->addColumn('store_id', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, array(
                 'nullable'  => false,
             ), 'StoreID')
             ->addColumn('is_active', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, null, array(
                 'nullable'  => false,
                 'default'  => 0,
             ), 'Is Active')
             ->addColumn('customer_groups', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, array(
                 'nullable'  => false,
             ), 'Customer Groups')
             ->addColumn('conditions_serialized', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, array(
                 'nullable'  => false,
             ), 'Conditions Serialized')
             ->addColumn('product_position', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, array(
                 'nullable'  => true,
                 'default'  => NULL,
             ), 'Product Position')
             ->addColumn('product_image', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, array(
                 'nullable'  => true,
                 'default'  => NULL,
             ), 'Product Image')
             ->addColumn('product_custom_style', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, array(
                 'nullable'  => true,
                 'default'  => NULL,
             ), 'Product Custom Style')
             ->addColumn('product_text', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, array(
                 'nullable'  => true,
                 'default'  => NULL,
             ), 'Product Text')
             ->addColumn('product_custom_url', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, array(
                 'nullable'  => true,
                 'default'  => NULL,
             ), 'Product Custom Url')
             ->addColumn('product_round_method', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, array(
                 'nullable'  => true,
                 'default'  => NULL,
             ), 'Product Round Method')
             ->addColumn('product_round_value', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, array(
                 'nullable'  => true,
                 'default'  => NULL,
             ), 'Product Round Value')
             ->addColumn('category_position', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, array(
                 'nullable'  => true,
                 'default'  => NULL,
             ), 'Category Position')
             ->addColumn('category_image', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, array(
                 'nullable'  => true,
                 'default'  => NULL,
             ), 'Category Image')
             ->addColumn('category_custom_style', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, array(
                 'nullable'  => true,
                 'default'  => NULL,
             ), 'Category Custom Style')
             ->addColumn('category_text', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, array(
                 'nullable'  => true,
                 'default'  => NULL,
             ), 'Category Text')
             ->addColumn('category_custom_url', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, array(
                 'nullable'  => true,
                 'default'  => NULL,
             ), 'Category Custom Url')
             ->addColumn('category_round_method', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, array(
                 'nullable'  => true,
                 'default'  => NULL,
             ), 'Category Round Method')
             ->addColumn('category_round_value', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, array(
                 'nullable'  => true,
                 'default'  => NULL,
             ), 'Category Round Value');

            $installer->getConnection()->createTable($table);

        /**
          * Create table 'swissup_prolabels_index'
          */
         $table = $installer->getConnection()->newTable(
                 $installer->getTable('swissup_prolabels_index')
             )->addColumn(
                 'index_id',
                 \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
                 null,
                 ['identity' => true, 'nullable' => false, 'primary' => true],
                 'Index ID'
             )->addColumn(
                 'label_id',
                 \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                 null,
                 ['unsigned' => true, 'nullable' => false],
                 'Label ID'
             )->addColumn(
                 'entity_id',
                 \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                 null,
                 ['unsigned' => true, 'nullable' => false],
                 'Entity ID'
             )->addIndex(
                 $installer->getIdxName('swissup_prolabels_label', ['label_id']),
                 ['label_id']
             )->addIndex(
                 $installer->getIdxName('swissup_prolabels_entity', ['entity_id']),
                 ['entity_id']
             )->addForeignKey(
                 $installer->getFkName('swissup_prolabels_index_label', 'label_id', 'swissup_prolabels_label', 'label_id'),
                 'label_id',
                 $installer->getTable('swissup_prolabels_label'),
                 'label_id',
                 \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
             )->addForeignKey(
                 $installer->getFkName('swissup_prolabels_index_product', 'entity_id', 'catalog_product_entity', 'entity_id'),
                 'entity_id',
                 $installer->getTable('catalog_product_entity'),
                 'entity_id',
                 \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
             )->setComment(
                 'Swissup Labels Index Table'
             );

             $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}
