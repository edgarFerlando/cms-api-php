<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Referral extends Model {

	public $table = 'affiliate_booking';
	protected $fillable = ['affiliate_id', 'booking_id'];

	public function booking() {
        return $this->hasOne('App\Models\BookingHeader', 'id', 'booking_id');
    }

    public function affiliate() {
        return $this->hasOne('App\Models\Affiliate', 'id', 'affiliate_id');
    }

}
