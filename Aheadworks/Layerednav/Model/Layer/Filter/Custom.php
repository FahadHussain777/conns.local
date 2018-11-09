<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Layerednav\Model\Layer\Filter;

use Aheadworks\Layerednav\Model\ResourceModel\Layer\Filter\Custom\AbstractFilter as ResourceAbstractFilter;
use Aheadworks\Layerednav\Model\ResourceModel\Layer\ConditionRegistry;
use Magento\Catalog\Model\Layer\Filter\AbstractFilter;
use Magento\Catalog\Model\Layer\Filter\ItemFactory;
use Magento\Catalog\Model\Layer\Filter\Item\DataBuilder as ItemDataBuilder;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Filter\StripTags;
use Magento\Framework\Stdlib\StringUtils;

/**
 * Custom Filter
 * @package Aheadworks\Layerednav\Model\Layer\Filter
 */
class Custom extends AbstractFilter
{
    /**
     * @var ResourceAbstractFilter
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
     * @var string
     */
    private $itemLabel;

    /**
     * @param ItemFactory $filterItemFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Layer $layer
     * @param ItemDataBuilder $itemDataBuilder
     * @param ConditionRegistry $conditionsRegistry
     * @param ResourceAbstractFilter $resource
     * @param StringUtils $stringUtils
     * @param StripTags $tagFilter
     * @param string $requestVar
     * @param string $itemLabel
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        ItemFactory $filterItemFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Layer $layer,
        ItemDataBuilder $itemDataBuilder,
        ConditionRegistry $conditionsRegistry,
        ResourceAbstractFilter $resource,
        StringUtils $stringUtils,
        StripTags $tagFilter,
        $requestVar,
        $itemLabel,
        array $data = []
    ) {
        parent::__construct(
            $filterItemFactory,
            $storeManager,
            $layer,
            $itemDataBuilder,
            $data
        );
        $this->conditionsRegistry = $conditionsRegistry;
        $this->resource = $resource;
        $this->stringUtils = $stringUtils;
        $this->tagFilter = $tagFilter;
        $this->itemLabel = $itemLabel;
        $this->setRequestVar($requestVar);
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

        if ($filter == '1') {
            $this->resource->joinFilterToCollection($this);
            foreach ($this->resource->getWhereConditions($this, $filter) as $attribute => $conditions) {
                $this->conditionsRegistry->addConditions($attribute, $conditions);
            }
            $this->getLayer()
                ->getState()
                ->addFilter($this->_createItem($this->itemLabel, $filter));
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _getItemsData()
    {
        $options = $this->getOptions();
        $optionsCount = $this->resource->getCount($this);
        $parentCount = $this->resource->getParentCount($this);
        foreach (array_keys($parentCount) as $key) {
            if (empty($parentCount[$key])) {
                unset($parentCount[$key]);
                continue;
            }
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
                if (array_key_exists($option['value'], $optionsCount)
                    && ($optionsCount[$option['value']] || $optionsCount[$option['value']] == '0')
                ) {
                    $this->itemDataBuilder->addItemData(
                        $this->tagFilter->filter($option['label']),
                        $option['value'],
                        $optionsCount[$option['value']]
                    );
                }
            }
        }

        return $this->itemDataBuilder->build();
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return __($this->itemLabel);
    }

    /**
     * @return array
     */
    private function getOptions()
    {
        return [
            [
                'value' => 1,
                'label' => $this->itemLabel
            ]
        ];
    }
}
