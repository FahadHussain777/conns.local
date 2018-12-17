<?php

namespace Conns\PowerStore\Controller\Locator;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;

class CheckResults extends Action
{
    protected $resultJsonFactory;

    public function __construct(
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        Context $context
    ){
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        if ($this->getRequest()->isAjax())
        {
            $postcode = $this->getRequest()->getParam('postcode');
            $distance = $this->getRequest()->getParam('distance');
            $test=Array
            (
                'Firstname' => 'What is your firstname',
                'Email' => 'What is your emailId',
                'Lastname' => 'What is your lastname',
                'Country' => 'Your Country',
                'postcode' => $postcode,
                'distance' => $distance
            );
            $data = [
                'success' => true,
                'data' => $test
            ];
            return $result->setData($data);
        }
    }

}