<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DebtRepayment extends Model {

	public $table = 'debt_repayments';
	public $timestamps = false;

	protected $fillable = [
        'user_id', 
        'taxo_wallet_id', 
        'nama', 
        'cicilan_perbulan',
        'sisa_durasi',
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
}
