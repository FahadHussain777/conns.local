<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Layerednav\Model\Layer\Filter\DataProvider;

use Aheadworks\Layerednav\Model\ResourceModel\Layer\Filter\Price as ResourcePrice;
use Magento\Catalog\Model\Layer;

/**
 * Price data provider
 * @package Aheadworks\Layerednav\Model\Layer\Filter\DataProvider
 */
class Price
{
    /**
     * @var ResourcePrice
     */
    private $resource;

    /**
     * @var array
     */
    private $interval = [];

    /**
     * @param ResourcePrice $resource
     */
    public function __construct(ResourcePrice $resource)
    {
        $this->resource = $resource;
    }

    /**
     * Set interval
     *
     * @param array $interval
     * @return void
     */
    public function setInterval(array $interval)
    {
        $this->interval = $interval;
    }

    /**
     * Get interval
     *
     * @return array
     */
    public function getInterval()
    {
        return $this->interval;
    }

    /**
     * Get resource model
     *
     * @return ResourcePrice
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * validate filters value and create intervals
     *
     * @param array $filter
     * @return array
     */
    public function getIntervals($filter)
    {
        $intervals = [];
        foreach ($filter as $filterValue) {
            if ($this->isIntervalValid($filterValue)) {
                $intervals[] = explode('-', $filterValue);
            }
        }
        return $intervals;
    }

    /**
     * Check is interval is valid
     *
     * @param string $interval
     * @return bool
     */
    private function isIntervalValid($interval)
    {
        $interval = explode('-', $interval);
        if (count($interval) != 2) {
            return false;
        }
        foreach ($interval as $v) {
            if ($v !== '' && $v !== '0' && (double)$v <= 0 || is_infinite((double)$v)) {
                return false;
            }
        }
        return true;
    }

    /**
     * @return string
     */
    public function getAdditionalRequestData()
    {
        return '';
    }
}
