<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CfpScheduleType extends Model {

	public $table = 'cfp_schedule_types';
	public $timestamps = false;

	//protected $primaryKey = 'internalid';
	protected $fillable = ['id', 'type_name'];
}