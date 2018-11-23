<?php namespace App\Repositories\InterestRate;

use App\Repositories\RepositoryInterface;

interface InterestRateInterface extends RepositoryInterface {

    /**
     * @param $slug
     * @return mixed
     */
    public function getBySlug($slug);
}