<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Layerednav\Test\Unit\Model\Layer\Filter;

use Aheadworks\Layerednav\Model\Layer\Filter\DataProvider\Category as DataProvider;
use Aheadworks\Layerednav\Model\Layer\Filter\DataProvider\CategoryFactory as DataProviderFactory;
use Aheadworks\Layerednav\Model\Layer\Filter\Category as CategoryFilter;
use Aheadworks\Layerednav\Model\PageTypeResolver;
use Aheadworks\Layerednav\Model\ResourceModel\Layer\Filter\Category as ResourceCategory;
use Aheadworks\Layerednav\Model\ResourceModel\Layer\ConditionRegistry;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\State;
use Magento\Catalog\Model\Layer\Filter\Item as FilterItem;
use Magento\Catalog\Model\Layer\Filter\ItemFactory;
use Magento\Catalog\Model\Layer\Filter\Item\DataBuilder as ItemDataBuilder;
use Magento\Catalog\Model\ResourceModel\Category\Collection as CategoryCollection;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Escaper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Layerednav\Model\Layer\Filter\Category
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CategoryTest extends \PHPUnit_Framework_TestCase
{
    const REQUEST_VAR = 'cat';

    const CATEGORY_ID = 1;
    const CATEGORY_NAME = 'Category';

    /**
     * @var CategoryFilter
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
     * @var DataProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataProviderMock;

    /**
     * @var ConditionRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    private $conditionRegistryMock;

    /**
     * @var Escaper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $escaperMock;

    /**
     * @var PageTypeResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    private $pageTypeResolverMock;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->layerMock = $this->getMock(
            Layer::class,
            ['getState', 'getCurrentCategory', 'getProductCollection'],
            [],
            '',
            false
        );
        $this->filterItemFactoryMock = $this->getMock(ItemFactory::class, ['create'], [], '', false);
        $this->itemDataBuilderMock = $this->getMock(
            ItemDataBuilder::class,
            ['addItemData', 'build'],
            [],
            '',
            false
        );
        $this->dataProviderMock = $this->getMock(
            DataProvider::class,
            ['validateFilter', 'getResource'],
            [],
            '',
            false
        );
        $this->conditionRegistryMock = $this->getMock(ConditionRegistry::class, ['addConditions'], [], '', false);
        $this->escaperMock = $this->getMock(Escaper::class, ['escapeHtml'], [], '', false);
        $this->pageTypeResolverMock = $this->getMock(PageTypeResolver::class, ['getType'], [], '', false);

        $dataProviderFactoryMock = $this->getMock(DataProviderFactory::class, ['create'], [], '', false);
        $dataProviderFactoryMock->expects($this->any())
            ->method('create')
            ->with($this->equalTo(['layer' => $this->layerMock]))
            ->willReturn($this->dataProviderMock);

        $this->filter = $objectManager->getObject(
            CategoryFilter::class,
            [
                'filterItemFactory' => $this->filterItemFactoryMock,
                'layer' => $this->layerMock,
                'itemDataBuilder' => $this->itemDataBuilderMock,
                'dataProviderFactory' => $dataProviderFactoryMock,
                'conditionsRegistry' => $this->conditionRegistryMock,
                'escaper' => $this->escaperMock,
                'pageTypeResolver' => $this->pageTypeResolverMock
            ]
        );
    }

    public function testApply()
    {
        $filterValue = '1,2';
        $categoryIds = [1, 2];
        $conditions = ["cat.category_id IN ('1','2')"];

        /** @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject $requestMock */
        $requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $resourceMock = $this->getMock(
            ResourceCategory::class,
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
            ->with($this->equalTo(self::REQUEST_VAR))
            ->willReturn($filterValue);
        $this->dataProviderMock->expects($this->once())
            ->method('validateFilter')
            ->with($this->equalTo(['1', '2']))
            ->willReturn($categoryIds);
        $this->dataProviderMock->expects($this->exactly(2))
            ->method('getResource')
            ->willReturn($resourceMock);
        $resourceMock->expects($this->once())
            ->method('joinFilterToCollection')
            ->with($this->equalTo($this->filter));
        $resourceMock->expects($this->once())
            ->method('getWhereConditions')
            ->with($this->equalTo($categoryIds))
            ->willReturn($conditions);
        $this->conditionRegistryMock->expects($this->once())
            ->method('addConditions')
            ->with($this->equalTo('category'), $this->equalTo($conditions));
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
            ->with($this->equalTo('category'))
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

    public function testGetName()
    {
        $filterTextLabel = 'Category';
        $this->assertEquals($filterTextLabel, $this->filter->getName());
    }

    /**
     * @param Category|\PHPUnit_Framework_MockObject_MockObject $category
     * @param Category[]|\PHPUnit_Framework_MockObject_MockObject[] $childCategories
     * @param int|null $count
     * @param array $itemsData
     * @dataProvider getItemsDataDataProvider
     */
    public function testGetItemsDataForCategoryPage($category, $childCategories, $count, $itemsData)
    {
        $categoryCollectionMock = $this->getMock(CategoryCollection::class, ['getIterator'], [], '', false);
        $productCollectionMock = $this->getMock(ProductCollection::class, ['addCountToCategories'], [], '', false);
        $resourceMock = $this->getMock(
            ResourceCategory::class,
            ['getProductCount'],
            [],
            '',
            false
        );

        $this->layerMock->expects($this->once())
            ->method('getCurrentCategory')
            ->willReturn($category);
        $category->expects($this->once())
            ->method('getChildrenCategories')
            ->willReturn($categoryCollectionMock);
        $this->layerMock->expects($this->once())
            ->method('getProductCollection')
            ->willReturn($productCollectionMock);
        $productCollectionMock->expects($this->once())
            ->method('addCountToCategories')
            ->with($this->equalTo($categoryCollectionMock));
        $this->dataProviderMock->expects($this->once())
            ->method('getResource')
            ->willReturn($resourceMock);
        $resourceMock->expects($this->any())
            ->method('getProductCount')
            ->willReturn($count);
        $categoryCollectionMock->expects($this->any())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($childCategories));
        if (count($itemsData)) {
            $this->escaperMock->expects($this->exactly(count($itemsData)))
                ->method('escapeHtml')
                ->willReturnArgument(0);
            $this->itemDataBuilderMock->expects($this->exactly(count($itemsData)))
                ->method('addItemData')
                ->with(
                    $this->equalTo(self::CATEGORY_NAME),
                    $this->equalTo(self::CATEGORY_ID),
                    $this->equalTo($count)
                );
        } else {
            $this->itemDataBuilderMock->expects($this->never())
                ->method('addItemData');
        }
        $this->itemDataBuilderMock->expects($this->once())
            ->method('build')
            ->willReturn($itemsData);

        $class = new \ReflectionClass($this->filter);
        $method = $class->getMethod('getItemsDataForCategoryPage');
        $method->setAccessible(true);

        $this->assertEquals($itemsData, $method->invoke($this->filter));
    }

    /**
     * @param Category|\PHPUnit_Framework_MockObject_MockObject $category
     * @param Category[]|\PHPUnit_Framework_MockObject_MockObject[] $childCategories
     * @param int|null $count
     * @param array $itemsData
     * @dataProvider getItemsDataDataProvider
     */
    public function testGetItemsDataForSearchPage($category, $childCategories, $count, $itemsData)
    {
        $categoryCollectionMock = $this->getMock(CategoryCollection::class, ['getIterator'], [], '', false);
        $productCollectionMock = $this->getMock(ProductCollection::class, ['getFacetedData'], [], '', false);

        $this->layerMock->expects($this->once())
            ->method('getCurrentCategory')
            ->willReturn($category);
        $category->expects($this->once())
            ->method('getChildrenCategories')
            ->willReturn($categoryCollectionMock);
        $this->layerMock->expects($this->once())
            ->method('getProductCollection')
            ->willReturn($productCollectionMock);
        $productCollectionMock->expects($this->once())
            ->method('getFacetedData')
            ->with($this->equalTo('category'))
            ->willReturn($count ? [self::CATEGORY_ID => ['count' => $count]] : []);
        $categoryCollectionMock->expects($this->any())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($childCategories));
        if (count($itemsData)) {
            $this->escaperMock->expects($this->exactly(count($itemsData)))
                ->method('escapeHtml')
                ->willReturnArgument(0);
            $this->itemDataBuilderMock->expects($this->exactly(count($itemsData)))
                ->method('addItemData')
                ->with(
                    $this->equalTo(self::CATEGORY_NAME),
                    $this->equalTo(self::CATEGORY_ID),
                    $this->equalTo($count)
                );
        } else {
            $this->itemDataBuilderMock->expects($this->never())
                ->method('addItemData');
        }
        $this->itemDataBuilderMock->expects($this->once())
            ->method('build')
            ->willReturn($itemsData);

        $class = new \ReflectionClass($this->filter);
        $method = $class->getMethod('getItemsDataForSearchPage');
        $method->setAccessible(true);

        $this->assertEquals($itemsData, $method->invoke($this->filter));
    }

    /**
     * Get current category mock
     *
     * @param bool $isActive
     * @return Category|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getCurrentCategoryMock($isActive)
    {
        $currentCategoryMock = $this->getMock(
            Category::class,
            ['getChildrenCategories', 'getIsActive'],
            [],
            '',
            false
        );
        $currentCategoryMock->expects($this->any())
            ->method('getIsActive')
            ->willReturn($isActive);

        return $currentCategoryMock;
    }

    /**
     * Get child category mock
     *
     * @param bool $isActive
     * @param int $productCount
     * @return Category|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getChildCategoryMock($isActive, $productCount)
    {
        $childCategoryMock = $this->getMock(
            Category::class,
            ['getId', 'getName', 'getIsActive', 'getProductCount'],
            [],
            '',
            false
        );
        $childCategoryMock->expects($this->any())
            ->method('getId')
            ->willReturn(self::CATEGORY_ID);
        $childCategoryMock->expects($this->any())
            ->method('getName')
            ->willReturn(self::CATEGORY_NAME);
        $childCategoryMock->expects($this->any())
            ->method('getIsActive')
            ->willReturn($isActive);
        $childCategoryMock->expects($this->any())
            ->method('getProductCount')
            ->willReturn($productCount);

        return $childCategoryMock;
    }

    /**
     * @return array
     */
    public function getItemsDataDataProvider()
    {
        return [
            [
                $this->getCurrentCategoryMock(true),
                [$this->getChildCategoryMock(true, 10)],
                10,
                [
                    [
                        'label' => self::CATEGORY_NAME,
                        'value' => self::CATEGORY_ID,
                        'count' => 10,
                    ]
                ]
            ],
            [
                $this->getCurrentCategoryMock(false),
                [$this->getChildCategoryMock(true, 10)],
                10,
                []
            ],
            [
                $this->getCurrentCategoryMock(true),
                [],
                null,
                []
            ],
            [
                $this->getCurrentCategoryMock(true),
                [$this->getChildCategoryMock(true, 0)],
                null,
                []
            ],
            [
                $this->getCurrentCategoryMock(true),
                [$this->getChildCategoryMock(false, 10)],
                10,
                []
            ]
        ];
    }
}
