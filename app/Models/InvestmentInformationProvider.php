<?php namespace App\Models;

use App\BaseModel;

class InvestmentInformationProvider extends BaseModel{
    public $table = 'investment_information_providers';
    public $timestamps = false;
    protected $fillable = [ 
        'investment_information_id',
        'provider_name',
        'pic_name',
        'pic_email'
     ];

    public function investment_information() {
        return $this->hasOne('App\Models\InvestmentInformation', 'id', 'investment_information_id');
    }
}
