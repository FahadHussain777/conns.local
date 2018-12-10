<?php
namespace Conns\PowerStore\Api;

use Conns\PowerStore\Api\Data\StoreHoursInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

interface StoreHoursRepositoryInterface 
{
    public function save(StoreHoursInterface $page);

    public function getById($id);

    public function getList(SearchCriteriaInterface $criteria);

    public function delete(StoreHoursInterface $page);

    public function deleteById($id);
}
