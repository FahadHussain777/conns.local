<?php

namespace Conns\PowerStore\Block\Adminhtml\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;

class VisitingHours extends Generic implements TabInterface
{
    protected $helper;
    public function __construct(
        \Conns\PowerStore\Helper\Data $helper,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ){
        $this->helper = $helper;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    public function getTabLabel()
    {
        return __('Visiting Hours');
    }

    public function getTabTitle()
    {
        return __('Visiting Hours');
    }

    public function canShowTab()
    {
        if ($this->_scopeConfig->getValue('brainacts_storelocator/item/separate_page')) {
            return true;
        }
        return false;
    }

    public function isHidden()
    {
        return false;
    }

    public function getLocator()
    {
        return $this->_coreRegistry->registry('storelocator_locator');
    }

    protected function _prepareForm()
    {
        // @codingStandardsIgnoreEnd
        $locatorId = $this->getLocator()->getId();
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
        );
        $days = $this->helper->getArrayOfDays();
        foreach ($days as $value => $label){
                $fieldset = $form->addFieldset(
                    strtolower($label) . '_fieldset',
                    array('legend' => $label)
                );

                $fieldset->addField("enabled_{$value}", 'select', [
                    'label'     => '',
                    'title'     => '',
                    'name'      => "hours[$value][enabled]",
                    'required'  => false,
                    'options'   => [
                        1 => 'Open',
                        0 => 'Closed',
                    ],
                ]);
                $fieldset->addField("open_{$value}", 'time', [
                    'name' => "hours[$value][open]",
                    'label' => 'Open Time',
                    'title' => 'Open Time',
                    'class' => "day_visibility_{$value}",
                    'required' => false,
                ]);

                $fieldset->addField("close_{$value}", 'time', [
                    'name' => "hours[$value][close]",
                    'label' => 'Close Time',
                    'title' => 'Close Time',
                    'class' => "day_visibility_{$value}",
                    'required' => false,
                ]);

        }
        $hours = $this->getLocator()->getHours();
        $data = array_merge($hours,$this->getLocator()->getData());
        $form->addValues($data);
        $this->setForm($form);
        return parent::_prepareForm();
    }

    public function getContinueUrl()
    {
        return $this->getUrl(
            'adminhtml/*/*',
            ['_current' => true, 'code' => '<%- data.code %>', 'theme_id' => '<%- data.theme_id %>']
        );
    }

}