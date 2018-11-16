<?php
/**
 * Copyright © 2018 Conn's. All rights reserved.
 */

namespace Conns\RefineBy\Model\Layer\Filter;

use Magento\Catalog\Model\Layer\Filter\ItemFactory;
use Magento\Framework\Filter\StripTags;
use Magento\Catalog\Model\Layer\Filter\Item\DataBuilder;
use Magento\Catalog\Model\Layer;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\RequestInterface;
use Conns\RefineBy\Model\Url\Builder;
use Conns\RefineBy\Model\Layer\ItemCollectionProvider;

/**
 * Class Attribute
 * @package Conns\RefineBy\Model\Layer\Filter
 */
class Attribute extends \Magento\CatalogSearch\Model\Layer\Filter\Attribute
{
    /**
     * @var StripTags
     */
    protected $tagFilter;
    /**
     * @var Builder
     */
    protected $urlBuilder;
    /**
     * @var RequestInterface
     */
    protected $request;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var ItemCollectionProvider
     */
    protected $collectionProvider;

    /**
     * Attribute constructor.
     * @param ItemFactory $filterItemFactory
     * @param StoreManagerInterface $storeManager
     * @param Layer $layer
     * @param DataBuilder $itemDataBuilder
     * @param StripTags $tagFilter
     * @param RequestInterface $request
     * @param Builder $urlBuilder
     * @param ItemCollectionProvider $collectionProvider
     * @param array $data
     */
    public function __construct(
        ItemFactory $filterItemFactory,
        StoreManagerInterface $storeManager,
        Layer $layer,
        DataBuilder $itemDataBuilder,
        StripTags $tagFilter,
        RequestInterface $request,
        Builder $urlBuilder,
        ItemCollectionProvider $collectionProvider,
        array $data = []
    ){
        parent::__construct(
            $filterItemFactory,
            $storeManager,
            $layer,
            $itemDataBuilder,
            $tagFilter,
            $data
        );
        $this->tagFilter = $tagFilter;
        $this->urlBuilder = $urlBuilder;
        $this->request = $request;
        $this->storeManager = $storeManager;
        $this->collectionProvider = $collectionProvider;
    }

    /**
     * @param RequestInterface $request
     * @return $this|\Magento\CatalogSearch\Model\Layer\Filter\Attribute
     */
    public function apply(RequestInterface $request)
    {
       $values = $this->urlBuilder->getValuesFromUrl($this->_requestVar);
        if (!$values){
            return $this;
        }
        $productCollection = $this->getLayer()->getProductCollection();
        $this->applyToCollection($productCollection);
        foreach ($values as $value){
            $label = $this->getOptionText($value);
            $this->getLayer()->getState()->addFilter($this->_createItem($label, $value));
        }
        return $this;
    }

    /**
     * @param $collection
     * @return $this
     */
    public function applyToCollection($collection){
        $attribute = $this->getAttributeModel();
        $attributeValue = $this->urlBuilder->getValuesFromUrl($this->_requestVar);
        if (empty($attributeValue)){
            return $this;
        }
        $collection->addFieldToFilter($attribute->getAttributeCode(), array('in' => $attributeValue));
    }

    /**
     * @return array
     */
    protected function _getItemsData(){
        $values = $this->urlBuilder->getValuesFromUrl($this->_requestVar);
        $productCollection = $this->getLayer()->getProductCollection();
        $collection = $this->collectionProvider->getCollection($this->getCurrentCategory());
        $collection->updateSearchCriteriaBuilder();
        $this->getLayer()->prepareProductCollection($collection);
        foreach ($productCollection->getAddedFilters() as $field => $condition) {
            if ($this->getAttributeModel()->getAttributeCode() == $field) {
                continue;
            }
            $collection->addFieldToFilter($field, $condition);
        }
        $attribute = $this->getAttributeModel();
        $optionsFacetedData = $this->getFacetedData();
        $options = $attribute->getFrontend()->getSelectOptions();
        foreach ($options as $option) {
            if(empty($option['value'])) {
                continue;
            }
            if(isset($optionsFacetedData[$option['value']])){
                $count = $this->getOptionItemsCount($optionsFacetedData, $option['value']);
                $this->itemDataBuilder->addItemData(
                    $this->tagFilter->filter($option['label']),
                    $option['value'],
                    $count
                );
            }
        }
        return $this->itemDataBuilder->build();
    }

    /**
     * @param $faceted
     * @param $key
     * @return int
     */
    private function getOptionItemsCount($faceted, $key){
        if(isset($faceted[$key]['count'])){
            return $faceted[$key]['count'];
        }
        return 0;
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getFacetedData(){
        $collection = $this->collectionProvider->getCollection($this->getCurrentCategory());
        $collection->updateSearchCriteriaBuilder();
        $collection->addCategoryFilter($this->getCurrentCategory());
        if($this->getCurrentCategory()->getId() == $this->storeManager->getStore()->getRootCategoryId()){
            $collection->addSearchFilter($this->request->getParam('q'));
        }
        return $collection->getFacetedData($this->getAttributeModel()->getAttributeCode());
    }

    /**
     * @return mixed
     */
    private function getCurrentCategory(){
        return $this->getLayer()->getCurrentCategory();
    }
}