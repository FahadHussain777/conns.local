<?php
/*
 * Copyright ©2016 Conn's. All rights reserved.
 */

namespace Conns\Pronav\Model;

/**
 * Class Navigation
 * @package Conns\Pronav\Model
 */
class Navigation extends \Magento\Framework\Model\AbstractModel implements \Conns\Pronav\Api\Data\NavigationInterface, \Magento\Framework\DataObject\IdentityInterface
{
    /**
     * const conns_pronav_navigation
     */
    const CACHE_TAG = 'conns_pronav_navigation';


    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Conns\Pronav\Model\ResourceModel\Navigation::class);
    }

    /**
     * @return array|string[]
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}
