<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Layerednav\Test\Unit\Model;

use Aheadworks\Layerednav\Model\PageTypeResolver;
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\View\Layout\ProcessorInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Layerednav\Model\PageTypeResolver
 */
class PageTypeResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PageTypeResolver
     */
    private $pageTypeResolver;

    /**
     * @var LayoutInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $layoutMock;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->layoutMock = $this->getMockForAbstractClass(LayoutInterface::class);
        $this->pageTypeResolver = $objectManager->getObject(
            PageTypeResolver::class,
            ['layout' => $this->layoutMock]
        );
    }

    /**
     * @param array $handles
     * @param string $pageType
     * @dataProvider getTypeDataProvider
     */
    public function testGetType($handles, $pageType)
    {
        $updateMock = $this->getMockForAbstractClass(ProcessorInterface::class);

        $this->layoutMock->expects($this->once())
            ->method('getUpdate')
            ->willReturn($updateMock);
        $updateMock->expects($this->once())
            ->method('getHandles')
            ->willReturn($handles);

        $this->assertEquals($pageType, $this->pageTypeResolver->getType());
    }

    /**
     * @return array
     */
    public function getTypeDataProvider()
    {
        return [
            'category page' => [['catalog_category_view'], PageTypeResolver::PAGE_TYPE_CATEGORY],
            'search page' => [['catalogsearch_result_index'], PageTypeResolver::PAGE_TYPE_CATALOG_SEARCH]
        ];
    }
}
