<?php
/**
 * Copyright © 2018 Conn's. All rights reserved.
 */

namespace Conns\RefineBy\Model\Layer;

/**
 * Class ItemCollectionProvider
 * @package Conns\RefineBy\Model\Layer
 */
class ItemCollectionProvider implements \Magento\Catalog\Model\Layer\ItemCollectionProviderInterface
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Conns\RefineBy\Model\ResourceModel\Fulltext\CollectionFactory
     */
    private $collectionFactory;

    /**
     * ItemCollectionProvider constructor.
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Conns\RefineBy\Model\ResourceModel\Fulltext\CollectionFactory $collectionFactory
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Conns\RefineBy\Model\ResourceModel\Fulltext\CollectionFactory $collectionFactory
    ){
        $this->storeManager = $storeManager;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @param \Magento\Catalog\Model\Category $category
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
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