<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Layerednav\Model;

use Aheadworks\Layerednav\Model\Layer\FilterListResolver;
use Aheadworks\Layerednav\Model\ResourceModel\Layer\ConditionRegistry;
use Magento\Catalog\Model\Layer;
use Magento\Framework\App\RequestInterface;

/**
 * Class Applier
 * @package Aheadworks\Layerednav\Model
 */
class Applier
{
    /**
     * @var FilterListResolver
     */
    private $filterListResolver;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var ConditionRegistry
     */
    private $conditionRegistry;

    /**
     * @param FilterListResolver $filterListResolver
     * @param RequestInterface $request
     * @param ConditionRegistry $conditionRegistry
     */
    public function __construct(
        FilterListResolver $filterListResolver,
        RequestInterface $request,
        ConditionRegistry $conditionRegistry
    ) {
        $this->filterListResolver = $filterListResolver;
        $this->request = $request;
        $this->conditionRegistry = $conditionRegistry;
    }

    /**
     * Apply filters
     *
     * @param Layer $layer
     * @return void
     */
    public function applyFilters(Layer $layer)
    {
        $filterList = $this->filterListResolver->get();
        foreach ($filterList->getFilters($layer) as $filter) {
            $filter->apply($this->request);
        }

        $collection = $layer->getProductCollection();
        $whereConditions = $this->conditionRegistry->getConditions();
        if ($whereConditions) {
            foreach ($whereConditions as $conditions) {
                $condition = implode(' OR ', $conditions);
                $collection->getSelect()->where("({$condition})");
            }
            $collection->getSize();
            $collection->getSelect()->group('e.entity_id');
        }

        $layer->apply();
    }
}
