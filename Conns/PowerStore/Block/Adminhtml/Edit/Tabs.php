<?php

namespace Conns\PowerStore\Block\Adminhtml\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * Internal constructor
     *
     * @return void
     * @codingStandardsIgnoreStart
     */
    protected function _construct()
    {
        // @codingStandardsIgnoreEnd
        parent::_construct();
        $this->setId('storelocator_edit_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Dealer\Store Locator'));
    }

    /**
     * @return $this
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     * @codingStandardsIgnoreStart
     */
    protected function _beforeToHtml()
    {
        // @codingStandardsIgnoreEnd
        $personalPageALlow = $this->_scopeConfig->getValue(
            'brainacts_storelocator/item/separate_page',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        $this->addTab(
            'settings_section',
            [
                'label' => __('Settings'),
                'title' => __('Settings'),
                'content' => $this->getLayout()
                    ->createBlock(\Conns\PowerStore\Block\Adminhtml\Edit\Tab\Settings::class)->toHtml(),
                'active' => true
            ]
        );

        $this->addTab(
            'address_section',
            [
                'label' => __('Address'),
                'title' => __('Address'),
                'content' => $this->getLayout()
                    ->createBlock(\Conns\PowerStore\Block\Adminhtml\Edit\Tab\Address::class)->toHtml(),
                'active' => false
            ]
        );

        if ($personalPageALlow) {
            $this->addTab(
                'search_section',
                [
                    'label' => __('Search Engine Optimisation'),
                    'title' => __('Search Engine Optimisation'),
                    'content' => $this->getLayout()
                        ->createBlock(\BrainActs\StoreLocator\Block\Adminhtml\Edit\Tab\Search::class)->toHtml(),
                    'active' => false
                ]
            );

            $this->addTab(
                'page',
                [
                    'label' => __('Page Details'),
                    'title' => __('Page Details'),
                    'content' => $this->getLayout()
                        ->createBlock(\Conns\PowerStore\Block\Adminhtml\Edit\Tab\Page::class)->toHtml(),
                    'active' => false
                ]
            );

            $this->addTab(
                'visiting_hours',
                [
                    'label' => __('Visiting Hours'),
                    'title' => __('Visiting Hours'),
                    'content' => $this->getLayout()
                        ->createBlock(\Conns\PowerStore\Block\Adminhtml\Edit\Tab\VisitingHours::class)->toHtml(),
                    'active' => false
                ]
            );
        }

        return parent::_beforeToHtml();
    }

}