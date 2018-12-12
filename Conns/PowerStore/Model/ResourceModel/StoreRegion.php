<?php

namespace Conns\PowerStore\Model\ResourceModel;

class StoreRegion extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('conns_powerstore_region','id');
    }
}
