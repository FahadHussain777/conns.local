<?php

namespace Conns\PowerStore\Block;

class View extends \BrainActs\StoreLocator\Block\View
{
    protected $psimageHelper;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \BrainActs\StoreLocator\Model\Locator $locator,
        \BrainActs\StoreLocator\Model\LocatorFactory $locatorFactory,
        \BrainActs\StoreLocator\Helper\Image $imageHelper,
        \Conns\PowerStore\Helper\Image $psimageHelper,
        array $data = []
    ) {
        parent::__construct($context,$locator,$locatorFactory,$imageHelper,$data);
        $this->psimageHelper = $psimageHelper;
    }

    public function resize($source)
    {
        return $this->psimageHelper->resize($source);
    }

    public function getAggregateHours(){
        return [
            'Mon-Tue' => '12:00am-12:00am',
            'Wed' => '1:00am-12:00am',
            'Thu' => '5:00am-12:00am',
            'Fri-Sun' => '12:00am-12:00am'
        ];
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