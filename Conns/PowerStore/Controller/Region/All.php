<?php

namespace Conns\PowerStore\Controller\Region;


class All extends \Magento\Framework\App\Action\Action
{
    protected $resultPageFactory;
    protected $helperLocator;
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \BrainActs\StoreLocator\Helper\Data $locatorHelper
    ){
        $this->resultPageFactory = $resultPageFactory;
        $this->helperLocator = $locatorHelper;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('All Store Locations'));
        $breadcrumbs = $resultPage->getLayout()->getBlock('breadcrumbs');
        $breadcrumbs->addCrumb('home', [
                'label' => __('Home'),
                'title' => __('Home'),
                'link' => $this->_url->getUrl('')
            ]
        );
        $breadcrumbs->addCrumb('conns_powerstore_store', [
                'label' => __('Store Locator'),
                'title' => __('Store Locator'),
                'link' => $this->_url->getUrl($this->helperLocator->getLocatorRoute())
            ]
        );
        $breadcrumbs->addCrumb('conns_powerstore_allstore', [
                'label' => __(' All Store Locations '),
                'title' => __('All Store Locations')
            ]
        );
        return $resultPage;
    }
}