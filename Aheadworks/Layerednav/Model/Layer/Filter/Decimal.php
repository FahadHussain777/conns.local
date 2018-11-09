<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Layerednav\Model\Layer\Filter;

use Aheadworks\Layerednav\Model\Layer\Filter\DataProvider\Decimal as DataProvider;
use Aheadworks\Layerednav\Model\Layer\Filter\DataProvider\DecimalFactory as DataProviderFactory;
use Aheadworks\Layerednav\Model\ResourceModel\Layer\ConditionRegistry;
use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Filter\AbstractFilter;
use Magento\Catalog\Model\Layer\Filter\ItemFactory;
use Magento\Catalog\Model\Layer\Filter\Item\DataBuilder as ItemDataBuilder;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Decimal Filter
 * @package Aheadworks\Layerednav\Model\Layer\Filter
 */
class Decimal extends AbstractFilter
{
    /**
     * @var DataProvider
     */
    private $dataProvider;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var ConditionRegistry
     */
    private $conditionsRegistry;

    /**
     * @param ItemFactory $filterItemFactory
     * @param StoreManagerInterface $storeManager
     * @param Layer $layer
     * @param ItemDataBuilder $itemDataBuilder
     * @param PriceCurrencyInterface $priceCurrency
     * @param DataProviderFactory $dataProviderFactory
     * @param ConditionRegistry $conditionsRegistry
     * @param array $data
     */
    public function __construct(
        ItemFactory $filterItemFactory,
        StoreManagerInterface $storeManager,
        Layer $layer,
        ItemDataBuilder $itemDataBuilder,
        PriceCurrencyInterface $priceCurrency,
        DataProviderFactory $dataProviderFactory,
        ConditionRegistry $conditionsRegistry,
        array $data = []
    ) {
        parent::__construct(
            $filterItemFactory,
            $storeManager,
            $layer,
            $itemDataBuilder,
            $data
        );
        $this->priceCurrency = $priceCurrency;
        $this->dataProvider = $dataProviderFactory->create();
        $this->conditionsRegistry = $conditionsRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(RequestInterface $request)
    {
        $filter = $request->getParam($this->getRequestVar());
        if (!$filter || is_array($filter)) {
            return $this;
        }

        $filterParams = explode(',', $filter);
        $intervals = $this->dataProvider->getIntervals($filterParams);
        if (!count($intervals)) {
            return $this;
        }

        $this->dataProvider->getResource()->joinFilterToCollection($this);
        $this->conditionsRegistry->addConditions(
            $this->getAttributeModel()->getAttributeCode(),
            $this->dataProvider->getResource()->getWhereConditions($this, $intervals)
        );

        $value = [];
        foreach ($intervals as $item) {
            $value[] = implode('-', $item);
        }
        $value = implode(',', $value);
        $this->getLayer()
            ->getState()
            ->addFilter(
                $this->_createItem($this->getRequestVar(), $value)
            );

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function _getItemsData()
    {
        $range = $this->dataProvider->getRange($this);
        $dbRanges = $this->dataProvider->getRangeItemCounts($range, $this);
        foreach ($dbRanges as $index => $count) {
            if ($index === '') {
                continue;
            }
            $this->itemDataBuilder->addItemData(
                $this->getItemLabel($range, $index),
                $index . '-' . $range,
                $count
            );
        }

        return $this->itemDataBuilder->build();
    }

    /**
     * Get prepared text of item label
     *
     * @param int $range
     * @param float $value
     * @return \Magento\Framework\Phrase
     */
    private function getItemLabel($range, $value)
    {
        $from = $this->priceCurrency->format(($value - 1) * $range, false);
        $to = $this->priceCurrency->format($value * $range, false);
        return __('%1 - %2', $from, $to);
    }
}
