<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Layerednav\Test\Unit\Model\ResourceModel\Layer\Filter;

use Aheadworks\Layerednav\Model\ResourceModel\Layer\Filter\Price as ResourcePrice;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Layerednav\Model\ResourceModel\Layer\Filter\Price
 */
class PriceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ResourcePrice
     */
    private $resourcePrice;

    /**
     * @var ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceConnectionMock;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->resourceConnectionMock = $this->getMock(
            ResourceConnection::class,
            ['getConnection'],
            [],
            '',
            false
        );
        $context = $objectManager->getObject(
            Context::class,
            ['resources' => $this->resourceConnectionMock]
        );

        $this->resourcePrice = $objectManager->getObject(ResourcePrice::class, ['context' => $context]);
    }

    /**
     * @param string $conditionString
     * @param string $result
     * @dataProvider replaceTableAliasDataProvider
     */
    public function testReplaceTableAlias($conditionString, $result)
    {
        $connectionMock = $this->getMockForAbstractClass(AdapterInterface::class);
        $this->resourceConnectionMock->expects($this->once())
            ->method('getConnection')
            ->with($this->equalTo(ResourceConnection::DEFAULT_CONNECTION))
            ->willReturn($connectionMock);
        $connectionMock->expects($this->exactly(3))
            ->method('quoteIdentifier')
            ->willReturnCallback(
                function ($ident) {
                    return '`' . $ident . '`';
                }
            );

        $class = new \ReflectionClass($this->resourcePrice);
        $method = $class->getMethod('replaceTableAlias');
        $method->setAccessible(true);

        $this->assertEquals($result, $method->invokeArgs($this->resourcePrice, [$conditionString]));
    }

    /**
     * @return array
     */
    public function replaceTableAliasDataProvider()
    {
        return [
            [
                'e.entity_id = stock_status_index.product_id',
                'product_entity.entity_id = stock_status_index.product_id'
            ],
            [
                '`e`.entity_id = stock_status_index.product_id',
                '`product_entity`.entity_id = stock_status_index.product_id'
            ],
            ['cat_index.product_id = e.entity_id', 'cat_index.product_id = product_entity.entity_id'],
            ['cat_index.product_id = `e`.entity_id', 'cat_index.product_id = `product_entity`.entity_id'],
            ['special_price.row_id = e.entity_id', 'special_price.row_id = product_entity.entity_id'],
            ['`special_price`.row_id = e.entity_id', '`special_price`.row_id = product_entity.entity_id'],
            ['price_index.website_id = 1', 'e.website_id = 1'],
            ['`price_index`.website_id = 1', '`e`.website_id = 1'],
            [
                'price_index.website_id = 1 AND cat_index.product_id = e.entity_id',
                'e.website_id = 1 AND cat_index.product_id = product_entity.entity_id'
            ],
        ];
    }
}
