<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentStatus extends Model {

	public $table = 'payment_status';
	public $timestamps = false;

	protected $fillable = [
        'id',
        'transaction_id',
        'insert_id',
        'payment_status',
        'payment_message',
        'flag_type',
        'transaction_status',
        'transaction_message',
        'payment_reff_id',
        'created_at',  
        'updated_at'
    ];

}