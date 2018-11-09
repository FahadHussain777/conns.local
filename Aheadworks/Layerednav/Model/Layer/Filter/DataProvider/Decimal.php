<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Layerednav\Model\Layer\Filter\DataProvider;

use Aheadworks\Layerednav\Model\ResourceModel\Layer\Filter\Decimal as ResourceDecimal;
use Magento\Catalog\Model\Layer\Filter\FilterInterface;

/**
 * Decimal data provider
 * @package Aheadworks\Layerednav\Model\Layer\Filter\DataProvider
 */
class Decimal
{
    const MIN_RANGE_POWER = 10;

    /**
     * @var ResourceDecimal
     */
    private $resource;

    /**
     * @var int
     */
    private $range;

    /**
     * @var array
     */
    private $rangeItemsCount = [];

    /**
     * @param ResourceDecimal $resource
     */
    public function __construct(ResourceDecimal $resource)
    {
        $this->resource = $resource;
    }

    /**
     * Get resource model
     *
     * @return ResourceDecimal
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * Create filter intervals
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
     * @param FilterInterface $filter
     * @return int
     */
    public function getRange(FilterInterface $filter)
    {
        $range = $this->range;
        if (!$range) {
            $maxValue = $this->getResource()->getMaxValue($filter);
            $index = 1;
            do {
                $range = pow(10, strlen(floor($maxValue)) - $index);
                $items = $this->getRangeItemCounts($range, $filter);
                $index++;
            } while ($range > self::MIN_RANGE_POWER && count($items) < 2);
            $this->range = $range;
        }
        return $range;
    }

    /**
     * @param int $range
     * @param FilterInterface $filter
     * @return array|null
     */
    public function getRangeItemCounts($range, FilterInterface $filter)
    {
        $count = array_key_exists($range, $this->rangeItemsCount)
            ? $this->rangeItemsCount[$range]
            : null;
        if ($count === null) {
            $count = $this->getResource()->getCount($filter, $range);
            $parentCount = $this->getResource()->getParentCount($filter, $range);
            foreach (array_keys($parentCount) as $key) {
                $parentCount[$key] = '0';
                if (array_key_exists($key, $count)) {
                    $parentCount[$key] = $count[$key];
                }
            }
            $this->rangeItemsCount[$range] = $parentCount;
        }
        return $count;
    }
}
