<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Layerednav\Model\Layer;

use Aheadworks\Layerednav\Model\PageTypeResolver;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class FilterListResolver
 * @package Aheadworks\Layerednav\Model\Layer
 */
class FilterListResolver
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var PageTypeResolver
     */
    private $pageTypeResolver;

    /**
     * @var array
     */
    private $filterListPool;

    /**
     * @var FilterList
     */
    private $filterList;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param PageTypeResolver $pageTypeResolver
     * @param array $filterListPool
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        PageTypeResolver $pageTypeResolver,
        array $filterListPool
    ) {
        $this->objectManager = $objectManager;
        $this->pageTypeResolver = $pageTypeResolver;
        $this->filterListPool = $filterListPool;
    }

    /**
     * Create filter list object
     *
     * @param string|null $pageType
     * @throws \Exception
     * @return void
     */
    public function create($pageType = null)
    {
        if (!$pageType) {
            $pageType = $this->pageTypeResolver->getType();
        }
        if (isset($this->filterList)) {
            throw new \RuntimeException('Filter List has been already created');
        }
        if (!isset($this->filterListPool[$pageType])) {
            throw new \InvalidArgumentException($pageType . ' does not belong to any registered filter list');
        }
        $this->filterList = $this->objectManager->create($this->filterListPool[$pageType]);
    }

    /**
     * Get filter list object
     *
     * @return FilterList
     * @throws \Exception
     */
    public function get()
    {
        if (!$this->filterList) {
            $this->filterList = $this->objectManager->create(
                $this->filterListPool[$this->pageTypeResolver->getType()]
            );
        }
        return $this->filterList;
    }
}
