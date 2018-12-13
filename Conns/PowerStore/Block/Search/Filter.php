<?php

namespace Conns\PowerStore\Block\Search;

use Magento\Framework\View\Element\Template;
use BrainActs\StoreLocator\Model\ResourceModel\LocatorFactory;
use BrainActs\StoreLocator\Model\ResourceModel\Locator\CollectionFactory as LocatorCollectionFactory;

class Filter extends \BrainActs\StoreLocator\Block\Search\Filter
{
    public $request;

    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \BrainActs\StoreLocator\Api\LocatorRepositoryInterface $locatorRepository,
        LocatorCollectionFactory $locatorCollectionFactory,
        LocatorFactory $locatorFactory,
        \Magento\Framework\Image\AdapterFactory $imageFactory,
        \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory,
        \BrainActs\StoreLocator\Model\Config\Source\Map\Style $style,
        \Magento\Framework\Locale\Resolver $locale,
        Template\Context $context,
        array $data = []
    ) {
        $this->request = $request;
        parent::__construct(
            $locatorRepository,
            $locatorCollectionFactory,
            $locatorFactory,
            $imageFactory,
            $regionCollectionFactory,
            $countryCollectionFactory,
            $jsonEncoder,
            $httpClientFactory,
            $style,
            $locale,
            $context,
            $data
        );
    }
}