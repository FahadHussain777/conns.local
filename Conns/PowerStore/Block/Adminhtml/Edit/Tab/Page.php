<?php

namespace Conns\PowerStore\Block\Adminhtml\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;

/**
 * Class Page
 * @author BrainActs Commerce OÜ Core Team <support@brainacts.com>
 */
class Page extends Generic implements TabInterface
{

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Single Page Details');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Single Page Details');
    }

    /**
     * Returns status flag about this tab can be showen or not
     *
     * @return true
     */
    public function canShowTab()
    {
        if ($this->_scopeConfig->getValue('brainacts_storelocator/item/separate_page')) {
            return true;
        }
        return false;
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
    public function getLocator()
    {
        return $this->_coreRegistry->registry('storelocator_locator');
    }

    /**
     * Prepare form before rendering HTML
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @codingStandardsIgnoreStart
     */
    protected function _prepareForm()
    {
        // @codingStandardsIgnoreEnd
        $locatorId = $this->getLocator()->getId();

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
        );

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Settings')]);

        $fieldset->addField(
            'description',
            'textarea',
            [
                'name' => 'description',
                'label' => __('Description'),
                'title' => __('Description'),
                'required' => false,
            ]
        );

        $fieldset->addField(
            'extra_info',
            'textarea',
            [
                'name' => 'extra_info',
                'label' => __('Extra Information'),
                'title' => __('Extra Information'),
                'required' => false,
            ]
        );

        $fieldset->addField(
            'email',
            'text',
            [
                'name' => 'email',
                'label' => __('Email'),
                'title' => __('Email')
            ]
        );

        $fieldset->addField(
            'visiting_hours',
            'text',
            [
                'name' => 'visiting_hours',
                'label' => __('Visiting Hours'),
                'title' => __('Visiting Hours')
            ]
        );

        $fieldset->addField(
            'website',
            'text',
            [
                'name' => 'website',
                'label' => __('Website'),
                'title' => __('Website')
            ]
        );

        $fieldset->addField(
            'banner',
            'hidden',
            [
                'name' => 'banner'
            ]
        );

        $locatorId = $this->getLocator()->getId();
        $value = '';
        if ($locatorId) {
            $value = $this->getLocator()->getBanner();
        }
        $fieldset->addField(
            'banner_locator',
            'image',
            [
                'name' => 'banner_locator',
                'label' => __('Banner'),
                'title' => __('Banner'),
                'value' => $value,
                'note' => __('Allowed image types: jpg, jpeg, gif, png.')
            ]
        );

        if ($locatorId) {
            $form->addValues($this->getLocator()->getData());
        }

        $this->setForm($form);

        return parent::_prepareForm();
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
