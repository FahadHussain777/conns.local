<?php

namespace Conns\PowerStore\Model\ResourceModel;

class StoreHours extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('conns_powerstore_hours','id');
    }

    public function loadByLocatorId($dow,$locatorId)
    {
        $table = $this->getMainTable();
        $connection = $this->getConnection();
        $bind = ['locator_id' => $locatorId];
        $select = $connection->select()->from(
            $table,
            ['id']
        )->where(
            'locator_id = :locator_id'
        );
        $bind['dow'] = $dow;
        $select->where('dow = :dow');
        $hourId = $connection->fetchOne($select, $bind);
        return $hourId;
    }
}
