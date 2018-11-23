<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetRepaymentPaid extends Model {

	public $table = 'asset_repayments_paid';
	public $timestamps = false;

	protected $fillable = [
        'user_id', 
        'asset_repayment_id', 
        'cicilan_terbayar',
        'catatan',
        'version',
        'created_by',
        'created_at', 
        'updated_by', 
        'updated_at'
    ];

    public function asset_repayment() {
        return $this->hasOne('App\Models\AssetRepayment', 'id', 'asset_repayment_id');
    }

    public function gettaxoWalletIcoAttribute($taxo_wallet_ico)
    {   
        if($taxo_wallet_ico == '' || is_null($taxo_wallet_ico))
            return '';
        else
            return url($taxo_wallet_ico);  
    }
}
