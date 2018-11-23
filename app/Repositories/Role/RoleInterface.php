<?php namespace App\Repositories\Role;

use App\Repositories\RepositoryInterface;

interface RoleInterface extends RepositoryInterface {

    /**
     * @param $slug
     * @return mixed
     */
    public function getBySlug($slug);
}