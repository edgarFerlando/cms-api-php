<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cycle extends Model {

	public $table = 'cycles';
	public $timestamps = false;
	protected $fillable = [
		'client_id',
		'cashflow_analysis_version_approved',
		'portfolio_analysis_version_approved',
		'plan_analysis_version_approved',
		'started_at',
		'completed_at'
	];
}
