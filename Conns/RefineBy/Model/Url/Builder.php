<?php
/**
 * Copyright © 2018 Conn's. All rights reserved.
 */
namespace Conns\RefineBy\Model\Url;

/**
 * Class Builder
 * @package Conns\RefineBy\Model\Url
 */
class Builder extends \Magento\Framework\Url
{
    /**
     * @param $code
     * @param $value
     * @param array $query
     * @param bool $singleValue
     * @return string
     */
    public function getFilterUrl($code, $value, $query = [], $singleValue = false){
        $params = ['_current' => true, '_use_rewrite' => true, '_query' => $query];
        $values = array_unique(
            array_merge(
                $this->getValuesFromUrl($code),
                [$value]
            )
        );
        $params['_query'][$code] = implode(',', $values);
        return urldecode($this->getUrl('*/*/*', $params));
    }

    /**
     * @param $code
     * @param $value
     * @param array $query
     * @return string
     */
    public function getRemoveFilterUrl($code, $value, $query = []){
        $params = ['_current' => true, '_use_rewrite' => true, '_query' => $query, '_escape' => true];
        $values = $this->getValuesFromUrl($code);
        $key = array_search($value, $values);
        unset($values[$key]);
        $params['_query'][$code] = $values ? implode(',', $values) : null;
        return urldecode($this->getUrl('*/*/*', $params));
    }

    /**
     * @param $code
     * @return array
     */
    public function getValuesFromUrl($code){
        return array_filter(explode(',', $this->_getRequest()->getParam($code)));
    }
}