<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanB extends Model {

	public $table = 'plan_b';
	public $timestamps = false;

	protected $fillable = [
        'plan_number',
        'user_id', 
        'asset_repayment_id', 
        'plan_name', 
        'kebutuhan_dana',
        'durasi_cicilan',
        'satuan_durasi_cicilan',
        'bunga_tahunan_flat',
        'plan_perbulan',
        'version',
        'parent_id',
        'status',
        'is_protected',
        'triangle_layer_detail_id',
        'created_by',
        'created_at', 
        'updated_by', 
        'updated_at',
        'record_flag',
        'deleted_by', 
        'deleted_at'
    ];

    public function asset_repayment() {
        return $this->hasOne('App\Models\AssetRepayment', 'id', 'asset_repayment_id');
    }

    public function detail_activation_latest_backup(){
        return $this->hasOne('App\Models\PlanAnalysisActivated', 'plan_id', 'id');
    }

    public function detail_activation_latest(){
        return $this->hasOne('App\Models\PlanAnalysisActivated', 'user_id', 'user_id');
    }

    public function active_version_plan_details(){
        return $this->hasMany('App\Models\ActiveVersionPlanDetail', 'plan_id', 'id')->where('plan_type', 'plan_b');
    }
}