<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanBalance extends Model {

	public $table = 'plan_balances';
	public $timestamps = false;
    //protected $primaryKey = 'user_id';

	protected $fillable = [
        'user_id', 
        'name', 
        'balance', 
        'balance_datetime', 
        'version',
        'created_by',
        'created_at', 
        'updated_by', 
        'updated_at'
    ];
}
