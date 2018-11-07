<?php

namespace Conns\LayeredNavigation\Model\Layer;


class MonthlyPaymentCollectionProvider implements \Magento\Catalog\Model\Layer\ItemCollectionProviderInterface
{
    private $storeManager;
    private $collectionFactory;
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Conns\LayeredNavigation\Model\ResourceModel\MonthlyPayment\CollectionFactory $collectionFactory
    ){
        $this->storeManager = $storeManager;
        $this->collectionFactory = $collectionFactory;
    }
    public function getCollection(\Magento\Catalog\Model\Category $category)
    {
        if ($category->getId() == $this->storeManager->getStore()->getRootCategoryId()) {
            $collection = $this->collectionFactory->create(['searchRequestName' => 'quick_search_container']);
        } else {
            $collection = $this->collectionFactory->create();
            $collection->addCategoryFilter($category);
        }
        return $collection;
    }

}