<?php

namespace Conns\PowerStore\Block\Adminhtml\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;

/**
 * Class Settings
 * @author BrainActs Commerce OÜ Core Team <support@brainacts.com>
 */
class Settings extends Generic implements TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    private $store;

    /**
     * Settings constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $store
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $store,
        array $data = []
    ) {
    
        $this->store = $store;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @return void
     * @codingStandardsIgnoreStart
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setActive(true);
    }
    // @codingStandardsIgnoreEnd

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Settings');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Settings');
    }

    /**
     * Returns status flag about this tab can be showen or not
     *
     * @return true
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return true
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Getter
     *
     * @return \Magento\Widget\Model\Widget\Instance
     */
    public function getWidgetInstance()
    {
        return $this->_coreRegistry->registry('storelocator_locator');
    }

    /**
     * Prepare form before rendering HTML
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @codingStandardsIgnoreStart
     */
    protected function _prepareForm()
    {
        // @codingStandardsIgnoreEnd
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
        );

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Settings')]);
        $fieldset->addField(
            'locator_id',
            'hidden',
            [
                'name' => 'locator_id'
            ]
        );

        $fieldset->addField(
            'is_active',
            'select',
            [
                'label' => __('Status'),
                'title' => __('Status'),
                'name' => 'is_active',
                'required' => true,
                'options' => ['1' => __('Enabled'), '0' => __('Disabled')]
            ]
        );

        $fieldset->addField(
            'name',
            'text',
            [
                'name' => 'name',
                'label' => __('Name'),
                'title' => __('Name'),
                'required' => true,
            ]
        );

        $fieldset->addField(
            'store_number',
            'text',
            [
                'name' => 'store_number',
                'label' => __('Store #'),
                'title' => __('Store #'),
                'required' => false,
            ]
        );

        $fieldset->addField(
            'short_description',
            'textarea',
            [
                'name' => 'short_description',
                'label' => __('Short Description'),
                'title' => __('Short Description'),
                'required' => false,
            ]
        );

        if (!$this->_storeManager->isSingleStoreMode()) {
            $field = $fieldset->addField(
                'stores',
                'multiselect',
                [
                    'name' => 'stores[]',
                    'label' => __('Assign to Store Views'),
                    'title' => __('Assign to Store Views'),
                    'required' => true,
                    'values' => $this->store->getStoreValuesForForm(false, true)
                ]
            );
            $renderer = $this->getLayout()->createBlock(
                \Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element::class
            );
            $field->setRenderer($renderer);
        }

        $fieldset->addField(
            'sort_order',
            'text',
            [
                'name' => 'sort_order',
                'label' => __('Sort Order'),
                'title' => __('Sort Order'),
                'value' => '0'
            ]
        );

        $fieldset->addField(
            'image',
            'hidden',
            [
                'name' => 'image'
            ]
        );

        $locatorId = $this->getLocator()->getId();
        $value = '';
        if ($locatorId) {
            $value = $this->getLocator()->getImage();
        }
        $fieldset->addField(
            'image_locator',
            'image',
            [
                'name' => 'image_locator',
                'label' => __('Image'),
                'title' => __('Image'),
                'value' => $value,
                'note' => __('Allowed image types: jpg, jpeg, gif, png.')
            ]
        );

        $message = __('If store pickup shipping method is active this item will be available to use in Store Pickup');
        $fieldset->addField(
            'store_pickup',
            'select',
            [
                'name' => 'store_pickup',
                'label' => __('Store Pickup?'),
                'title' => __('Store Pickup?'),
                'options' => ['1' => __('Yes'), '0' => __('No')],
                'note' => $message,
            ]
        );

        $fieldset->addField(
            'ship_price',
            'text',
            [
                'name' => 'ship_price',
                'label' => __('Shipping Price'),
                'title' => __('Shipping Price'),
                'required' => false,
                'class'=>'validate-number'
            ]
        );

        if ($locatorId) {
            $form->addValues($this->getLocator()->getData());
        }

        $this->setForm($form);

        return parent::_prepareForm();
    }

    public function getLocator()
    {
        return $this->_coreRegistry->registry('storelocator_locator');
    }

    /**
     * Return url for continue button
     *
     * @return string
     */
    public function getContinueUrl()
    {
        return $this->getUrl(
            'adminhtml/*/*',
            ['_current' => true, 'code' => '<%- data.code %>', 'theme_id' => '<%- data.theme_id %>']
        );
    }
}
