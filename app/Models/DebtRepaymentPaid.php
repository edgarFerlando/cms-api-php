<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DebtRepaymentPaid extends Model {

	public $table = 'wallet';
	public $timestamps = false;

	//protected $primaryKey = 'id';
	protected $fillable = [
        'transaction_type_id', 
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
}
