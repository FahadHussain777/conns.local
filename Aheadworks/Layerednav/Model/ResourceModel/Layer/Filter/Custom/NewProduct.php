<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Layerednav\Model\ResourceModel\Layer\Filter\Custom;

use Magento\Catalog\Model\Layer\Filter\FilterInterface;
use Magento\Catalog\Model\ResourceModel\Product\Collection;

/**
 * Class NewProduct
 * @package Aheadworks\Layerednav\Model\ResourceModel\Layer\Filter\Custom
 */
class NewProduct extends AbstractFilter
{
    const DATE_TO_ATTRIBUTE_CODE = 'news_to_date';
    const DATE_FROM_ATTRIBUTE_CODE = 'news_from_date';

    /**
     * {@inheritdoc}
     */
    protected function getDateFromAttrCode()
    {
        return self::DATE_FROM_ATTRIBUTE_CODE;
    }

    /**
     * {@inheritdoc}
     */
    protected function getDateToAttrCode()
    {
        return self::DATE_TO_ATTRIBUTE_CODE;
    }

    /**
     * {@inheritdoc}
     */
    protected function joinDateAttributes(Collection $collection, FilterInterface $filter)
    {
        if (!$collection->getFlag($this->getDateToAttrCode() . '_table_joined')) {
            $collection->getSelect()
                ->where(
                    '('
                    . 'COALESCE(' . $this->getDateFromAttrCode(). '.value, '
                    . $this->getDateFromAttrCode() . '_default.value, '
                    . $this->getDateFromAttrCode() . '_children.value) IS NOT NULL'
                    . ' OR COALESCE(' . $this->getDateToAttrCode() . '.value, '
                    . $this->getDateToAttrCode(). '_default.value, '
                    . $this->getDateToAttrCode(). '_children.value) IS NOT NULL'
                    . ')'
                );
        }
        return parent::joinDateAttributes($collection, $filter);
    }

    /**
     * {@inheritdoc}
     */
    protected function getSpecialConditions(FilterInterface $filter, $value)
    {
        $conditions = [];
        if (!$filter->getLayer()->getProductCollection()->getFlag($this->getDateToAttrCode() . '_table_joined')) {
            $conditions['aw_new_spec_condition'] = [
                '('
                    . 'COALESCE(' . $this->getDateFromAttrCode() . '.value, '
                        . $this->getDateFromAttrCode() . '_default.value, '
                        . $this->getDateFromAttrCode() . '_children.value) IS NOT NULL'
                    . ' OR COALESCE(' . $this->getDateToAttrCode()
                        . '.value, ' . $this->getDateToAttrCode(). '_default.value, '
                        . $this->getDateToAttrCode() . '_children.value) IS NOT NULL'
                . ')'
            ];
        }
        return $conditions;
    }
}
