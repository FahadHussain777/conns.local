<?php
/*
 * Copyright ©2016 Conn's. All rights reserved.
 */

namespace Conns\Pronav\Controller\Adminhtml\Navigation;

use Magento\Backend\App\Action;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Save
 * @package Conns\Pronav\Controller\Adminhtml\Navigation
 */
class Save extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Conns_Pronav::config';

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;
    
    /**
     * @var \Conns\Pronav\Model\NavigationRepository
     */
    protected $objectRepository;

    /**
     * @param Action\Context $context
     * @param DataPersistorInterface $dataPersistor
     * @param \Conns\Pronav\Model\NavigationRepository $objectRepository
     */
    public function __construct(
        Action\Context $context,
        DataPersistorInterface $dataPersistor,
        \Conns\Pronav\Model\NavigationRepository $objectRepository
    ) {
        $this->dataPersistor    = $dataPersistor;
        $this->objectRepository  = $objectRepository;
        
        parent::__construct($context);
    }


    /**
     * @return \Magento\Backend\Model\View\Result\Redirect|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            if (isset($data['is_active']) && $data['is_active'] === 'true') {
                $data['is_active'] = Conns\Pronav\Model\Navigation::STATUS_ENABLED;
            }
            if (empty($data['pronav_id'])) {
                $data['pronav_id'] = null;
            }

            /** @var \Conns\Pronav\Model\Navigation $model */
            $model = $this->_objectManager->create('Conns\Pronav\Model\Navigation');

            $id = $this->getRequest()->getParam('pronav_id');
            if ($id) {
                $model = $this->objectRepository->getById($id);
            }

            $model->setData($data);

            try {
                $this->objectRepository->save($model);
                $this->messageManager->addSuccess(__('You saved the item.'));
                $this->dataPersistor->clear('conns_pronav_navigation');
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['pronav_id' => $model->getId(), '_current' => true]);
                }
                return $resultRedirect->setPath('*/items/');
            } catch (LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the data.'));
            }

            $this->dataPersistor->set('conns_pronav_navigation', $data);
            return $resultRedirect->setPath('*/*/edit', ['pronav_id' => $this->getRequest()->getParam('pronav_id')]);
        }
        return $resultRedirect->setPath('*/items/');
    }    
}
