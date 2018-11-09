<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Layerednav\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Layered Navigation config
 */
class Config
{
    /**
     * Configuration path to display 'New' filter flag
     */
    const XML_PATH_NEW_FILTER_ENABLED = 'aw_layerednav/general/display_new';

    /**
     * Configuration path to display 'On Sale' filter flag
     */
    const XML_PATH_ON_SALE_FILTER_ENABLED = 'aw_layerednav/general/display_sales';

    /**
     * Configuration path to display 'In Stock' filter flag
     */
    const XML_PATH_STOCK_FILTER_ENABLED = 'aw_layerednav/general/display_stock';

    /**
     * Configuration path to Enable AJAX flag
     */
    const XML_PATH_AJAX_ENABLED = 'aw_layerednav/general/enable_ajax';

    /**
     * Configuration path to Enable AJAX flag
     */
    const XML_PATH_POPOVER_DISABLED = 'aw_layerednav/general/disable_popover';

    /**
     * Configuration path to enable price slider
     */
    const XML_PATH_PRICE_SLIDER_ENABLED = 'aw_layerednav/general/enable_price_slider';

    /**
     * Configuration path to enable price from-to inputs
     */
    const XML_PATH_PRICE_FROM_TO_ENABLED = 'aw_layerednav/general/enable_price_from_to_inputs';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Check if 'New' filter enabled
     *
     * @return bool
     */
    public function isNewFilterEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_NEW_FILTER_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Check if 'On Sale' filter enabled
     *
     * @return bool
     */
    public function isOnSaleFilterEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ON_SALE_FILTER_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Check if 'In Stock' filter enabled
     *
     * @return bool
     */
    public function isInStockFilterEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_STOCK_FILTER_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Check if AJAX on storefront enabled
     *
     * @return bool
     */
    public function isAjaxEnabled()
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_AJAX_ENABLED);
    }

    /**
     * Check if "Show X Items" Pop-over disabled
     *
     * @return bool
     */
    public function isPopoverDisabled()
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_POPOVER_DISABLED);
    }

    /**
     * Check if need to show products count in the parentheses
     *
     * @return bool
     */
    public function isNeedToShowProductsCount()
    {
        return $this->isPopoverDisabled();
    }

    /**
     * Check if price slider enabled
     *
     * @return bool
     */
    public function isPriceSliderEnabled()
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_PRICE_SLIDER_ENABLED);
    }

    /**
     * Check if price from-to inputs enabled
     *
     * @return bool
     */
    public function isPriceFromToEnabled()
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_PRICE_FROM_TO_ENABLED);
    }
}
