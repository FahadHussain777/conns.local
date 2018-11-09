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
 * Class Attribute
 * @package Aheadworks\Layerednav\Model\ResourceModel\Layer\Filter
 */
class Attribute extends AbstractDb
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('catalog_product_index_eav', 'entity_id');
    }

    /**
     * @param FilterInterface $filter
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return $this
     */
    public function joinFilterToCollection(FilterInterface $filter)
    {
        $collection = $filter->getLayer()->getProductCollection();
        $attribute = $filter->getAttributeModel();
        $connection = $this->getConnection();
        $tableAlias = $attribute->getAttributeCode() . '_idx';
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
     * Get where condition
     *
     * @param FilterInterface $filter
     * @param string|array $value
     * @return array
     */
    public function getWhereConditions(FilterInterface $filter, $value)
    {
        if (is_string($value)) {
            $value = explode(',', $value);
        }
        $attribute = $filter->getAttributeModel();
        $connection = $this->getConnection();
        $tableAlias = $attribute->getAttributeCode() . '_idx';
        return [$connection->quoteInto($tableAlias . '.value IN (?)', $value)];
    }

    /**
     * Retrieve array with products counts per attribute option
     *
     * @param FilterInterface $filter
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return array
     */
    public function getCount(FilterInterface $filter)
    {
        // Clone select from collection with filters
        $select = clone $filter->getLayer()->getProductCollection()->getSelect();
        // Remove where conditions for current attribute - compatibility with multiselect attributes
        $whereConditions = $select->getPart(Select::WHERE);
        $select->reset(Select::WHERE);

        $attribute = $filter->getAttributeModel();
        $tableAlias = sprintf('%s_idx', $attribute->getAttributeCode());

        foreach ($whereConditions as $key => $condition) {
            if (false !== strpos($condition, $tableAlias)) {
                unset($whereConditions[$key]);
                continue;
            }
            if (0 === strpos($condition, 'AND ')) {
                $condition = preg_replace("/AND /", "", $condition, 1);
            }
            $whereConditions[$key] = $condition;
        }
        if ($whereConditions) {
            $where[] = implode(' AND ', $whereConditions);
            $select->setPart(Select::WHERE, $where);
        }

        return $this->fetchCount($select, $filter);
    }

    /**
     * @param FilterInterface $filter
     * @return array
     */
    public function getParentCount(FilterInterface $filter)
    {
        // Clone select from collection with filters
        $select = clone $filter->getLayer()->getProductCollection()->getSelect();
        $select->reset(Select::WHERE);
        return $this->fetchCount($select, $filter);
    }

    /**
     * @param Select $select
     * @param FilterInterface $filter
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function fetchCount(Select $select, FilterInterface $filter)
    {
        // Reset columns, order and limitation conditions
        $select->reset(Select::COLUMNS)
            ->reset(Select::ORDER)
            ->reset(Select::LIMIT_COUNT)
            ->reset(Select::LIMIT_OFFSET)
            ->reset(Select::GROUP)
        ;

        $connection = $this->getConnection();
        $attribute = $filter->getAttributeModel();
        $tableAlias = sprintf('%s_idx', $attribute->getAttributeCode());
        $flag = 'attribute_joined_' . $tableAlias;
        if (!$filter->getLayer()->getProductCollection()->getFlag($flag)) {
            $conditions = [
                $tableAlias . '.entity_id = e.entity_id',
                $connection->quoteInto($tableAlias . '.attribute_id = ?', $attribute->getAttributeId()),
                $connection->quoteInto($tableAlias . '.store_id = ?', $filter->getStoreId()),
            ];

            $select->joinLeft(
                [$tableAlias => $this->getMainTable()],
                join(' AND ', $conditions),
                []
            );
        }
        $select->columns(
            [
                'value' => $tableAlias . '.value',
                'count' => new \Zend_Db_Expr('COUNT(' . $tableAlias . '.entity_id)')
            ]
        );
        $select->group("{$tableAlias}.value");

        return $connection->fetchPairs($select);
    }
}
