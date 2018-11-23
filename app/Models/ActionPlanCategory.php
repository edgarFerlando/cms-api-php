<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActionPlanCategory extends Model {

	public $table = 'action_plan_categories';
	public $timestamps = false;

	protected $fillable = [
        'title', 
        'created_by',
        'created_at', 
        'updated_by', 
        'updated_at'
    ];

}
