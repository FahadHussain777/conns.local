<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Layerednav\Model\Layer;

use Aheadworks\Layerednav\Model\Config;
use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Filter\AbstractFilter;
use Magento\Catalog\Model\Layer\FilterableAttributeListInterface;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class FilterList
 * @package Aheadworks\Layerednav\Model\Layer
 */
class FilterList
{
    // General filters
    const CATEGORY_FILTER = 'category';
    const ATTRIBUTE_FILTER = 'attribute';
    const PRICE_FILTER = 'price';
    const DECIMAL_FILTER = 'decimal';

    // Special filters
    const SALES_FILTER = 'sales';
    const NEW_FILTER = 'new';
    const STOCK_FILTER = 'stock';

    /**
     * @var string[]
     */
    private $filterTypes = [
        self::CATEGORY_FILTER => \Aheadworks\Layerednav\Model\Layer\Filter\Category::class,
        self::ATTRIBUTE_FILTER => \Aheadworks\Layerednav\Model\Layer\Filter\Attribute::class,
        self::PRICE_FILTER => \Aheadworks\Layerednav\Model\Layer\Filter\Price::class,
        self::DECIMAL_FILTER => \Aheadworks\Layerednav\Model\Layer\Filter\Decimal::class,
        self::STOCK_FILTER => 'Aheadworks\Layerednav\Model\Layer\Filter\Custom\Stock',
        self::SALES_FILTER => 'Aheadworks\Layerednav\Model\Layer\Filter\Custom\Sales',
        self::NEW_FILTER => 'Aheadworks\Layerednav\Model\Layer\Filter\Custom\NewProduct'
    ];

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var FilterableAttributeListInterface
     */
    private $filterableAttributes;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var AbstractFilter[]
     */
    private $filters;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param FilterableAttributeListInterface $filterableAttributes
     * @param Config $config
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        FilterableAttributeListInterface $filterableAttributes,
        Config $config
    ) {
        $this->objectManager = $objectManager;
        $this->filterableAttributes = $filterableAttributes;
        $this->config = $config;
    }

    /**
     * Get filters
     *
     * @param Layer $layer
     * @return AbstractFilter[]
     */
    public function getFilters(Layer $layer)
    {
        if (!$this->filters) {
            $filters = [];
            $filters[] = $this->objectManager->create($this->filterTypes[self::CATEGORY_FILTER], ['layer' => $layer]);
            foreach ($this->getAvailableCustomFilters() as $customFilterCode) {
                $filters[] = $this->createCustomFilter($layer, $customFilterCode);
            }
            foreach ($this->filterableAttributes->getList() as $attribute) {
                $filters[] = $this->createAttributeFilter($attribute, $layer);
            }
            $this->filters = $filters;
        }
        return $this->filters;
    }

    /**
     * Create attribute filter
     *
     * @param Attribute $attribute
     * @param Layer $layer
     * @return AbstractFilter
     */
    private function createAttributeFilter(Attribute $attribute, Layer $layer)
    {
        $filterClassName = $this->filterTypes[self::ATTRIBUTE_FILTER];
        if ($attribute->getAttributeCode() == 'price') {
            $filterClassName = $this->filterTypes[self::PRICE_FILTER];
        } elseif ($attribute->getBackendType() == 'decimal') {
            $filterClassName = $this->filterTypes[self::DECIMAL_FILTER];
        }

        $filter = $this->objectManager->create(
            $filterClassName,
            ['data' => ['attribute_model' => $attribute], 'layer' => $layer]
        );

        return $filter;
    }

    /**
     * Create custom filter
     *
     * @param Layer $layer
     * @param string $filterCode
     * @return AbstractFilter
     */
    private function createCustomFilter(Layer $layer, $filterCode)
    {
        return $this->objectManager->create(
            $this->filterTypes[$filterCode],
            ['layer' => $layer]
        );
    }

    /**
     * Get available custom filter codes
     *
     * @return array
     */
    private function getAvailableCustomFilters()
    {
        $customFilters = [];
        if ($this->config->isNewFilterEnabled()) {
            $customFilters[] = self::NEW_FILTER;
        }
        if ($this->config->isInStockFilterEnabled()) {
            $customFilters[] = self::STOCK_FILTER;
        }
        if ($this->config->isOnSaleFilterEnabled()) {
            $customFilters[] = self::SALES_FILTER;
        }
        return $customFilters;
    }
}
