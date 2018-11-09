<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Layerednav\Test\Unit\Model\Layer\Filter;

use Aheadworks\Layerednav\Model\Layer\Filter\DataProvider\Decimal as DataProvider;
use Aheadworks\Layerednav\Model\Layer\Filter\DataProvider\DecimalFactory as DataProviderFactory;
use Aheadworks\Layerednav\Model\Layer\Filter\Decimal as DecimalFilter;
use Aheadworks\Layerednav\Model\ResourceModel\Layer\ConditionRegistry;
use Aheadworks\Layerednav\Model\ResourceModel\Layer\Filter\Decimal as ResourceDecimal;
use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\State;
use Magento\Catalog\Model\Layer\Filter\Item as FilterItem;
use Magento\Catalog\Model\Layer\Filter\ItemFactory;
use Magento\Catalog\Model\Layer\Filter\Item\DataBuilder as ItemDataBuilder;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Layerednav\Model\Layer\Filter\Decimal
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DecimalTest extends \PHPUnit_Framework_TestCase
{
    const ATTRIBUTE_CODE = 'decimal_attribute_code';

    /**
     * @var DecimalFilter
     */
    private $filter;

    /**
     * @var Layer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $layerMock;

    /**
     * @var ItemFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filterItemFactoryMock;

    /**
     * @var ItemDataBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $itemDataBuilderMock;

    /**
     * @var PriceCurrencyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $priceCurrencyMock;

    /**
     * @var DataProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataProviderMock;

    /**
     * @var ConditionRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    private $conditionRegistryMock;

    /**
     * @var Attribute|\PHPUnit_Framework_MockObject_MockObject
     */
    private $attributeMock;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->layerMock = $this->getMock(Layer::class, ['getState'], [], '', false);
        $this->filterItemFactoryMock = $this->getMock(ItemFactory::class, ['create'], [], '', false);
        $this->itemDataBuilderMock = $this->getMock(
            ItemDataBuilder::class,
            ['addItemData', 'build'],
            [],
            '',
            false
        );
        $this->priceCurrencyMock = $this->getMockForAbstractClass(PriceCurrencyInterface::class);
        $this->dataProviderMock = $this->getMock(
            DataProvider::class,
            [
                'getIntervals',
                'getResource',
                'getRange',
                'getRangeItemCounts'
            ],
            [],
            '',
            false
        );
        $dataProviderFactoryMock = $this->getMock(DataProviderFactory::class, ['create'], [], '', false);
        $dataProviderFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->dataProviderMock);
        $this->conditionRegistryMock = $this->getMock(ConditionRegistry::class, ['addConditions'], [], '', false);
        $this->attributeMock = $this->getMock(
            Attribute::class,
            ['getFrontend', 'getIsFilterable', 'getAttributeCode'],
            [],
            '',
            false
        );
        $this->attributeMock->expects($this->any())
            ->method('getAttributeCode')
            ->willReturn(self::ATTRIBUTE_CODE);

        $this->filter = $objectManager->getObject(
            DecimalFilter::class,
            [
                'filterItemFactory' => $this->filterItemFactoryMock,
                'layer' => $this->layerMock,
                'itemDataBuilder' => $this->itemDataBuilderMock,
                'priceCurrency' => $this->priceCurrencyMock,
                'dataProviderFactory' => $dataProviderFactoryMock,
                'conditionsRegistry' => $this->conditionRegistryMock,
                'data' => ['attribute_model' => $this->attributeMock]
            ]
        );
    }

    public function testApply()
    {
        $from = '1.00';
        $to = '2.00';
        $conditions = ['filter_value > 1.00 AND filter_value < 2.00'];

        /** @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject $requestMock */
        $requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $resourceMock = $this->getMock(
            ResourceDecimal::class,
            ['joinFilterToCollection', 'getWhereConditions'],
            [],
            '',
            false
        );
        $stateMock = $this->getMock(State::class, ['addFilter'], [], '', false);
        $filterItemMock = $this->getMock(
            FilterItem::class,
            ['setFilter', 'setLabel', 'setValue', 'setCount'],
            [],
            '',
            false
        );

        $requestMock->expects($this->once())
            ->method('getParam')
            ->with($this->equalTo(self::ATTRIBUTE_CODE))
            ->willReturn($from . '-' . $to);
        $this->dataProviderMock->expects($this->once())
            ->method('getIntervals')
            ->with($this->equalTo([$from . '-' . $to]))
            ->willReturn([[$from, $to]]);
        $this->dataProviderMock->expects($this->any())
            ->method('getResource')
            ->willReturn($resourceMock);
        $resourceMock->expects($this->once())
            ->method('joinFilterToCollection')
            ->with($this->filter);
        $resourceMock->expects($this->once())
            ->method('getWhereConditions')
            ->with(
                $this->equalTo($this->filter),
                $this->equalTo([[$from, $to]])
            )
            ->willReturn($conditions);
        $this->conditionRegistryMock->expects($this->once())
            ->method('addConditions')
            ->with($this->equalTo(self::ATTRIBUTE_CODE), $this->equalTo($conditions));
        $this->layerMock->expects($this->once())
            ->method('getState')
            ->willReturn($stateMock);
        $this->filterItemFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($filterItemMock);
        $filterItemMock->expects($this->once())
            ->method('setFilter')
            ->with($this->equalTo($this->filter))
            ->willReturnSelf();
        $filterItemMock->expects($this->once())
            ->method('setLabel')
            ->with($this->equalTo(self::ATTRIBUTE_CODE))
            ->willReturnSelf();
        $filterItemMock->expects($this->once())
            ->method('setValue')
            ->with($this->equalTo($from . '-' . $to))
            ->willReturnSelf();
        $filterItemMock->expects($this->once())
            ->method('setCount')
            ->with($this->equalTo(0))
            ->willReturnSelf();
        $stateMock->expects($this->once())
            ->method('addFilter')
            ->with($this->equalTo($filterItemMock));

        $this->assertSame($this->filter, $this->filter->apply($requestMock));
    }

    public function testGetItemsData()
    {
        $range = 10;
        $index = 2;
        $productsCount = 15;
        $itemsData = [
            [
                'label' => '10.00-20.00',
                'value' => $index . '-' . $range,
                'count' => $productsCount
            ]
        ];

        $this->dataProviderMock->expects($this->once())
            ->method('getRange')
            ->with($this->equalTo($this->filter))
            ->willReturn($range);
        $this->dataProviderMock->expects($this->once())
            ->method('getRangeItemCounts')
            ->with($this->equalTo($range), $this->equalTo($this->filter))
            ->willReturn([$index => $productsCount]);
        $this->priceCurrencyMock->expects($this->exactly(2))
            ->method('format')
            ->willReturnMap(
                [
                    [10, false, PriceCurrencyInterface::DEFAULT_PRECISION, null, null, '10.00'],
                    [20, false, PriceCurrencyInterface::DEFAULT_PRECISION, null, null, '20.00']
                ]
            );
        $this->itemDataBuilderMock->expects($this->once())
            ->method('addItemData')
            ->with(
                $this->equalTo('10.00 - 20.00'),
                $this->equalTo($index . '-' . $range),
                $this->equalTo($productsCount)
            );
        $this->itemDataBuilderMock->expects($this->once())
            ->method('build')
            ->willReturn($itemsData);

        $class = new \ReflectionClass($this->filter);
        $method = $class->getMethod('_getItemsData');
        $method->setAccessible(true);

        $this->assertEquals($itemsData, $method->invoke($this->filter));
    }
}
