<?php namespace App\Repositories\Permission;

use App\Repositories\RepositoryInterface;

interface PermissionInterface extends RepositoryInterface {

    /**
     * @param $slug
     * @return mixed
     */
    public function getBySlug($slug);
}