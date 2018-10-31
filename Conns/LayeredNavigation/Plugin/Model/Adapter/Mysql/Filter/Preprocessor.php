<?php

namespace Conns\LayeredNavigation\Plugin\Model\Adapter\Mysql\Filter;

use Conns\LayeredNavigation\Model\Url\Builder;
use Magento\CatalogSearch\Model\Adapter\Mysql\Filter\Preprocessor as MagentoPreprocessor;
use Magento\Framework\Search\Request\FilterInterface;
use Magento\Framework\App\ObjectManager;

class Preprocessor
{
    protected $urlBuilder;

    protected $customerSession;

    public function __construct(Builder $urlBuilder)
    {
        $this->urlBuilder = $urlBuilder;
        $this->customerSession = ObjectManager::getInstance()->get(\Magento\Customer\Model\Session::class);
    }

    public function aroundProcess(
        MagentoPreprocessor  $subject,
        \Closure $proceed,
        FilterInterface $filter,
        $isNegation,
        $query
    ){
        if($filter->getField() === 'price'){
            $values = $this->urlBuilder->getValuesFromUrl('price');
            if(!empty($values)){
                $statements = [];
                foreach ($values as $value) {
                    list($from, $to) = explode("-", $value);
                    $statement = [
                        $this->getSqlStringByArray(
                            [(float)$from],
                            '`price_index`.`min_price`',
                            '>='
                        ),
                        $this->getSqlStringByArray(
                            [(float) round($to)],
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
}