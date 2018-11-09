<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Layerednav\Test\Unit\Model\Layer;

use Aheadworks\Layerednav\Model\Config;
use Aheadworks\Layerednav\Model\Layer\FilterList;
use Aheadworks\Layerednav\Model\Layer\Filter\Attribute as AttributeFilter;
use Aheadworks\Layerednav\Model\Layer\Filter\Category as CategoryFilter;
use Aheadworks\Layerednav\Model\Layer\Filter\Decimal as DecimalFilter;
use Aheadworks\Layerednav\Model\Layer\Filter\Price as PriceFilter;
use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Filter\AbstractFilter;
use Magento\Catalog\Model\Layer\FilterableAttributeListInterface;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Layerednav\Model\Layer\FilterList
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FilterListTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FilterList
     */
    private $filterList;

    /**
     * @var ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManagerMock;

    /**
     * @var FilterableAttributeListInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filterableAttributesMock;

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->filterableAttributesMock = $this->getMockForAbstractClass(FilterableAttributeListInterface::class);
        $this->configMock = $this->getMock(
            Config::class,
            [
                'isNewFilterEnabled',
                'isInStockFilterEnabled',
                'isOnSaleFilterEnabled'
            ],
            [],
            '',
            false
        );
        $this->filterList = $objectManager->getObject(
            FilterList::class,
            [
                'objectManager' => $this->objectManagerMock,
                'filterableAttributes' => $this->filterableAttributesMock,
                'config' => $this->configMock
            ]
        );
    }

    public function testGetFilters()
    {
        /** @var Layer|\PHPUnit_Framework_MockObject_MockObject $layerMock */
        $layerMock = $this->getMock(Layer::class, [], [], '', false);
        $attributeMock = $this->getMock(Attribute::class, ['getAttributeCode', 'getBackendType'], [], '', false);
        $categoryFilterMock = $this->getMockForAbstractClass(AbstractFilter::class, [], '', false, false, true);
        $newFilterMock = $this->getMockForAbstractClass(AbstractFilter::class, [], '', false, false, true);
        $attributeFilterMock = $this->getMockForAbstractClass(AbstractFilter::class, [], '', false, false, true);

        $this->configMock->expects($this->once())
            ->method('isNewFilterEnabled')
            ->willReturn(true);
        $this->configMock->expects($this->once())
            ->method('isInStockFilterEnabled')
            ->willReturn(false);
        $this->configMock->expects($this->once())
            ->method('isOnSaleFilterEnabled')
            ->willReturn(false);

        $this->filterableAttributesMock->expects($this->once())
            ->method('getList')
            ->willReturn([$attributeMock]);

        $this->objectManagerMock->expects($this->exactly(3))
            ->method('create')
            ->willReturnMap(
                [
                    [
                        CategoryFilter::class,
                        ['layer' => $layerMock],
                        $categoryFilterMock
                    ],
                    [
                        'Aheadworks\Layerednav\Model\Layer\Filter\Custom\NewProduct',
                        ['layer' => $layerMock],
                        $newFilterMock
                    ],
                    [
                        AttributeFilter::class,
                        ['data' => ['attribute_model' => $attributeMock], 'layer' => $layerMock],
                        $attributeFilterMock
                    ]
                ]
            );
        $this->assertEquals(
            [$categoryFilterMock, $newFilterMock, $attributeFilterMock],
            $this->filterList->getFilters($layerMock)
        );
        $this->filterList->getFilters($layerMock);
    }

    /**
     * @param Attribute|\PHPUnit_Framework_MockObject_MockObject $attribute
     * @param string $filterClassName
     * @dataProvider createAttributeFilterDataProvider
     */
    public function testCreateAttributeFilter($attribute, $filterClassName)
    {
        /** @var Layer|\PHPUnit_Framework_MockObject_MockObject $layerMock */
        $layerMock = $this->getMock(Layer::class, [], [], '', false);
        $filterMock = $this->getMockForAbstractClass(AbstractFilter::class, [], '', false, false, true);

        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with(
                $this->equalTo($filterClassName),
                $this->equalTo(['data' => ['attribute_model' => $attribute], 'layer' => $layerMock])
            )
            ->willReturn($filterMock);

        $class = new \ReflectionClass($this->filterList);
        $method = $class->getMethod('createAttributeFilter');
        $method->setAccessible(true);

        $this->assertEquals($filterMock, $method->invokeArgs($this->filterList, [$attribute, $layerMock]));
    }

    /**
     * @param string $filterCode
     * @param string $filterClassName
     * @dataProvider createCustomFilterDataProvider
     */
    public function testCreateCustomFilter($filterCode, $filterClassName)
    {
        /** @var Layer|\PHPUnit_Framework_MockObject_MockObject $layerMock */
        $layerMock = $this->getMock(Layer::class, [], [], '', false);
        $filterMock = $this->getMockForAbstractClass(AbstractFilter::class, [], '', false, false, true);

        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with($this->equalTo($filterClassName), $this->equalTo(['layer' => $layerMock]))
            ->willReturn($filterMock);

        $class = new \ReflectionClass($this->filterList);
        $method = $class->getMethod('createCustomFilter');
        $method->setAccessible(true);

        $this->assertEquals($filterMock, $method->invokeArgs($this->filterList, [$layerMock, $filterCode]));
    }

    /**
     * @param bool $isNewFilter
     * @param bool $isInStockFilter
     * @param bool $isOnSaleFilter
     * @param array $customFilters
     * @dataProvider getAvailableCustomFiltersDataProvider
     */
    public function testGetAvailableCustomFilters(
        $isNewFilter,
        $isInStockFilter,
        $isOnSaleFilter,
        $customFilters
    ) {
        $this->configMock->expects($this->once())
            ->method('isNewFilterEnabled')
            ->willReturn($isNewFilter);
        $this->configMock->expects($this->once())
            ->method('isInStockFilterEnabled')
            ->willReturn($isInStockFilter);
        $this->configMock->expects($this->once())
            ->method('isOnSaleFilterEnabled')
            ->willReturn($isOnSaleFilter);

        $class = new \ReflectionClass($this->filterList);
        $method = $class->getMethod('getAvailableCustomFilters');
        $method->setAccessible(true);

        $this->assertEquals($customFilters, $method->invoke($this->filterList));
    }

    /**
     * Create attribute mock
     *
     * @param string|null $attributeCode
     * @param string|null $backendType
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function createAttributeMock($attributeCode = null, $backendType = null)
    {
        $attributeMock = $this->getMock(Attribute::class, ['getAttributeCode', 'getBackendType'], [], '', false);
        if ($attributeCode) {
            $attributeMock->expects($this->any())
                ->method('getAttributeCode')
                ->willReturn($attributeCode);
        }
        if ($backendType) {
            $attributeMock->expects($this->any())
                ->method('getBackendType')
                ->willReturn($backendType);
        }
        return $attributeMock;
    }

    /**
     * @return array
     */
    public function createAttributeFilterDataProvider()
    {
        return [
            'default' => [$this->createAttributeMock(), AttributeFilter::class],
            'price' => [$this->createAttributeMock('price'), PriceFilter::class],
            'decimal' => [$this->createAttributeMock('decimal', 'decimal'), DecimalFilter::class]
        ];
    }

    /**
     * @return array
     */
    public function createCustomFilterDataProvider()
    {
        return [
            'new product filter' => [
                FilterList::NEW_FILTER,
                'Aheadworks\Layerednav\Model\Layer\Filter\Custom\NewProduct'
            ],
            'on sale filter' => [
                FilterList::SALES_FILTER,
                'Aheadworks\Layerednav\Model\Layer\Filter\Custom\Sales'
            ],
            'in stock filter' => [
                FilterList::STOCK_FILTER,
                'Aheadworks\Layerednav\Model\Layer\Filter\Custom\Stock'
            ]
        ];
    }

    /**
     * @return array
     */
    public function getAvailableCustomFiltersDataProvider()
    {
        return [
            'no custom filters' => [false, false, false, []],
            'new product filter' => [true, false, false, [FilterList::NEW_FILTER]],
            'in stock filter' => [false, true, false, [FilterList::STOCK_FILTER]],
            'on sale filter' => [false, false, true, [FilterList::SALES_FILTER]],
            'all custom filters' => [
                true,
                true,
                true,
                [
                    FilterList::NEW_FILTER,
                    FilterList::STOCK_FILTER,
                    FilterList::SALES_FILTER
                ]
            ]
        ];
    }
}
