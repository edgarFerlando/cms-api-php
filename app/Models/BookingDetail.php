<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingDetail extends Model {

	protected $fillable = ['booking_header_id', 'variation_id', 'check_in', 'check_out', 'no_of_rooms', 'qty', 'price', 'weekend_price' ];

	public function productVariation() {
        return $this->hasOne('App\ProductVariation', 'id', 'variation_id');
    }

}
