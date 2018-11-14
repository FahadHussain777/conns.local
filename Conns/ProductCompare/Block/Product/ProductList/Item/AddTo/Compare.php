<?php

namespace Conns\ProductCompare\Block\Product\ProductList\Item\AddTo;


class Compare extends \Magento\Catalog\Block\Product\ProductList\Item\Block
{

    protected $compareHelper;

    public function __construct(
        \Conns\ProductCompare\Block\Context $context,
        array $data = [])
    {
        parent::__construct($context, $data);
    }

    public function getCompareHelper()
    {
        return $this->_compareProduct;
    }

    public function isChecked()
    {
        return rand(0,1)
            ? true
            : false;
    }
}
