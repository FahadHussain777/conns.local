<?php

namespace Conns\PowerStore\Model;


class Locator extends \BrainActs\StoreLocator\Model\Locator
{
    private $connection;

    public function __construct(
        \Magento\Framework\App\ResourceConnection $connection,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->connection = $connection;
        parent::__construct($context, $registry, $resource,$resourceCollection, $data);
    }

    public function checkRegionIdentifier($identifier, $region_id, $store, $isActive = null)
    {
        $select = $this->connection->getConnection()->select()
            ->from('brainacts_storelocator')
            ->where('identifier = ?', $identifier)
            ->where('region_assigned = ?', $region_id);

        if ($isActive !== null) {
            $select->where('sp.is_active = ?', $isActive);
        }
        $select->limit(1);
        return $this->connection->getConnection()->fetchOne($select);
    }
}