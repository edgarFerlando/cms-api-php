<?php namespace App\Models;

use App\BaseModel;

class ActualInterestRate extends BaseModel {

    public $table = 'actual_interest_rates';
    protected $fillable = [ 
        'interest_rate_id',
        'period',
        'rate', 
        'created_by',
        'created_at', 
        'updated_by', 
        'updated_at',
        'record_flag',
        'deleted_by', 
        'deleted_at'
     ];

    /*public function product() {
        return $this->hasOne('App\Taxonomy', 'id','taxo_wallet_asset_id');
    }*/
    public function interest_rate() {
        return $this->hasOne('App\Models\InterestRate', 'id', 'interest_rate_id');
    }

}