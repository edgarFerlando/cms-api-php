<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentInsert extends Model {

	public $table = 'payment_bcava_insert';
	public $timestamps = false;

	protected $fillable = [
        'id',
        'transaction_id',
        'currency',
        'transaction_amount',
        'transaction_date',
        'insert_status',
        'insert_message',
        'insert_id',
        'status',
        'created_at',  
        'updated_at'
    ];

}