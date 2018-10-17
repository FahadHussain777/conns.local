<?php
/*
 * Copyright ©2016 Conn's. All rights reserved.
 */

namespace Conns\Pronav\Model\ResourceModel\Navigation;

/**
 * Class Collection
 * @package Conns\Pronav\Model\ResourceModel\Navigation
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'pronav_id';

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Conns\Pronav\Model\Navigation','Conns\Pronav\Model\ResourceModel\Navigation');
    }
}
