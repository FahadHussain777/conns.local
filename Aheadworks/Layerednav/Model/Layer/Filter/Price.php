<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Layerednav\Model\Layer\Filter;

use Aheadworks\Layerednav\Model\Layer\Filter\DataProvider\Price as DataProvider;
use Aheadworks\Layerednav\Model\Layer\Filter\DataProvider\PriceFactory as DataProviderFactory;
use Aheadworks\Layerednav\Model\ResourceModel\Layer\ConditionRegistry;
use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Filter\AbstractFilter;
use Magento\Catalog\Model\Layer\Filter\Dynamic\AlgorithmFactory;
use Magento\Catalog\Model\Layer\Filter\ItemFactory;
use Magento\Catalog\Model\Layer\Filter\Item\DataBuilder as ItemDataBuilder;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\RequestInterface;

/**
 * Price Filter
 * @package Aheadworks\Layerednav\Model\Layer\Filter
 */
class Price extends AbstractFilter
{
    /**
     * @var AlgorithmFactory
     */
    private $algorithmFactory;

    /**
     * @var DataProvider
     */
    private $dataProvider;

    /**
     * @var ConditionRegistry
     */
    private $conditionsRegistry;

    /**
     * @param ItemFactory $filterItemFactory
     * @param StoreManagerInterface $storeManager
     * @param Layer $layer
     * @param ItemDataBuilder $itemDataBuilder
     * @param AlgorithmFactory $algorithmFactory
     * @param DataProviderFactory $dataProviderFactory
     * @param ConditionRegistry $conditionsRegistry
     * @param array $data
     */
    public function __construct(
        ItemFactory $filterItemFactory,
        StoreManagerInterface $storeManager,
        Layer $layer,
        ItemDataBuilder $itemDataBuilder,
        AlgorithmFactory $algorithmFactory,
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
        $this->algorithmFactory = $algorithmFactory;
        $this->dataProvider = $dataProviderFactory->create();
        $this->conditionsRegistry = $conditionsRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(RequestInterface $request)
    {
        /**
         * Filter must be string: $fromPrice-$toPrice
         */
        $filter = $request->getParam($this->getRequestVar());
        if (!$filter || is_array($filter)) {
            return $this;
        }

        // Validate filter
        $filterParams = explode(',', $filter);
        $intervals = $this->dataProvider->getIntervals($filterParams);
        if (!count($intervals)) {
            return $this;
        }
        $this->dataProvider->setInterval($intervals);
        $this->conditionsRegistry->addConditions(
            'price',
            $this->dataProvider->getResource()->getWhereConditions(
                $this,
                $this->dataProvider->getInterval()
            )
        );

        $value = [];
        foreach ($intervals as $item) {
            $value[] = implode('-', $item);
        }
        $value = implode(',', $value);
        $this->getLayer()
            ->getState()
            ->addFilter(
                $this->_createItem('price', $value)
            );

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function _getItemsData()
    {
        $algorithm = $this->algorithmFactory->create();
        return $algorithm->getItemsData(
            (array)$this->dataProvider->getInterval(),
            $this->dataProvider->getAdditionalRequestData()
        );
    }

    /**
     * Get min and max values for price slider
     *
     * @return array
     */
    public function getMinMaxPrices()
    {
        return $this->dataProvider->getResource()->getMinMaxPrices();
    }
}
