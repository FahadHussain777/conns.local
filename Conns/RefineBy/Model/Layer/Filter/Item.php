<?php

namespace Conns\RefineBy\Model\Layer\Filter;

use Magento\Framework\App\ObjectManager;

class Item extends \Magento\Catalog\Model\Layer\Filter\Item
{
    public function getRemoveUrl(){
        return $this->_url->getRemoveFilterUrl(
            $this->getFilter()->getRequestVar(),
            $this->getValue(),
            [$this->_htmlPagerBlock->getPageVarName() => null]
        );
    }
    public function getUrl(){
        return $this->_url->getFilterUrl(
            $this->getFilter()->getRequestVar(),
            $this->getValue(),
            [$this->_htmlPagerBlock->getPageVarName() => null],
            false
        );
    }
    public function isActive(){
        $values = ObjectManager::getInstance()->create(
            \Conns\RefineBy\Model\Url\Builder::class
        )
            ->getValuesFromUrl($this->getFilter()->getRequestVar());
        if(!empty($values)){
            return in_array($this->getValue(), $values);
        }
    }

    public function getRequestVar(){
        return $this->getFilter()->getRequestVar();
    }
}