<?php

namespace Conns\PowerStore\Block;

class View extends \BrainActs\StoreLocator\Block\View
{
    protected $psimageHelper;

    protected $hourCollectionFactory;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \BrainActs\StoreLocator\Model\Locator $locator,
        \BrainActs\StoreLocator\Model\LocatorFactory $locatorFactory,
        \BrainActs\StoreLocator\Helper\Image $imageHelper,
        \Conns\PowerStore\Helper\Image $psimageHelper,
        \Conns\PowerStore\Model\ResourceModel\StoreHours\Collection $hourCollectionFactory,
        array $data = []
    ) {
        $this->hourCollectionFactory = $hourCollectionFactory;
        parent::__construct($context,$locator,$locatorFactory,$imageHelper,$data);
        $this->psimageHelper = $psimageHelper;
    }

    public function resize($source,$width = 0,$height = 0)
    {
        return $this->psimageHelper->resize($source,$width,$height);
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

    public function getAggregateHours(){
        $hours = $this->hourCollectionFactory;
        $hours->addFieldToFilter('locator_id',$this->getLocator()->getId())->setOrder('dow','ASC');
        foreach ($hours as $value){
            $value->setData('open',date('H:i:s',$value->getData('open')));
            $value->setData('close',date('H:i:s',$value->getData('close')));
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

    public function getLocatorUrl($identifier)
    {
        $route = $this->personalPageAllow = $this->_scopeConfig->getValue(
            'brainacts_storelocator/general/page_url',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $this->getUrl($route . '/' . $identifier);
    }

    public function getStates(){
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $states = $objectManager
            ->create('Magento\Directory\Model\RegionFactory')
            ->create()->getCollection()->addFieldToFilter('country_id','US');
        $states = array_column($states->getData(), 'name');
        $exceptions = [
            0 => "Armed Forces Africa",
            1 => "Armed Forces Americas",
            2 => "Armed Forces Canada",
            3 => "Armed Forces Europe",
            4 => "Armed Forces Middle East",
            5 => "Armed Forces Pacific"
        ];
        $states = array_diff($states,$exceptions);
        array_unshift($states,"-- Please select --");
        return $states;
    }
}