<?php

namespace Conns\RefineBy\Plugin\Model\Layer;

class FilterList
{
    const MONTHLY_PAYMENT_FILTER   = \Conns\RefineBy\Model\Layer\Filter\MonthlyPayment::class;

    protected $objectManager;

    protected $filterableAttributes;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Catalog\Model\Layer\Category\FilterableAttributeList $filterableAttributes
    ){
        $this->objectManager = $objectManager;
        $this->filterableAttributes = $filterableAttributes;
    }

    public function aroundGetFilters(
        \Magento\Catalog\Model\Layer\FilterList $subject,
        callable $proceed,
        \Magento\Catalog\Model\Layer $layer
    ){
        $results = $proceed($layer);
        foreach ( $results as $index => $attribute) {
            if($attribute->getRequestVar() === "monthly_payment"){
                foreach($this->filterableAttributes->getList() as $attributelist) {
                    if($attributelist->getName() == 'monthly_payment' ){
                        $results[$index] = $this->createAttributeFilter($attributelist, $layer);
                    }
                }

            }
        }
        return $results;
    }

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
    protected function getAttributeFilterClass(\Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute)
    {
        $filterClassName = self::MONTHLY_PAYMENT_FILTER;
        return $filterClassName;
    }
}