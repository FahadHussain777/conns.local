<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Layerednav\Test\Unit\Model\Layer\Filter\Dynamic;

use Aheadworks\Layerednav\Model\Layer\Filter\Dynamic\Manual;
use Aheadworks\Layerednav\Model\ResourceModel\Layer\Filter\Price as ResourcePrice;
use Magento\Catalog\Model\Layer\Filter\Price\Range;
use Magento\Catalog\Model\Layer\Filter\Price\Render;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\ScopeInterface;

/**
 * Test for \Aheadworks\Layerednav\Model\Layer\Filter\Dynamic\Manual
 */
class ManualTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Manual
     */
    private $algorithm;

    /**
     * @var Render|\PHPUnit_Framework_MockObject_MockObject
     */
    private $renderMock;

    /**
     * @var Range|\PHPUnit_Framework_MockObject_MockObject
     */
    private $rangeMock;

    /**
     * @var ResourcePrice|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceMock;

    /**
     * @var ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfigMock;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->renderMock = $this->getMock(Render::class, ['renderRangeData'], [], '', false);
        $this->rangeMock = $this->getMock(Range::class, ['getPriceRange', 'getConfigRangeStep'], [], '', false);
        $this->resourceMock = $this->getMock(ResourcePrice::class, ['getCount', 'getParentCount'], [], '', false);
        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);

        $this->algorithm = $objectManager->getObject(
            Manual::class,
            [
                'render' => $this->renderMock,
                'range' => $this->rangeMock,
                'resource' => $this->resourceMock,
                'scopeConfig' => $this->scopeConfigMock
            ]
        );
    }

    public function testGetItemsData()
    {
        $configRangeStep = 10;
        $count = [1 => '1', 2 => '2'];
        $range = 10;
        $itemsData = [
            [
                'label' => 'Item Label',
                'value' => 'value',
                'count' => 'count'
            ]
        ];

        $this->rangeMock->expects($this->once())
            ->method('getConfigRangeStep')
            ->willReturn($configRangeStep);
        $this->resourceMock->expects($this->once())
            ->method('getCount')
            ->willReturn($count);
        $this->resourceMock->expects($this->once())
            ->method('getParentCount')
            ->willReturn($count);
        $this->renderMock->expects($this->once())
            ->method('renderRangeData')
            ->with($range, $count)
            ->willReturn($itemsData);
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(
                $this->equalTo(Manual::XML_PATH_RANGE_MAX_INTERVALS),
                $this->equalTo(ScopeInterface::SCOPE_STORE)
            )
            ->willReturn(5);

        $this->assertEquals($itemsData, $this->algorithm->getItemsData());
    }
}
