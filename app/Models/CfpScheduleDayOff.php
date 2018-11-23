<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CfpScheduleDayOff extends Model {

	use SoftDeletes;

	public $table = 'cfp_schedule_day_offs';
	protected $primaryKey = 'id';
    protected $fillable = [
		'cfp_id',
		'cfp_schedule_day_off_start_date',
		'cfp_schedule_day_off_end_date',
		'description',
		'is_approval',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at',
        'deleted_by',
        'deleted_at',
        'record_flag' 
    ];

}
