<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetRepaymentPaidoff extends Model {

	public $table = 'asset_repayments_paidoff';
	public $timestamps = false;

	protected $fillable = [
        'user_id', 
        'taxo_wallet_id', 
        'nama', 
        'nilai_aset',
        'catatan',
        'version',
        'created_by',
        'created_at', 
        'updated_by', 
        'updated_at'
    ];

    public function gettaxoWalletIcoAttribute($taxo_wallet_ico)
    {   
        if($taxo_wallet_ico == '' || is_null($taxo_wallet_ico))
            return '';
        else
            return url($taxo_wallet_ico);  
    }

    public function taxo_wallet() {
        return $this->hasOne('App\Taxonomy', 'id', 'taxo_wallet_id');
    }
}
