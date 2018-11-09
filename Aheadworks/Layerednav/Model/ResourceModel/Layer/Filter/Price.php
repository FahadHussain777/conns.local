<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Layerednav\Model\ResourceModel\Layer\Filter;

use Aheadworks\Layerednav\Model\Config;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Model\Layer\Filter\FilterInterface;
use Magento\Framework\DB\Select;

/**
 * Class Price
 * @package Aheadworks\Layerednav\Model\ResourceModel\Layer\Filter
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Price extends \Magento\Catalog\Model\ResourceModel\Layer\Filter\Price
{
    /**
     * Alias for catalog_product_entity table
     */
    const PRODUCT_ENTITY_TABLE_ALIAS = 'product_entity';

    /**
     * @var \Magento\Catalog\Model\Layer
     */
    private $layer;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $session;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Catalog\Model\Layer\Resolver $layerResolver
     * @param \Magento\Customer\Model\Session $session
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param Config $config
     * @param null $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Magento\Customer\Model\Session $session,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Config $config,
        $connectionName = null
    ) {
        $this->layer = $layerResolver->get();
        $this->session = $session;
        $this->storeManager = $storeManager;
        $this->config = $config;
        parent::__construct(
            $context,
            $eventManager,
            $layerResolver,
            $session,
            $storeManager,
            $connectionName
        );
    }

    /**
     * Retrieve min and max values for price range
     *
     * @return array
     */
    public function getMinMaxPrices()
    {
        $priceData = $this->fetchPriceData($this->getParentSelect());
        return ['minPrice' => $priceData['min_value'], 'maxPrice' => $priceData['max_value']];
    }

    /**
     * Retrieve array with products counts per price range
     *
     * @param int $range
     * @return array
     */
    public function getCount($range)
    {
        return $this->fetchCount($this->getSelect(), $range);
    }

    /**
     * @param int $range
     * @return array
     */
    public function getParentCount($range)
    {
        return $this->fetchCount($this->getParentSelect(), $range);
    }

    /**
     * Retrieve product counts for a range
     *
     * @param Select $select
     * @param int $range
     * @return array
     */
    private function fetchCount(Select $select, $range)
    {
        $select = $this->removePriceConditions($select);
        $priceExpression = $this->_getFullPriceExpression($select);

        // Check and set correct variable values to prevent SQL-injections
        $range = floatval($range);
        if ($range == 0) {
            $range = 1;
        }
        $countExpr = new \Zend_Db_Expr('COUNT(*)');
        $rangeExpr = new \Zend_Db_Expr("FLOOR(({$priceExpression}) / {$range}) + 1");

        $select->reset(\Zend_Db_Select::GROUP);
        $select->columns(['range' => $rangeExpr, 'count' => $countExpr])
            ->group($rangeExpr)
            ->order("({$rangeExpr}) ASC");
        return $this->getConnection()->fetchPairs($select);
    }

    /**
     * Remove where conditions for price - allow to select multiple price ranges
     *
     * @param Select $select
     * @return Select
     */
    private function removePriceConditions($select)
    {
        $whereConditions = $select->getPart(Select::WHERE);
        $select->reset(Select::WHERE);

        foreach ($whereConditions as $key => $condition) {
            if (false !== strpos($condition, 'min_price')) {
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
        return $select;
    }

    /**
     * Retrieve min and max values for price slider
     *
     * @param Select $select
     * @return array
     */
    private function fetchPriceData($select)
    {
        $select->columns(
            [
                'min_value' => new \Zend_Db_Expr('MIN(e.min_price)'),
                'max_value' => new \Zend_Db_Expr('MAX(e.min_price)'),
            ]
        );
        $select->reset(\Zend_Db_Select::GROUP);
        return $this->getConnection()->fetchRow($select);
    }

    /**
     * Retrieve clean select with joined price index table
     *
     * @return Select
     */
    private function getSelect()
    {
        $collection = $this->layer->getProductCollection();
        $collection->addPriceData(
            $this->session->getCustomerGroupId(),
            $this->storeManager->getStore()->getWebsiteId()
        );
        $select = clone $collection->getSelect();
        return $this->prepareSelectForCount($select);
    }

    /**
     * @return Select
     */
    private function getParentSelect()
    {
        $collection = $this->layer->getProductCollection();
        $collection->addPriceData(
            $this->session->getCustomerGroupId(),
            $this->storeManager->getStore()->getWebsiteId()
        );
        $select = clone $collection->getSelect();
        $select->reset(Select::WHERE);
        return $this->prepareSelectForCount($select);
    }

    /**
     * Prepare select to get count
     *
     * @param Select $select
     * @return Select
     * @throws \Zend_Db_Select_Exception
     */
    private function prepareSelectForCount(Select $select)
    {
        $select->reset(Select::COLUMNS)
            ->reset(Select::ORDER)
            ->reset(Select::LIMIT_COUNT)
            ->reset(Select::LIMIT_OFFSET);

        $fromPart = $select->getPart(Select::FROM);
        if (!isset($fromPart[ProductCollection::INDEX_TABLE_ALIAS])
            || !isset($fromPart[ProductCollection::MAIN_TABLE_ALIAS])
        ) {
            return $select;
        }

        $select->setPart(Select::FROM, $this->prepareFromPartForCountSelect($fromPart));
        $select->setPart(
            Select::WHERE,
            $this->prepareWherePartForCountSelect($select->getPart(Select::WHERE))
        );

        $priceIndexJoinConditions = explode(
            'AND',
            $fromPart[ProductCollection::INDEX_TABLE_ALIAS]['joinCondition']
        );
        $excludeJoinPart = ProductCollection::MAIN_TABLE_ALIAS . '.entity_id';
        foreach ($priceIndexJoinConditions as $condition) {
            if (strpos($condition, $excludeJoinPart) !== false) {
                continue;
            }
            $select->where($this->replaceTableAlias($condition));
        }
        $select->where($this->_getPriceExpression($select) . ' IS NOT NULL');

        return $select;
    }

    /**
     * Prepare FROM part for retrieving count
     *
     * @param array $fromPart
     * @return array
     */
    private function prepareFromPartForCountSelect($fromPart)
    {
        $fromPartPrepared = [];

        $priceIndexJoinPart = $fromPart[ProductCollection::INDEX_TABLE_ALIAS];
        $priceIndexJoinPart['joinType'] = Select::FROM;
        $priceIndexJoinPart['joinCondition'] = null;
        $fromPartPrepared[ProductCollection::MAIN_TABLE_ALIAS] = $priceIndexJoinPart;

        // Add join to catalog_product_entity,
        // because we should support join conditions and where conditions of this table
        $fromPartPrepared[self::PRODUCT_ENTITY_TABLE_ALIAS] = [
            'joinType' => Select::INNER_JOIN,
            'schema' => null,
            'tableName' => $this->getTable('catalog_product_entity'),
            'joinCondition' => self::PRODUCT_ENTITY_TABLE_ALIAS . '.entity_id = e.entity_id'
        ];

        foreach ($fromPart as $key => $item) {
            if (!array_key_exists($key, $fromPartPrepared) && $key != ProductCollection::INDEX_TABLE_ALIAS) {
                $fromPartPrepared[$key] = $item;
                $fromPartPrepared[$key]['joinCondition'] = $this->replaceTableAlias($item['joinCondition']);
            }
        }

        return $fromPartPrepared;
    }

    /**
     * Prepare WHERE part for retrieving count
     *
     * @param array $wherePart
     * @return mixed
     */
    private function prepareWherePartForCountSelect($wherePart)
    {
        $wherePartPrepared = [];
        foreach ($wherePart as $key => $item) {
            $wherePartPrepared[$key] = $this->replaceTableAlias($item);
        }
        return $wherePartPrepared;
    }

    /**
     * Replace table alias in condition string:
     * 'e' -> 'product_entity'
     * 'price_index' -> 'e'
     *
     * @param string|null $conditionString
     * @return string
     */
    private function replaceTableAlias($conditionString)
    {
        if ($conditionString == null) {
            return $conditionString;
        }

        $pattern = '/\b%s\b\./';
        $patternQuoted = '/%s\./';
        $replacement = '%s.';
        $connection = $this->getConnection();

        $productEntityTableAliasReplacements = [
            sprintf($replacement, self::PRODUCT_ENTITY_TABLE_ALIAS),
            sprintf($replacement, $connection->quoteIdentifier(self::PRODUCT_ENTITY_TABLE_ALIAS))
        ];

        $indexTableAliasPatterns = [
            sprintf($pattern, ProductCollection::INDEX_TABLE_ALIAS),
            sprintf($patternQuoted, $connection->quoteIdentifier(ProductCollection::INDEX_TABLE_ALIAS))
        ];

        $mainTableAliasQuoted = $connection->quoteIdentifier(ProductCollection::MAIN_TABLE_ALIAS);
        $mainTableAliasPatterns = [
            sprintf($pattern, ProductCollection::MAIN_TABLE_ALIAS),
            sprintf($patternQuoted, $mainTableAliasQuoted)
        ];
        $mainTableTableAliasReplacements = [
            sprintf($replacement, ProductCollection::MAIN_TABLE_ALIAS),
            sprintf($replacement, $mainTableAliasQuoted)
        ];

        $conditionString = preg_replace(
            $mainTableAliasPatterns,
            $productEntityTableAliasReplacements,
            $conditionString
        );
        return preg_replace(
            $indexTableAliasPatterns,
            $mainTableTableAliasReplacements,
            $conditionString
        );
    }

    /**
     * Get where conditions
     *
     * @param FilterInterface $filter
     * @param array $intervals
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getWhereConditions(FilterInterface $filter, $intervals = [])
    {
        $whereConditions = [];

        foreach ($intervals as $interval) {
            list($from, $to) = $interval;
            if ($from === '' && $to === '') {
                return $whereConditions;
            }

            $select = $filter->getLayer()->getProductCollection()->getSelect();
            $priceExpr = $this->_getPriceExpression($select, false);

            if ($to !== '') {
                $to = (double)$to;
                if ($from == $to) {
                    $to += self::MIN_POSSIBLE_PRICE;
                }
            }
            $connection = $this->getConnection();
            $conditions = [];
            if ($from !== '') {
                $conditions[] = $connection->quoteInto("{$priceExpr} >= ?", $this->_getComparingValue($from));
            }
            if ($to !== '') {
                $decrease = true;
                if ($this->config->isPriceSliderEnabled() || $this->config->isPriceFromToEnabled()) {
                    // Do not decrease 'to' price, when slider or 'from-to' inputs are enabled
                    $decrease = false;
                }
                $conditions[] = $connection->quoteInto(
                    "{$priceExpr} < ?",
                    $this->_getComparingValue($to, $decrease)
                );
            }
            $whereConditions[] = '(' . implode(' AND ', $conditions) . ')';
        }

        return $whereConditions;
    }
}
