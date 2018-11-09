<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Layerednav\Model\Layer\Filter\Dynamic;

use Aheadworks\Layerednav\Model\ResourceModel\Layer\Filter\Price;
use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Filter\Dynamic\AlgorithmInterface;
use Magento\Catalog\Model\Layer\Filter\Price\Range;
use Magento\Catalog\Model\Layer\Filter\Price\Render;
use Magento\Catalog\Model\Layer\Resolver as LayerResolver;

/**
 * Class Auto
 * @package Aheadworks\Layerednav\Model\Layer\Filter\Dynamic
 */
class Auto implements AlgorithmInterface
{
    const MIN_RANGE_POWER = 10;

    /**
     * @var Layer
     */
    private $layer;

    /**
     * @var Render
     */
    private $render;

    /**
     * @var Range
     */
    private $range;

    /**
     * @var Price
     */
    private $resource;

    /**
     * @param LayerResolver $layerResolver
     * @param Render $render
     * @param Range $range
     * @param Price $resource
     */
    public function __construct(
        LayerResolver $layerResolver,
        Render $render,
        Range $range,
        Price $resource
    ) {
        $this->layer = $layerResolver->get();
        $this->render = $render;
        $this->range = $range;
        $this->resource = $resource;
    }

    /**
     * {@inheritdoc}
     */
    public function getItemsData(array $intervals = [], $additionalRequestData = '')
    {
        $data = [];
        $range = $this->range->getPriceRange() ? $this->range->getPriceRange() : $this->getRange();

        if ($range) {
            $dbRanges = $this->resource->getCount($range);

            $dbParentRanges = $this->resource->getParentCount($range);
            foreach (array_keys($dbParentRanges) as $key) {
                $dbParentRanges[$key] = '0';
                if (array_key_exists($key, $dbRanges)) {
                    $dbParentRanges[$key] = $dbRanges[$key];
                }
            }
            $dbRanges = $dbParentRanges;
            $data = $this->render->renderRangeData($range, $dbRanges);
        }

        return $data;
    }

    /**
     * @return number
     */
    private function getRange()
    {
        $maxPrice = $this->getMaxPriceInt();
        $index = 1;
        do {
            $range = pow(10, strlen(floor($maxPrice)) - $index);
            $index++;
        } while ($range > self::MIN_RANGE_POWER);

        return $range;
    }

    /**
     * Get maximum price from layer products set
     *
     * @return float
     */
    private function getMaxPriceInt()
    {
        $maxPrice = $this->layer->getProductCollection()
            ->getMaxPrice();
        $maxPrice = floor($maxPrice);

        return $maxPrice;
    }
}
