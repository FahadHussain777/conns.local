<?php

namespace Conns\PowerStore\Block\Region;


use Magento\Framework\View\Element\Template;

class All extends \Magento\Framework\View\Element\Template
{
    protected $collectionFactory;
    protected $locatorFactory;
    protected $hourCollectionFactory;
    protected $helperLocator;

    public function __construct(
        Template\Context $context,
        \Conns\PowerStore\Model\ResourceModel\StoreRegion\CollectionFactory $collectionFactory,
        \Conns\PowerStore\Model\ResourceModel\StoreHours\Collection $hourCollectionFactory,
        \BrainActs\StoreLocator\Model\LocatorFactory $locatorFactory,
        \BrainActs\StoreLocator\Helper\Data $locatorHelper,
        array $data = []
    ){
        $this->collectionFactory = $collectionFactory;
        $this->locatorFactory = $locatorFactory;
        $this->hourCollectionFactory = $hourCollectionFactory;
        $this->helperLocator = $locatorHelper;
        parent::__construct($context, $data);
    }

    function _prepareLayout(){}

    public function getRegionCollection(){
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('enabled',1);
        $collection->addOrder('region','asc');
        $collection->addOrder('city','asc');
        return $collection;
    }

    public function getRelatedStores($region_id){
        $locator = $this->locatorFactory->create();
        return $locator->getStoresByRegion($region_id);
    }

    public function getStore($store_id){
        $locator = $this->locatorFactory->create();
        return $locator->load($store_id);
    }

    public function getAggregateHours($store_id){
        $hours = $this->hourCollectionFactory;
        $hours->addFieldToFilter('locator_id',$store_id)->setOrder('dow','ASC');
        foreach ($hours as $value){

            $value->setData('open',date('H:i:s',(int)$value->getData('open')));
            $value->setData('close',date('H:i:s',(int)$value->getData('close')));

        }
        $days = array();
        foreach ($hours as $data) {
            $days[$data->getDow()] = $data;
        }
        $data = array();
        if (count($days)) {
            $dayArr = $this->getArrayOfDays();
            $numDays = count($dayArr);
            $groups = array();
            $group = 0;
            for ($i = 0; $i < $numDays; $i++) {
                if (!isset($days[$i])) {
                    if (isset($groups[$group])) {
                        $group++;
                    }
                    continue;
                }

                if (!isset($groups[$group])) {
                    $groups[$group] = array('start' => substr($dayArr[$i], 0, 3), 'end' => null, 'open' => $days[$i]['open'], 'close' => $days[$i]['close']);
                } else {
                    if (($groups[$group]['open'] == $days[$i]['open']) && ($groups[$group]['close'] == $days[$i]['close'])) {
                        $groups[$group]['end'] = substr($dayArr[$i], 0, 3);
                    } else {
                        $group++;
                        $groups[$group] = array('start' => substr($dayArr[$i], 0, 3), 'end' => null, 'open' => $days[$i]['open'], 'close' => $days[$i]['close']);
                    }
                }
            }

            foreach($groups as $group) {
                $title = (!empty($group['end']) ? ($group['start'] . '-' . $group['end']) : $group['start']);
                $value = date('g:ia', strtotime($group['open'])) . '-' . date('g:ia', strtotime($group['close']));
                $data[$title] = $value;
            }
        }
        return $data;
    }

    public function getStoreUrl($regionKey,$storeKey){
        $locatorIdentifier = $this->helperLocator->getLocatorRoute();
        return $this->getBaseUrl().$locatorIdentifier.'/'.$regionKey.'/'.$storeKey;
    }
    public function getArrayOfDays() {
        return array(
            0 => 'Monday',
            1 => 'Tuesday',
            2 => 'Wednesday',
            3 => 'Thursday',
            4 => 'Friday',
            5 => 'Saturday',
            6 => 'Sunday'
        );
    }
}