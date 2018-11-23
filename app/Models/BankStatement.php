<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BankStatement extends Model {
    use SoftDeletes;
	public $table = 'bank_statements';
    protected $fillable = [
        'user_id',
        'bank_code_name',
        'bank_account_number',
        'transaction_type',
        'transaction_description',
        'transaction_date',
        'transaction_branch',
        'transaction_amount',
        'transaction_balance',
        'user_expense_id',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at',
        'deleted_by',
        'deleted_at',
        'record_flag',
        'is_categorized' 
    ];
}
