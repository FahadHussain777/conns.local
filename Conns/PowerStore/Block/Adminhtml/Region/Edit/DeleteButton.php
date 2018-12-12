<?php
/*
 * Copyright ©2016 Conn's. All rights reserved.
 */
namespace Conns\PowerStore\Block\Adminhtml\Region\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class DeleteButton
 * @package Conns\Pronav\Block\Adminhtml\Navigation\Edit
 */
class DeleteButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        if(!$this->getObjectId()) { return []; }
        return [
                'label' => __('Delete Item'),
                'class' => 'delete',
                'on_click' => 'deleteConfirm( \'' . __(
                    'Are you sure you want to do this item?'
                ) . '\', \'' . $this->getDeleteUrl() . '\')',
                'sort_order' => 20,
            ];
    }
}
