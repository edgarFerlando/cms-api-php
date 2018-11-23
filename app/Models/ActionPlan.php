<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActionPlan extends Model {

	public $table = 'action_plans';
	public $timestamps = false;

	protected $fillable = [
        'title',
        'action_plan_category_id',
        'created_by',
        'created_at', 
        'updated_by', 
        'updated_at'
    ];

}
