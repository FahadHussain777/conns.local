<?php


namespace Conns\Checkout\Block\Cart;


class Renderer extends \Magento\Checkout\Block\Cart\Item\Renderer
{
    public function getAvailability(){
        return "Conn's (White Glove)+ installation";
    }

}