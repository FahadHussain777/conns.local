<?php
namespace Conns\PowerStore\Model;

class StoreHours extends \Magento\Framework\Model\AbstractModel implements \Conns\PowerStore\Api\Data\StoreHoursInterface, \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG = 'conns_powerstore_storehours';

    protected function _construct()
    {
        $this->_init('Conns\PowerStore\Model\ResourceModel\StoreHours');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function loadByLocatorId($dow,$locatorId){
        $id = $this->getResource()->loadByLocatorId($dow,$locatorId);
        return $this->load($id);
    }
}
