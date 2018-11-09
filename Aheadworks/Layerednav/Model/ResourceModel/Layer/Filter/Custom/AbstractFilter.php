<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Layerednav\Model\ResourceModel\Layer\Filter\Custom;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Layer\Filter\FilterInterface;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Eav\Model\ResourceModel\Entity\Attribute as ResourceAttribute;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\DB\Select;

/**
 * Class AbstractFilter
 * @package Aheadworks\Layerednav\Model\ResourceModel\Layer\Filter\Custom
 */
abstract class AbstractFilter extends AbstractDb
{
    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * @var ResourceAttribute
     */
    protected $resourceAttribute;

    /**
     * @param Context $context
     * @param MetadataPool $metadataPool
     * @param ResourceAttribute $resourceAttribute
     * @param string|null $connectionName
     */
    public function __construct(
        Context $context,
        MetadataPool $metadataPool,
        ResourceAttribute $resourceAttribute,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->metadataPool = $metadataPool;
        $this->resourceAttribute = $resourceAttribute;
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('catalog_product_index_eav', 'entity_id');
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
        $collection = clone $filter->getLayer()->getProductCollection();
        $this->joinDateAttributes($collection, $filter);
        $select = clone $collection->getSelect();

        $whereConditions = [];
        foreach ($this->getWhereConditions($filter, null) as $conditions) {
            $condition = implode(' OR ', $conditions);
            $whereConditions[] = "({$condition})";
        }
        $condition = implode(' AND ', $whereConditions);
        $select->where($condition);

        return $this->fetchCount($select);
    }

    /**
     * @param FilterInterface $filter
     * @return array
     */
    public function getParentCount(FilterInterface $filter)
    {
        $collection = clone $filter->getLayer()->getProductCollection();
        $this->joinDateAttributes($collection, $filter);
        $select = clone $collection->getSelect();
        $select->reset(Select::WHERE);

        $whereConditions = [];
        foreach ($this->getWhereConditions($filter, null) as $conditions) {
            $condition = implode(' OR ', $conditions);
            $whereConditions[] = "({$condition})";
        }
        $condition = implode(' AND ', $whereConditions);
        $select->where($condition);

        return $this->fetchCount($select);
    }

    /**
     * @param Select $select
     * @return array
     */
    private function fetchCount(Select $select)
    {
        // Reset columns, order and limitation conditions
        $select->reset(Select::COLUMNS)
            ->reset(Select::ORDER)
            ->reset(Select::LIMIT_COUNT)
            ->reset(Select::LIMIT_OFFSET);

        $connection = $this->getConnection();

        $select->columns(
            [
                'value' => new \Zend_Db_Expr('1'),
                'count' => new \Zend_Db_Expr('COUNT(*)')
            ]
        );
        return $connection->fetchPairs($select);
    }

    /**
     * Join date attributes for special filters
     *
     * @param Collection $collection
     * @param FilterInterface $filter
     * @return $this
     */
    protected function joinDateAttributes(Collection $collection, FilterInterface $filter)
    {
        if ($collection->getFlag($this->getDateToAttrCode() . '_table_joined')) {
            return $this;
        }

        $dateFromAttrId = $this->resourceAttribute->getIdByCode('catalog_product', $this->getDateFromAttrCode());
        $dateToAttrId = $this->resourceAttribute->getIdByCode('catalog_product', $this->getDateToAttrCode());

        $tableAlias = $this->getTable('catalog_product_entity_datetime');
        $linkFieldName = $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField();

        $superLinkAlias = $filter->getRequestVar() . '_super_link';
        $dateFromDefaultAlias = $this->getDateFromAttrCode() . '_default';
        $dateToDefaultAlias = $this->getDateToAttrCode() . '_default';
        $dateFromChildrenAlias = $this->getDateFromAttrCode() . '_children';
        $dateToChildrenAlias = $this->getDateToAttrCode() . '_children';

        $collection
            ->getSelect()
            ->joinLeft(
                [$dateFromDefaultAlias => $tableAlias],
                "({$dateFromDefaultAlias}.{$linkFieldName} = e.entity_id)"
                . " AND ({$dateFromDefaultAlias}.attribute_id = {$dateFromAttrId})"
                . " AND {$dateFromDefaultAlias}.store_id = 0",
                [
                    $this->getDateFromAttrCode() => new \Zend_Db_Expr(
                        "COALESCE({$this->getDateFromAttrCode()}.value, "
                        . "{$dateFromDefaultAlias}.value, {$dateFromChildrenAlias}.value)"
                    ),
                    $this->getDateToAttrCode() => new \Zend_Db_Expr(
                        "COALESCE({$this->getDateToAttrCode()}.value, "
                        . "{$dateToDefaultAlias}.value, {$dateToChildrenAlias}.value)"
                    )
                ]
            )
            ->joinLeft(
                [$this->getDateFromAttrCode() => $tableAlias],
                "({$this->getDateFromAttrCode()}.{$linkFieldName} = e.entity_id)"
                . " AND ({$this->getDateFromAttrCode()}.attribute_id = {$dateFromAttrId})"
                . " AND {$this->getDateFromAttrCode()}.store_id = {$filter->getStoreId()}",
                []
            )
            ->joinLeft(
                [$this->getDateToAttrCode() => $tableAlias],
                "({$this->getDateToAttrCode()}.{$linkFieldName} = e.entity_id)"
                . " AND ({$this->getDateToAttrCode()}.attribute_id = {$dateToAttrId})"
                . " AND {$this->getDateToAttrCode()}.store_id = {$filter->getStoreId()}",
                []
            )
            ->joinLeft(
                [$dateToDefaultAlias => $tableAlias],
                "({$dateToDefaultAlias}.{$linkFieldName} = e.entity_id)"
                . " AND ({$dateToDefaultAlias}.attribute_id = {$dateToAttrId})"
                . " AND {$dateToDefaultAlias}.store_id = 0",
                []
            )
            // Join children values for configurable and bundle
            ->joinLeft(
                [$superLinkAlias => $this->getTable('catalog_product_super_link')],
                "({$superLinkAlias}.parent_id = e.entity_id)",
                []
            )
            ->joinLeft(
                [$dateFromChildrenAlias => $tableAlias],
                "({$dateFromChildrenAlias}.{$linkFieldName} = {$superLinkAlias}.product_id)"
                . " AND ({$dateFromChildrenAlias}.attribute_id = {$dateFromAttrId})"
                . " AND ({$dateFromChildrenAlias}.store_id = {$filter->getStoreId()}"
                . " OR {$dateFromChildrenAlias}.store_id = 0)",
                []
            )
            ->joinLeft(
                [$dateToChildrenAlias => $tableAlias],
                "({$dateToChildrenAlias}.{$linkFieldName} = {$superLinkAlias}.product_id)"
                . " AND ({$dateToChildrenAlias}.attribute_id = {$dateToAttrId})"
                . " AND ({$dateToChildrenAlias}.store_id = {$filter->getStoreId()}"
                . " OR {$dateToChildrenAlias}.store_id = 0)",
                []
            );
        $collection->setFlag($this->getDateToAttrCode() . '_table_joined', true);
        return $this;
    }

    /**
     * Apply special filter to product collection
     *
     * @param \Magento\Catalog\Model\Layer\Filter\FilterInterface $filter
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return $this
     */
    public function joinFilterToCollection(FilterInterface $filter)
    {
        $collection = $filter->getLayer()->getProductCollection();
        $this->joinDateAttributes($collection, $filter);
        return $this;
    }

    /**
     * Get where conditions
     *
     * @param FilterInterface $filter
     * @param mixed $value
     * @return array
     */
    public function getWhereConditions(FilterInterface $filter, $value)
    {
        $connection = $this->getConnection();
        $currentDate = new \DateTime();
        $dateFormat = $currentDate->format('Y-m-d 00:00:00');

        $tableAlias = $this->getTable('catalog_product_entity_datetime');

        $dateToDefaultAlias = $this->getDateToAttrCode() . '_default';
        $dateFromDefaultAlias = $this->getDateFromAttrCode() . '_default';
        $dateFromChildrenAlias = $this->getDateFromAttrCode() . '_children';
        $dateToChildrenAlias = $this->getDateToAttrCode() . '_children';

        return array_merge(
            [
                $tableAlias => [
                    $connection->quoteInto(
                        '('
                        . '(COALESCE(' . $this->getDateToAttrCode() . '.value, '
                        . $dateToDefaultAlias . '.value, '
                        . $dateToChildrenAlias . '.value) IS NULL'
                        . ' OR COALESCE(' . $this->getDateToAttrCode()
                        . '.value, ' . $dateToDefaultAlias . '.value, '
                        . $dateToChildrenAlias . '.value) >= ?)'
                        . ' AND (COALESCE('. $this->getDateFromAttrCode() . '.value, '
                        . $dateFromDefaultAlias . '.value, '
                        . $dateFromChildrenAlias . '.value) IS NULL'
                        . ' OR COALESCE(' . $this->getDateFromAttrCode()
                        . '.value, ' . $dateFromDefaultAlias . '.value, '
                        . $dateFromChildrenAlias . '.value) <= ?)'
                        . ')',
                        $dateFormat
                    )
                ]
            ],
            $this->getSpecialConditions($filter, $value)
        );
    }

    /**
     * @return string
     */
    abstract protected function getDateFromAttrCode();

    /**
     * @return string
     */
    abstract protected function getDateToAttrCode();

    /**
     * @param FilterInterface $filter
     * @param mixed $value
     * @return array
     */
    abstract protected function getSpecialConditions(FilterInterface $filter, $value);
}
