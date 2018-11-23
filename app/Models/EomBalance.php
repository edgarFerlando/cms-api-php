<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EomBalance extends Model {

	public $table = 'eom_balances';
	public $timestamps = false;
	protected $fillable = [
		'input_type', 
		'client_id',
		'period',
		'balance',
		'notes',
		'created_by',
		'created_at',
		'updated_by',
		'updated_at',
		'record_flag',
		'deleted_by',
		'deleted_at'
	];
}
