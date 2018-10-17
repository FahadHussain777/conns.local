<?php
/*
 * Copyright ©2016 Conn's. All rights reserved.
 */

namespace Conns\Pronav\Ui\Component\Listing\Column\ConnsPronavItems;

use Magento\Store\Ui\Component\Listing\Column\Store\Options as StoreOptions;

/**
 * Class Stores
 * @package Conns\Pronav\Ui\Component\Listing\Column\ConnsPronavItems
 */
class Stores extends StoreOptions
{
    /**
     * All Store Views value
     */
    const ALL_STORE_VIEWS = '0';

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->options !== null) {
            return $this->options;
        }

        $this->currentOptions['All Store Views']['label'] = __('All Store Views');
        $this->currentOptions['All Store Views']['value'] = self::ALL_STORE_VIEWS;

        $this->generateCurrentOptions();

        $this->options = array_values($this->currentOptions);

        return $this->options;
    }
}