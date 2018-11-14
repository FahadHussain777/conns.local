<?php

namespace Conns\ProductCompare\Helper;

class Data extends \Magento\Catalog\Helper\Product\Compare
{
    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoder;

    protected $storeManager;

    protected $imageHelperFactory;

    protected $listProduct;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\ResourceModel\Product\Compare\Item\CollectionFactory $itemCollectionFactory
     * @param \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility
     * @param \Magento\Customer\Model\Visitor $customerVisitor
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Catalog\Model\Session $catalogSession
     * @param \Magento\Framework\Data\Form\FormKey $formKey
     * @param \Magento\Wishlist\Helper\Data $wishlistHelper
     * @param \Magento\Framework\Data\Helper\PostHelper $postHelper
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ResourceModel\Product\Compare\Item\CollectionFactory $itemCollectionFactory,
        \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
        \Magento\Customer\Model\Visitor $customerVisitor,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Catalog\Model\Session $catalogSession,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Wishlist\Helper\Data $wishlistHelper,
        \Magento\Framework\Data\Helper\PostHelper $postHelper,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Catalog\Helper\ImageFactory $imageHelperFactory,
        \Magento\Catalog\Block\Product\AbstractProduct $listProduct
    ) {
        $this->_jsonEncoder = $jsonEncoder;
        $this->storeManager = $storeManager;
        $this->imageHelperFactory = $imageHelperFactory;
        $this->listProduct = $listProduct;

        parent::__construct(
            $context,
            $storeManager,
            $itemCollectionFactory,
            $catalogProductVisibility,
            $customerVisitor,
            $customerSession,
            $catalogSession,
            $formKey,
            $wishlistHelper,
            $postHelper
        );
    }

    public function getCompareData(\Magento\Catalog\Api\Data\ProductInterface $product)
    {
        $config = [
            'id' => $product->getId(),
            'name' => $product->getName(),
            'image_url' => $this->imageHelperFactory->create()->init($product, 'product_thumbnail_image')->getUrl(),
            'small_image_url' => $this->imageHelperFactory->create()->init($product, 'product_thumbnail_image')->resize(25)->getUrl(),
            'product_url' => $product->getUrlModel()->getUrl($product),
            'remove_url' => $this->getAjaxPostDataRemove($product),
            'add_data'    => $this->getAjaxPostDataParams($product),
            'price' => $this->listProduct->getProductPrice($product)
        ];
        return $this->_jsonEncoder->encode($config);
    }

    /**
     * Get parameters used to add product to compare list urls by ajax call.
     *
     * @param Product $product
     * @return string
     */
    public function getAjaxPostDataParams($product)
    {
        return $this->postHelper->getPostData($this->getAjaxAddUrl(), ['product' => $product->getId()]);
    }

    /**
     * Retrieve url for adding product to compare list by ajax call.
     *
     * @return string
     */
    public function getAjaxAddUrl()
    {
        return $this->_getUrl('catalog/product_compare/ajaxAdd');
    }

    /**
     * Get parameters to remove a product from the compare list by ajax call.
     *
     * @param Product $product
     * @return string
     */
    public function getAjaxPostDataRemove($product)
    {
        $listCleanUrl = $this->getEncodedUrl($this->_getUrl('catalog/product_compare'));
        $data = [
            \Magento\Framework\App\ActionInterface::PARAM_NAME_URL_ENCODED => '',
            'product' => $product->getId()
        ];
        return $this->postHelper->getPostData($this->getAjaxRemoveUrl(), $data);
    }

    /**
     * Retrieve url for removing product from compare list by ajax call.
     *
     * @return string
     */
    public function getAjaxRemoveUrl()
    {
        return $this->_getUrl('catalog/product_compare/ajaxremove');
    }
}