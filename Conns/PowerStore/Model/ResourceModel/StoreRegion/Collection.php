<?php

namespace Conns\PowerStore\Model\ResourceModel\StoreRegion;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'id';

    protected function _construct()
    {
        $this->_init('Conns\PowerStore\Model\StoreRegion','Conns\PowerStore\Model\ResourceModel\StoreRegion');
    }
}
