<?php
/*
 * Copyright ©2016 Conn's. All rights reserved.
 */

namespace Conns\PowerStore\Controller\Adminhtml\Region;

use Magento\Backend\App\Action;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;


class Save extends \Magento\Backend\App\Action
{

    const ADMIN_RESOURCE = 'BrainActs_StoreLocator::save';


    protected $dataPersistor;
    

    protected $objectRepository;


    public function __construct(
        Action\Context $context,
        DataPersistorInterface $dataPersistor,
        \Conns\PowerStore\Model\StoreRegionRepository $objectRepository
    ) {
        $this->dataPersistor    = $dataPersistor;
        $this->objectRepository  = $objectRepository;
        
        parent::__construct($context);
    }


    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            if (isset($data['is_active']) && $data['is_active'] === 'true') {
                $data['is_active'] = Conns\Pronav\Model\Navigation::STATUS_ENABLED;
            }
            if (empty($data['id'])) {
                $data['id'] = null;
            }

            /** @var \Conns\Pronav\Model\Navigation $model */
            $model = $this->_objectManager->create('Conns\PowerStore\Model\StoreRegion');

            $id = $this->getRequest()->getParam('id');
            if ($id) {
                $model = $this->objectRepository->getById($id);
            }

            $model->setData($data);

            try {
                $this->objectRepository->save($model);
                $this->messageManager->addSuccess(__('You saved the item.'));
                $this->dataPersistor->clear('conns_powerstore_region');
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId(), '_current' => true]);
                }
                return $resultRedirect->setPath('*/region/');
            } catch (LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the data.'));
            }

            $this->dataPersistor->set('conns_powerstore_region', $data);
            return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
        }
        return $resultRedirect->setPath('*/region/');
    }    
}
