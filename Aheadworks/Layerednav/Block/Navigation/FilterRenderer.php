<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Layerednav\Block\Navigation;

use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Filter\FilterInterface;
use Magento\Catalog\Model\Layer\Filter\Item as FilterItem;
use Magento\Catalog\Model\Layer\Resolver as LayerResolver;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\LayeredNavigation\Block\Navigation\FilterRendererInterface;
use Aheadworks\Layerednav\Model\Config;

/**
 * Class FilterRenderer
 * @package Aheadworks\Layerednav\Block\Navigation\FilterRenderer
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
     * @param Context $context
     * @param LayerResolver $layerResolver
     * @param Config $config
     * @param array $data
     */
    public function __construct(
        Context $context,
        LayerResolver $layerResolver,
        Config $config,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->layer = $layerResolver->get();
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function render(FilterInterface $filter)
    {
        $this->assign('filterItems', $filter->getItems());
        $html = $this->_toHtml();
        $this->assign('filterItems', []);
        return $html;
    }

    /**
     * Check if filter item is active
     *
     * @param FilterItem $item
     * @return bool
     * @throws LocalizedException
     */
    public function isActiveItem(FilterItem $item)
    {
        foreach ($this->layer->getState()->getFilters() as $filter) {
            $filterValues = explode(',', $filter->getValue());
            if ($filter->getFilter()->getRequestVar() == $item->getFilter()->getRequestVar()
                && false !== array_search($item->getValue(), $filterValues)
            ) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if need to show products count in the parentheses
     *
     * @param FilterItem $item
     * @return bool
     */
    public function isNeedToShowProductsCount(FilterItem $item)
    {
        return $this->config->isNeedToShowProductsCount()
            && (!$this->isActiveItem($item))
            && $item->getCount();
    }

    /**
    * Sort the filters w.r.t active
    * flag of filters
    *
    * @param void
    * @return array
    **/
    public function getSortedFilters($filterItems){
        $active = array();
        $notactive = array();
        foreach ($filterItems as $filterItem):
            if( $this->isActiveItem($filterItem) ):
                $active[] = $filterItem;
            else:
                $notactive[] = $filterItem;
            endif;
        endforeach;
        return array_merge($active, $notactive);
    }
}
