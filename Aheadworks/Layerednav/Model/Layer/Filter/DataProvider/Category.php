<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Layerednav\Model\Layer\Filter\DataProvider;

use Aheadworks\Layerednav\Model\ResourceModel\Layer\Filter\Category as ResourceCategory;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\Category as CategoryModel;
use Magento\Catalog\Model\Layer;

/**
 * Category data provider
 * @package Aheadworks\Layerednav\Model\Layer\Filter\DataProvider
 */
class Category
{
    /**
     * @var ResourceCategory
     */
    private $resource;

    /**
     * @var Layer
     */
    private $layer;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @param ResourceCategory $resource
     * @param Layer $layer
     * @param CategoryRepositoryInterface $categoryRepository
     */
    public function __construct(
        ResourceCategory $resource,
        Layer $layer,
        CategoryRepositoryInterface $categoryRepository
    ) {
        $this->resource = $resource;
        $this->layer = $layer;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * Get resource model
     *
     * @return ResourceCategory
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * Validate filters value and get valid IDs
     *
     * @param array $filter
     * @return array|bool
     */
    public function validateFilter($filter)
    {
        $categoryIds = [];
        $storeId = $this->layer->getCurrentStore()->getStoreId();
        foreach ($filter as $categoryId) {
            $category = $this->categoryRepository->get($categoryId, $storeId);
            if ($this->isValid($category)) {
                $categoryIds[] = $categoryId;
            }
        }
        return $categoryIds ? $categoryIds : false;
    }

    /**
     * Validate category for using as filter
     *
     * @param CategoryInterface $category
     * @return bool
     */
    private function isValid(CategoryInterface $category)
    {
        if ($category->getId()) {
            while ($category->getLevel() != 0) {
                if (!$category->getIsActive()) {
                    return false;
                }
                $category = $this->categoryRepository->get($category->getParentId());
            }
            return true;
        }
        return false;
    }
}
