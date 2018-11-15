<?php

namespace Conns\RefineBy\Block\Swatches\RefineBy;

use Magento\Swatches\Block\LayeredNavigation\RenderLayered as MagentoRenderLayered;

class RenderLayered extends MagentoRenderLayered
{
    protected $urlBuilder;
    protected $_template = 'Conns_RefineBy::swatch.phtml';


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

    public function buildUrl($attributeCode, $optionId){
        if(in_array($optionId, $this->urlBuilder->getValuesFromUrl($attributeCode))){
            return $this->urlBuilder->getRemoveFilterUrl($attributeCode, $optionId);
        }
        else{
            return $this->urlBuilder->getFilterUrl($attributeCode, $optionId);
        }
    }

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