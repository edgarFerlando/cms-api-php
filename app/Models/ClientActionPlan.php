<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientActionPlan extends Model {

	public $table = 'client_action_plans';
	public $timestamps = false;

	protected $fillable = [
        'user_id', 
        'plan_analysis_id', 
        'version',
        'created_by',
        'created_at', 
        'updated_by', 
        'updated_at'
    ];

    public function details(){
        return $this->hasMany('App\Models\ClientActionPlanDetail', 'client_action_plan_id', 'id');
    }

    public function activeVersionDetail(){
        return $this->hasMany('App\Models\ActiveVersionDetail', 'user_id', 'user_id');
    }
}
