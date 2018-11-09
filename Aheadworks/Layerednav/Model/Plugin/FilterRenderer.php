<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Layerednav\Model\Plugin;

use Aheadworks\Layerednav\Block\Navigation\Swatches\FilterRenderer as SwatchesFilterRenderer;
use Aheadworks\Layerednav\Block\Navigation\PriceSlider\FilterRenderer as PriceFilterRenderer;
use Aheadworks\Layerednav\Model\Config;
use Aheadworks\Layerednav\Model\Layer\FilterList;
use Magento\Catalog\Model\Layer\Filter\FilterInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Swatches\Helper\Data as SwatchesHelper;

/**
 * Class FilterRenderer
 * @package Aheadworks\Layerednav\Model\Plugin
 */
class FilterRenderer
{
    /**
     * @var LayoutInterface
     */
    private $layout;

    /**
     * @var SwatchesHelper
     */
    private $swatchHelper;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param LayoutInterface $layout
     * @param SwatchesHelper $swatchHelper
     * @param Config $config
     */
    public function __construct(
        LayoutInterface $layout,
        SwatchesHelper $swatchHelper,
        Config $config
    ) {
        $this->layout = $layout;
        $this->swatchHelper = $swatchHelper;
        $this->config = $config;
    }

    /**
     * @param \Aheadworks\Layerednav\Block\Navigation\FilterRenderer $subject
     * @param \Closure $proceed
     * @param FilterInterface $filter
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundRender(
        \Aheadworks\Layerednav\Block\Navigation\FilterRenderer $subject,
        \Closure $proceed,
        FilterInterface $filter
    ) {
        if ($filter->hasAttributeModel()) {
            if ($this->swatchHelper->isSwatchAttribute($filter->getAttributeModel())) {
                /** @var SwatchesFilterRenderer $swatchFilterRenderer */
                $swatchFilterRenderer = $this->layout->createBlock(SwatchesFilterRenderer::class);
                return $swatchFilterRenderer->setSwatchFilter($filter)->toHtml();
            } elseif (
                $filter->getAttributeModel()->getAttributeCode() == FilterList::PRICE_FILTER
                && ($this->config->isPriceSliderEnabled() || $this->config->isPriceFromToEnabled())
                ) {
                $priceFilterRenderer = $this->layout->createBlock(PriceFilterRenderer::class);
                return $priceFilterRenderer->render($filter);
            }
        }
        return $proceed($filter);
    }
}
