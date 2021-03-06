<?php
/**
 * Copyright © 2018 Conn's. All rights reserved.
 */
namespace Conns\RefineBy\Model\Layer\Filter;

use Magento\Catalog\Model\Layer\Filter\ItemFactory;
use Magento\Catalog\Ui\DataProvider\Product\ProductCollection;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Filter\Item\DataBuilder;
use Magento\Catalog\Model\ResourceModel\Layer\Filter\Price as FilterPrice;
use Magento\Customer\Model\Session;
use Magento\Framework\Search\Dynamic\Algorithm;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Catalog\Model\Layer\Filter\Dynamic\AlgorithmFactory;
use Magento\Catalog\Model\Layer\Filter\DataProvider\PriceFactory;
use Conns\RefineBy\Model\Url\Builder;
use Conns\RefineBy\Model\Layer\ItemCollectionProvider;

/**
 * Class Price
 * @package Conns\RefineBy\Model\Layer\Filter
 */
class Price extends \Magento\CatalogSearch\Model\Layer\Filter\Price
{
    /**
     * Decimal places precision
     */
    const PRICE_DELTA = 0.01;
    /**
     * @var PriceFactory $dataProvider
     */
    protected $dataProvider;
    /**
     * @var Builder
     */
    protected $urlBuilder;
    /**
     * @var ItemCollectionProvider
     */
    protected $collectionProvider;
    /**
     * @var ProductCollection
     */
    protected $emptyCollection;
    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * Price constructor.
     * @param ItemFactory $filterItemFactory
     * @param StoreManagerInterface $storeManager
     * @param Layer $layer
     * @param DataBuilder $itemDataBuilder
     * @param FilterPrice $resource
     * @param Session $customerSession
     * @param Algorithm $priceAlgorithm
     * @param PriceCurrencyInterface $priceCurrency
     * @param AlgorithmFactory $algorithmFactory
     * @param PriceFactory $dataProviderFactory
     * @param Builder $urlBuilder
     * @param ItemCollectionProvider $collectionProvider
     * @param array $data
     */
    public function __construct(
        ItemFactory $filterItemFactory,
        StoreManagerInterface $storeManager,
        Layer $layer,
        DataBuilder $itemDataBuilder,
        FilterPrice $resource,
        Session $customerSession,
        Algorithm $priceAlgorithm,
        PriceCurrencyInterface $priceCurrency,
        AlgorithmFactory $algorithmFactory,
        PriceFactory $dataProviderFactory,
        Builder $urlBuilder,
        ItemCollectionProvider $collectionProvider,
        array $data = [])
    {
        parent::__construct(
            $filterItemFactory,
            $storeManager,
            $layer,
            $itemDataBuilder,
            $resource,
            $customerSession,
            $priceAlgorithm,
            $priceCurrency,
            $algorithmFactory,
            $dataProviderFactory,
            $data
        );
        $this->dataProvider = $dataProviderFactory->create(['layer' => $this->getLayer()]);
        $this->urlBuilder = $urlBuilder;
        $this->collectionProvider = $collectionProvider;
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @return $this|\Magento\CatalogSearch\Model\Layer\Filter\Price
     */
    public function apply(\Magento\Framework\App\RequestInterface $request){
        $this->applyToCollection($this->getLayer()->getProductCollection(), true);
        return $this;
    }

    /**
     * @param $collection
     * @param bool $addFilter
     * @return $this
     */
    public function applyToCollection($collection, $addFilter = false){
        $values = $this->urlBuilder->getValuesFromUrl($this->_requestVar);
        if (!$values){
            return $this;
        }
        if($addFilter) {
            foreach ($values as $value) {
                list($from, $to) = explode("-", $value);
                $label = $this->_renderRangeLabel($from, $to);
                $this->getLayer()->getState()->addFilter($this->_createItem($label, $value));
            }
        }
        $collection->addFieldToFilter(
            'price',
            [
                'from' => $this->getMin(),
                'to' => $this->getMax()
            ]
        );
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMax(){
        return $this->getCollectionWithoutFilter()->getMaxPrice();
    }

    /**
     * @return mixed
     */
    public function getMin(){
        return $this->getCollectionWithoutFilter()->getMinPrice();
    }

    /**
     * @param float $from
     * @return float|string
     */
    protected function getTo($from){
        $to = '';
        $interval = $this->dataProvider->getInterval();
        if ($interval && is_numeric($interval[1]) && $interval[1] > $from) {
            $to = $interval[1];
        }
        return $to;
    }

    /**
     * @param float $from
     * @return float|string
     */
    protected function getFrom($from){
        $to = '';
        $interval = $this->dataProvider->getInterval();
        if ($interval && is_numeric($interval[0]) && $interval[0] < $from) {
            $to = $interval[0];
        }
        return $to;
    }

    /**
     * @return array
     */
    protected function _getItemsData(){
        $values = $this->urlBuilder->getValuesFromUrl($this->_requestVar);
        $attribute = $this->getAttributeModel();
        $productCollection = $this->getLayer()->getProductCollection();
        $facets = $this->getCollectionWithoutFilter()->getFacetedData($attribute->getAttributeCode());
        $data = [];
        if(!empty($facets)){
            $i=0;
            foreach ($facets as $key => $aggregation) {
                if (strpos($key, '_') === false) {
                    continue;
                }
                list($from, $to) = explode('_', $key);
                if($from == '*') {
                    $from = $this->getMin();
                }
                if($to == '*') {
                    $to = $this->getMax();
                }
                if($to !== $this->getMax()){
                    $to -= self::PRICE_DELTA;
                }
                $item = [
                    'label' => $this->_renderRangeLabel($from, $to),
                    'value' => $from.'-'.$to,
                    'count' => $aggregation['count'],
                    'from' => $from,
                    'to' => $to
                ];
                $data[$i] = $item;
                $i++;
            }
        }
        if(count($data) > 1) {
            foreach ($data as $item) {
                $this->itemDataBuilder->addItemData(
                    $item['label'],
                    $item['value'],
                    $item['count']
                );
            }
        }
        return $this->itemDataBuilder->build();
    }

    /**
     * @param float|string $fromPrice
     * @param float|string $toPrice
     * @return float|\Magento\Framework\Phrase
     */
    protected function _renderRangeLabel($fromPrice, $toPrice){
        $fromPrice = empty($fromPrice) ? 0 : $fromPrice * $this->getCurrencyRate();
        $toPrice = empty($toPrice) ? $toPrice : $toPrice * $this->getCurrencyRate();
        $formattedFromPrice = $this->priceCurrency->format($fromPrice);
        $maxValue = $this->getMax();
        if ($toPrice === $maxValue ) {
            return __('%1 and above', $formattedFromPrice);
        } elseif ($fromPrice == $toPrice && $this->dataProvider->getOnePriceIntervalValue()) {
            return $formattedFromPrice;
        } else {
            return __('%1 - %2', $formattedFromPrice, $this->priceCurrency->format($toPrice));
        }
    }

    /**
     * @return mixed
     */
    protected function getCollectionWithoutFilter(){
        if (!$this->emptyCollection) {
            $productCollection = $this->getLayer()->getProductCollection();
            $this->emptyCollection = $this->collectionProvider->getCollection(
                $this->getLayer()->getCurrentCategory()
            );
            $this->emptyCollection->updateSearchCriteriaBuilder();
            $this->getLayer()->prepareProductCollection($this->emptyCollection);
            foreach ($productCollection->getAddedFilters() as $field => $condition) {
                if ($this->getAttributeModel()->getAttributeCode() == $field) {
                    continue;
                }
                $this->emptyCollection->addFieldToFilter($field, $condition);
            }
        }
        return $this->emptyCollection;
    }
}