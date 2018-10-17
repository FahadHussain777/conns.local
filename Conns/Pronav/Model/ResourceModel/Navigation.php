<?php
/*
 * Copyright ©2016 Conn's. All rights reserved.
 */

namespace Conns\Pronav\Model\ResourceModel;

/**
 * Class Navigation
 * @package Conns\Pronav\Model\ResourceModel
 */
class Navigation extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('conns_pronav','pronav_id');
    }
}
