<?php namespace App\Repositories\Referral;

use App\Repositories\RepositoryInterface;

interface ReferralInterface extends RepositoryInterface {

    /**
     * @param $slug
     * @return mixed
     */
    public function getBySlug($slug);
}