<?php
/*
 * Copyright ©2016 Conn's. All rights reserved.
 */

namespace Conns\PowerStore\Ui\Component\Listing\Column;


/**
 * Class Status
 * @package Conns\Pronav\Ui\Component\Listing\Column\ConnsPronavItems
 */
class Status implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $optionArray = [
            ['value' => 1, 'label' => __('Enable')],
            ['value' => 0, 'label' => __('Disable')]
        ];
        return $optionArray;
    }
}