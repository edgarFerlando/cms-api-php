<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingConfirmedDetail extends Model {

	protected $fillable = ['booking_detail_id', 'booking_header_id', 'variation_id', 'check_in', 'check_out', 'no_of_rooms', 'price', 'weekend_price', 'remark', 'created_by', 'updated_by' ];

	public function productVariation() {
        return $this->hasOne('App\ProductVariation', 'id', 'variation_id');
    }

}
