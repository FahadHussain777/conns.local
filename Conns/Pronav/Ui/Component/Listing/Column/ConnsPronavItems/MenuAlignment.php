<?php
/*
 * Copyright ©2016 Conn's. All rights reserved.
 */

namespace Conns\Pronav\Ui\Component\Listing\Column\ConnsPronavItems;

/**
 * Class MenuAlignment
 * @package Conns\Pronav\Ui\Component\Listing\Column\ConnsPronavItems
 */
class MenuAlignment implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $optionArray = [
            ['value' => 1, 'label' => __('Align to Relative Nav Item')],
            ['value' => 2, 'label' => __('Align to Navigation Start')],
            ['value' => 3, 'label' => __('Align to Navigation End')]
        ];
        return $optionArray;
    }
}