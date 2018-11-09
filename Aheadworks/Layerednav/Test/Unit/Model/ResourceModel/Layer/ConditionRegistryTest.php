<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Layerednav\Test\Unit\Model\ResourceModel\Layer;

use Aheadworks\Layerednav\Model\ResourceModel\Layer\ConditionRegistry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Layerednav\Model\ResourceModel\Layer\ConditionRegistry
 */
class ConditionRegistryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConditionRegistry
     */
    private $conditionRegistry;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->conditionRegistry = $objectManager->getObject(ConditionRegistry::class);
    }

    public function testAddConditions()
    {
        $attribute = 'attribute_code';
        $condition = ['value = 1'];
        $this->conditionRegistry->addConditions($attribute, $condition);
        $this->assertEquals([$attribute => $condition], $this->conditionRegistry->getConditions());
    }

    public function testReset()
    {
        $attribute = 'attribute_code';
        $condition = ['value = 1'];
        $this->conditionRegistry->addConditions($attribute, $condition);
        $this->conditionRegistry->reset();
        $this->assertEmpty($this->conditionRegistry->getConditions());
    }
}
