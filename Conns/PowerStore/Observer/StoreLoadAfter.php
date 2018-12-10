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
        //echo "<pre>asa";
        $store = $observer->getEvent()->getDataObject();
        $hoursCollection = $this->storeHoursCollectionFactory->create();
        $hoursCollection->addFieldToFilter('locator_id',$store->getId())->setOrder('dow','ASC');;
        foreach ($hoursCollection as $hour){
            $open = $hour['open'];
            $open =  gmdate("H i s", $open);
            $open = explode(' ',$open);
            $close = $hour['close'];
            $close =  gmdate("H i s", $close);
            $close = explode(' ',$close);
            $time[$hour['dow']] = [
                'enabled' => 1,
                'open' => [0=>$open[0],1=>$open[1],2=>$open[2]],
                'close' => [0=>$close[0],1=>$close[1],2=>$close[2]]
            ];
            $store->setData('hours',$time);
            $store->setData('fahad','asasasa');
            $store->setData('website','asasasa');

        }
        //print_r(get_class_methods());exit;
    }

}