<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Layerednav\Model\Layer\Filter;

use Aheadworks\Layerednav\Model\ResourceModel\Layer\Filter\Attribute as ResourceAttribute;
use Aheadworks\Layerednav\Model\ResourceModel\Layer\ConditionRegistry;
use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Filter\AbstractFilter;
use Magento\Catalog\Model\Layer\Filter\ItemFactory;
use Magento\Catalog\Model\Layer\Filter\Item\DataBuilder as ItemDataBuilder;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Filter\StripTags;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Attribute Filter
 * @package Aheadworks\Layerednav\Model\Layer\Filter
 */
class Attribute extends AbstractFilter
{
    /**
     * @var ResourceAttribute
     */
    private $resource;

    /**
     * @var StringUtils
     */
    private $stringUtils;

    /**
     * @var StripTags
     */
    private $tagFilter;

    /**
     * @var ConditionRegistry
     */
    private $conditionsRegistry;

    /**
     * @param ItemFactory $filterItemFactory
     * @param StoreManagerInterface $storeManager
     * @param Layer $layer
     * @param ItemDataBuilder $itemDataBuilder
     * @param ResourceAttribute $resource
     * @param StringUtils $stringUtils
     * @param StripTags $tagFilter
     * @param ConditionRegistry $conditionsRegistry
     * @param array $data
     */
    public function __construct(
        ItemFactory $filterItemFactory,
        StoreManagerInterface $storeManager,
        Layer $layer,
        ItemDataBuilder $itemDataBuilder,
        ResourceAttribute $resource,
        StringUtils $stringUtils,
        StripTags $tagFilter,
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
        $this->resource = $resource;
        $this->stringUtils = $stringUtils;
        $this->tagFilter = $tagFilter;
        $this->conditionsRegistry = $conditionsRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(RequestInterface $request)
    {
        $filter = $request->getParam($this->_requestVar);

        if (is_array($filter)) {
            return $this;
        }

        $text = $this->getOptionText($filter);
        if ($filter && $text) {
            $this->resource->joinFilterToCollection($this);
            $this->conditionsRegistry->addConditions(
                $this->getAttributeModel()->getAttributeCode(),
                $this->resource->getWhereConditions($this, $filter)
            );
            $this->getLayer()
                ->getState()
                ->addFilter($this->_createItem($text, $filter));
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _getItemsData()
    {
        $attribute = $this->getAttributeModel();

        $options = $attribute->getFrontend()->getSelectOptions();
        $optionsCount = $this->resource->getCount($this);
        $parentCount = $this->resource->getParentCount($this);

        foreach (array_keys($parentCount) as $key) {
            $parentCount[$key] = '0';
            if (array_key_exists($key, $optionsCount)) {
                $parentCount[$key] = $optionsCount[$key];
            }
        }
        $optionsCount = $parentCount;

        foreach ($options as $option) {
            if (is_array($option['value'])) {
                continue;
            }
            if ($this->stringUtils->strlen($option['value'])) {
                // Check filter type
                if ($this->getAttributeIsFilterable($attribute) == self::ATTRIBUTE_OPTIONS_ONLY_WITH_RESULTS) {
                    if (array_key_exists($option['value'], $optionsCount)
                        && ($optionsCount[$option['value']] || $optionsCount[$option['value']] == '0')
                    ) {
                        $this->itemDataBuilder->addItemData(
                            $this->tagFilter->filter($option['label']),
                            $option['value'],
                            $optionsCount[$option['value']]
                        );
                    }
                } else {
                    $this->itemDataBuilder->addItemData(
                        $this->tagFilter->filter($option['label']),
                        $option['value'],
                        isset($optionsCount[$option['value']]) ? $optionsCount[$option['value']] : 0
                    );
                }
            }
        }

        return $this->itemDataBuilder->build();
    }
}
