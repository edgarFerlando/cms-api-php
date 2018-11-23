<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActiveVersionPlan extends Model {

	public $table = 'active_version_plan';
	public $timestamps = false;

	protected $fillable = [
        'user_id', 
        'plan_type',
        'plan_id', 
        'version',
        'created_by',
        'created_at', 
        'updated_by', 
        'updated_at'
    ];
}
