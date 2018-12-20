<?php

namespace Conns\PowerStore\Block\Region;

use Magento\Framework\View\Element\Template;

class Description extends \Magento\Framework\View\Element\Template
{
    protected $regionModel;
    public function __construct(
        Template\Context $context,
        \Conns\PowerStore\Model\StoreRegion $regionModel,
        array $data = []
    ){
        $this->regionModel = $regionModel;
        parent::__construct($context, $data);
    }

    public function getRegionDescription(){
        $region = $this->regionModel->load($this->getRequest()->getParam('id'));
        return $region->getDescription();
    }
}