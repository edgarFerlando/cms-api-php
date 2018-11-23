<?php namespace App\Repositories\Article;

use App\Repositories\RepositoryInterface;

interface ArticleInterface extends RepositoryInterface {

    /**
     * @param $slug
     * @return mixed
     */
    public function getBySlug($slug);
}