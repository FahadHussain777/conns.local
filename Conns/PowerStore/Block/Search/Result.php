<?php

namespace Conns\PowerStore\Block\Search;

use BrainActs\StoreLocator\Block\Search;
use BrainActs\StoreLocator\Model\ResourceModel\LocatorFactory;
use BrainActs\StoreLocator\Model\ResourceModel\Locator\CollectionFactory as LocatorCollectionFactory; //@codingStandardsIgnoreLine
use Magento\Framework\View\Element\Template;

class Result extends Search
{

    private $viewOnMap = null;

    private $personalPageAllow = null;

    private $hourCollectionFactory;

    private $storeRegion;

    private $locatorHelper;

    public function __construct(
        \BrainActs\StoreLocator\Api\LocatorRepositoryInterface $locatorRepository,
        LocatorCollectionFactory $locatorCollectionFactory,
        LocatorFactory $locatorFactory,
        \Conns\PowerStore\Model\ResourceModel\StoreHours\Collection $hourCollectionFactory,
        \Conns\PowerStore\Model\StoreRegion $storeRegion,
        \Magento\Framework\Image\AdapterFactory $imageFactory,
        \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory,
        \BrainActs\StoreLocator\Model\Config\Source\Map\Style $style,
        \BrainActs\StoreLocator\Helper\Data $locatorHelper,
        \Magento\Framework\Locale\Resolver $locale,
        Template\Context $context,
        array $data = []
    ) {
        $this->hourCollectionFactory = $hourCollectionFactory;
        $this->storeRegion = $storeRegion;
        $this->locatorHelper = $locatorHelper;

        parent::__construct(
            $locatorRepository,
            $locatorCollectionFactory,
            $locatorFactory,
            $imageFactory,
            $regionCollectionFactory,
            $countryCollectionFactory,
            $jsonEncoder,
            $httpClientFactory,
            $style,
            $locale,
            $context,
            $data
        );
        $this->setTemplate('Conns_PowerStore::search/items/list.phtml');
    }


    public function getCachePath()
    {
        $cacheDir = $this->locatorDirectory->getAbsolutePath('storelocator/cache/250/250');
        return $cacheDir;
    }


    public function getNotFoundMessage()
    {
        return __($this->_scopeConfig->getValue(
            'brainacts_storelocator/search/error_message',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ));
    }

    public function getUnit($distance)
    {
        if (empty($distance)) {
            return '';
        }
        $defaultUnit = $this->_scopeConfig->getValue(
            'brainacts_storelocator/search/distance_unit',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if ($defaultUnit == 1) {
            $postfix = __('Km');
        } else {
            $postfix = __('Miles');
        }
        return round($distance, 2) . ' ' . $postfix;
    }

    public function getRequireJsonField()
    {
        return ['latitude', 'longitude', 'name'];
    }

    public function toJsonItem($item)
    {
        $fields = $this->getRequireJsonField();
        $array = $item->toArray($fields);
        $address = $item->getAddress();
        $street = $address->getStreet()[0];
        $city = $address->getCity();
        $state = $address->getRegionCode();
        $telephone = $address->getTelephone();
        $postalCode = $address->getPostcode();
        $popupContent = $this->getLayout()
            ->createBlock('Conns\PowerStore\Block\Search\MarkerPopUp')
            ->setStoreName($item->getName())
            ->setAddress(['street'=> $street,'city'=>$city,'state'=>$state,'postal_code'=>$postalCode, 'telephone' => $telephone ])
            ->setAggregateHours($this->getAggregateHours($item->getLocatorId()))
            ->setStoreUrl($this->getStoreUrl($item->getRegionAssigned(),$item->getIdentiFier()))
            ->toHtml();
        $array["popupContent"] = $popupContent;
        return json_encode($array, JSON_HEX_APOS);
    }

    public function isViewOnMap()
    {
        if ($this->viewOnMap == null) {
            $this->viewOnMap = $this->_scopeConfig->getValue(
                'brainacts_storelocator/search/allow_map',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        }

        return $this->viewOnMap;
    }

    public function isPersonalAllow()
    {
        if ($this->personalPageAllow == null) {
            $this->personalPageAllow = $this->_scopeConfig->getValue(
                'brainacts_storelocator/item/separate_page',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        }

        return $this->personalPageAllow;
    }

    public function getLocatorUrl($identifier)
    {
        $route = $this->personalPageAllow = $this->_scopeConfig->getValue(
            'brainacts_storelocator/general/page_url',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $this->getUrl($route . '/' . $identifier);
    }

    public function getDirection($item)
    {
        $place = [];
        $place[] = $item->getLatitude();
        $place[] = $item->getLongitude();

        return 'https://www.google.com/maps/place/' . implode(',', $place);
    }

    public function getAggregateHours($store_id){
        $hours = $this->hourCollectionFactory;
        $hours->addFieldToFilter('locator_id',$store_id)->setOrder('dow','ASC');
        //echo "<pre>";print_r($hours->toArray());exit;
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

    public function getStoreUrl($regionId,$storeUrlKey){
        $region = $this->storeRegion->load($regionId);
        $regionKey = $region->getUrlKey();
        $locatorIdentifier = $this->locatorHelper->getLocatorRoute();
        return $this->getBaseUrl().$locatorIdentifier.'/'.$regionKey.'/'.$storeUrlKey;
    }


}