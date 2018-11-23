<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingConfirmation extends Model {

	protected $fillable = ['email', 'booking_id', 'amount', 'account_name', 'account_no', 'bank_account_id', 'customer_bank_account_id', 'note', 'status', 'updated_by', 'confirmed_at', 'confirmed_by' ];

	public function booking() {
        return $this->hasOne('App\Models\BookingHeader', 'id', 'booking_id');
    }

    public function customer_bank_account() {
        return $this->hasOne('App\Models\CustomerBankAccount', 'id', 'customer_bank_account_id');
    }

}
