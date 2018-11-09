<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Layerednav\Model\ResourceModel\Layer\Filter;

use Magento\Catalog\Model\Layer\Filter\FilterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class Decimal
 * @package Aheadworks\Layerednav\Model\ResourceModel\Layer\Filter
 */
class Decimal extends AbstractDb
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('catalog_product_index_eav_decimal', 'entity_id');
    }

    /**
     * Retrieve array with products counts per range
     *
     * @param FilterInterface $filter
     * @param int $range
     * @return array
     */
    public function getCount(FilterInterface $filter, $range)
    {
        return $this->fetchCount(
            $this->getSelect($filter),
            $range,
            $filter->getAttributeModel()->getAttributeCode()
        );
    }

    /**
     * @param FilterInterface $filter
     * @param int $range
     * @return array
     */
    public function getParentCount(FilterInterface $filter, $range)
    {
        return $this->fetchCount(
            $this->getParentSelect($filter),
            $range,
            $filter->getAttributeModel()->getAttributeCode()
        );
    }

    /**
     * Retrieve product counts for a range
     *
     * @param Select $select
     * @param int $range
     * @param string $attributeCode
     * @return array
     */
    private function fetchCount(Select $select, $range, $attributeCode)
    {
        $connection = $this->getConnection();

        $tableAlias = sprintf('%s_idx', $attributeCode);
        $countExpr = new \Zend_Db_Expr('COUNT(*)');
        $rangeExpr = new \Zend_Db_Expr('FLOOR(' . $tableAlias . '.value / ' . $range . ') + 1');

        $select->columns(['decimal_range' => $rangeExpr, 'count' => $countExpr])
            ->group($rangeExpr);
        return $connection->fetchPairs($select);
    }

    /**
     * Retrieve clean select with joined index table
     * Joined table has index
     *
     * @param FilterInterface $filter
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return Select
     */
    private function getSelect(FilterInterface $filter)
    {
        $select = clone $filter->getLayer()->getProductCollection()->getSelect();
        return $this->prepareSelectForCount($select, $filter);
    }

    /**
     * @param FilterInterface $filter
     * @return Select
     */
    private function getParentSelect(FilterInterface $filter)
    {
        // Clone select from collection with filters
        $select = clone $filter->getLayer()
            ->getProductCollection()
            ->getSelect();
        $select->reset(Select::WHERE);
        return $this->prepareSelectForCount($select, $filter);
    }

    /**
     * Prepare select to get count
     *
     * @param Select $select
     * @param FilterInterface $filter
     * @return Select
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function prepareSelectForCount(Select $select, FilterInterface $filter)
    {
        $connection = $this->getConnection();
        // Reset columns, order and limitation conditions
        $select->reset(Select::COLUMNS)
            ->reset(Select::ORDER)
            ->reset(Select::LIMIT_COUNT)
            ->reset(Select::LIMIT_OFFSET);

        $attribute = $filter->getAttributeModel();
        $collection = $filter->getLayer()->getProductCollection();

        $tableAlias = sprintf('%s_idx', $attribute->getAttributeCode());
        $flag = 'attribute_joined_' . $tableAlias;
        if (!$collection->getFlag($flag)) {
            $cond = 'e.entity_id = ' . $tableAlias . '.entity_id'
                . ' AND ' . $connection->quoteInto($tableAlias . '.attribute_id = ?', $attribute->getId())
                . ' AND ' . $connection->quoteInto($tableAlias . '.store_id = ?', $collection->getStoreId());
            $select->joinLeft(
                [$tableAlias => $this->getMainTable()],
                $cond,
                []
            );
        }
        return $select;
    }

    /**
     * @param FilterInterface $filter
     * @return int
     */
    public function getMaxValue(FilterInterface $filter)
    {
        $select = $this->getParentSelect($filter);
        $connection = $this->getConnection();

        $tableAlias = sprintf('%s_idx', $filter->getAttributeModel()->getAttributeCode());
        $select->columns(new \Zend_Db_Expr('MAX(' . $tableAlias . '.value)'));

        return (int)$connection->fetchOne($select);
    }

    /**
     * @param FilterInterface $filter
     * @return $this
     */
    public function joinFilterToCollection(FilterInterface $filter)
    {
        $connection = $this->getConnection();
        $collection = $filter->getLayer()->getProductCollection();
        $attribute = $filter->getAttributeModel();
        $tableAlias = sprintf('%s_idx', $attribute->getAttributeCode());

        $flag = 'attribute_joined_' . $tableAlias;
        if (!$collection->getFlag($flag)) {
            $conditions = [
                $tableAlias . '.entity_id = e.entity_id',
                $connection->quoteInto($tableAlias . '.attribute_id = ?', $attribute->getAttributeId()),
                $connection->quoteInto($tableAlias . '.store_id = ?', $collection->getStoreId()),
            ];

            $collection->getSelect()->joinLeft(
                [$tableAlias => $this->getMainTable()],
                implode(' AND ', $conditions),
                []
            );
            $collection->setFlag($flag, true);
        }

        return $this;
    }

    /**
     * Get where conditions
     *
     * @param FilterInterface $filter
     * @param array $intervals
     * @return array
     */
    public function getWhereConditions(FilterInterface $filter, $intervals = [])
    {
        $whereConditions = [];

        $attribute = $filter->getAttributeModel();
        $connection = $this->getConnection();
        $tableAlias = sprintf('%s_idx', $attribute->getAttributeCode());

        foreach ($intervals as $interval) {
            list($range, $value) = $interval;
            $conditions = [];
            $conditions[] = $connection->quoteInto('(' . $tableAlias . '.value >= ?', $value * ($range - 1));
            $conditions[] = $connection->quoteInto($tableAlias . '.value < ?)', $value * $range);

            $whereConditions[] = implode(' AND ', $conditions);
        }

        return $whereConditions;
    }
}
