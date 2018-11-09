<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Layerednav\Test\Unit\Model;

use Aheadworks\Layerednav\Model\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\ScopeInterface;

/**
 * Test for \Aheadworks\Layerednav\Model\Config
 */
class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfigMock;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->config = $objectManager->getObject(
            Config::class,
            ['scopeConfig' => $this->scopeConfigMock]
        );
    }

    /**
     * @param bool $value
     * @dataProvider boolDataProvider
     */
    public function testIsNewFilterEnabled($value)
    {
        $this->scopeConfigMock->expects($this->any())
            ->method('isSetFlag')
            ->with(Config::XML_PATH_NEW_FILTER_ENABLED, ScopeInterface::SCOPE_STORE)
            ->willReturn($value);
        $this->assertSame($value, $this->config->isNewFilterEnabled());
    }

    /**
     * @param bool $value
     * @dataProvider boolDataProvider
     */
    public function testIsOnSaleFilterEnabled($value)
    {
        $this->scopeConfigMock->expects($this->any())
            ->method('isSetFlag')
            ->with(Config::XML_PATH_ON_SALE_FILTER_ENABLED, ScopeInterface::SCOPE_STORE)
            ->willReturn($value);
        $this->assertSame($value, $this->config->isOnSaleFilterEnabled());
    }

    /**
     * @param bool $value
     * @dataProvider boolDataProvider
     */
    public function testIsInStockFilterEnabled($value)
    {
        $this->scopeConfigMock->expects($this->any())
            ->method('isSetFlag')
            ->with(Config::XML_PATH_STOCK_FILTER_ENABLED, ScopeInterface::SCOPE_STORE)
            ->willReturn($value);
        $this->assertSame($value, $this->config->isInStockFilterEnabled());
    }

    /**
     * @param bool $value
     * @dataProvider boolDataProvider
     */
    public function testIsAjaxEnabled($value)
    {
        $this->scopeConfigMock->expects($this->any())
            ->method('isSetFlag')
            ->with(Config::XML_PATH_AJAX_ENABLED)
            ->willReturn($value);
        $this->assertSame($value, $this->config->isAjaxEnabled());
    }

    /**
     * @param bool $value
     * @dataProvider boolDataProvider
     */
    public function testIsPopoverDisabled($value)
    {
        $this->scopeConfigMock->expects($this->any())
            ->method('isSetFlag')
            ->with(Config::XML_PATH_POPOVER_DISABLED)
            ->willReturn($value);
        $this->assertSame($value, $this->config->isPopoverDisabled());
    }

    /**
     * @return array
     */
    public function boolDataProvider()
    {
        return [[true], [false]];
    }
}
