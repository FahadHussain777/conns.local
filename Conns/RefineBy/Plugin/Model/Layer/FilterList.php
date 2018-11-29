<?php
/**
 * Copyright © 2018 Conn's. All rights reserved.
 */
namespace Conns\RefineBy\Plugin\Model\Layer;

/**
 * Class FilterList
 * @package Conns\RefineBy\Plugin\Model\Layer
 */
class FilterList
{
    /**
     * @class \Conns\RefineBy\Model\Layer\Filter\MonthlyPayment
     */
    const MONTHLY_PAYMENT_AMOUNT_FILTER   = \Conns\RefineBy\Model\Layer\Filter\MonthlyPayment::class;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Magento\Catalog\Model\Layer\Category\FilterableAttributeList
     */
    protected $filterableAttributes;

    /**
     * FilterList constructor.
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Catalog\Model\Layer\Category\FilterableAttributeList $filterableAttributes
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Catalog\Model\Layer\Category\FilterableAttributeList $filterableAttributes
    ){
        $this->objectManager = $objectManager;
        $this->filterableAttributes = $filterableAttributes;
    }

    /**
     * @param \Magento\Catalog\Model\Layer\FilterList $subject
     * @param callable $proceed
     * @param \Magento\Catalog\Model\Layer $layer
     * @return mixed
     */
    public function aroundGetFilters(
        \Magento\Catalog\Model\Layer\FilterList $subject,
        callable $proceed,
        \Magento\Catalog\Model\Layer $layer
    ){
        $results = $proceed($layer);
        foreach ( $results as $index => $attribute) {
            if($attribute->getRequestVar() === "monthly_payment_amount"){
                foreach($this->filterableAttributes->getList() as $attributelist) {
                    if($attributelist->getName() == 'monthly_payment_amount' ){
                        $results[$index] = $this->createAttributeFilter($attributelist, $layer);
                    }
                }

            }
        }
        return $results;
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute
     * @param \Magento\Catalog\Model\Layer $layer
     * @return mixed
     */
    protected function createAttributeFilter(
        \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute,
        \Magento\Catalog\Model\Layer $layer
    ) {
        $filterClassName = $this->getAttributeFilterClass($attribute);

        $filter = $this->objectManager->create(
            $filterClassName,
            ['data' => ['attribute_model' => $attribute], 'layer' => $layer]
        );
        return $filter;
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute
     * @return string
     */
    protected function getAttributeFilterClass(\Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute)
    {
        $filterClassName = self::MONTHLY_PAYMENT_AMOUNT_FILTER;
        return $filterClassName;
    }
}