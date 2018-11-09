<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Layerednav\Model\ResourceModel\Layer\Filter\Custom;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Layer\Filter\FilterInterface;
use Magento\Catalog\Model\ResourceModel\Product\Collection;

/**
 * Class Sales
 * @package Aheadworks\Layerednav\Model\ResourceModel\Layer\Filter\Custom
 */
class Sales extends AbstractFilter
{
    const DATE_TO_ATTRIBUTE_CODE = 'special_to_date';
    const DATE_FROM_ATTRIBUTE_CODE = 'special_from_date';

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
        $selectInnerJoinAliases = $collection->getSelect()->getPart(\Zend_Db_Select::FROM);
        if (!array_key_exists('special_price', $selectInnerJoinAliases)) {
            $collection->setFlag($this->getDateToAttrCode() . '_table_joined', false);
        }
        if (!$collection->getFlag($this->getDateToAttrCode() . '_table_joined')) {
            $linkFieldName = $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField();

            $specialPriceId = $this->resourceAttribute->getIdByCode('catalog_product', 'special_price');
            $tableAliasPrice = $this->getTable('catalog_product_entity_decimal');
            $collection
                ->getSelect()
                ->joinLeft(
                    ['special_price' => $tableAliasPrice],
                    '(special_price.' . $linkFieldName . ' = e.entity_id)'
                    . ' AND (special_price.attribute_id = ' . $specialPriceId . ')'
                    . ' AND special_price.store_id = 0'
                    . ' AND NOT special_price.value IS NULL'
                    . ' AND special_price.value < price_index.price',
                    []
                )
                ->joinLeft(
                    ['spec_super_link' => $this->getTable('catalog_product_super_link')],
                    '(spec_super_link.parent_id = e.entity_id)',
                    []
                )
                ->joinLeft(
                    ['children_special_price' => $tableAliasPrice],
                    '(children_special_price.' . $linkFieldName . ' = spec_super_link.product_id)'
                    . ' AND (children_special_price.attribute_id = ' . $specialPriceId . ')'
                    . ' AND children_special_price.store_id = 0'
                    . ' AND NOT children_special_price.value IS NULL'
                    . ' AND children_special_price.value < price_index.price',
                    []
                )
                ->where("(COALESCE(special_price.value, children_special_price.value) IS NOT NULL)");
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
                '(COALESCE(special_price.value, children_special_price.value) IS NOT NULL)'
            ];
        }
        return $conditions;
    }
}
