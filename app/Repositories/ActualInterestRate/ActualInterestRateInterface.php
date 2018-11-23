<?php namespace App\Repositories\ActualInterestRate;

use App\Repositories\RepositoryInterface;

interface ActualInterestRateInterface extends RepositoryInterface {

    /**
     * @param $slug
     * @return mixed
     */
    public function getBySlug($slug);
}