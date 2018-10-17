<?php
/*
 * Copyright ©2016 Conn's. All rights reserved.
 */

namespace Conns\Pronav\Ui\Component\Listing\DataProviders\Conns\Pronav;

use Conns\Pronav\Model\ResourceModel\Navigation\CollectionFactory;

/**
 * Class Items
 * @package Conns\Pronav\Ui\Component\Listing\DataProviders\Conns\Pronav
 */
class Items extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * Items constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
    }
}
