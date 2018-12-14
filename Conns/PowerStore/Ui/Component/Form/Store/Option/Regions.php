<?php
/*
 * Copyright ©2016 Conn's. All rights reserved.
 */

namespace Conns\PowerStore\Ui\Component\Form\Store\Option;

use Conns\PowerStore\Model\ResourceModel\StoreRegion\CollectionFactory;

class Regions implements \Magento\Framework\Option\ArrayInterface
{

    protected $regions;


    public function __construct(
        CollectionFactory $collectionFactory
    ){
        $this->regions = $collectionFactory->create();
        $this->regions->setOrder('region','asc');
    }


    public function toOptionArray()
    {
        $optionArray = [];
        foreach ($this->regions as $region){
            $optionArray[] = [
                'value' => $region->getId(),
                'label' => __($region->getRegion().', '.$region->getCity())
            ];
        }
        return $optionArray;
    }

    public function optionArray(){
        $optionArray = [];
        foreach ($this->regions as $region){
            $city = $region->getCity();
            if(!empty($city)){
                $format = '%s, %s';
                $regionTitle = sprintf($format,$region->getRegion(),$city);
            }else{
                $format = '%s';
                $regionTitle = sprintf($format,$region->getRegion());
            }
            $optionArray[$region->getId()] = $regionTitle;
        }
        return $optionArray;
    }
}