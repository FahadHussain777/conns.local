<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Layerednav\Block\Navigation\PriceSlider;

use Aheadworks\Layerednav\Model\Config;
use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Filter\FilterInterface;
use Magento\Catalog\Model\Layer\Filter\Item as FilterItem;
use Magento\Catalog\Model\Layer\Resolver as LayerResolver;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\LayeredNavigation\Block\Navigation\FilterRendererInterface;

/**
 * Class FilterRenderer
 * @package Aheadworks\Layerednav\Block\Navigation\PriceSlider
 */
class FilterRenderer extends Template implements FilterRendererInterface
{
    /**
     * @var Layer
     */
    private $layer;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * {@inheritdoc}
     */
    protected $_template = 'Aheadworks_Layerednav::layer/renderer/price_slider/filter.phtml';

    /**
     * @param Context $context
     * @param LayerResolver $layerResolver
     * @param Config $config
     * @param PriceCurrencyInterface $priceCurrency
     * @param array $data
     */
    public function __construct(
        Context $context,
        LayerResolver $layerResolver,
        Config $config,
        PriceCurrencyInterface $priceCurrency,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->layer = $layerResolver->get();
        $this->config = $config;
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * {@inheritdoc}
     */
    public function render(FilterInterface $filter)
    {
        $priceData = $filter->getMinMaxPrices();
        // Case filter is not set
        $priceData['minPrice'] = floor($this->priceCurrency->convertAndRound($priceData['minPrice']));
        $priceData['maxPrice'] = ceil($this->priceCurrency->convertAndRound($priceData['maxPrice']));
        $priceData['fromPrice'] = $priceData['minPrice'];
        $priceData['toPrice'] = $priceData['maxPrice'];

        foreach ($this->layer->getState()->getFilters() as $layerFilter) {
            if ($filter->getRequestVar() == $layerFilter->getFilter()->getRequestVar()) {
                list($fromPrice, $toPrice) = explode('-', $layerFilter->getValue());
                $priceData['fromPrice'] = (double)$fromPrice;
                $priceData['toPrice'] = (double)$toPrice;
            }
        }
        $this->assign($priceData);
        $html = $this->_toHtml();
        return $html;
    }

    /**
     * Is price slider enabled.
     *
     * @return bool
     */
    public function isPriceSliderEnabled()
    {
        return $this->config->isPriceSliderEnabled();
    }

    /**
     * Is from-to inputs enabled
     *
     * @return bool
     */
    public function isFromToInputsEnabled()
    {
        return $this->config->isPriceFromToEnabled();
    }

    /**
     * Is filter button disabled.
     * If popover is enabled, price filter updates immediately on change
     *
     * @return bool
     */
    public function isFilterButtonDisabled()
    {
        return $this->config->isAjaxEnabled() && !$this->config->isPopoverDisabled();
    }

    /**
     * Get currency symbol
     *
     * @return string
     */
    public function getCurrencySymbol()
    {
        return $this->priceCurrency->getCurrencySymbol();
    }
}
