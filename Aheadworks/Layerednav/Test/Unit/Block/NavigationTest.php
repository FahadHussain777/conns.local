<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Layerednav\Test\Unit\Block;

use Aheadworks\Layerednav\Model\Applier;
use Aheadworks\Layerednav\Block\Navigation;
use Aheadworks\Layerednav\Model\Config;
use Aheadworks\Layerednav\Model\Layer\FilterList;
use Aheadworks\Layerednav\Model\Layer\FilterListResolver;
use Aheadworks\Layerednav\Model\PageTypeResolver;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\AvailabilityFlagInterface;
use Magento\Catalog\Model\Layer\Filter\AbstractFilter;
use Magento\Catalog\Model\Layer\Resolver as LayerResolver;
use Magento\Catalog\Model\Layer\State;
use Magento\Search\Model\QueryFactory;
use Magento\Search\Model\QueryInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Test for \Aheadworks\Layerednav\Block\Navigation
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class NavigationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Navigation
     */
    private $block;

    /**
     * @var Layer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $layerMock;

    /**
     * @var FilterList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filterListMock;

    /**
     * @var AvailabilityFlagInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $visibilityFlagMock;

    /**
     * @var Applier|\PHPUnit_Framework_MockObject_MockObject
     */
    private $applierMock;

    /**
     * @var PageTypeResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    private $pageTypeResolverMock;

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /**
     * @var QueryFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchQueryFactoryMock;

    /**
     * @var UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlBuilderMock;

    /**
     * @var StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->layerMock = $this->getMock(Layer::class, ['getState', 'getCurrentCategory'], [], '', false);
        $this->filterListMock = $this->getMock(FilterList::class, ['getFilters'], [], '', false);
        $this->visibilityFlagMock = $this->getMockForAbstractClass(AvailabilityFlagInterface::class);
        $this->applierMock = $this->getMock(Applier::class, ['applyFilters'], [], '', false);
        $this->pageTypeResolverMock = $this->getMock(PageTypeResolver::class, ['getType'], [], '', false);
        $this->configMock = $this->getMock(Config::class, ['isAjaxEnabled', 'isPopoverDisabled'], [], '', false);
        $this->searchQueryFactoryMock = $this->getMock(QueryFactory::class, ['get'], [], '', false);

        $layerResolverMock = $this->getMock(LayerResolver::class, ['get'], [], '', false);
        $layerResolverMock->expects($this->once())
            ->method('get')
            ->willReturn($this->layerMock);
        $filterListResolverMock = $this->getMock(FilterListResolver::class, ['get'], [], '', false);
        $filterListResolverMock->expects($this->once())
            ->method('get')
            ->willReturn($this->filterListMock);

        $this->urlBuilderMock = $this->getMockForAbstractClass(UrlInterface::class);
        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $context = $objectManager->getObject(
            Context::class,
            [
                'urlBuilder' => $this->urlBuilderMock,
                'storeManager' => $this->storeManagerMock
            ]
        );

        $this->block = $objectManager->getObject(
            Navigation::class,
            [
                'context' => $context,
                'layerResolver' => $layerResolverMock,
                'filterListResolver' => $filterListResolverMock,
                'visibilityFlag' => $this->visibilityFlagMock,
                'applier' => $this->applierMock,
                'pageTypeResolver' => $this->pageTypeResolverMock,
                'config' => $this->configMock,
                'searchQueryFactory' => $this->searchQueryFactoryMock
            ]
        );
    }

    public function testPrepareLayout()
    {
        $this->applierMock->expects($this->once())
            ->method('applyFilters')
            ->with($this->layerMock);

        $class = new \ReflectionClass($this->block);
        $method = $class->getMethod('_prepareLayout');
        $method->setAccessible(true);

        $this->assertEquals($this->block, $method->invoke($this->block));
    }

    public function testToHtmlWhenFilterDisable()
    {
        $filterMock = $this->getMockForAbstractClass(AbstractFilter::class, [], '', false, false, true);
        $this->filterListMock->expects($this->once())
            ->method('getFilters')
            ->with($this->equalTo($this->layerMock))
            ->willReturn([$filterMock]);
        $this->visibilityFlagMock->expects($this->once())
            ->method('isEnabled')
            ->with(
                $this->equalTo($this->layerMock),
                $this->equalTo([$filterMock])
            )
            ->willReturn(false);
        $this->assertEquals('', $this->block->toHtml());
    }

    public function testGetFilters()
    {
        $filterMock = $this->getMockForAbstractClass(AbstractFilter::class, [], '', false, false, true);
        $this->filterListMock->expects($this->once())
            ->method('getFilters')
            ->with($this->equalTo($this->layerMock))
            ->willReturn([$filterMock]);
        $this->assertEquals([$filterMock], $this->block->getFilters());
    }

    public function testGetItemsCountUrl()
    {
        $itemsCountUrl = 'http://localhost/awlayerednav/ajax/itemsCount';
        $isCurrentlySecure = false;

        $storeMock = $this->getMock(Store::class, ['isCurrentlySecure'], [], '', false);

        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($storeMock);
        $storeMock->expects($this->once())
            ->method('isCurrentlySecure')
            ->willReturn($isCurrentlySecure);
        $this->urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->with(
                $this->equalTo('awlayerednav/ajax/itemsCount'),
                $this->equalTo(['_secure' => $isCurrentlySecure])
            )
            ->willReturn($itemsCountUrl);

        $this->assertEquals($itemsCountUrl, $this->block->getItemsCountUrl());
    }

    public function testGetCategoryId()
    {
        $categoryId = 1;
        $categoryMock = $this->getMock(Category::class, ['getId'], [], '', false);
        $this->layerMock->expects($this->once())
            ->method('getCurrentCategory')
            ->willReturn($categoryMock);
        $categoryMock->expects($this->once())
            ->method('getId')
            ->willReturn($categoryId);
        $this->assertEquals($categoryId, $this->block->getCategoryId());
    }

    /**
     * @param AbstractFilter[]|\PHPUnit_Framework_MockObject_MockObject[] $filters
     * @param bool $hasActiveFilters
     * @dataProvider hasActiveFiltersDataProvider
     */
    public function testHasActiveFilters($filters, $hasActiveFilters)
    {
        $stateMock = $this->getMock(State::class, ['getFilters'], [], '', false);
        $this->layerMock->expects($this->once())
            ->method('getState')
            ->willReturn($stateMock);
        $stateMock->expects($this->once())
            ->method('getFilters')
            ->willReturn($filters);
        $this->assertSame($hasActiveFilters, $this->block->hasActiveFilters());
    }

    public function testGetPageType()
    {
        $pageType = 'category';
        $this->pageTypeResolverMock->expects($this->once())
            ->method('getType')
            ->willReturn($pageType);
        $this->assertEquals($pageType, $this->block->getPageType());
    }

    /**
     * @param string $pageType
     * @param string|null $queryText
     * @param string $result
     * @dataProvider getSearchQueryTextDataProvider
     */
    public function testGetSearchQueryText($pageType, $queryText, $result)
    {
        $this->pageTypeResolverMock->expects($this->once())
            ->method('getType')
            ->willReturn($pageType);
        if ($queryText) {
            $queryMock = $this->getMockForAbstractClass(QueryInterface::class);
            $this->searchQueryFactoryMock->expects($this->once())
                ->method('get')
                ->willReturn($queryMock);
            $queryMock->expects($this->once())
                ->method('getQueryText')
                ->willReturn($queryText);
        }
        $this->assertEquals($result, $this->block->getSearchQueryText());
    }

    /**
     * @param bool $value
     * @dataProvider boolDataProvider
     */
    public function testIsAjaxEnabled($value)
    {
        $this->configMock->expects($this->once())
            ->method('isAjaxEnabled')
            ->willReturn($value);
        $this->assertSame($value, $this->block->isAjaxEnabled());
    }

    /**
     * @param bool $value
     * @dataProvider boolDataProvider
     */
    public function testIsPopoverDisabled($value)
    {
        $this->configMock->expects($this->once())
            ->method('isPopoverDisabled')
            ->willReturn($value);
        $this->assertSame($value, $this->block->isPopoverDisabled());
    }

    /**
     * @return array
     */
    public function hasActiveFiltersDataProvider()
    {
        return [
            'has active filters' => [
                [
                    $this->getMockForAbstractClass(AbstractFilter::class, [], '', false, false, true)
                ],
                true
            ],
            'has no active filters' => [[], false]
        ];
    }

    /**
     * @return array
     */
    public function getSearchQueryTextDataProvider()
    {
        return [
            [PageTypeResolver::PAGE_TYPE_CATEGORY, null, ''],
            [PageTypeResolver::PAGE_TYPE_CATALOG_SEARCH, 'query text', 'query text']
        ];
    }

    /**
     * @return array
     */
    public function isAjaxEnabledDataProvider()
    {
        return [[true], [false]];
    }

    /**
     * @return array
     */
    public function boolDataProvider()
    {
        return [[true], [false]];
    }
}
