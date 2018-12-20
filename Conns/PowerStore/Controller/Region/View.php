<?php

namespace Conns\PowerStore\Controller\Region;

use Magento\Framework\App\RequestInterface;

class View extends \Magento\Framework\App\Action\Action
{
    protected $resultPageFactory;
    protected $request;
    protected $regionModel;
    protected $_scopeConfig;
    protected $storeManager;
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Conns\PowerStore\Model\StoreRegion $region,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        RequestInterface $request
    ){
        $this->storeManager = $storeManager;
        $this->request = $request;
        $this->regionModel = $region;
        $this->resultPageFactory = $resultPageFactory;
        $this->_scopeConfig = $scopeConfig;
        parent::__construct($context);
    }

    public function execute()
    {
        $regionId = $this->request->getParam('id');
        $region = $this->regionModel->load($regionId);
        $state = $region->getData('region');
        $city = $region->getData('city');
        $distance = $this->getDefaultDistance();
        if(!empty($city)){
            $search = $city.', '.$state;
        }
        else{
            $search = $state;
        }
        $this->request->setParams(
            [
                'current_page' => 1,
                'distance' => !empty($distance)?$distance:'50',
                'page' => 1,
                'page_size' => 10,
                'search' => $search,
                'search_type' => 'simple'
            ]
        );

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Find Your Conn\'s HomePlus'));
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
                'link' => $this->storeManager->getStore()->getBaseUrl().$this->getLocatorRoute()
            ]
        );
        $breadcrumbs->addCrumb('conns_powerstor_region', [
                'label' => empty($city)?$state.' Area Store':$city.' Area Store',
                'title' => empty($city)?$state.' Area Store':$city.' Area Store'
            ]
        );

        return $resultPage;
    }

    public function getDefaultDistance()
    {
        $defaultDistance = $this->_scopeConfig->getValue(
            'brainacts_storelocator/search/distance_default',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $defaultDistance;
    }

    public function getLocatorRoute()
    {
        return $this->_scopeConfig->getValue(
            'brainacts_storelocator/general/page_url',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}