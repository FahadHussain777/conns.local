<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\Layerednav\Model\ResourceModel\Layer;

/**
 * Class ConditionRegistry
 * @package Aheadworks\Layerednav\Model\ResourceModel\Layer
 */
class ConditionRegistry
{
    /**
     * Conditions
     *
     * @var string[]
     */
    private $conditions = [];

    /**
     * Add conditions
     *
     * @param string $attribute
     * @param array $condition
     * @return void
     */
    public function addConditions($attribute, $condition)
    {
        $this->conditions[$attribute] = $condition;
    }

    /**
     * Get conditions
     *
     * @return string[]
     */
    public function getConditions()
    {
        return $this->conditions;
    }

    /**
     * Reset registry
     *
     * @return void
     */
    public function reset()
    {
        $this->conditions = [];
    }
}
