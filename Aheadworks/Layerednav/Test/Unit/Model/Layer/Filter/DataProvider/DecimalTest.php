<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Layerednav\Test\Unit\Model\Layer\Filter\DataProvider;

use Aheadworks\Layerednav\Model\Layer\Filter\DataProvider\Decimal as DecimalDataProvider;
use Aheadworks\Layerednav\Model\ResourceModel\Layer\Filter\Decimal as ResourceDecimal;
use Magento\Catalog\Model\Layer\Filter\AbstractFilter;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Layerednav\Model\Layer\Filter\DataProvider\Decimal
 */
class DecimalTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DecimalDataProvider
     */
    private $dataProvider;

    /**
     * @var ResourceDecimal|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceMock;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->resourceMock = $this->getMock(
            ResourceDecimal::class,
            ['getMaxValue', 'getCount', 'getParentCount'],
            [],
            '',
            false
        );
        $this->dataProvider = $objectManager->getObject(
            DecimalDataProvider::class,
            ['resource' => $this->resourceMock]
        );
    }

    public function testGetResource()
    {
        $this->assertEquals($this->resourceMock, $this->dataProvider->getResource());
    }

    /**
     * @param array $filter
     * @param array $intervals
     * @dataProvider getIntervalsDataProvider
     */
    public function testGetIntervals($filter, $intervals)
    {
        $this->assertEquals($intervals, $this->dataProvider->getIntervals($filter));
    }

    /**
     * @param string $interval
     * @param bool $result
     * @dataProvider isIntervalValidDataProvider
     */
    public function testIsIntervalValid($interval, $result)
    {
        $class = new \ReflectionClass($this->dataProvider);
        $method = $class->getMethod('isIntervalValid');
        $method->setAccessible(true);

        $this->assertSame($result, $method->invokeArgs($this->dataProvider, [$interval]));
    }

    public function testGetRangeItemCounts()
    {
        $maxValue = 10.50;
        $count = [1 => '1', 2 => '2'];
        $range = 10;

        /** @var AbstractFilter|\PHPUnit_Framework_MockObject_MockObject $filterMock */
        $filterMock = $this->getMockForAbstractClass(AbstractFilter::class, [], '', false, false, true);

        $this->resourceMock->expects($this->once())
            ->method('getMaxValue')
            ->willReturn($maxValue);
        $this->resourceMock->expects($this->once())
            ->method('getCount')
            ->willReturn($count);
        $this->resourceMock->expects($this->once())
            ->method('getParentCount')
            ->willReturn($count);

        $this->assertEquals($range, $this->dataProvider->getRange($filterMock));
    }

    /**
     * @return array
     */
    public function getIntervalsDataProvider()
    {
        return [
            'correct' => [['1.00-2.00'], [['1.00', '2.00']]],
            'incorrect' => [['1.002.00'], []]
        ];
    }

    /**
     * @return array
     */
    public function isIntervalValidDataProvider()
    {
        return [['1.0-2.0', true], ['1.0', false]];
    }
}
