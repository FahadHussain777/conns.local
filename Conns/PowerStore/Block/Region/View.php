<?php

namespace Conns\PowerStore\Block\Region;

use Magento\Framework\View\Element\Template;

class View extends \Magento\Framework\View\Element\Template
{
    private $mapRegions = [
        'nevada' => [],
        'arizona' => [],
        'colorado' => [],
        'new-mexico' => [],
        'oklahoma' => [],
        'louisiana' => [],
        'mississippi' => [],
        'georgia' => [],
        'south-carolina' => [],
        'north-carolina' => [],
        'tennessee' => [],
        'alabama' => [],
        'virginia' => []
    ];
    protected $locatorHelper;
    protected $regionCollectionFactory;

    public function __construct(
        Template\Context $context,
        \BrainActs\StoreLocator\Helper\Data $locatorHelper,
        \Conns\PowerStore\Model\ResourceModel\StoreRegion\CollectionFactory $regionCollectionFactory,
        array $data = []
    ){
        $this->locatorHelper = $locatorHelper;
        $this->regionCollectionFactory = $regionCollectionFactory;
        parent::__construct($context, $data);
    }

    function _prepareLayout(){}

    public function getSearchPageUrl(){
        return $this->getBaseUrl().$this->locatorHelper->getLocatorRoute().'/search';
    }

    public function getCheckResultsUrl(){
        return $this->getBaseUrl().'powerstore/locator/checkresults';
    }

    public function getZipRequestUrl(){
        return $this->getBaseUrl().'powerstore/locator/geolocation';
    }

    public function getRegionPageUrl($urlKey){
        return $this->getBaseUrl().$this->locatorHelper->getLocatorRoute().'/'.$urlKey;
    }

    public function getAllRegionPageUrl(){
        return $this->getBaseUrl().$this->locatorHelper->getLocatorRoute().'/all';
    }

    public function getMapRegions(){
        $collection = $this->regionCollectionFactory->create();
        $collection->addFieldToFilter('enabled',1);
        $regions = [];
        foreach ($collection as $region){
            $regions[$region['url_key']] = $region->getData();
        }
        $regions = array_intersect_key($regions,$this->mapRegions);
        return $regions;
    }

    public function getAvailableDistance()
    {

        $distanceValue = $this->_scopeConfig->getValue(
            'brainacts_storelocator/search/distance_list',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $list = explode(',', $distanceValue);

        return $list;
    }

    public function getUnit($distance = null)
    {
        $distance = strtolower($distance);
        if ($distance == 'all') {
            return __(' ');
        }
        $units = $this->_scopeConfig->getValue(
           'brainacts_storelocator/search/distance_unit',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );

        if ($units == 1) {
            return __('Km(s)');
        }

        return __('Mile(s)');
    }

    public function getAllRegions($selected = null){
        $collection = $this->regionCollectionFactory->create();
        $collection->addFieldToFilter('enabled',1)->addOrder('region','asc')->addOrder('city','asc');
        $sortedRegions = $this->sortSpecial($collection);
        $regions = [];
        foreach ($sortedRegions as $region) {
            $city = $region->getCity();
            if (empty($city)) {
                $regions[$region->getRegion()] = $region->getData();
                if ($region->getId() == $selected) {
                    $regions[$region->getRegion()]['selected'] = true;
                }
            } else {
                $regions[$region->getRegion()]['cities'][$city] = $region->getData();
                if ($region->getId() == $selected) {
                    $regions[$region->getRegion()]['open'] = true;
                    $regions[$region->getRegion()]['cities'][$city]['selected'] = true;
                }
            }
        }
        return $regions;
    }

    protected function sortSpecial($collection){
        $collectionNew = [];
        $viewAllObj = null;
        foreach($collection as $region){
            if($region->getUrlKey() == "allsites"){
                $viewAllObj = $region;
            }else{
                $collectionNew[] = $region;
            }
        }
        if($viewAllObj != null){
            array_push($collectionNew, $viewAllObj);
        }

        return $collectionNew;
    }
}
