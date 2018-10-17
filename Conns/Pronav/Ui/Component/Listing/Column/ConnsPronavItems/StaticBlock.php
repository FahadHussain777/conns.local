<?php
/*
 * Copyright ©2016 Conn's. All rights reserved.
 */

namespace Conns\Pronav\Ui\Component\Listing\Column\ConnsPronavItems;

use Magento\Cms\Model\ResourceModel\Block\CollectionFactory;

/**
 * Class StaticBlock
 * @package Conns\Pronav\Ui\Component\Listing\Column\ConnsPronavItems
 */
class StaticBlock implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magento\Cms\Model\ResourceModel\Block\Collection
     */
    protected $cmsBlock;

    /**
     * StaticBlock constructor.
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        CollectionFactory $collectionFactory
    ){
        $this->cmsBlock = $collectionFactory
            ->create()
            ->addFieldToFilter('title',array('like' => 'ProNav'. '%'));
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $optionArray = [];
        foreach ($this->cmsBlock as $block){
            $optionArray[] = [
                'value' => $block->getId(),
                'label' => __($block->getTitle())
            ];
        }
        return $optionArray;
    }
}