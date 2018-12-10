<?php

namespace Conns\PowerStore\Model\ResourceModel\StoreHours;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Conns\PowerStore\Model\StoreHours','Conns\PowerStore\Model\ResourceModel\StoreHours');
    }
}
