<?php namespace App\Interfaces;

interface ModelInterface {

    /**
     * @param $value
     * @return mixed
     */
    public function setUrlAttribute($value);

    /**
     * @return mixed
     */
    public function getUrlAttribute();
}