<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Layerednav\Block;

use Aheadworks\Layerednav\Model\Applier;
use Aheadworks\Layerednav\Model\Config;
use Aheadworks\Layerednav\Model\Layer\FilterList;
use Aheadworks\Layerednav\Model\Layer\FilterListResolver;
use Aheadworks\Layerednav\Model\PageTypeResolver;
use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\AvailabilityFlagInterface;
use Magento\Catalog\Model\Layer\Filter\Item as FilterItem;
use Magento\Catalog\Model\Layer\Resolver as LayerResolver;
use Magento\Search\Model\QueryFactory;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class Navigation
 * @package Aheadworks\Layerednav\Block
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Navigation extends Template
{
    /**
     * @var Layer
     */
    private $layer;

    /**
     * @var FilterList
     */
    private $filterList;

    /**
     * @var AvailabilityFlagInterface
     */
    private $visibilityFlag;

    /**
     * @var Applier
     */
    private $applier;

    /**
     * @var PageTypeResolver
     */
    private $pageTypeResolver;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var QueryFactory
     */
    private $searchQueryFactory;

    /**
     * @param Context $context
     * @param LayerResolver $layerResolver
     * @param FilterListResolver $filterListResolver
     * @param AvailabilityFlagInterface $visibilityFlag
     * @param Applier $applier
     * @param PageTypeResolver $pageTypeResolver
     * @param Config $config
     * @param QueryFactory $searchQueryFactory
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        LayerResolver $layerResolver,
        FilterListResolver $filterListResolver,
        AvailabilityFlagInterface $visibilityFlag,
        Applier $applier,
        PageTypeResolver $pageTypeResolver,
        Config $config,
        QueryFactory $searchQueryFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->layer = $layerResolver->get();
        $this->filterList = $filterListResolver->get();
        $this->visibilityFlag = $visibilityFlag;
        $this->applier = $applier;
        $this->pageTypeResolver = $pageTypeResolver;
        $this->config = $config;
        $this->searchQueryFactory = $searchQueryFactory;
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareLayout()
    {
        $this->applier->applyFilters($this->layer);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function toHtml()
    {
        if (!$this->visibilityFlag->isEnabled($this->layer, $this->getFilters())) {
            return '';
        }
        return parent::toHtml();
    }

    /**
     * Get filters
     *
     * @return Layer\Filter\AbstractFilter[]
     */
    public function getFilters()
    {
        return $this->filterList->getFilters($this->layer);
    }

    /**
     * Get items count url
     *
     * @return string
     */
    public function getItemsCountUrl()
    {
        return $this->_urlBuilder->getUrl(
            'awlayerednav/ajax/itemsCount',
            ['_secure' => $this->_storeManager->getStore()->isCurrentlySecure()]
        );
    }

    /**
     * Get category id
     *
     * @return int
     */
    public function getCategoryId()
    {
        return $this->layer->getCurrentCategory()->getId();
    }

    /**
     * Check if block has active filters
     *
     * @return bool
     */
    public function hasActiveFilters()
    {
        return !empty($this->layer->getState()->getFilters());
    }

    /**
     * Get page type
     *
     * @return string
     * @throws \Exception
     */
    public function getPageType()
    {
        return $this->pageTypeResolver->getType();
    }

    /**
     * Get search query text
     *
     * @return string
     */
    public function getSearchQueryText()
    {
        if ($this->getPageType() == PageTypeResolver::PAGE_TYPE_CATALOG_SEARCH) {
            return $this->searchQueryFactory->get()->getQueryText();
        }
        return '';
    }

    /**
     * Check if AJAX enabled on storefront
     *
     * @return bool
     */
    public function isAjaxEnabled()
    {
        return $this->config->isAjaxEnabled();
    }

    /**
     * Check if "Show X Items" Pop-over disabled
     *
     * @return bool
     */
    public function isPopoverDisabled()
    {
        return $this->config->isPopoverDisabled();
    }

    /**
     * Get page layout
     *
     * @return string
     */
    public function getPageLayout()
    {
        return $this->pageConfig->getPageLayout() ?: $this->getLayout()->getUpdate()->getPageLayout();
    }
    
    /**
     * Get Current Category Name
     *
     * @return string
     */
    public function getCurrentCategoryName()
    {
    	return $this->layer->getCurrentCategory()->getName();
    }
    
    /**
     * Get Parent Category of current category
     *
     * @return Magento\Catalog\Model\Category
     */
    public function getParentCategory()
    {
    	return $this->layer->getCurrentCategory()->getParentCategory();
    }

    public function countActiveFilters()
    {
        $activeCount = 0;
        foreach($this->layer->getState()->getFilters() as $eachFilter) {
            $filterValues = explode(',', $eachFilter->getValue());
            $activeCount += count($filterValues);
        }

        return $activeCount;
    }    
}
