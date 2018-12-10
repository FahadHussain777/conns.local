<?php

namespace Conns\PowerStore\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    public function getArrayOfDays(){
        return [
            0 => 'Monday',
            1 => 'Tuesday',
            2 => 'Wednesday',
            3 => 'Thirsday',
            4 => 'Friday',
            5 => 'Saturday',
            6 => 'Sunday',
        ];
    }

}