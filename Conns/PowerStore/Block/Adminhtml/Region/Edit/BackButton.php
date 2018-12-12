<?php
/*
 * Copyright ©2016 Conn's. All rights reserved.
 */
namespace Conns\PowerStore\Block\Adminhtml\Region\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class BackButton extends GenericButton implements ButtonProviderInterface
{

    public function getButtonData()
    {
        
        return [
            'label' => __('Back'),
            'on_click' => sprintf("location.href = '%s';", $this->getBackUrl()),
            'class' => 'back',
            'sort_order' => 10    
        ];
    }
}
