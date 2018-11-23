<?php namespace Kokiweb;

use Illuminate\Support\Facades\Facade;

class MerchantCart extends Facade {

    protected static function getFacadeAccessor()
    {
        return 'merchantcart';
    }
}