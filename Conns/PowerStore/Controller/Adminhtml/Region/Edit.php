<?php
/*
 * Copyright ©2016 Conn's. All rights reserved.
 */

namespace Conns\PowerStore\Controller\Adminhtml\Region;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;


class Edit extends \Magento\Backend\App\Action
{

    const ADMIN_RESOURCE = 'BrainActs_StoreLocator::save';



    protected $resultPageFactory;


    public function __construct(
        Context $context,
        PageFactory $resultPageFactory)
    {
        $this->resultPageFactory = $resultPageFactory;        
        parent::__construct($context);
    }


    public function execute()
    {
        return $this->resultPageFactory->create();  
    }    
}
