<?php

namespace Conns\LayeredNavigation\Model\Layer\Filter;

use Magento\Catalog\Model\Layer\Filter\ItemFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Filter\Item\DataBuilder;
use Magento\Catalog\Model\ResourceModel\Layer\Filter\Price as FilterPrice;
use Magento\Customer\Model\Session;
use Magento\Framework\Search\Dynamic\Algorithm;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Catalog\Model\Layer\Filter\Dynamic\AlgorithmFactory;
use Magento\Catalog\Model\Layer\Filter\DataProvider\PriceFactory;
use Conns\LayeredNavigation\Model\Url\Builder;
use Conns\LayeredNavigation\Model\Layer\MonthlyPaymentCollectionProvider;

class MonthlyPayment extends \Magento\CatalogSearch\Model\Layer\Filter\Price
{
    const PRICE_DELTA = 0.01;
    protected $dataProvider;
    protected $urlBuilder;
    protected $collectionProvider;
    protected $emptyCollection;
    protected $priceCurrency;
    protected $_requestVar;
    protected $priceAlgorithm;

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
        MonthlyPaymentCollectionProvider $collectionProvider,
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
        $this->priceAlgorithm = $priceAlgorithm;
        $this->_requestVar = 'monthly_payment';
    }
    public function apply(\Magento\Framework\App\RequestInterface $request){
        $this->applyToCollection($this->getLayer()->getProductCollection(), true);
        return $this;
    }
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
        list($from, $blank) = explode("-", $values[0]);
        list($blank, $to) = explode("-", $values[count($values)-1]);
        $collection->addFieldToFilter(
            'monthly_payment',
            [
                'from' => $from,
                'to' => (float) $to
            ]
        );
        return $this;
    }
    public function getMax(){
        $collection = $this->getCollectionWithoutFilter();
        $collection->addAttributeToSelect('monthly_payment');
        $data = [];
        foreach ($collection as $product){
            if($product->getData('monthly_payment') !== null){
                $data[] = $product->getData('monthly_payment');
            }
        }
        if(empty($data)){
            return false;
        }
        return max($data);
    }
    public function getMin(){
        $collection = $this->getCollectionWithoutFilter();
        $collection->addAttributeToSelect('monthly_payment');
        $data = [];
        foreach ($collection as $product){
            if($product->getData('monthly_payment') !== null){
                $data[] = $product->getData('monthly_payment');
            }
        }
        if(empty($data)){
            return false;
        }
        return min($data);
    }
    protected function getTo($from){
        $to = '';
        $interval = $this->dataProvider->getInterval();
        if ($interval && is_numeric($interval[1]) && $interval[1] > $from) {
            $to = $interval[1];
        }
        return $to;
    }
    protected function getFrom($from){
        $to = '';
        $interval = $this->dataProvider->getInterval();
        if ($interval && is_numeric($interval[0]) && $interval[0] < $from) {
            $to = $interval[0];
        }
        return $to;
    }
    protected function _getItemsData(){
        $values = $this->urlBuilder->getValuesFromUrl($this->_requestVar);
        $attribute = $this->getAttributeModel();
        $productCollection = $this->getLayer()->getProductCollection();
        $facets = $this->getCollectionWithoutFilter()->getFacetedData($attribute->getAttributeCode(),$this->getMin(),$this->getMax());
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
    protected function _renderRangeLabel($fromPrice, $toPrice){
        $fromPrice = empty($fromPrice) ? 0 : $fromPrice * $this->getCurrencyRate();
        $toPrice = empty($toPrice) ? $toPrice : $toPrice * $this->getCurrencyRate();
        $formattedFromPrice = $this->priceCurrency->format($fromPrice);
        $maxValue = $this->getMax();
        $maxValue -= self::PRICE_DELTA;
        if ($fromPrice == $toPrice && $this->dataProvider->getOnePriceIntervalValue()) {
            return $formattedFromPrice;
        } else {
            return __('%1 - %2', $formattedFromPrice, $this->priceCurrency->format($toPrice));
        }
    }
    protected function getCollectionWithoutFilter(){
        if (!$this->emptyCollection) {
            $productCollection = $this->getLayer()->getProductCollection();
            $this->emptyCollection = $this->collectionProvider->getCollection(
                $this->getLayer()->getCurrentCategory()
            );
            $this->emptyCollection->updateSearchCriteriaBuilder();
            $this->getLayer()->prepareProductCollection($this->emptyCollection);
            foreach ($productCollection->getAddedFilters() as $field => $condition) {
                if ($this->getAttributeModel()->getAttributeCode() === $field) {
                    continue;
                }
                $this->emptyCollection->addFieldToFilter($field, $condition);
            }
        }
        return $this->emptyCollection;
    }
}