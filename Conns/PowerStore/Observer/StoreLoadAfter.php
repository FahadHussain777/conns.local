<?php

namespace Conns\PowerStore\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use function PHPSTORM_META\type;

class StoreLoadAfter implements ObserverInterface
{

    protected $storeHoursCollectionFactory;

    public function __construct(
        \Conns\PowerStore\Model\ResourceModel\StoreHours\CollectionFactory $storeHoursCollectionFactory
    ){
        $this->storeHoursCollectionFactory = $storeHoursCollectionFactory;
    }
    public function execute(Observer $observer)
    {
        $store = $observer->getEvent()->getDataObject();
        $hoursCollection = $this->storeHoursCollectionFactory->create();
        $hoursCollection->addFieldToFilter('locator_id',$store->getId())->setOrder('dow','ASC');
        $data = [];
        if(count($hoursCollection) !== 0) {
            foreach ($hoursCollection as $key => $hour) {
                $data['enabled_'.$hour['dow']] = 1;
                $open = gmdate("H,i,s", $hour['open']);
                $data['open_'.$hour['dow']] = $open;
                $close = gmdate("H,i,s", $hour['close']);
                $data['close_'.$hour['dow']] = $close;
            }
        }
        $store->setData('hours',$data);
    }

}