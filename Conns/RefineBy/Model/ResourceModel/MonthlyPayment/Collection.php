<?php

namespace Conns\RefineBy\Model\ResourceModel\MonthlyPayment;
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

    public function getFacetedData($field,$all=[])
    {
        if(empty($all)){
            return false;
        }
        $interval = $this->_scopeConfig->getValue(self::XML_PATH_MONTHLY_PAYMENT_INTERVAL, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if(!isset($interval)){
            $interval = 25;
        }
        $data= [];
        sort($all);
        foreach ($all as $key=>$value){
            $all[$key] = ceil($value);
        }
        $max = max($all);
        $from = 0;
        $to = $interval;
        while($to <= $max+$interval){
            $bucket = array_filter(
                $all,
                function ($value) use($from,$to) {
                    return ($value >= $from && $value <= ($to-1));
                }
            );
            if(!empty($bucket)){
                    $data[$from.'_'.($to-1)] = ['value' => $from.'_'.($to-1), 'count' => count($bucket)];
            }
            $from += $interval;
            $to += $interval;
        }
        return $data;
    }
}