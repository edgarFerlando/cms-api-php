<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanAnalysis extends Model {

	public $table = 'plan_analysis';
	public $timestamps = false;

	protected $fillable = [
        'user_id', 
        'version', 
        'note', 
        'internal_note_calc',
        'record_flag',
        'created_by',
        'created_at', 
        'updated_by', 
        'updated_at'
    ];

    //client_action_plan.client_action_plan_detail
    public function client_action_plan(){
        return $this->hasOne('App\Models\ClientActionPlan', 'plan_analysis_id', 'id');
    }

    public function active_version_details(){
        return $this->hasMany('App\Models\ActiveVersionDetail', 'user_id', 'user_id')->where('active_version_key', 'planAnalysis');
    }
}
