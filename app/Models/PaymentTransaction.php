<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model {

	public $table = 'payment_bcava_transaction';
	public $timestamps = false;

	protected $fillable = [
        'id',
        'user_id',
        'transaction_date',
        'transaction_expire',
        'transaction_amount',
        'transaction_fee',
        'currency',
        'description',
        'additional_data',
        'customer_name',
        'customer_account',
        'customer_email',
        'customer_phone',
        'customer_bill_address',
        'customer_bill_city',
        'customer_bill_state',
        'customer_bill_country',
        'customer_bill_zip_code',
        'type',
        'created_at',  
        'updated_at'
    ];

}
