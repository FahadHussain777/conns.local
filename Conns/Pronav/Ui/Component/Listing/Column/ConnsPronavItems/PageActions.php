<?php
/*
 * Copyright ©2016 Conn's. All rights reserved.
 */

namespace Conns\Pronav\Ui\Component\Listing\Column\ConnsPronavItems;

/**
 * Class PageActions
 * @package Conns\Pronav\Ui\Component\Listing\Column\ConnsPronavItems
 */
class PageActions extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $name = $this->getData('name');
                $id = 'X';
                if(isset($item['pronav_id']))
                {
                    $id = $item['pronav_id'];
                }
                $item[$name]['view'] = [
                    'href'=>$this->getContext()->getUrl(
                        'pronav/navigation/edit',['pronav_id'=>$id]),
                    'label'=>__('Edit')
                ];
            }
        }

        return $dataSource;
    }    
    
}
