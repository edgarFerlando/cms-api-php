<?php namespace Kokiweb;

use Illuminate\Support\Facades\Facade;

class TripCart extends Facade {

    protected static function getFacadeAccessor()
    {
        return 'tripcart';
    }
}