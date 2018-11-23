<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reminder extends Model {

	public $table = 'reminders';
	public $timestamps = false;

	protected $fillable = [
		'user_id', 
		'module_name',
		'screen_name',
		'reminder_datetime',
		'is_repeated',
		'is_predefined',
		'record_flag',
		'created_by',
		'created_at',
		'updated_by',
		'updated_at',
		'deleted_by',
		'deleted_at'
	];

	public function reminderMetas() {

        return $this->hasMany('App\Models\ReminderMeta', 'reminder_id', 'id');
    }
}
