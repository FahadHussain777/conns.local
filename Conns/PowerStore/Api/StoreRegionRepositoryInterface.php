<?php
namespace Conns\PowerStore\Api;

use Conns\PowerStore\Api\Data\StoreRegionInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

interface StoreRegionRepositoryInterface
{
    public function save(StoreRegionInterface $page);

    public function getById($id);

    public function getList(SearchCriteriaInterface $criteria);

    public function delete(StoreRegionInterface $page);

    public function deleteById($id);
}
