<?php namespace App\Models;

use App\BaseModel;

class InvestmentInformationClient extends BaseModel{
	public $table = 'investment_information_client';
    protected $fillable = [ 
        'client_id',
        'investment_information_id',
        'investment_information_provider_id',
        'is_email_sent',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at'
     ];

}
