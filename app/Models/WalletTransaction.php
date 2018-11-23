<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model {

	public $table = 'wallet_transactions';
	public $timestamps = false;

	//protected $primaryKey = 'internalid';
	protected $fillable = [
		'id', 
		'user_id', 
		'taxo_wallet_module_title',
		'detail_id',
		'amount',
		'note',
		'transaction_date',
		'created_by',
		'created_at',
		'updated_by',
		'updated_at',
		'deleted_by',
		'deleted_at',
		'record_flag',
		'cashflow_analysis_version'
	];

	public function detail() {
		return $this->hasOne('App\Models\Expense', 'id', 'detail_id');
		//->select('taxonomies.title as taxo_wallet_name', 'taxonomies.image as taxo_wallet_ico')
		//->join('taxonomies', 'taxonomies.id', '=', 'expenses.taxo_wallet_id', 'left');
    }
}