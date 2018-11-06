<?php

namespace Conns\LayeredNavigation\Model\ResourceModel\MonthlyPayment;
use Magento\Framework\App\ObjectManager;

class Collection extends \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection
{
    const XML_PATH_MONTHLY_PAYMENT_INTERVAL = 'connsrefineby/general/price_interval';
    protected $_addedFilters = [];

    public function addFieldToFilter($field, $condition = null)
    {
        if(is_string($field)){
            $this->_addedFilters[$field] = $condition;
        }
        return parent::addFieldToFilter($field, $condition);
    }

    public function addCategoriesFilter(array $categoriesFilter)
    {
        $this->addFieldToFilter('category_ids', $categoriesFilter);
        return $this;
    }

    public function getAddedFilters(){
        return $this->_addedFilters;
    }

    public function updateSearchCriteriaBuilder(){
        $searchCriteriaBuilder = ObjectManager::getInstance()
            ->create(\Magento\Framework\Api\Search\SearchCriteriaBuilder::class);
        $this->setSearchCriteriaBuilder($searchCriteriaBuilder);
        return $this;
    }
    protected function _prepareStatisticsData(){
        $this->_renderFilters();
        return parent::_prepareStatisticsData();
    }

    public function getFacetedData($field,$min=0,$max=0)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $scopeConfig = $objectManager->create(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $interval = $this->_scopeConfig->getValue(self::XML_PATH_MONTHLY_PAYMENT_INTERVAL, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if(!isset($interval)){
            $interval = 25;
        }
        $data= [];
        $result = parent::getFacetedData($field);
        foreach ($result as $key => $value){
            $data[$result[$key]['value']]['value'] = $result[$key]['value'];
            $data[$result[$key]['value']]['count'] = $result[$key]['count'];
        }
        return $data;
    }
}