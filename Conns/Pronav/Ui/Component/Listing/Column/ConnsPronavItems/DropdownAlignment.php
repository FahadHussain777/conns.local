<?php
/*
 * Copyright ©2016 Conn's. All rights reserved.
 */

namespace Conns\Pronav\Ui\Component\Listing\Column\ConnsPronavItems;


/**
 * Class DropdownAlignment
 * @package Conns\Pronav\Ui\Component\Listing\Column\ConnsPronavItems
 */
class DropdownAlignment implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $optionArray = [
            ['value' => 1, 'label' => __('Left')],
            ['value' => 2, 'label' => __('Right')]
        ];
        return $optionArray;
    }
}