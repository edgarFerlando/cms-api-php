<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CfpScheduleReminder extends Model {
	public $table = 'cfp_schedule_reminder';
	public $timestamps = false;
	protected $fillable = ['user_id', 'cfp_schedule_id', 'reminder_id'];

	public function reminder() {
		return $this->hasOne('App\Models\Reminder', 'id', 'reminder_id');
    }

}