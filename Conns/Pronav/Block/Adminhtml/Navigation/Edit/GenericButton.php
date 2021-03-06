<?php
/*
 * Copyright ©2016 Conn's. All rights reserved.
 */

namespace Conns\Pronav\Block\Adminhtml\Navigation\Edit;

/**
 * Class GenericButton
 * @package Conns\Pronav\Block\Adminhtml\Navigation\Edit
 */
class GenericButton
{
    /**
     * @var \Magento\Backend\Block\Widget\Context
     */
    protected $context;

    /**
     * GenericButton constructor.
     * @param \Magento\Backend\Block\Widget\Context $context
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context
    ) {
        $this->context = $context;
    }

    /**
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('*/items/');
    }

    /**
     * @return string
     */
    public function getDeleteUrl()
    {
        return $this->getUrl('*/*/delete', ['object_id' => $this->getObjectId()]);
    }

    /**
     * @param string $route
     * @param array $params
     * @return string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }

    /**
     * @return mixed
     */
    public function getObjectId()
    {
        return $this->context->getRequest()->getParam('pronav_id');
    }     
}
