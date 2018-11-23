<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CfpSchedule extends Model {

	public $table = 'cfp_schedules';
	public $timestamps = false;

	protected $primaryKey = 'internalid';
	protected $fillable = [
		'schedule_type_id', 
		'title',
		'client_id',
		'cfp_id',
		'cycle_id',
		'schedule_start_date_plus_spare',
		'schedule_start_date',
		'schedule_end_date',
		'location',
		'notes',
		'created_by',
		'created_at',
		'updated_by',
		'updated_at',
		'record_flag',
		'deleted_by',
		'deleted_at'
	];

	public function userCustomer() {
        return $this->hasOne('App\User')->where('id', '=', 'customer_id');
    }
    
	public function userCfp() {
        return $this->hasOne('App\User')->where('id', '=', 'cfp_id');
    }

    public function client() {
        return $this->hasOne('App\User', 'id', 'client_id');
    }

    public function cfp() {
        return $this->hasOne('App\User', 'id', 'cfp_id');
	}

	public function reminders() {
		return $this->hasMany('App\Models\CfpScheduleReminder', 'cfp_schedule_id', 'internalid');
    }
}
