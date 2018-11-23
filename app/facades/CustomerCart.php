<?php namespace Kokiweb;

use Illuminate\Support\Facades\Facade;

class PlaygroundCart extends Facade {

    protected static function getFacadeAccessor()
    {
        return 'playgroundcart';
    }
}