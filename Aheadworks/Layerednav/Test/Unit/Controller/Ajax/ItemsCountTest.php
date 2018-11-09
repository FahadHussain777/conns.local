<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Layerednav\Test\Unit\Controller\Ajax;

use Aheadworks\Layerednav\Controller\Ajax\ItemsCount;
use Aheadworks\Layerednav\Model\Applier;
use Aheadworks\Layerednav\Model\Layer\FilterList;
use Aheadworks\Layerednav\Model\Layer\FilterListResolver;
use Aheadworks\Layerednav\Model\PageTypeResolver;
use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Filter\AbstractFilter;
use Magento\Catalog\Model\Layer\Filter\Item as FilterItem;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Catalog\Model\Layer\State;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Search\Model\QueryFactory;

/**
 * Test for \Aheadworks\Layerednav\Controller\Ajax\ItemsCount
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ItemsCountTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ItemsCount
     */
    private $action;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Resolver|\PHPUnit_Framework_MockObject_MockObject
     */
    private $layerResolverMock;

    /**
     * @var FilterListResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filterListResolverMock;

    /**
     * @var Applier|\PHPUnit_Framework_MockObject_MockObject
     */
    private $applierMock;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $contextMock;

    /**
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var ResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultFactoryMock;

    public function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->layerResolverMock = $this->getMock(Resolver::class, ['create', 'get'], [], '', false);
        $this->filterListResolverMock = $this->getMock(
            FilterListResolver::class,
            ['create', 'get'],
            [],
            '',
            false
        );
        $this->applierMock = $this->getMock(Applier::class, ['applyFilters'], [], '', false);
        $this->requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $this->resultFactoryMock = $this->getMock(ResultFactory::class, ['create'], [], '', false);
        $this->contextMock = $this->getMock(
            Context::class,
            ['getRequest', 'getRedirect', 'getResultFactory'],
            [],
            '',
            false
        );
        $this->contextMock->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->requestMock);
        $this->contextMock->expects($this->any())
            ->method('getResultFactory')
            ->willReturn($this->resultFactoryMock);

        $this->action = $this->objectManager->getObject(
            ItemsCount::class,
            [
                'context' => $this->contextMock,
                'layerResolver' => $this->layerResolverMock,
                'filterListResolver' => $this->filterListResolverMock,
                'applier' => $this->applierMock
            ]
        );
    }

    /**
     * @param array $requestParams
     * @dataProvider executeDataProvider
     */
    public function testExecute($requestParams)
    {
        $itemsCount = 10;

        $resultJsonMock = $this->getMock(Json::class, ['setData'], [], '', false);
        $layerMock = $this->getMock(
            Layer::class,
            ['getProductCollection', 'setCurrentCategory'],
            [],
            '',
            false
        );
        $productCollectionMock = $this->getMock(Collection::class, ['getSize'], [], '', false);

        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->with(ResultFactory::TYPE_JSON)
            ->willReturn($resultJsonMock);
        $this->requestMock->expects($this->once())
            ->method('getParams')
            ->willReturn($requestParams);
        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->willReturnMap(
                [
                    ['pageType', null, $requestParams['pageType']],
                    ['categoryId', null, $requestParams['categoryId']],
                    ['searchQueryText', null, $requestParams['searchQueryText']],
                    ['sequence', null, $requestParams['sequence']]
                ]
            );
        $this->requestMock->expects($this->once())
            ->method('setParams')
            ->with(
                $this->callback(
                    function ($params) use ($requestParams) {
                        $filterValueKey = $requestParams['filterValue'][0]['key'];
                        $filterValue = $requestParams['filterValue'][0]['value'];

                        $isValid = array_key_exists($filterValueKey, $params)
                            && $params[$filterValueKey] == $filterValue;
                        if ($requestParams['pageType'] == 'catalog_search') {
                            $isValid = $isValid && array_key_exists(QueryFactory::QUERY_VAR_NAME, $params)
                                && $params[QueryFactory::QUERY_VAR_NAME] == $requestParams['searchQueryText'];
                        }

                        return $isValid;
                    }
                )
            );
        $this->filterListResolverMock->expects($this->once())
            ->method('create')
            ->with($this->equalTo($requestParams['pageType']));
        $this->layerResolverMock->expects($this->once())
            ->method('get')
            ->willReturn($layerMock);
        $this->applierMock->expects($this->once())
            ->method('applyFilters')
            ->with($this->equalTo($layerMock));
        $layerMock->expects($this->once())
            ->method('getProductCollection')
            ->willReturn($productCollectionMock);
        $productCollectionMock->expects($this->once())
            ->method('getSize')
            ->willReturn($itemsCount);
        $resultJsonMock->expects($this->once())
            ->method('setData')
            ->with(
                $this->equalTo(
                    [
                        'success' => true,
                        'sequence' => $requestParams['sequence'],
                        'itemsCount' => $itemsCount,
                        'itemsContent' => $itemsCount . ' items'
                    ]
                )
            )
            ->willReturnSelf();

        $this->assertEquals($resultJsonMock, $this->action->execute());
    }

    public function testPrepareFilterValue()
    {
        $filterValue = [
            ['key' => 'filter_request_var1', 'value' => 'filter_value1'],
            ['key' => 'filter_request_var1', 'value' => 'filter_value2'],
            ['key' => 'filter_request_var2', 'value' => 'filter_value3']
        ];
        $preparedFilterValue = [
            'filter_request_var1' => 'filter_value1,filter_value2',
            'filter_request_var2' => 'filter_value3'
        ];

        $class = new \ReflectionClass($this->action);
        $method = $class->getMethod('prepareFilterValue');
        $method->setAccessible(true);

        $this->assertEquals($preparedFilterValue, $method->invokeArgs($this->action, [$filterValue]));
    }

    /**
     * @param string $pageType
     * @param string $layerType
     * @dataProvider getLayerDataProvider
     */
    public function testGetLayer($pageType, $layerType)
    {
        $categoryId = 1;
        $layerMock = $this->getMock(Layer::class, ['setCurrentCategory'], [], '', false);

        $this->requestMock->expects($this->exactly(2))
            ->method('getParam')
            ->willReturnMap(
                [
                    ['pageType', null, $pageType],
                    ['categoryId', null, $categoryId]
                ]
            );
        $this->layerResolverMock->expects($this->once())
            ->method('create')
            ->with($this->equalTo($layerType));
        $this->layerResolverMock->expects($this->once())
            ->method('get')
            ->willReturn($layerMock);
        $layerMock->expects($this->once())
            ->method('setCurrentCategory')
            ->with($this->equalTo($categoryId));

        $class = new \ReflectionClass($this->action);
        $method = $class->getMethod('getLayer');
        $method->setAccessible(true);

        $this->assertEquals($layerMock, $method->invoke($this->action));
    }

    /**
     * @return array
     */
    public function executeDataProvider()
    {
        return [
            'category page' => [
                [
                    'isAjax' => true,
                    'filterValue' => [['key' => 'filter_request_var', 'value' => 'filter_value']],
                    'pageType' => PageTypeResolver::PAGE_TYPE_CATEGORY,
                    'categoryId' => 1,
                    'searchQueryText' => '',
                    'sequence' => 1
                ]
            ],
            'search page' => [
                [
                    'isAjax' => true,
                    'filterValue' => [['key' => 'filter_request_var', 'value' => 'filter_value']],
                    'pageType' => PageTypeResolver::PAGE_TYPE_CATALOG_SEARCH,
                    'categoryId' => 1,
                    'searchQueryText' => 'search text',
                    'sequence' => 1
                ]
            ]
        ];
    }

    /**
     * @return array
     */
    public function getLayerDataProvider()
    {
        return [
            'category layer' => [
                PageTypeResolver::PAGE_TYPE_CATEGORY,
                Resolver::CATALOG_LAYER_CATEGORY
            ],
            'catalog search layer' => [
                PageTypeResolver::PAGE_TYPE_CATALOG_SEARCH,
                Resolver::CATALOG_LAYER_SEARCH
            ]
        ];
    }
}
