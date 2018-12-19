<?php

namespace Conns\PowerStore\Block\Search;

class MarkerPopUp extends \Magento\Framework\View\Element\Template
{
    function _prepareLayout(){
        $this->setTemplate('Conns_PowerStore::search/items/popup.phtml');
    }
}