<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Layerednav\Model\Plugin;

use Aheadworks\Layerednav\Model\PageTypeResolver;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Result
 * @package Aheadworks\Layerednav\Model\Plugin
 */
class Result
{
    const PROCESS_OUTPUT_FLAG = 'aw_layered_nav_process_output';

    /**
     * @var RequestInterface|\Magento\Framework\App\Request\Http
     */
    private $request;

    /**
     * @var LayoutInterface
     */
    private $layout;

    /**
     * @var PageTypeResolver
     */
    private $pageTypeResolver;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param RequestInterface $request
     * @param LayoutInterface $layout
     * @param PageTypeResolver $pageTypeResolver
     */
    public function __construct(
        RequestInterface $request,
        LayoutInterface $layout,
        PageTypeResolver $pageTypeResolver,
        StoreManagerInterface $storeManager
    ) {
        $this->request = $request;
        $this->layout = $layout;
        $this->pageTypeResolver = $pageTypeResolver;
        $this->storeManager = $storeManager;
    }

    /**
     * @param ResultInterface $result
     * @param \Closure $proceed
     * @param ResponseInterface $response
     * @return ResultInterface
     */
    public function aroundRenderResult(
        ResultInterface $result,
        \Closure $proceed,
        ResponseInterface $response
    ) {
        if ($this->request->isAjax() && $this->request->getParam(self::PROCESS_OUTPUT_FLAG)) {
            $navigationBlockName = $this->pageTypeResolver->getType() == PageTypeResolver::PAGE_TYPE_CATALOG_SEARCH
                ? 'catalogsearch.leftnav'
                : 'catalog.leftnav';

            $elements = [
                'mainColumn' => $this->layout->renderElement('main'),
                'navigation' => $this->layout->renderElement($navigationBlockName)
            ];

            if ($this->storeManager->getStore()->getId() == 3
                && $this->layout->hasElement('top.layered.nav'))
            {
                $elements['topnav'] = $this->layout->renderElement('top.layered.nav');
            }

            /** @var \Magento\Framework\App\Response\Http $response */
            $response->setBody(
                \Zend_Json::encode($elements)
            );
            return $result;
        }
        return $proceed($response);
    }
}
