<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanAnalysisActivated extends Model {

	public $table = 'plan_analysis_activated';
	public $timestamps = false;

	protected $fillable = [
        'user_id',
        'version',
        'plan_type',
        'plan_id', 
        'asset_repayment_id',
        'created_by',
        'created_at', 
        'updated_by', 
        'updated_at'
    ];

    public function asset_repayment() {
        return $this->hasOne('App\Models\AssetRepayment', 'id', 'asset_repayment_id');
    }
}
