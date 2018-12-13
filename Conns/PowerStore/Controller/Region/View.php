<?php

namespace Conns\PowerStore\Controller\Region;


class View extends \Magento\Framework\App\Action\Action
{
    protected $resultPageFactory;
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory)
    {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Name of Store'));
        $breadcrumbs = $resultPage->getLayout()->getBlock('breadcrumbs');
        $breadcrumbs->addCrumb('home', [
                'label' => __('Home'),
                'title' => __('Home'),
                'link' => $this->_url->getUrl('')
            ]
        );
        $breadcrumbs->addCrumb('conns_powerstore_store', [
                'label' => __('Store Locator'),
                'title' => __('Store Locator')
            ]
        );
        $breadcrumbs->addCrumb('conns_powerstor_region', [
                'label' => __('Region name'),
                'title' => __('Region name')
            ]
        );
        return $resultPage;
    }
}