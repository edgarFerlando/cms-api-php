<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanAnalysisPlan extends Model {

	public $table = 'plan_analysis_plans';
	public $timestamps = false;

	protected $fillable = [
        'user_id', 
        'version', 
        'plan_analysis_id', 
        'plan_type',
        'plan_id',
        'status',
        'record_flag',
        'total',
        'created_by',
        'created_at', 
        'updated_by', 
        'updated_at'
    ];
/*    public function plan($query){
        return $query
              ->when($this->plan_type === 'a',function($q){
                  return $q->with('agentProfile');
             })
             ->when($this->type === 'school',function($q){
                  return $q->with('schoolProfile');
             })
             ->when($this->type === 'academy',function($q){
                  return $q->with('academyProfile');
             },function($q){
                 return $q->with('institutionProfile');
             });
        switch ($this->plan_type)
        {
            case 'a':
                return $this->hasOne('App\Models\PlanA', 'id', 'plan_id');
            case 'b':
                return $this->hasOne('App\Models\PlanB', 'id', 'plan_id');
        }
    }
*/

    /*public function plan(){ //dd(parent::attributesToArray());
        if ($this->attributes['plan_type'] === 'a')
            return $this->hasOne('App\Models\PlanA', 'id', 'plan_id');

        if ($this->attributes['plan_type'] === 'b')
            return $this->hasOne('App\Models\PlanB', 'id', 'plan_id');*/
        /*return $query
            ->when($this->plan_type === 'a',function($q){
                return $q->with('App\Models\PlanA');
            });*/
    //}

    public function plan_a() {
        return $this->hasOne('App\Models\PlanA', 'id', 'plan_id');
    }

    public function plan_b() {
        return $this->hasOne('App\Models\PlanB', 'id', 'plan_id');
    }
}
