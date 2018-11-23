<?php namespace App\Repositories\Affiliate;

use App\Repositories\RepositoryInterface;

interface AffiliateInterface extends RepositoryInterface {

    /**
     * @param $slug
     * @return mixed
     */
    public function getBySlug($slug);
}