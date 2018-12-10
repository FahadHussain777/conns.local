<?php

namespace Conns\PowerStore\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class StoreSaveAfter implements ObserverInterface
{
    protected $request;

    protected $storeHoursFactory;

    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Conns\PowerStore\Model\StoreHoursFactory $storeHoursFactory

    ){
        $this->storeHoursFactory = $storeHoursFactory;
        $this->request = $request;
    }

    public function execute(Observer $observer)
    {
        $id = $this->request->getParam('locator_id');
        $params = $this->request->getParams();
        if(isset($params['hours'])){
            foreach ($params['hours'] as $dow => $value){
                $storehour = $this->storeHoursFactory->create();
                if(!empty($id)){
                    if($params['hours'][$dow]['enabled'] == 1) {
                        $item = $storehour->loadByLocatorId($dow, $id);
                        if ($item->getId()) {
                            $data = [
                                'locator_id' => $params['locator_id'],
                                'dow' => $dow,
                                'open' => strtotime($params['hours'][$dow]['open'][0].':'.$params['hours'][$dow]['open'][1].':'.$params['hours'][$dow]['open'][2]),
                                'close' => strtotime($params['hours'][$dow]['close'][0].':'.$params['hours'][$dow]['close'][1].':'.$params['hours'][$dow]['close'][2]),
                            ];
                            $item->addData($data)->save();
                        }
                        else{
                            $data = [
                                'locator_id' => $params['locator_id'],
                                'dow' => $dow,
                                'open' => strtotime($params['hours'][$dow]['open'][0].':'.$params['hours'][$dow]['open'][1].':'.$params['hours'][$dow]['open'][2]),
                                'close' => strtotime($params['hours'][$dow]['close'][0].':'.$params['hours'][$dow]['close'][1].':'.$params['hours'][$dow]['close'][2]),
                            ];
                            $item->setData($data)->save();
                            }
                        }
                    else{
                        $item = $storehour->loadByLocatorId($dow, $id);
                        if ($item->getId()) {
                            $item->delete()->save();
                        }
                    }
                }
                else{
                    $store = $observer->getEvent()->getDataObject();
                    if($params['hours'][$dow]['enabled'] == 1){
                        $data = [
                            'locator_id' => $store->getId(),
                            'dow' => $dow,
                            'open' => strtotime($params['hours'][$dow]['open'][0].':'.$params['hours'][$dow]['open'][1].':'.$params['hours'][$dow]['open'][2]),
                            'close' => strtotime($params['hours'][$dow]['close'][0].':'.$params['hours'][$dow]['close'][1].':'.$params['hours'][$dow]['close'][2]),
                        ];
                        $storehour->setData($data)->save();
                    }
                }
            }
        }
    }

}