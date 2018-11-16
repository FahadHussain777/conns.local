<?php
/**
 * Copyright © 2018 Conn's. All rights reserved.
 */
namespace Conns\RefineBy\Model\Layer\Filter;

use Magento\Framework\App\ObjectManager;
use Magento\Catalog\Model\Layer\Filter\ItemFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Filter\Item\DataBuilder;
use Magento\Framework\Escaper;
use Magento\Catalog\Model\Layer\Filter\DataProvider\CategoryFactory;
use Conns\RefineBy\Model\Url\Builder;
use Conns\RefineBy\Model\Layer\ItemCollectionProvider;


/**
 * Class Category
 * @package Conns\RefineBy\Model\Layer\Filter
 */
class Category extends \Magento\CatalogSearch\Model\Layer\Filter\Category
{
    /**
     * @var Escaper
     */
    protected $escaper;
    /**
     * @var
     */
    protected $dataProvider;
    /**
     * @var Builder
     */
    protected $urlBuilder;
    /**
     * @var ItemCollectionProvider
     */
    protected $collectionProvider;

    /**
     * Category constructor.
     * @param ItemFactory $filterItemFactory
     * @param StoreManagerInterface $storeManager
     * @param Layer $layer
     * @param DataBuilder $itemDataBuilder
     * @param Escaper $escaper
     * @param CategoryFactory $categoryDataProviderFactory
     * @param Builder $urlBuilder
     * @param ItemCollectionProvider $collectionProvider
     * @param array $data
     */
    public function __construct(
        ItemFactory $filterItemFactory,
        StoreManagerInterface $storeManager,
        Layer $layer,
        DataBuilder $itemDataBuilder,
        Escaper $escaper,
        CategoryFactory $categoryDataProviderFactory,
        Builder $urlBuilder,
        ItemCollectionProvider $collectionProvider,
        array $data = []
    ){
        parent::__construct(
            $filterItemFactory,
            $storeManager,
            $layer,
            $itemDataBuilder,
            $escaper,
            $categoryDataProviderFactory,
            $data
        );
        $this->escaper = $escaper;
        $this->dataProvider = $categoryDataProviderFactory->create(['layer' => $this->getLayer()]);
        $this->urlBuilder = $urlBuilder;
        $this->collectionProvider = $collectionProvider;
    }

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @return $this|\Magento\CatalogSearch\Model\Layer\Filter\Category
     */
    public function apply(\Magento\Framework\App\RequestInterface $request){
        $values = $this->urlBuilder->getValuesFromUrl($this->_requestVar);
        if (!$values) {
            return $this;
        }
        $productCollection = $this->getLayer()->getProductCollection();
        $this->applyToCollection($productCollection);
        $categoryCollection = ObjectManager::getInstance()->create(
            \Magento\Catalog\Model\ResourceModel\Category\Collection::class
        );
        $categoryCollection->addAttributeToFilter('entity_id', ['in' => $values])->addAttributeToSelect('name');
        $categoryItems = $categoryCollection->getItems();
        foreach ($values as $value) {
            if (isset($categoryItems[$value])) {
                $category = $categoryItems[$value];
                $label = $category->getName();
                $this->getLayer()
                    ->getState()
                    ->addFilter($this->_createItem($label, $value));
            }
        }
        return $this;
    }

    /**
     * @return array
     */
    protected function _getItemsData(){
        $values = $this->urlBuilder->getValuesFromUrl($this->_requestVar);
        $productCollection = $this->getLayer()->getProductCollection();
        $collection = $this->collectionProvider->getCollection($this->getLayer()->getCurrentCategory());
        $collection->updateSearchCriteriaBuilder();
        $this->getLayer()->prepareProductCollection($collection);
        foreach ($productCollection->getAddedFilters() as $field => $condition) {
            if ($field === 'category_ids') {
                $collection->addFieldToFilter($field, $this->getLayer()->getCurrentCategory()->getId());
                continue;
            }
            $collection->addFieldToFilter($field, $condition);
        }
        $optionsFacetedData = $collection->getFacetedData('category');
        $category = $this->dataProvider->getCategory();
        $categories = $category->getChildrenCategories();
        if ($category->getIsActive()) {
            foreach ($categories as $category) {
                if ($category->getIsActive()) {
                    if(isset($optionsFacetedData[$category->getId()])){
                        $count = $this->getOptionItemsCount($optionsFacetedData, $category->getId());
                        $this->itemDataBuilder->addItemData(
                            $this->escaper->escapeHtml($category->getName()),
                            $category->getId(),
                            $count
                        );
                    }
                }
            }
        }
        return $this->itemDataBuilder->build();
    }

    /**
     * @param $collection
     * @return $this
     */
    public function applyToCollection($collection){
        $values = $this->urlBuilder->getValuesFromUrl($this->_requestVar);
        if (empty($values)) {
            return $this;
        }
        $collection->addCategoriesFilter(['in' => $values]);
        return $this;
    }
    private function getOptionItemsCount($faceted, $key){
        if(isset($faceted[$key]['count'])){
            return $faceted[$key]['count'];
        }
        return 0;
    }
}