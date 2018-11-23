<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientActionPlanDetail extends Model {

	public $table = 'client_action_plan_details';
	public $timestamps = false;

	protected $fillable = [
        'client_action_plan_id', 
        'taxo_action_plan_id',
        'timeline',
        'note',
        'created_by',
        'created_at', 
        'updated_by', 
        'updated_at',
        'status',
        'reason_reject',
        'rejected_at',
        'rejected_by'
    ];

    public function clientActionPlan(){
        return $this->hasOne('App\Models\ClientActionPlan', 'id', 'client_action_plan_id');
    }
}
