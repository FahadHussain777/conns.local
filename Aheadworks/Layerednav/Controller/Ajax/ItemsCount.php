<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Layerednav\Controller\Ajax;

use Aheadworks\Layerednav\Model\Applier;
use Aheadworks\Layerednav\Model\Layer\FilterListResolver;
use Aheadworks\Layerednav\Model\PageTypeResolver;
use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Search\Model\QueryFactory;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class ItemsCount
 * @package Aheadworks\Layerednav\Controller\Ajax
 */
class ItemsCount extends Action
{
    /**
     * @var Resolver
     */
    private $layerResolver;

    /**
     * @var FilterListResolver
     */
    private $filterListResolver;

    /**
     * @var Applier
     */
    private $applier;

    /**
     * @param Context $context
     * @param Resolver $layerResolver
     * @param FilterListResolver $filterListResolver
     * @param Applier $applier
     */
    public function __construct(
        Context $context,
        Resolver $layerResolver,
        FilterListResolver $filterListResolver,
        Applier $applier
    ) {
        parent::__construct($context);
        $this->layerResolver = $layerResolver;
        $this->filterListResolver = $filterListResolver;
        $this->applier = $applier;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        /** @var Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        $requestParams = $this->getRequest()->getParams();
        if (isset($requestParams['filterValue'])) {
            $requestParams = array_merge($requestParams, $this->prepareFilterValue($requestParams['filterValue']));
        }
        if ($this->getRequest()->getParam('pageType') == PageTypeResolver::PAGE_TYPE_CATALOG_SEARCH) {
            $requestParams[QueryFactory::QUERY_VAR_NAME] = $this->getRequest()->getParam('searchQueryText');
        }
        $this->getRequest()->setParams($requestParams);

        try {
            $this->filterListResolver->create($this->getRequest()->getParam('pageType'));

            $layer = $this->getLayer();
            $this->applier->applyFilters($layer);

            $itemsCount = $layer->getProductCollection()->getSize();
            return $resultJson->setData(
                [
                    'success' => true,
                    'sequence' => $this->getRequest()->getParam('sequence'),
                    'itemsCount' => $itemsCount,
                    'itemsContent' => __($itemsCount == 1 ? __('%1 item') : __('%1 items'), $itemsCount)
                ]
            );
        } catch (\Exception $e) {
            return $resultJson->setData(['success' => false]);
        }
    }

    /**
     * Prepare filter value
     *
     * @param array $filterValue
     * @return array
     */
    private function prepareFilterValue($filterValue)
    {
        $result = [];
        foreach ($filterValue as $value) {
            $result[$value['key']][] = $value['value'];
        }
        foreach ($result as $key => $param) {
            $result[$key] = implode(',', $param);
        }
        return $result;
    }

    /**
     * Get layer object
     *
     * @return Layer
     * @throws LocalizedException
     */
    private function getLayer()
    {
        $pageType = $this->getRequest()->getParam('pageType');
        if ($pageType == PageTypeResolver::PAGE_TYPE_CATALOG_SEARCH) {
            $this->layerResolver->create(Resolver::CATALOG_LAYER_SEARCH);
        } else {
            $this->layerResolver->create(Resolver::CATALOG_LAYER_CATEGORY);
        }

        $layer = $this->layerResolver->get();
        $layer->setCurrentCategory($this->getRequest()->getParam('categoryId'));

        return $layer;
    }
}
