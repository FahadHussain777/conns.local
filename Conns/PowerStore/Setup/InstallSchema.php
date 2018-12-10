<?php

namespace Conns\PowerStore\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $table = $setup->getConnection()->newTable(
            $setup->getTable('conns_powerstore_hours')
        )->addColumn(
            'id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true,'identity' => true, 'nullable' => false, 'primary' => true],
            'ID'
        )->addColumn(
            'locator_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            10,
            ['unsigned' => true, 'nullable' => false],
            'Store Id'
        )->addColumn(
            'dow',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true,'nullable' => false],
            'Day of week'
        )->addColumn(
            'open',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            ['nullable' => false],
            'Open time'
        )->addColumn(
            'close',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            ['nullable' => false],
            'Close time'
        )->addForeignKey(
            $setup->getFkName(
                'conns_powerstore_hours',
                'locator_id',
                'brainacts_storelocator',
                'locator_id'),
            'locator_id',
            $setup->getTable('brainacts_storelocator'),
            'locator_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment('Power store visiting hours');
        $setup->getConnection()->createTable($table);
        $setup->endSetup();
    }

}