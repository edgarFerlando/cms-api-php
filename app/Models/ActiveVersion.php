<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActiveVersion extends Model {

	public $table = 'active_version';
	public $timestamps = false;

	protected $fillable = [
        'user_id', 
        'key', 
        'version',
        'created_by',
        'created_at', 
        'updated_by', 
        'updated_at'
    ];

    public function details(){
        return $this->hasMany('App\Models\ActiveVersionDetail', 'user_id', 'user_id');
    }

    public function asset_repayment() {
        return $this->hasOne('App\Models\AssetRepayment', 'id', 'asset_repayment_id');
    }
}
