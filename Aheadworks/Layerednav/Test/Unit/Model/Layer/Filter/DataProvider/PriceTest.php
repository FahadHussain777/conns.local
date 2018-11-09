<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Layerednav\Test\Unit\Model\Layer\Filter\DataProvider;

use Aheadworks\Layerednav\Model\Layer\Filter\DataProvider\Price as PriceDataProvider;
use Aheadworks\Layerednav\Model\ResourceModel\Layer\Filter\Price as ResourcePrice;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Layerednav\Model\Layer\Filter\DataProvider\Price
 */
class PriceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PriceDataProvider
     */
    private $dataProvider;

    /**
     * @var ResourcePrice|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceMock;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->resourceMock = $this->getMock(ResourcePrice::class, [], [], '', false);
        $this->dataProvider = $objectManager->getObject(
            PriceDataProvider::class,
            ['resource' => $this->resourceMock]
        );
    }

    public function testGetResource()
    {
        $this->assertEquals($this->resourceMock, $this->dataProvider->getResource());
    }

    public function testSetInterval()
    {
        $interval = [['10.00', '20.00']];
        $this->dataProvider->setInterval($interval);
        $this->assertEquals($interval, $this->dataProvider->getInterval());
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
