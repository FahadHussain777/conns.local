<?php

namespace Conns\PowerStore\Controller;

class Router extends \BrainActs\StoreLocator\Controller\Router
{
    public $helperLocator;
    public $actionFactory;
    public $url;
    public $eventManager;
    public $locatorFactory;
    public $storeManager;
    public $response;
    protected $locatorHelper;

    public function __construct(
        \Magento\Framework\App\ActionFactory $actionFactory,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\UrlInterface $url,
        \BrainActs\StoreLocator\Model\LocatorFactory $locatorFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\ResponseInterface $response,
        \BrainActs\StoreLocator\Helper\Data $locatorHelper
    ) {
        $this->actionFactory = $actionFactory;
        $this->eventManager = $eventManager;
        $this->url = $url;
        $this->locatorFactory = $locatorFactory;
        $this->storeManager = $storeManager;
        $this->response = $response;
        $this->helperLocator = $locatorHelper;

        parent::__construct(
            $actionFactory,
            $eventManager,
            $url,
            $locatorFactory,
            $storeManager,
            $response,
            $locatorHelper
            );
    }

    public function match(\Magento\Framework\App\RequestInterface $request)
    {
        $identifier = trim($request->getPathInfo(), '/');
        $locatorIdentifier = $this->helperLocator->getLocatorRoute();
        if ($identifier === $locatorIdentifier) {
            $request->setModuleName('brainacts_storelocator')
                ->setControllerName('locator')
                ->setActionName('search');

            $request->setAlias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, $identifier);

            return $this->actionFactory->create('Magento\Framework\App\Action\Forward');
        }

        $pos = strpos($identifier, $locatorIdentifier);

        if ($identifier === false) {
            return null;
        }

        $identifier = str_replace($locatorIdentifier, '', $identifier);
        $identifier = str_replace('/', '', $identifier);

        /** @var \BrainActs\StoreLocator\Model\Locator $locator */
        $locator = $this->locatorFactory->create();

        $locatorId = $locator->checkIdentifier($identifier, $this->storeManager->getStore()->getId());


        if (!$locatorId) {
            return null;
        }

        $request->setModuleName('brainacts_storelocator')
            ->setControllerName('locator')
            ->setActionName('view')
            ->setParam('locator_id', $locatorId);

        $request->setAlias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, $identifier);

        return $this->actionFactory->create('Magento\Framework\App\Action\Forward');
    }

}