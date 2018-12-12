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
        $this->createPowerStoreHours($setup);
        $this->createPowerStoreRegion($setup);
        $setup->endSetup();
    }

    public function createPowerStoreHours($setup){
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
        return $this;
    }

    public function createPowerStoreRegion($setup){
        $table = $setup->getConnection()->newTable(
            $setup->getTable('conns_powerstore_region')
        )->addColumn(
            'id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true,'identity' => true, 'nullable' => false, 'primary' => true],
            'ID'
        )->addColumn(
            'enabled',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            6,
            ['nullable' => false],
            'Enabled'
        )->addColumn(
            'country',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Country'
        )->addColumn(
            'region',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Region'
        )->addColumn(
            'city',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'City'
        )->addColumn(
            'title',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Title'
        )->addColumn(
            'meta_title',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Meta title'
        )->addColumn(
            'description',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64k',
            ['nullable' => false],
            'Description'
        )->addColumn(
            'meta_description',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Meta Description'
        )->addColumn(
            'latitude',
            \Magento\Framework\DB\Ddl\Table::TYPE_FLOAT,
            [11],
            ['default' => null, 'COMMENT' => 'Latitude']
        )->addColumn(
            'longitude',
            \Magento\Framework\DB\Ddl\Table::TYPE_FLOAT,
            [11],
            ['default' => null, 'COMMENT' => 'Longitude']
        )->addColumn(
            'url_key',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            100,
            ['default' => null, 'COMMENT' => 'Url Key']
        )->addIndex(
            $setup->getIdxName(
                'conns_powerstore_region',
                'region',
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
            ),
            'region',
            ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT]
        )->setComment('Power store region');
        $setup->getConnection()->createTable($table);
        return $this;
    }

}