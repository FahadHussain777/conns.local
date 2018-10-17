<?php
/*
 * Copyright ©2016 Conn's. All rights reserved.
 */
namespace Conns\Pronav\Controller\Adminhtml\Navigation;

/**
 * Class Delete
 * @package Conns\Pronav\Controller\Adminhtml\Navigation
 */
class Delete extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Conns_Pronav::config';
    
    /**
     * @var \Conns\Pronav\Model\NavigationRepository
     */
    protected $objectRepository;

    /**
     * Delete constructor.
     * @param \Conns\Pronav\Model\NavigationRepository $objectRepository
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Conns\Pronav\Model\NavigationRepository $objectRepository,
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
                return $resultRedirect->setPath('*/items/');
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addError($e->getMessage());
                // go back to edit form
                return $resultRedirect->setPath('*/*/edit', ['pronav_id' => $id]);
            }
        }
        // display error message
        $this->messageManager->addError(__('We can not find an object to delete.'));
        // go to grid
        return $resultRedirect->setPath('*/items/');
        
    }    
    
}
