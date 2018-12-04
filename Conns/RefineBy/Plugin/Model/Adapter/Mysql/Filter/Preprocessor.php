<?php
/**
 * Copyright © 2018 Conn's. All rights reserved.
 */
namespace Conns\RefineBy\Plugin\Model\Adapter\Mysql\Filter;

use Conns\RefineBy\Model\Url\Builder;
use Magento\CatalogSearch\Model\Adapter\Mysql\Filter\Preprocessor as MagentoPreprocessor;
use Magento\Framework\Search\Request\FilterInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Eav\Model\Config;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\ScopeResolverInterface;

/**
 * Class Preprocessor
 * @package Conns\RefineBy\Plugin\Model\Adapter\Mysql\Filter
 */
class Preprocessor
{
    /**
     * @var Builder
     */
    protected $urlBuilder;

    /**
     * @var mixed
     */
    protected $customerSession;

    protected $config;

    protected $scopeResolver;

    /**
     * Preprocessor constructor.
     * @param Builder $urlBuilder
     */
    public function __construct(
        Builder $urlBuilder,
        ScopeResolverInterface $scopeResolver,
        Config $config
    ){
        $this->config = $config;
        $this->urlBuilder = $urlBuilder;
        $this->scopeResolver = $scopeResolver;
        $this->customerSession = ObjectManager::getInstance()->get(\Magento\Customer\Model\Session::class);
    }


    /**
     * @param MagentoPreprocessor $subject
     * @param \Closure $proceed
     * @param FilterInterface $filter
     * @param $isNegation
     * @param $query
     * @return mixed|string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundProcess(
        MagentoPreprocessor  $subject,
        \Closure $proceed,
        FilterInterface $filter,
        $isNegation,
        $query
    ){
        if($filter->getField() === 'monthly_payment_amount'){
            $monthlypayment = $this->urlBuilder->getValuesFromUrl('monthly_payment_amount');
            $attribute = $this->config->getAttribute(Product::ENTITY, $filter->getField());
            if(!empty($monthlypayment)){
                $statements = [];
                foreach ($monthlypayment as $value){
                    list($from,$to) = explode('-',$value);
                    $statement = $this->getMonthlyPaymentQuery($from,$to,$attribute);
                    $statements[] = implode(" AND ", $statement);
                }
                return implode(" OR ", $statements);
            }
        }
        if($filter->getField() === 'price'){
            $price = $this->urlBuilder->getValuesFromUrl('price');
            if(!empty($price)){
                $statements = [];
                foreach ($price as $value) {
                    list($from, $to) = explode("-", $value);
                    $statement = [
                        $this->getSqlStringByArray(
                            [(float)$from],
                            '`price_index`.`min_price`',
                            '>='
                        ),
                        $this->getSqlStringByArray(
                            [(float) $to],
                            '`price_index`.`min_price`',
                            '<='
                        )
                    ];
                    //$statements[] = '('.implode(" AND ", $statement).')';
                    $statements[] = implode(" AND ", $statement);
                }
                $query = implode(" OR ", $statements)." AND `price_index`.`customer_group_id` =".$this->customerSession->getCustomerGroupId();
                return $query;
            }
        }
        if($filter->getField() === 'category_ids'){
            if(is_array($filter->getValue())){
                if(isset($filter->getValue()['in'])){
                    return $this->getSqlStringByArray($filter->getValue()['in']);
                }
                return $this->getSqlStringByArray($filter->getValue());
            }
            elseif(is_string($filter->getValue())){
                return $this->getSqlStringByArray([$filter->getValue()]);
            }
        }
        return $proceed($filter, $isNegation, $query);
    }

    /**
     * @param array $array
     * @param string $field
     * @param string $operator
     * @param string $rule
     * @return string
     */
    private function getSqlStringByArray(
        $array = [],
        $field = 'category_ids_index.category_id',
        $operator = '=',
        $rule = 'OR'
    ){
        $statements = [];
        if(!empty($array)){
            foreach ($array as $value) {
                $statements[] = $field.' '.$operator.' '.$value;
            }
        }
        return implode(' '.$rule.' ', $statements);
    }

    /**
     * @param $from
     * @param $to
     * @return array
     */
    private function getMonthlyPaymentQuery($from, $to,$attribute){
        if($from === 0){
            $from=1;
        }
        $attributeId = $attribute->getAttributeId();
        $currentStoreId = $this->scopeResolver->getScope()->getId();
        $query = array(0=>"search_index.entity_id IN (select entity_id from  (SELECT `e`.`entity_id`, `main_table`.`value` AS `monthly_payment_amount` FROM `catalog_product_entity` AS `e` INNER JOIN `catalog_product_index_eav_decimal` AS `main_table` ON main_table.entity_id = e.entity_id WHERE ((main_table.attribute_id = '".$attributeId."') AND (main_table.store_id = '".$currentStoreId."')) AND (e.created_in <= 1) AND (e.updated_in > 1) HAVING (`monthly_payment_amount` >= '".(float)($from-0.99)."' AND `monthly_payment_amount` <= '".$to."')) as filter )");
        return $query;
    }
}