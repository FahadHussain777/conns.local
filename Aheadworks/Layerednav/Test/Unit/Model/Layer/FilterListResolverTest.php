<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Layerednav\Test\Unit\Model\Layer;

use Aheadworks\Layerednav\Model\Layer\FilterList;
use Aheadworks\Layerednav\Model\Layer\FilterListResolver;
use Aheadworks\Layerednav\Model\PageTypeResolver;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Layerednav\Model\Layer\FilterListResolver
 */
class FilterListResolverTest extends \PHPUnit_Framework_TestCase
{
    const PAGE_TYPE1 = 'page_type1';
    const PAGE_TYPE2 = 'page_type2';
    const PAGE_TYPE_NOT_ASSIGNED = 'page_type_not_assigned';

    const FILTER_LIST_CLASS_NAME1 = 'FilterListClassName1';
    const FILTER_LIST_CLASS_NAME2 = 'FilterListClassName2';

    /**
     * @var FilterListResolver
     */
    private $filterListResolver;

    /**
     * @var ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManagerMock;

    /**
     * @var PageTypeResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    private $pageTypeResolverMock;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->pageTypeResolverMock = $this->getMock(PageTypeResolver::class, ['getType'], [], '', false);

        $this->filterListResolver = $objectManager->getObject(
            FilterListResolver::class,
            [
                'objectManager' => $this->objectManagerMock,
                'pageTypeResolver' => $this->pageTypeResolverMock,
                'filterListPool' => [
                    self::PAGE_TYPE1 => self::FILTER_LIST_CLASS_NAME1,
                    self::PAGE_TYPE2 => self::FILTER_LIST_CLASS_NAME2
                ]
            ]
        );
    }

    /**
     * @param string $pageType
     * @param string $filterListClassName
     * @dataProvider createDataProvider
     */
    public function testCreate($pageType, $filterListClassName)
    {
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with($filterListClassName);
        $this->filterListResolver->create($pageType);
    }

    /**
     * Testing creation for current page type
     */
    public function testCreateForCurrent()
    {
        $this->pageTypeResolverMock->expects($this->once())
            ->method('getType')
            ->willReturn(self::PAGE_TYPE1);
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with(self::FILTER_LIST_CLASS_NAME1);
        $this->filterListResolver->create();
    }

    /**
     * Testing of exception while filter list creation
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage page_type_not_assigned does not belong to any registered filter list
     */
    public function testCreateException()
    {
        $this->filterListResolver->create(self::PAGE_TYPE_NOT_ASSIGNED);
    }

    public function testGet()
    {
        $filterListMock = $this->getMock(FilterList::class, [], [], '', false);
        $this->pageTypeResolverMock->expects($this->once())
            ->method('getType')
            ->willReturn(self::PAGE_TYPE1);
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with(self::FILTER_LIST_CLASS_NAME1)
            ->willReturn($filterListMock);
        $this->assertEquals($filterListMock, $this->filterListResolver->get());
    }

    /**
     * @return array
     */
    public function createDataProvider()
    {
        return [
            'page type 1' => [self::PAGE_TYPE1, self::FILTER_LIST_CLASS_NAME1],
            'page type 2' => [self::PAGE_TYPE2, self::FILTER_LIST_CLASS_NAME2]
        ];
    }
}
