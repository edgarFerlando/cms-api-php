<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeekendDays extends Model {

	public $table = 'weekend_days';
	protected $fillable = ['weekend_date', 'description' ];

}
