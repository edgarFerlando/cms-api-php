<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Insurance extends Model {

	public $table = 'insurances';
	public $timestamps = false;

	protected $fillable = [
        'user_id', 
        
        'taxo_wallet_id',
        'no_polis',
        'premi_perbulan',
        'taxo_insurance_type_id',
        'nilai_pertanggungan',
        'catatan',

        'pendapatan_bulanan', 
        'pendapatan_lain', 
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
