<?php

namespace Conns\PowerStore\Model;

class StoreRegion extends \Magento\Framework\Model\AbstractModel implements \Conns\PowerStore\Api\Data\StoreRegionInterface, \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG = 'conns_powerstore_region';

    protected function _construct()
    {
        $this->_init('Conns\PowerStore\Model\ResourceModel\StoreRegion');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}
