<?php
/**
 * Copyright © 2018 Conn's. All rights reserved.
 */
namespace Conns\RefineBy\Plugin\Model\Adapter\Aggregation\Checker\Query;

use Magento\Framework\Search\RequestInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Search\Request\QueryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\CatalogSearch\Model\Adapter\Aggregation\Checker\Query\CatalogView as MagentoCatalogView;

/**
 * Class CatalogView
 * @package Conns\RefineBy\Plugin\Model\Adapter\Aggregation\Checker\Query
 */
class CatalogView {
    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * CatalogView constructor.
     * @param CategoryRepositoryInterface $categoryRepository
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        CategoryRepositoryInterface $categoryRepository,
        StoreManagerInterface $storeManager
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->storeManager = $storeManager;
    }

    /**
     * @param MagentoCatalogView $subject
     * @param \Closure $proceed
     * @param RequestInterface $request
     * @return bool|mixed
     */
    public function aroundIsApplicable(
        MagentoCatalogView $subject,
        \Closure $proceed,
        RequestInterface $request
    ){
        if ($request->getName() === 'catalog_view_container') {
            return $this->hasAnchorCategory($request);
        }
        return $proceed($request);
    }

    /**
     * @param RequestInterface $request
     * @return bool
     */
    private function hasAnchorCategory(RequestInterface $request){
        $queryType = $request->getQuery()->getType();
        $result = false;
        if ($queryType === QueryInterface::TYPE_BOOL) {
            $categories = $this->getCategoriesFromQuery($request->getQuery());
            foreach ($categories as $category) {
                if ($category && $category->getIsAnchor()) {
                    $result = true;
                    break;
                }
            }
        }
        return $result;
    }

    /**
     * @param QueryInterface $queryExpression
     * @return array
     */
    private function getCategoriesFromQuery(QueryInterface $queryExpression){
        $categoryIds = $this->getCategoryIdsFromQuery($queryExpression);
        $categories = [];
        foreach ($categoryIds as $categoryId) {
            try {
                $categories[] = $this->categoryRepository
                    ->get($categoryId, $this->storeManager->getStore()->getId());
            } catch (NoSuchEntityException $e) {}
        }
        return $categories;
    }

    /**
     * @param QueryInterface $queryExpression
     * @return array
     */
    private function getCategoryIdsFromQuery(QueryInterface $queryExpression){
        $queryFilterArray = [];
        $queryFilterArray[] = $queryExpression->getMust();
        $queryFilterArray[] = $queryExpression->getShould();
        $categoryIds = [];
        foreach ($queryFilterArray as $item) {
            if (!empty($item) && isset($item['category'])) {
                $queryFilter = $item['category'];
                $values = $queryFilter->getReference()->getValue();
                if (is_array($values)) {
                    $categoryIds = array_merge($categoryIds, $values['in']);
                } else {
                    $categoryIds[] = $values;
                }
            }
        }
        return $categoryIds;
    }

}