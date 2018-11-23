<?php namespace App\Repositories\Page;

use App\Repositories\RepositoryInterface;

interface PageInterface extends RepositoryInterface {

    /**
     * @param $slug
     * @return mixed
     */
    public function getBySlug($slug, $isPublished = false);

    /**
     * @param $slug
     * @return mixed
     */
    public function getSlugsByID($id);
}