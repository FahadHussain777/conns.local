<?php
/**
 * Copyright © 2018 Conn's. All rights reserved.
 */
namespace Conns\RefineBy\Block\Swatches\RefineBy;

use Magento\Swatches\Block\LayeredNavigation\RenderLayered as MagentoRenderLayered;

/**
 * Class RenderLayered
 * @package Conns\RefineBy\Block\Swatches\RefineBy
 */
class RenderLayered extends MagentoRenderLayered
{
    /**
     * @var \Conns\RefineBy\Model\Url\Builder
     */
    protected $urlBuilder;
    /**
     * @var string
     */
    protected $_template = 'Conns_RefineBy::swatch.phtml';


    /**
     * RenderLayered constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Eav\Model\Entity\Attribute $eavAttribute
     * @param \Magento\Catalog\Model\ResourceModel\Layer\Filter\AttributeFactory $layerAttribute
     * @param \Magento\Swatches\Helper\Data $swatchHelper
     * @param \Magento\Swatches\Helper\Media $mediaHelper
     * @param \Conns\RefineBy\Model\Url\Builder $urlBuilder
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Eav\Model\Entity\Attribute $eavAttribute,
        \Magento\Catalog\Model\ResourceModel\Layer\Filter\AttributeFactory $layerAttribute,
        \Magento\Swatches\Helper\Data $swatchHelper,
        \Magento\Swatches\Helper\Media $mediaHelper,
        \Conns\RefineBy\Model\Url\Builder $urlBuilder,
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct(
            $context,
            $eavAttribute,
            $layerAttribute,
            $swatchHelper,
            $mediaHelper,
            $data
        );
    }

    /**
     * @param string $attributeCode
     * @param int $optionId
     * @return string
     */
    public function buildUrl($attributeCode, $optionId){
        if(in_array($optionId, $this->urlBuilder->getValuesFromUrl($attributeCode))){
            return $this->urlBuilder->getRemoveFilterUrl($attributeCode, $optionId);
        }
        else{
            return $this->urlBuilder->getFilterUrl($attributeCode, $optionId);
        }
    }

    /**
     * @param $optionId
     * @return bool
     */
    public function isActive($optionId){
        $params = $this->getRequest()->getParams();
        $swatchData = $this->getSwatchData();
        if(isset($params[$swatchData['attribute_code']])){
            $ids = explode(',',$params[$swatchData['attribute_code']]);
            if(in_array($optionId,$ids)){
                return true;
            }else
                return false;
        }
        return false;
    }
}