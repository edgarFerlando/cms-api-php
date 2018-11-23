<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReminderMeta extends Model {

	public $table = 'reminder_metas';
	public $timestamps = false;

	protected $fillable = [
		'reminder_id', 
		'meta_key', 
		'meta_value'
	];
}
