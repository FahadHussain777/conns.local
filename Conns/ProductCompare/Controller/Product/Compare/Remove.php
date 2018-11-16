<?php

namespace Conns\ProductCompare\Controller\Product\Compare;


use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\View\Result\PageFactory;

class Remove extends \Magento\Catalog\Controller\Product\Compare\Remove
{
    protected $resultJsonFactory;
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Catalog\Model\Product\Compare\ItemFactory $compareItemFactory,
        \Magento\Catalog\Model\ResourceModel\Product\Compare\Item\CollectionFactory $itemCollectionFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\Visitor $customerVisitor,
        \Magento\Catalog\Model\Product\Compare\ListCompare $catalogProductCompareList,
        \Magento\Catalog\Model\Session $catalogSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Validator $formKeyValidator,
        PageFactory $resultPageFactory,
        ProductRepositoryInterface $productRepository
    )
    {
        parent::__construct($context,
            $compareItemFactory,
            $itemCollectionFactory,
            $customerSession,
            $customerVisitor,
            $catalogProductCompareList,
            $catalogSession,
            $storeManager,
            $formKeyValidator,
            $resultPageFactory,
            $productRepository
        );
    }

    public function execute()
    {
        $productId = (int)$this->getRequest()->getParam('product');
        if ($productId) {
            $storeId = $this->_storeManager->getStore()->getId();
            try {
                $product = $this->productRepository->getById($productId, false, $storeId);
            } catch (NoSuchEntityException $e) {
                $product = null;
            }

            if ($product) {
                /** @var $item \Magento\Catalog\Model\Product\Compare\Item */
                $item = $this->_compareItemFactory->create();
                if ($this->_customerSession->isLoggedIn()) {
                    $item->setCustomerId($this->_customerSession->getCustomerId());
                } elseif ($this->_customerId) {
                    $item->setCustomerId($this->_customerId);
                } else {
                    $item->addVisitorId($this->_customerVisitor->getId());
                }

                /** @var $helper \Magento\Catalog\Helper\Product\Compare */
                $helper = $this->_objectManager->get(\Magento\Catalog\Helper\Product\Compare::class);
                if($helper->getItemCollection()->count() === 2){
                    foreach ($helper->getItemCollection() as $entity){
                        if($entity['entity_id'] !== $productId){
                            $secondProduct = $this->productRepository->getById($entity['entity_id'], false, $storeId);
                            $item->loadByProduct($secondProduct);
                            if($item->getId()){
                                $item->delete();
                                $secondProductName = $this->_objectManager->get(\Magento\Framework\Escaper::class)
                                    ->escapeHtml($secondProduct->getName());
                            }
                        }
                    }
                    $item->loadByProduct($product);
                    if ($item->getId()) {
                        $item->delete();
                        $productName = $this->_objectManager->get(\Magento\Framework\Escaper::class)
                            ->escapeHtml($product->getName());
                        $this->messageManager->addSuccessMessage(
                            __('You removed products, %1 and %2 from the comparison list.', $productName,$secondProductName)
                        );
                        $this->_eventManager->dispatch(
                            'catalog_product_compare_remove_product',
                            ['product' => $item]
                        );
                        $helper->calculate();
                    }
                }else {
                    $item->loadByProduct($product);
                    if ($item->getId()) {
                        $item->delete();
                        $productName = $this->_objectManager->get(\Magento\Framework\Escaper::class)
                            ->escapeHtml($product->getName());
                        $this->messageManager->addSuccessMessage(
                            __('You removed product %1 from the comparison list.', $productName)
                        );
                        $this->_eventManager->dispatch(
                            'catalog_product_compare_remove_product',
                            ['product' => $item]
                        );
                        $helper->calculate();
                    }
                }
            }
        }
        if (!$this->getRequest()->getParam('isAjax', false)) {
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setRefererOrBaseUrl();
        }
    }
}