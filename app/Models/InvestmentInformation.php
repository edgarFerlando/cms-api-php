<?php namespace App\Models;

use App\BaseModel;

class InvestmentInformation extends BaseModel{
	public $table = 'investment_informations';
    protected $fillable = [ 
        'product_name',
        'description',
        'nab',
        'scoring_3_thn',
        'inv_1_hr',
        'inv_1_bln',
        'inv_1_thn',
        'inv_3_thn',
        'since_launched',
        'fluctuation',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at',
        'record_flag'
     ];

    public function providers() {
        return $this->hasMany('App\Models\InvestmentInformationProvider', 'investment_information_id', 'id');
    }
}
