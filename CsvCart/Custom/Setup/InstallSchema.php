<?php
namespace CsvCart\Custom\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        $table = $installer->getConnection()->newTable(
            $installer->getTable('custom_csv_cart')
        )->addColumn(
            'entity_id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true],
            'Entity ID'
        )->addColumn(
            'customer_id',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'Customer ID'
        )->addColumn(
            'csv_file_path',
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'CSV File Path'
        )->addColumn(
            'csv_path_absolute',
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Absolute CSV File Path'
        )->setComment(
            'Custom CSV Cart Table'
        );

        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}
