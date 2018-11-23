<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model {

	public $table = 'expenses';
	public $timestamps = false;

	//protected $primaryKey = 'id';
	protected $fillable = [
        'transaction_type_id', 
        
        'user_id',
        'taxo_wallet_id',
        'anggaran_perbulan',
        'catatan',
        'version',

        'amount', 
        'category_id', 
        'category_type_id',
        'notes',
        'transaction_date',
        'created_by',
        'created_at', 
        'updated_by', 
        'updated_at', 
        'record_flag'
    ];

    public function transaction_type() {
        return $this->hasOne('App\Taxonomy', 'id', 'transaction_type_id');
    }

    public function category() {
        return $this->hasOne('App\Taxonomy', 'id','category_id');
    }

    public function category_type() {
        return $this->hasOne('App\Taxonomy', 'id','category_type_id');
    }

    public function income() {
        return $this->hasOne('App\Models\Income', 'user_id','user_id')->where('version', 5);
    }

    public function wallet_transactions() {
        return $this->hasMany('App\Models\WalletTransaction', 'detail_id', 'id');
    }

    public function gettaxoWalletIcoAttribute($taxo_wallet_ico)
    {   
        if($taxo_wallet_ico == '' || is_null($taxo_wallet_ico))
            return '';
        else
            return url($taxo_wallet_ico);  
    }
}
