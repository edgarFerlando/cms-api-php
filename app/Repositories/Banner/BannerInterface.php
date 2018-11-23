<?php namespace App\Repositories\Banner;

use App\Repositories\RepositoryInterface;

interface BannerInterface extends RepositoryInterface {

    /**
     * @param $slug
     * @return mixed
     */
    public function getBySlug($slug);
}