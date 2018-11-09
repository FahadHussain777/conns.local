<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Layerednav\Model\ResourceModel\Layer\Filter\Custom;

use Magento\Catalog\Model\Layer\Filter\FilterInterface;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\DB\Select;

/**
 * Class Stock
 * @package Aheadworks\Layerednav\Model\ResourceModel\Layer\Filter\Custom
 */
class Stock extends AbstractFilter
{
    /**
     * {@inheritdoc}
     */
    protected function getDateFromAttrCode()
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    protected function getDateToAttrCode()
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getCount(FilterInterface $filter)
    {
        $collection = $filter->getLayer()->getProductCollection();
        $select = clone $collection->getSelect();
        return $this->fetchCount($select, $collection);
    }

    /**
     * {@inheritdoc}
     */
    public function getParentCount(FilterInterface $filter)
    {
        $collection = $filter->getLayer()->getProductCollection();
        $select = clone $collection->getSelect();
        $select->reset(Select::WHERE);
        return $this->fetchCount($select, $collection);
    }

    /**
     * @param Select $select
     * @param Collection $collection
     * @return array
     */
    private function fetchCount(Select $select, Collection $collection)
    {
        // Reset columns, order and limitation conditions
        $select->reset(Select::COLUMNS)
            ->reset(Select::ORDER)
            ->reset(Select::LIMIT_COUNT)
            ->reset(Select::LIMIT_OFFSET);

        $connection = $this->getConnection();
        $flag = 'stock_table_joined';
        if (!$collection->getFlag($flag)) {
            $table = $this->getTable('cataloginventory_stock_status');
            $select->join(
                ['stock_table' => $table],
                'stock_table.product_id = e.entity_id',
                []
            );
        }

        // Check out of stock products
        $outOfStockSelect = clone $select;
        $outOfStockSelect
            ->columns(['count' => new \Zend_Db_Expr('COUNT(stock_table.stock_status)')])
            ->where('stock_table.stock_status = 0');
        $outOfStockCount = $connection->fetchOne($outOfStockSelect);
        if (!$outOfStockCount) {
            return ['1' => 0];
        }

        $select->columns(
            [
                'value' => new \Zend_Db_Expr('1'),
                'count' => new \Zend_Db_Expr('COUNT(stock_table.stock_status)')
            ]
        )->where('stock_table.stock_status = 1');

        return $connection->fetchPairs($select);
    }

    /**
     * {@inheritdoc}
     */
    public function joinFilterToCollection(FilterInterface $filter)
    {
        $collection = $filter->getLayer()->getProductCollection();

        $flag = 'stock_table_joined';
        if (!$filter->getLayer()->getProductCollection()->getFlag($flag)) {
            $table = $this->getTable('cataloginventory_stock_status');
            $collection->getSelect()->join(
                ['stock_table' => $table],
                'stock_table.product_id = e.entity_id',
                []
            );
            $collection->setFlag($flag, true);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getWhereConditions(FilterInterface $filter, $value)
    {
        $tableAlias = 'stock_table';
        return [$tableAlias => [$tableAlias . '.stock_status = 1']];
    }

    /**
     * {@inheritdoc}
     */
    protected function getSpecialConditions(FilterInterface $filter, $value)
    {
        return [];
    }
}
