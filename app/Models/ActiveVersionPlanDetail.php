<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActiveVersionPlanDetail extends Model {

	public $table = 'active_version_plan_details';
	public $timestamps = false;

	protected $fillable = [
        'user_id', 
        'version',
        'status',
        'plan_type',
        'plan_id',
        'approved_by',
        'approved_at',
        'created_by',
        'created_at', 
        'updated_by', 
        'updated_at',
        'reason_reject',
        'rejected_at',
        'rejected_by'
    ];
}
