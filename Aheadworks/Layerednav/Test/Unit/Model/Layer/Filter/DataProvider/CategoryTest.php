<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Layerednav\Test\Unit\Model\Layer\Filter\DataProvider;

use Aheadworks\Layerednav\Model\Layer\Filter\DataProvider\Category as CategoryDataProvider;
use Aheadworks\Layerednav\Model\ResourceModel\Layer\Filter\Category as ResourceCategory;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\Layer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\Store;

/**
 * Test for \Aheadworks\Layerednav\Model\Layer\Filter\DataProvider\Category
 */
class CategoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CategoryDataProvider
     */
    private $dataProvider;

    /**
     * @var ResourceCategory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceMock;

    /**
     * @var Layer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $layerMock;

    /**
     * @var CategoryRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $categoryRepositoryMock;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->resourceMock = $this->getMock(ResourceCategory::class, [], [], '', false);
        $this->layerMock = $this->getMock(Layer::class, ['getCurrentStore'], [], '', false);
        $this->categoryRepositoryMock = $this->getMockForAbstractClass(CategoryRepositoryInterface::class);
        $this->dataProvider = $objectManager->getObject(
            CategoryDataProvider::class,
            [
                'resource' => $this->resourceMock,
                'layer' => $this->layerMock,
                'categoryRepository' => $this->categoryRepositoryMock
            ]
        );
    }

    public function testGetResource()
    {
        $this->assertEquals($this->resourceMock, $this->dataProvider->getResource());
    }

    /**
     * @param int $categoryId
     * @param CategoryInterface|\PHPUnit_Framework_MockObject_MockObject $category
     * @param int $parentCategoryId
     * @param CategoryInterface|\PHPUnit_Framework_MockObject_MockObject $parentCategory
     * @param array|bool $result
     * @dataProvider validateFilterDataProvider
     */
    public function testValidateFilter(
        $categoryId,
        $category,
        $parentCategoryId,
        $parentCategory,
        $result
    ) {
        $storeId = 2;
        $storeMock = $this->getMock(Store::class, ['getStoreId'], [], '', false);

        $this->layerMock->expects($this->once())
            ->method('getCurrentStore')
            ->willReturn($storeMock);
        $storeMock->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeId);
        $this->categoryRepositoryMock->expects($parentCategoryId ? $this->exactly(2) : $this->once())
            ->method('get')
            ->willReturnMap(
                [
                    [$categoryId, $storeId, $category],
                    [$parentCategoryId, null, $parentCategory]
                ]
            );

        $this->assertEquals($result, $this->dataProvider->validateFilter([$categoryId]));
    }

    /**
     * @param CategoryInterface|\PHPUnit_Framework_MockObject_MockObject $category
     * @param int $parentCategoryId
     * @param CategoryInterface|\PHPUnit_Framework_MockObject_MockObject $parentCategory
     * @param bool $result
     * @dataProvider isValidDataProvider
     */
    public function testIsValid($category, $parentCategoryId, $parentCategory, $result)
    {
        if ($parentCategoryId) {
            $this->categoryRepositoryMock->expects($this->once())
                ->method('get')
                ->with($this->equalTo($parentCategoryId))
                ->willReturn($parentCategory);
        }

        $class = new \ReflectionClass($this->dataProvider);
        $method = $class->getMethod('isValid');
        $method->setAccessible(true);

        $this->assertSame($result, $method->invokeArgs($this->dataProvider, [$category]));
    }

    /**
     * Create category mock
     *
     * @param int $id
     * @param int $level
     * @param int|null $parentId
     * @param bool $isActive
     * @return CategoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createCategoryMock($id, $level = 0, $parentId = null, $isActive = true)
    {
        $categoryMock = $this->getMockForAbstractClass(CategoryInterface::class);
        $categoryMock->expects($this->any())
            ->method('getId')
            ->willReturn($id);
        $categoryMock->expects($this->any())
            ->method('getLevel')
            ->willReturn($level);
        $categoryMock->expects($this->any())
            ->method('getParentId')
            ->willReturn($parentId);
        $categoryMock->expects($this->any())
            ->method('getIsActive')
            ->willReturn($isActive);
        return $categoryMock;
    }

    /**
     * @return array
     */
    public function validateFilterDataProvider()
    {
        return [
            'valid filter' => [
                1,
                $this->createCategoryMock(1, 1, 2, true),
                2,
                $this->createCategoryMock(2),
                [1]
            ],
            'invalid filter' => [
                1,
                $this->createCategoryMock(1, 1, 2, false),
                null,
                null,
                false
            ]
        ];
    }

    /**
     * @return array
     */
    public function isValidDataProvider()
    {
        return [
            [$this->createCategoryMock(1), null, null, true],
            [$this->createCategoryMock(1, 1, 2, true), 2, $this->createCategoryMock(2), true],
            [$this->createCategoryMock(1, 1, 2, false), null, null, false],
            [$this->createCategoryMock(null), null, null, false]
        ];
    }
}
