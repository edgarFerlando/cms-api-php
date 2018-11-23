<?php namespace App\Models;

use App\BaseModel;

class InterestRate extends BaseModel {

    public $table = 'interest_rates';
    protected $fillable = [ 
        'taxo_wallet_asset_id',
        'rate', 
        'bgcolor',
        'bgcolor2',
        'created_by',
        'created_at', 
        'updated_by', 
        'updated_at',
        'record_flag',
        'deleted_by', 
        'deleted_at'
     ];

     public function product() {
        return $this->hasOne('App\Taxonomy', 'id','taxo_wallet_asset_id');
    }
}