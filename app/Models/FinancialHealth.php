<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinancialHealth extends Model {

	public $table = 'financial_healths';
	public $timestamps = false;

	protected $fillable = [
		'user_id', 
		'mail_to',
		'is_email_sent',
		'report',
		'result',
		'record_flag',
		'created_by',
		'created_at',
		'updated_by',
		'updated_at'
	];

	public function metas() {
        return $this->hasMany('App\Models\FinancialHealthMeta', 'financial_health_id', 'id');
    }
}
