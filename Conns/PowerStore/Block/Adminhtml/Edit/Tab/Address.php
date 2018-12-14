<?php

namespace Conns\PowerStore\Block\Adminhtml\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;

/**
 * Class Address
 * @author BrainActs Commerce OÜ Core Team <support@brainacts.com>
 */
class Address extends Generic implements TabInterface
{
    /**
     * @var \Magento\Directory\Model\ResourceModel\Country\CollectionFactory
     */
    private $countryCollectionFactory;

    /**
     * @var \Magento\Directory\Model\ResourceModel\Region\CollectionFactory
     */
    private $regionCollectionFactory;

    /**
     * @var \Magento\Directory\Helper\Data
     */
    private $directoryHelper;

    /**
     * Address constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory
     * @param \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory,
        \Magento\Directory\Helper\Data $directoryHelper,
        array $data = []
    ) {
        $this->countryCollectionFactory = $countryCollectionFactory;
        $this->regionCollectionFactory = $regionCollectionFactory;
        $this->directoryHelper = $directoryHelper;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Address');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Address');
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
        $address = false;
        if ($locatorId) {
            $address = $this->getLocator()->getAddress();
        }

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
        );

        $fieldset = $form->addFieldset('address_fieldset', ['legend' => __('Address')]);

        $fieldset->addField(
            'address_id',
            'hidden',
            [
                'name' => 'address[address_id]'
            ]
        );

        $fieldset->addField(
            'latitude',
            'text',
            [
                'name' => 'latitude',
                'label' => __('Latitude'),
                'title' => __('Latitude'),
                'required' => true,
                'class'     => 'required-entry validate-number'
            ]
        );

        $fieldset->addField(
            'longitude',
            'text',
            [
                'name' => 'longitude',
                'label' => __('Longitude'),
                'title' => __('Longitude'),
                'required' => true,
                'class'     => 'required-entry validate-number'
            ]
        );

        $addressButton = $this->getLayout()->createBlock(
            \Magento\Backend\Block\Widget\Button::class
        )->setData(
            [
                'label' => __('Get Address By Lat/Long'),
                'id' => 'address_by_coordinate',
                'class' => 'save',
            ]
        );

        $fieldset->addField('address_button', 'note', ['text' => $addressButton->toHtml()]);

        $fieldset->addField(
            'company',
            'text',
            [
                'name' => 'address[company]',
                'label' => __('Company'),
                'title' => __('Company'),
                'required' => false,
            ]
        );

        $fieldset->addField(
            'street',
            'text',
            [
                'name' => 'address[street]',
                'label' => __('Street'),
                'title' => __('Street'),
                'required' => true,
            ]
        );

        $fieldset->addField(
            'city',
            'text',
            [
                'name' => 'address[city]',
                'label' => __('City'),
                'title' => __('City'),
                'required' => true,
            ]
        );

        $options = $this->getRegionCollection($address)->toOptionArray();
        $fieldset->addField(
            'region_id',
            'select',
            [
                'name' => 'address[region_id]',
                'label' => __('State/Province'),
                'title' => __('State/Province'),
                'required' => true,
                'values' => $options
            ]
        );

        $fieldset->addField(
            'region',
            'text',
            [
                'name' => 'address[region]',
                'label' => __('State/Province'),
                'title' => __('State/Province'),
                'required' => false,
                'visibility' => false,
            ]
        );

        $options = $this->getCountryCollection()->toOptionArray();
        $fieldset->addField(
            'country_id',
            'select',
            [
                'name' => 'address[country_id]',
                'label' => __('Country'),
                'title' => __('Country'),
                'required' => true,
                'values' => $options,

                'value' => $this->directoryHelper->getDefaultCountry()

            ]
        );

        $fieldset->addField(
            'postcode',
            'text',
            [
                'name' => 'address[postcode]',
                'label' => __('Zip/Postal Code'),
                'title' => __('Zip/Postal Code'),
                'required' => true,
            ]
        );

        $fieldset->addField(
            'telephone',
            'text',
            [
                'name' => 'address[telephone]',
                'label' => __('Phone Number'),
                'title' => __('Phone Number'),
                'class'     => 'required-entry validate-phoneStrict',
                'required' => false,
            ]
        );

        if ($locatorId) {
            $data = array_merge($address->getData(), $this->getLocator()->getData());
            $form->addValues($data);
        }

        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * @return \Magento\Directory\Model\ResourceModel\Country\Collection
     */
    public function getCountryCollection()
    {
        $collection = $this->getData('country_collection');
        if ($collection === null) {
            $collection = $this->countryCollectionFactory->create()->loadByStore();
            $this->setData('country_collection', $collection);
        }

        return $collection;
    }

    /**
     * @return \Magento\Directory\Model\ResourceModel\Region\Collection
     */
    public function getRegionCollection($address)
    {
        $collection = $this->getData('region_collection');
        if ($collection === null) {
            if (!$address) {
                $collection = $this->regionCollectionFactory->create()
                    ->addCountryFilter($this->getCountryId())->load();
            } else {
                $collection = $this->regionCollectionFactory->create()
                    ->addCountryFilter($address->getCountryId())->load();
            }

            $this->setData('region_collection', $collection);
        }
        return $collection;
    }

    /**
     * @return string
     */
    public function getCountryId()
    {
        $countryId = $this->getData('country_id');
        if ($countryId === null) {
            $countryId = $this->directoryHelper->getDefaultCountry();
        }
        return $countryId;
    }
}
