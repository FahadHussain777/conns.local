<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Layerednav\Model\Layer\Filter;

use Aheadworks\Layerednav\Model\Layer\Filter\DataProvider\Category as DataProvider;
use Aheadworks\Layerednav\Model\Layer\Filter\DataProvider\CategoryFactory as DataProviderFactory;
use Aheadworks\Layerednav\Model\PageTypeResolver;
use Aheadworks\Layerednav\Model\ResourceModel\Layer\ConditionRegistry;
use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Filter\AbstractFilter;
use Magento\Catalog\Model\Layer\Filter\ItemFactory;
use Magento\Catalog\Model\Layer\Filter\Item\DataBuilder as ItemDataBuilder;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Escaper;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Category Filter
 * @package Aheadworks\Layerednav\Model\Layer\Filter
 */
class Category extends AbstractFilter
{
    /**
     * @var DataProvider
     */
    private $dataProvider;

    /**
     * @var ConditionRegistry
     */
    private $conditionsRegistry;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var PageTypeResolver
     */
    private $pageTypeResolver;

    /**
     * @param ItemFactory $filterItemFactory
     * @param StoreManagerInterface $storeManager
     * @param Layer $layer
     * @param ItemDataBuilder $itemDataBuilder
     * @param DataProviderFactory $dataProviderFactory
     * @param ConditionRegistry $conditionsRegistry
     * @param Escaper $escaper
     * @param PageTypeResolver $pageTypeResolver
     * @param array $data
     */
    public function __construct(
        ItemFactory $filterItemFactory,
        StoreManagerInterface $storeManager,
        Layer $layer,
        ItemDataBuilder $itemDataBuilder,
        DataProviderFactory $dataProviderFactory,
        ConditionRegistry $conditionsRegistry,
        Escaper $escaper,
        PageTypeResolver $pageTypeResolver,
        array $data = []
    ) {
        parent::__construct(
            $filterItemFactory,
            $storeManager,
            $layer,
            $itemDataBuilder,
            $data
        );
        $this->_requestVar = 'cat';
        $this->dataProvider = $dataProviderFactory->create(['layer' => $layer]);
        $this->conditionsRegistry = $conditionsRegistry;
        $this->escaper = $escaper;
        $this->pageTypeResolver = $pageTypeResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(RequestInterface $request)
    {
        $filter = $request->getParam($this->getRequestVar());
        if (!$filter) {
            return $this;
        }

        $filterParams = explode(',', $filter);
        $categoryIds = $this->dataProvider->validateFilter($filterParams);
        if (!$categoryIds) {
            return $this;
        }

        $this->dataProvider->getResource()->joinFilterToCollection($this);
        $this->conditionsRegistry->addConditions(
            'category',
            $this->dataProvider->getResource()->getWhereConditions($categoryIds)
        );
        $this->getLayer()
            ->getState()
            ->addFilter(
                $this->_createItem('category', $filter)
            );

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return __('Category');
    }

    /**
     * {@inheritdoc}
     */
    protected function _getItemsData()
    {
        if ($this->pageTypeResolver->getType() == PageTypeResolver::PAGE_TYPE_CATALOG_SEARCH) {
            return $this->getItemsDataForSearchPage();
        }
        return $this->getItemsDataForCategoryPage();
    }

    /**
     * Get filter items data for category page
     *
     * @return array
     */
    private function getItemsDataForCategoryPage()
    {
        $category = $this->getLayer()->getCurrentCategory();
        $childCategories = $category->getChildrenCategories();
        $collection = $this->getLayer()->getProductCollection();
        $collection->addCountToCategories($childCategories);
        $resource = $this->dataProvider->getResource();

        if ($category->getIsActive()) {
            foreach ($childCategories as $category) {
                if ($category->getIsActive()) {
                    if ($productCount = $resource->getProductCount($this, $category)) {
                        $this->itemDataBuilder->addItemData(
                            $this->escaper->escapeHtml($category->getName()),
                            $category->getId(),
                            $productCount
                        );
                    }
                }
            }
        }
        return $this->itemDataBuilder->build();
    }

    /**
     * Get filter items data for search page
     *
     * @return array
     */
    private function getItemsDataForSearchPage()
    {
        $category = $this->getLayer()->getCurrentCategory();
        $childCategories = $category->getChildrenCategories();
        /** @var \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $collection */
        $collection = $this->getLayer()->getProductCollection();
        $optionsFacetedData = $collection->getFacetedData('category');

        if ($category->getIsActive()) {
            foreach ($childCategories as $category) {
                if ($category->getIsActive()
                    && isset($optionsFacetedData[$category->getId()])
                ) {
                    $this->itemDataBuilder->addItemData(
                        $this->escaper->escapeHtml($category->getName()),
                        $category->getId(),
                        $optionsFacetedData[$category->getId()]['count']
                    );
                }
            }
        }
        return $this->itemDataBuilder->build();
    }
}
