<?php
/*
 * Copyright ©2016 Conn's. All rights reserved.
 */
namespace Conns\PowerStore\Controller\Adminhtml\Region;


class Delete extends \Magento\Backend\App\Action
{

    const ADMIN_RESOURCE = 'BrainActs_StoreLocator::delete';
    

    protected $objectRepository;


    public function __construct(
        \Conns\PowerStore\Model\StoreRegionRepository $objectRepository,
        \Magento\Backend\App\Action\Context $context
    ) {
        $this->objectRepository = $objectRepository;

        parent::__construct($context);
    }
          
    public function execute()
    {
        // check if we know what should be deleted
        $id = $this->getRequest()->getParam('object_id');
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            try {
                // delete model
                $this->objectRepository->deleteById($id);
                // display success message
                $this->messageManager->addSuccess(__('You have deleted the item.'));
                // go to grid
                return $resultRedirect->setPath('*/region/');
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addError($e->getMessage());
                // go back to edit form
                return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
            }
        }
        // display error message
        $this->messageManager->addError(__('We can not find an object to delete.'));
        // go to grid
        return $resultRedirect->setPath('*/region/');
        
    }    
    
}
