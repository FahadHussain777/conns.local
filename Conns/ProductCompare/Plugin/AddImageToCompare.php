<?php

namespace Conns\ProductCompare\Plugin;

use Magento\Catalog\Helper\ImageFactory;
use Magento\Catalog\Helper\Product\Compare;
use Magento\Catalog\Model\ProductRepository;
use Conns\ProductCompare\Helper\Data;
use Magento\Catalog\Block\Product\ListProduct;

class AddImageToCompare
{
    protected $helper;
    protected $imageHelperFactory;
    protected $productRepository;
    protected $listProduct;
    protected $connsHelper;

    public function __construct(
        Compare $helper,
        ImageFactory $imageHelperFactory,
        ProductRepository $productRepository,
        ListProduct $listProduct,
        Data $connsHelper
    )
    {
        $this->connsHelper = $connsHelper;
        $this->helper = $helper;
        $this->imageHelperFactory = $imageHelperFactory;
        $this->productRepository = $productRepository;
        $this->listProduct = $listProduct;
    }
    public function afterGetSectionData(\Magento\Catalog\CustomerData\CompareProducts $subject, $result)
    {
        $data = [];
        foreach ($this->helper->getItemCollection() as $item) {
            $imageHelper = $this->imageHelperFactory->create();
            try {
                $product = $this->productRepository->getById($item->getId());
                $data['remove_url'][$item->getId()] = $this->connsHelper->getAjaxPostDataRemove($product);
                $data['image_url'][$item->getId()] = $imageHelper->init($product, 'product_thumbnail_image')->getUrl();
                $data['small_image_url'][$item->getId()] = $imageHelper->init($product, 'product_thumbnail_image')->resize(25)->getUrl();
                $data['price'][$item->getId()] = '<strong>$'.number_format($product->getPriceInfo()->getPrice('final_price')->getValue(),2).'</strong>';
            } catch (\Exception $ex) {
                $data['image_url'][$item->getId()] = $imageHelper->getDefaultPlaceholderUrl();
            }
        }
        $items = $result['items'];
        foreach ($items as $key=>$value) {
            $items[$key]['image_url'] = $data['image_url'][$items[$key]['id']];
            $items[$key]['small_image_url'] = $data['small_image_url'][$items[$key]['id']];
            $items[$key]['remove_url'] = $data['remove_url'][$items[$key]['id']];
            $items[$key]['price'] = $data['price'][$items[$key]['id']];
        }
        $result['items'] = $items;
        return $result;
    }

}