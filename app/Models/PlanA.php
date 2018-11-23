<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanA extends Model {

	public $table = 'plan_a';
	public $timestamps = false;

	protected $fillable = [
        'plan_number',
        'user_id', 
        'version', 
        'kebutuhan_dana', 
        'status_perkawinan',
        'umur',
        'umur_pensiun',
        'inflasi_pertahun',
        'durasi_tahun_inflasi',
        'durasi_tahun_investasi',
        'fv_kebutuhan_dana',
        'income_simulation_id',
        'ambil_asuransi',
        'pendapatan_pensiun',
        'parent_id',
        'triangle_layer_detail_id',
        'status',
        'created_by',
        'created_at', 
        'updated_by', 
        'updated_at',
        'record_flag',
        'deleted_by', 
        'deleted_at'
    ];

    public function income_simulation(){
        return $this->hasOne('App\Models\IncomeSimulation', 'id', 'income_simulation_id');
    }

    public function detail_activation_latest_backup(){
        return $this->hasOne('App\Models\PlanAnalysisActivated', 'plan_id', 'id');//jika plan_id nya tahu pasti
    }

    public function detail_activation_latest(){
        return $this->hasOne('App\Models\PlanAnalysisActivated', 'user_id', 'user_id');
    }

    public function active_version_plan_details(){
        return $this->hasMany('App\Models\ActiveVersionPlanDetail', 'plan_id', 'id')->where('plan_type', 'plan_a');
    }

  /*  public function plan_analysis_plan(){
        return $this->hasOne('App\Models\PlanA', 'id', 'id');
    }*/
}
