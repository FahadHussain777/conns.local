<?php

namespace Conns\PowerStore\Setup;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ){
        $setup->startSetup();
        if (version_compare($context->getVersion(), '0.0.2') < 0) {
            $tableName = $setup->getTable('brainacts_storelocator');
            if ($setup->getConnection()->isTableExists($tableName) == true) {
                $columns = [
                    'store_number' => [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'nullable' => true,
                        'comment' => 'Store Number',
                    ],
                ];
                $columns = [
                    'extra_info' => [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'nullable' => true,
                        'comment' => 'Extra Description',
                    ],
                ];
                $connection = $setup->getConnection();
                foreach ($columns as $name => $definition) {
                    $connection->addColumn($tableName, $name, $definition);
                }

            }
        }
        $setup->endSetup();
    }

}