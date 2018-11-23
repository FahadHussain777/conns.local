<?php

namespace Conns\ProductCompare\Block\Product\Compare;

class ListCompare extends \Magento\Catalog\Block\Product\Compare\ListCompare
{
    protected $redirect;
    public function __construct(
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        \Magento\Catalog\Model\ResourceModel\Product\Compare\Item\CollectionFactory $itemCollectionFactory,
        \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
        \Magento\Customer\Model\Visitor $customerVisitor,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer,
        array $data = []
    ){
        $this->redirect = $redirect;
        parent::__construct(
            $context,
            $urlEncoder,
            $itemCollectionFactory,
            $catalogProductVisibility,
            $customerVisitor,
            $httpContext,
            $currentCustomer,
            $data
        );
    }

    public function getBackUrl(){
        $url =  $this->redirect->getRefererUrl();
        if(strpos($url,'product_compare') !== false){
            return $this->getBaseUrl();
        }
        return $url;
    }
}
