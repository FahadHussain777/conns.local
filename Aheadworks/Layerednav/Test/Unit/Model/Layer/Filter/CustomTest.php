<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Layerednav\Test\Unit\Model\Layer\Filter;

use Aheadworks\Layerednav\Model\Layer\Filter\Custom as CustomFilter;
use Aheadworks\Layerednav\Model\ResourceModel\Layer\ConditionRegistry;
use Aheadworks\Layerednav\Model\ResourceModel\Layer\Filter\Custom\AbstractFilter as ResourceAbstractFilter;
use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\State;
use Magento\Catalog\Model\Layer\Filter\Item as FilterItem;
use Magento\Catalog\Model\Layer\Filter\ItemFactory;
use Magento\Catalog\Model\Layer\Filter\Item\DataBuilder as ItemDataBuilder;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Filter\StripTags;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Layerednav\Model\Layer\Filter\Custom
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CustomTest extends \PHPUnit_Framework_TestCase
{
    const REQUEST_VAR = 'custom';
    const ITEM_LABEL = 'Custom';

    /**
     * @var CustomFilter
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
     * @var ConditionRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    private $conditionRegistryMock;

    /**
     * @var ResourceAbstractFilter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceMock;

    /**
     * @var StringUtils|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stringUtilsMock;

    /**
     * @var StripTags|\PHPUnit_Framework_MockObject_MockObject
     */
    private $tagFilterMock;

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
        $this->conditionRegistryMock = $this->getMock(ConditionRegistry::class, ['addConditions'], [], '', false);
        $this->resourceMock = $this->getMockForAbstractClass(
            ResourceAbstractFilter::class,
            [],
            '',
            false,
            false,
            true,
            [
                'joinFilterToCollection',
                'getWhereConditions',
                'getCount',
                'getParentCount'
            ]
        );
        $this->stringUtilsMock = $this->getMock(StringUtils::class, ['strlen'], [], '', false);
        $this->tagFilterMock = $this->getMock(StripTags::class, ['filter'], [], '', false);

        $this->filter = $objectManager->getObject(
            CustomFilter::class,
            [
                'filterItemFactory' => $this->filterItemFactoryMock,
                'layer' => $this->layerMock,
                'itemDataBuilder' => $this->itemDataBuilderMock,
                'conditionsRegistry' => $this->conditionRegistryMock,
                'resource' => $this->resourceMock,
                'stringUtils' => $this->stringUtilsMock,
                'tagFilter' => $this->tagFilterMock,
                'requestVar' => self::REQUEST_VAR,
                'itemLabel' => self::ITEM_LABEL
            ]
        );
    }

    public function testApply()
    {
        $filterValue = '1';
        $attributeCode = 'attribute';
        $conditions = ['filter_value = 1'];

        /** @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject $requestMock */
        $requestMock = $this->getMockForAbstractClass(RequestInterface::class);
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
            ->with($this->equalTo(self::REQUEST_VAR))
            ->willReturn($filterValue);
        $this->resourceMock->expects($this->once())
            ->method('joinFilterToCollection')
            ->with($this->filter);
        $this->resourceMock->expects($this->once())
            ->method('getWhereConditions')
            ->with(
                $this->equalTo($this->filter),
                $this->equalTo($filterValue)
            )
            ->willReturn([$attributeCode => $conditions]);
        $this->conditionRegistryMock->expects($this->once())
            ->method('addConditions')
            ->with($this->equalTo($attributeCode), $this->equalTo($conditions));
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
            ->with($this->equalTo(self::ITEM_LABEL))
            ->willReturnSelf();
        $filterItemMock->expects($this->once())
            ->method('setValue')
            ->with($this->equalTo($filterValue))
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
        $productsCount = 10;
        $itemsData = [
            [
                'label' => self::ITEM_LABEL,
                'value' => 1,
                'count' => $productsCount
            ]
        ];

        $this->resourceMock->expects($this->once())
            ->method('getCount')
            ->with($this->equalTo($this->filter))
            ->willReturn([1 => $productsCount]);
        $this->resourceMock->expects($this->once())
            ->method('getParentCount')
            ->with($this->equalTo($this->filter))
            ->willReturn([1 => $productsCount]);
        $this->stringUtilsMock->expects($this->once())
            ->method('strlen')
            ->with($this->equalTo(1))
            ->willReturn(6);
        $this->tagFilterMock->expects($this->once())
            ->method('filter')
            ->with($this->equalTo(self::ITEM_LABEL))
            ->willReturnArgument(0);
        $this->itemDataBuilderMock->expects($this->once())
            ->method('addItemData')
            ->with(
                $this->equalTo(self::ITEM_LABEL),
                $this->equalTo(1),
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
