<?php namespace App\Repositories\Product;

use App\Repositories\RepositoryInterface;

interface ProductInterface extends RepositoryInterface {

    /**
     * @param $slug
     * @return mixed
     */
    public function getBySlug($slug);
}