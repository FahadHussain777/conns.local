<?php
/*
 * Copyright ©2016 Conn's. All rights reserved.
 */

namespace Conns\Pronav\Api;

use Conns\Pronav\Api\Data\NavigationInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Interface NavigationRepositoryInterface
 * @package Conns\Pronav\Api
 */
interface NavigationRepositoryInterface
{
    /**
     * @param NavigationInterface $page
     * @return mixed
     */
    public function save(NavigationInterface $page);

    /**
     * @param $id
     * @return mixed
     */
    public function getById($id);

    /**
     * @param SearchCriteriaInterface $criteria
     * @return mixed
     */
    public function getList(SearchCriteriaInterface $criteria);

    /**
     * @param NavigationInterface $page
     * @return mixed
     */
    public function delete(NavigationInterface $page);

    /**
     * @param $id
     * @return mixed
     */
    public function deleteById($id);
}
