<?php
/*
 * Copyright ©2016 Conn's. All rights reserved.
 */

namespace Conns\PowerStore\Controller\Adminhtml\Region;

use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Conns\PowerStore\Model\ResourceModel\StoreRegion\CollectionFactory;
use Magento\Framework\Controller\ResultFactory;

class MassEnable extends \Magento\Backend\App\Action
{
    protected $filter;
    protected $collectionFactory;

    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory
    ){
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $collectionSize = $collection->getSize();
        foreach ($collection as $item) {
            $item->setEnabled(true);
            $item->save();
        }
        $this->messageManager->addSuccess(__('A total of %1 record(s) have been enabled.', $collectionSize));

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/region/');
    }
}