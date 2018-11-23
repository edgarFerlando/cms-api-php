<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinancialHealthMeta extends Model {

	public $table = 'financial_health_metas';
	public $timestamps = false;

	protected $fillable = [
		'financial_health_id', 
		'meta_key', 
		'meta_value'
	];
}
