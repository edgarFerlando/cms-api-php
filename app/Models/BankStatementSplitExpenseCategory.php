<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BankStatementSplitExpenseCategory extends Model {

	use SoftDeletes;
	public $table = 'bank_statements_split_expense_categories';
	protected $fillable = [
		'bank_statement_id',
		'user_expense_id',
		'user_id',
		'description',
		'amount',
		'bank_account_id',
		'created_at',
		'created_by',
		'updated_at',
		'updated_by',
		'record_flag',
		'deleted_at',
		'deleted_by'
	];

}
