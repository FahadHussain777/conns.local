<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Layerednav\Model\Layer\Filter\Dynamic;

use Aheadworks\Layerednav\Model\ResourceModel\Layer\Filter\Price;
use Magento\Catalog\Model\Layer\Filter\Dynamic\AlgorithmInterface;
use Magento\Catalog\Model\Layer\Filter\Price\Range;
use Magento\Catalog\Model\Layer\Filter\Price\Render;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Manual
 * @package Aheadworks\Layerednav\Model\Layer\Filter\Dynamic
 */
class Manual implements AlgorithmInterface
{
    const XML_PATH_RANGE_MAX_INTERVALS = 'catalog/layered_navigation/price_range_max_intervals';

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
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param Render $render
     * @param Range $range
     * @param Price $resource
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Render $render,
        Range $range,
        Price $resource,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->render = $render;
        $this->range = $range;
        $this->resource = $resource;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function getItemsData(array $intervals = [], $additionalRequestData = '')
    {
        $data = [];
        $range = $this->range->getPriceRange() ?
            $this->range->getPriceRange() :
            $this->range->getConfigRangeStep();

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
            $dbRanges = $this->processRange($dbRanges);
            $data = $this->render->renderRangeData($range, $dbRanges);
        }

        return $data;
    }

    /**
     * @param array $items
     * @return array
     */
    private function processRange($items)
    {
        $i = 0;
        $lastIndex = null;
        $maxIntervalsNumber = (int)$this->scopeConfig->getValue(
            self::XML_PATH_RANGE_MAX_INTERVALS,
            ScopeInterface::SCOPE_STORE
        );
        foreach ($items as $k => $v) {
            ++$i;
            if ($i > 1 && $i > $maxIntervalsNumber) {
                $items[$lastIndex] += $v;
                unset($items[$k]);
            } else {
                $lastIndex = $k;
            }
        }
        return $items;
    }
}
