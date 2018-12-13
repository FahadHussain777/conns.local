<?php

namespace Conns\PowerStore\Model\ResourceModel;

use Magento\Framework\EntityManager\MetadataPool;
use Conns\PowerStore\Api\Data\StoreRegionInterface;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Store\Model\Store;
use Magento\Framework\DB\Select;

class StoreRegion extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    public function __construct(
        Context $context,
        $connectionName = null
    ){
        parent::__construct($context, $connectionName);
    }

    protected function _construct(){
        $this->_init('conns_powerstore_region','id');
    }

    public function checkIdentifier($identifier)
    {
        $select = $this->getConnection()->select();
        $select->from('conns_powerstore_region')->where('url_key = ?',$identifier)->limit(1);
        return $this->getConnection()->fetchOne($select);
    }


}
