<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IncomeSimulation extends Model {

	public $table = 'income_simulations';
	public $timestamps = false;
    //protected $primaryKey = 'user_id';

	protected $fillable = [
        'user_id', 
        'version', 
        'interest_rate_id',
        'bunga_investasi_pertahun', 
        'cicilan_perbulan',
        'produk',
        'total_investasi',
        'plan_type',
        'plan_id',
        'bgcolor',
        'bgcolor2',
        'taxo_wallet_id',
        'created_by',
        'created_at', 
        'updated_by', 
        'updated_at'
    ];

    /*public function expenses() {
        return $this->hasMany('App\Models\Expense', 'user_id', 'user_id')->where('version', 5);
    }

    public function user() {
        return $this->hasOne('App\User', 'id', 'user_id');
    }*/

    public function actual_interest_rates() {
        return $this->hasMany('App\Models\ActualInterestRate', 'interest_rate_id', 'interest_rate_id');
    }
}
