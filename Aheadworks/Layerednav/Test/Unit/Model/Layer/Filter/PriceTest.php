<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Layerednav\Test\Unit\Model\Layer\Filter;

use Aheadworks\Layerednav\Model\Layer\Filter\DataProvider\Price as DataProvider;
use Aheadworks\Layerednav\Model\Layer\Filter\DataProvider\PriceFactory as DataProviderFactory;
use Aheadworks\Layerednav\Model\Layer\Filter\Price as PriceFilter;
use Aheadworks\Layerednav\Model\ResourceModel\Layer\ConditionRegistry;
use Aheadworks\Layerednav\Model\ResourceModel\Layer\Filter\Price as ResourcePrice;
use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\State;
use Magento\Catalog\Model\Layer\Filter\Dynamic\AlgorithmFactory;
use Magento\Catalog\Model\Layer\Filter\Dynamic\AlgorithmInterface;
use Magento\Catalog\Model\Layer\Filter\Item as FilterItem;
use Magento\Catalog\Model\Layer\Filter\ItemFactory;
use Magento\Catalog\Model\Layer\Filter\Item\DataBuilder as ItemDataBuilder;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Layerednav\Model\Layer\Filter\Price
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PriceTest extends \PHPUnit_Framework_TestCase
{
    const ATTRIBUTE_CODE = 'price';

    /**
     * @var PriceFilter
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
     * @var AlgorithmFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $algorithmFactoryMock;

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
        $this->algorithmFactoryMock = $this->getMock(AlgorithmFactory::class, ['create'], [], '', false);
        $this->dataProviderMock = $this->getMock(
            DataProvider::class,
            [
                'getIntervals',
                'setInterval',
                'getInterval',
                'getResource',
                'getAdditionalRequestData'
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
            PriceFilter::class,
            [
                'filterItemFactory' => $this->filterItemFactoryMock,
                'layer' => $this->layerMock,
                'itemDataBuilder' => $this->itemDataBuilderMock,
                'algorithmFactory' => $this->algorithmFactoryMock,
                'dataProviderFactory' => $dataProviderFactoryMock,
                'conditionsRegistry' => $this->conditionRegistryMock,
                'data' => ['attribute_model' => $this->attributeMock]
            ]
        );
    }

    public function testApply()
    {
        $from = '10.00';
        $to = '20.00';
        $conditions = ['filter_value > 10.00 AND filter_value < 20.00'];

        /** @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject $requestMock */
        $requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $resourceMock = $this->getMock(ResourcePrice::class, ['getWhereConditions'], [], '', false);
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
        $this->dataProviderMock->expects($this->once())
            ->method('setInterval')
            ->with($this->equalTo([[$from, $to]]));
        $this->dataProviderMock->expects($this->once())
            ->method('getInterval')
            ->willReturn([[$from, $to]]);
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
        $interval = [['10.00', '20.00']];
        $additionalRequestData = '';
        $itemsData = [
            [
                'label' => '10.00-20.00',
                'value' => '10.00-20.00',
                'count' => 10
            ]
        ];

        $algorithmMock = $this->getMockForAbstractClass(AlgorithmInterface::class);

        $this->algorithmFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($algorithmMock);
        $this->dataProviderMock->expects($this->once())
            ->method('getInterval')
            ->willReturn($interval);
        $this->dataProviderMock->expects($this->once())
            ->method('getAdditionalRequestData')
            ->willReturn($additionalRequestData);
        $algorithmMock->expects($this->once())
            ->method('getItemsData')
            ->with(
                $this->equalTo($interval),
                $this->equalTo($additionalRequestData)
            )
            ->willReturn($itemsData);

        $class = new \ReflectionClass($this->filter);
        $method = $class->getMethod('_getItemsData');
        $method->setAccessible(true);

        $this->assertEquals($itemsData, $method->invoke($this->filter));
    }
}
