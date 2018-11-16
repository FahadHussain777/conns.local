<?php
/**
 * Copyright © 2018 Conn's. All rights reserved.
 */
namespace Conns\RefineBy\Model\ResourceModel\Fulltext;

use Magento\Framework\App\ObjectManager;

/**
 * Class Collection
 * @package Conns\RefineBy\Model\ResourceModel\Fulltext
 */
class Collection extends \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection
{
    /**
     * @var array
     */
    protected $_addedFilters = [];

    /**
     * @param string $field
     * @param null $condition
     * @return \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if(is_string($field)){
            $this->_addedFilters[$field] = $condition;
        }
        return parent::addFieldToFilter($field, $condition);
    }

    /**
     * @param array $categoriesFilter
     * @return $this|\Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection
     */
    public function addCategoriesFilter(array $categoriesFilter)
    {
        $this->addFieldToFilter('category_ids', $categoriesFilter);
        return $this;
    }

    /**
     * @return array
     */
    public function getAddedFilters(){
        return $this->_addedFilters;
    }

    /**
     * @return $this
     */
    public function updateSearchCriteriaBuilder(){
        $searchCriteriaBuilder = ObjectManager::getInstance()
            ->create(\Magento\Framework\Api\Search\SearchCriteriaBuilder::class);
        $this->setSearchCriteriaBuilder($searchCriteriaBuilder);
        return $this;
    }

    /**
     * @return \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection
     */
    protected function _prepareStatisticsData(){
        $this->_renderFilters();
        return parent::_prepareStatisticsData();
    }
}