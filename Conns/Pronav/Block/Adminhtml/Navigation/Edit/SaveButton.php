<?php
/*
 * Copyright ©2016 Conn's. All rights reserved.
 */

namespace Conns\Pronav\Block\Adminhtml\Navigation\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class SaveButton
 * @package Conns\Pronav\Block\Adminhtml\Navigation\Edit
 */
class SaveButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        
        return [
            'label' => __('Save Item'),
            'class' => 'save primary',
            'data_attribute' => [
                'mage-init' => ['button' => ['event' => 'save']],
                'form-role' => 'save',
            ],
            'sort_order' => 90,
        ];
    }
}
