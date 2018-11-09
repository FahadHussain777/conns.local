<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Layerednav\Model;

use Magento\Framework\View\LayoutInterface;

/**
 * Class PageTypeResolver
 * @package Aheadworks\Layerednav\Model
 */
class PageTypeResolver
{
    /**
     * Category page
     */
    const PAGE_TYPE_CATEGORY = 'category';

    /**
     * Catalog search page
     */
    const PAGE_TYPE_CATALOG_SEARCH = 'catalog_search';

    /**
     * @var LayoutInterface
     */
    private $layout;

    /**
     * @var array
     */
    private $pageHandles = [
        self::PAGE_TYPE_CATEGORY => 'catalog_category_view',
        self::PAGE_TYPE_CATALOG_SEARCH => 'catalogsearch_result_index'
    ];

    /**
     * @param LayoutInterface $layout
     * @param array $pageHandles
     */
    public function __construct(
        LayoutInterface $layout,
        $pageHandles = []
    ) {
        $this->layout = $layout;
        $this->pageHandles = array_merge($this->pageHandles, $pageHandles);
    }

    /**
     * Get page type
     *
     * @return string
     * @throws \Exception
     */
    public function getType()
    {
        $handles = $this->layout->getUpdate()->getHandles();
        foreach ($this->pageHandles as $pageType => $pageHandle) {
            if (in_array($pageHandle, $handles)) {
                return $pageType;
            }
        }
        throw new \Exception('Unable to resolve page type.');
    }
}
