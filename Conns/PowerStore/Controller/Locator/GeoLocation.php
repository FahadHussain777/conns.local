<?php


namespace Conns\PowerStore\Controller\Locator;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use BrainActs\StoreLocator\Helper\GoogleApi;

class GeoLocation extends Action
{
    protected $resultJsonFactory;
    protected $helper;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        GoogleApi $helper
    ) {
        $this->helper = $helper;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $latitude = $this->getRequest()->getParam('lat');
        $longitude = $this->getRequest()->getParam('long');
        $responseAPI = $this->helper->getAddressByLocation($latitude,$longitude);
        foreach ($responseAPI['results'] as $result){
            foreach ($result['address_components'] as $address){
                if(in_array('postal_code',$address['types'])){
                    $postalCode = $address['long_name'];
                }
            }
        }
        if(isset($postalCode)){
            $data = [
                'success' => true,
                'zip' => $postalCode
            ];
        }
        else{
            $data = [
                'success' => false
            ];
        }
        $result = $this->resultJsonFactory->create();
        return $result->setData($data);
    }
}