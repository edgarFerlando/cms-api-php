<?php namespace App\Repositories\EmailTemplate;

use App\Repositories\RepositoryInterface;

interface EmailTemplateInterface extends RepositoryInterface {

    /**
     * @param $slug
     * @return mixed
     */
    public function getBySlug($slug);
}