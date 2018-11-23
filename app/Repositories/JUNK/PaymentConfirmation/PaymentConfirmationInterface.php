<?php namespace App\Repositories\PaymentConfirmation;

use App\Repositories\RepositoryInterface;

interface PaymentConfirmationInterface extends RepositoryInterface {

    /**
     * @param $slug
     * @return mixed
     */
    public function getBySlug($slug);
}