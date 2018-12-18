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
    public $regionFactory;
    protected $locatorHelper;
    public function __construct(
        \Magento\Framework\App\ActionFactory $actionFactory,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\UrlInterface $url,
        \BrainActs\StoreLocator\Model\LocatorFactory $locatorFactory,
        \Conns\PowerStore\Model\StoreRegionFactory $regionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\ResponseInterface $response,
        \BrainActs\StoreLocator\Helper\Data $locatorHelper
    ) {
        $this->regionFactory = $regionFactory;
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
            $request->setModuleName('powerstore')
                ->setControllerName('region')
                ->setActionName('index');
            $request->setAlias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, $identifier);
            return $this->actionFactory->create('Magento\Framework\App\Action\Forward');
        }
        $urlSegments = explode('/',$identifier);
        if($urlSegments[0] === $locatorIdentifier){
            if($urlSegments[1] === 'search'){
                $request->setModuleName('brainacts_storelocator')
                    ->setControllerName('locator')
                    ->setActionName('search');
                $request->setAlias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, $identifier);
                return $this->actionFactory->create('Magento\Framework\App\Action\Forward');
            }
            $regionKey = $this->regionFactory->create()->checkIdentifier($urlSegments[1]);
            if(empty($regionKey) && $urlSegments[1] != 'all') return null;
            if(count($urlSegments) == 2 ){
                if($urlSegments[1] == 'all'){
                    $request->setModuleName('powerstore')
                        ->setControllerName('region')
                        ->setActionName('all');
                    $request->setAlias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, $identifier);
                    return $this->actionFactory->create('Magento\Framework\App\Action\Forward');

                }
                $request->setModuleName('powerstore')
                    ->setControllerName('region')
                    ->setActionName('view')->setParam('id',$regionKey);
                $request->setAlias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, $identifier);
                return $this->actionFactory->create('Magento\Framework\App\Action\Forward');
            }
            elseif (count($urlSegments) == 3){
                $locator = $this->locatorFactory->create();
                $locatorId = $locator->checkRegionIdentifier($urlSegments[2], $regionKey,$this->storeManager->getStore()->getId());
                if(empty($locatorId)) return null;
                $request->setModuleName('brainacts_storelocator')
                    ->setControllerName('locator')
                    ->setActionName('view')
                    ->setParam('locator_id', $locatorId);
                $request->setAlias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, $identifier);
                $request->setAlias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, $identifier);
                return $this->actionFactory->create('Magento\Framework\App\Action\Forward');
            }
            else{
                return null;
            }
        }
    }
}