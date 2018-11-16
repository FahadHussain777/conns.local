<?php
/**
 * Copyright © 2018 Conn's. All rights reserved.
 */
namespace Conns\RefineBy\Model\Layer\Filter;

use Magento\Framework\App\ObjectManager;

/**
 * Class Item
 * @package Conns\RefineBy\Model\Layer\Filter
 */
class Item extends \Magento\Catalog\Model\Layer\Filter\Item
{
    /**
     * @return string
     */
    public function getRemoveUrl(){
        return $this->_url->getRemoveFilterUrl(
            $this->getFilter()->getRequestVar(),
            $this->getValue(),
            [$this->_htmlPagerBlock->getPageVarName() => null]
        );
    }

    /**
     * @return string
     */
    public function getUrl(){
        return $this->_url->getFilterUrl(
            $this->getFilter()->getRequestVar(),
            $this->getValue(),
            [$this->_htmlPagerBlock->getPageVarName() => null],
            false
        );
    }

    /**
     * @return bool
     */
    public function isActive(){
        $values = ObjectManager::getInstance()->create(
            \Conns\RefineBy\Model\Url\Builder::class
        )
            ->getValuesFromUrl($this->getFilter()->getRequestVar());
        if(!empty($values)){
            return in_array($this->getValue(), $values);
        }
    }

    /**
     * @return mixed
     */
    public function getRequestVar(){
        return $this->getFilter()->getRequestVar();
    }
}