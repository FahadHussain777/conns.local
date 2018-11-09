<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Layerednav\Test\Unit\Model\Layer\Filter;

use Aheadworks\Layerednav\Model\Layer\Filter\Attribute as AttributeFilter;
use Aheadworks\Layerednav\Model\ResourceModel\Layer\Filter\Attribute as ResourceAttribute;
use Aheadworks\Layerednav\Model\ResourceModel\Layer\ConditionRegistry;
use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\State;
use Magento\Catalog\Model\Layer\Filter\Item as FilterItem;
use Magento\Catalog\Model\Layer\Filter\ItemFactory;
use Magento\Catalog\Model\Layer\Filter\Item\DataBuilder as ItemDataBuilder;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Eav\Model\Entity\Attribute\Frontend\AbstractFrontend;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Filter\StripTags;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Layerednav\Model\Layer\Filter\Attribute
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AttributeTest extends \PHPUnit_Framework_TestCase
{
    const ATTRIBUTE_CODE = 'attribute_code';

    /**
     * @var AttributeFilter
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
     * @var ResourceAttribute|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceMock;

    /**
     * @var ConditionRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    private $conditionRegistryMock;

    /**
     * @var StringUtils|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stringUtilsMock;

    /**
     * @var StripTags|\PHPUnit_Framework_MockObject_MockObject
     */
    private $tagFilterMock;

    /**
     * @var Attribute|\PHPUnit_Framework_MockObject_MockObject
     */
    private $attributeMock;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->layerMock = $this->getMock(Layer::class, ['getState'], [], '', false);
        $this->filterItemFactoryMock = $this->getMock(ItemFactory::class, ['create'], [], '', false);
        $this->itemDataBuilderMock = $this->getMock(ItemDataBuilder::class, ['addItemData', 'build'], [], '', false);
        $this->resourceMock = $this->getMock(
            ResourceAttribute::class,
            [
                'joinFilterToCollection',
                'getWhereConditions',
                'getCount',
                'getParentCount'
            ],
            [],
            '',
            false
        );
        $this->conditionRegistryMock = $this->getMock(ConditionRegistry::class, ['addConditions'], [], '', false);
        $this->stringUtilsMock = $this->getMock(StringUtils::class, ['strlen'], [], '', false);
        $this->tagFilterMock = $this->getMock(StripTags::class, ['filter'], [], '', false);
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
            AttributeFilter::class,
            [
                'layer' => $this->layerMock,
                'filterItemFactory' => $this->filterItemFactoryMock,
                'itemDataBuilder' => $this->itemDataBuilderMock,
                'resource' => $this->resourceMock,
                'conditionsRegistry' => $this->conditionRegistryMock,
                'stringUtils' => $this->stringUtilsMock,
                'tagFilter' => $this->tagFilterMock,
                'data' => ['attribute_model' => $this->attributeMock]
            ]
        );
    }

    public function testApply()
    {
        $filterValue = 1;
        $filterOptionText = 'option';
        $conditions = ['value IN (1)'];

        /** @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject $requestMock */
        $requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $frontendMock = $this->getMockForAbstractClass(
            AbstractFrontend::class,
            [],
            '',
            false,
            false,
            true,
            ['getOption']
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
            ->willReturn($filterValue);
        $this->attributeMock->expects($this->once())
            ->method('getFrontend')
            ->willReturn($frontendMock);
        $frontendMock->expects($this->once())
            ->method('getOption')
            ->with($filterValue)
            ->willReturn($filterOptionText);
        $this->resourceMock->expects($this->once())
            ->method('joinFilterToCollection')
            ->with($this->equalTo($this->filter));
        $this->resourceMock->expects($this->once())
            ->method('getWhereConditions')
            ->with($this->equalTo($this->filter), $this->equalTo($filterValue))
            ->willReturn($conditions);
        $this->conditionRegistryMock->expects($this->once())
            ->method('addConditions')
            ->with(
                $this->equalTo(self::ATTRIBUTE_CODE),
                $this->equalTo($conditions)
            );
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
            ->with($this->equalTo($filterOptionText))
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
        $optionValue = 1;
        $optionLabel = 'option';
        $productsCount = '10';
        $itemsData = [
            [
                'label' => $optionLabel,
                'value' => $optionValue,
                'count' => $productsCount
            ]
        ];

        $frontendMock = $this->getMockForAbstractClass(
            AbstractFrontend::class,
            [],
            '',
            false,
            false,
            true,
            ['getSelectOptions']
        );

        $this->attributeMock->expects($this->once())
            ->method('getFrontend')
            ->willReturn($frontendMock);
        $frontendMock->expects($this->once())
            ->method('getSelectOptions')
            ->willReturn([['value' => $optionValue, 'label' => $optionLabel]]);
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
            ->with($this->equalTo($optionValue))
            ->willReturn(1);
        $this->attributeMock->expects($this->once())
            ->method('getIsFilterable')
            ->willReturn(true);
        $this->tagFilterMock->expects($this->once())
            ->method('filter')
            ->with($this->equalTo($optionLabel))
            ->willReturnArgument(0);
        $this->itemDataBuilderMock->expects($this->once())
            ->method('addItemData')
            ->with(
                $this->equalTo($optionLabel),
                $this->equalTo($optionValue),
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
