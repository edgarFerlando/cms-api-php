<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingHeader extends Model {

	protected $fillable = ['booking_no', 'user_id', 'status_id', 'payment_deadline'];

    public function allBookingDetails() {
        return $this->hasMany('App\Models\BookingDetail', 'booking_header_id', 'id');
    }

	public function bookingDetails() {
        return $this->hasMany('App\Models\BookingDetail', 'booking_header_id', 'id')->where('post_type', 'hotel');
    }

    public function playgroundBookingDetails() {
        return $this->hasMany('App\Models\BookingDetail', 'booking_header_id', 'id')->where('post_type', 'playground');
    }

    public function tripBookingDetails() {
        return $this->hasMany('App\Models\BookingDetail', 'booking_header_id', 'id')->where('post_type', 'trip');
    }

    public function merchantBookingDetails() {
        return $this->hasMany('App\Models\BookingDetail', 'booking_header_id', 'id')->where('post_type', 'merchant');
    }

    public function allBookingConfirmedDetails() {
        return $this->hasMany('App\Models\BookingConfirmedDetail', 'booking_header_id', 'id');
    }

    public function bookingConfirmedDetails() {
        return $this->hasMany('App\Models\BookingConfirmedDetail', 'booking_header_id', 'id')->where('post_type', 'hotel');
    }

    public function playgroundBookingConfirmedDetails() {
        return $this->hasMany('App\Models\BookingConfirmedDetail', 'booking_header_id', 'id')->where('post_type', 'playground');
    }

    public function tripBookingConfirmedDetails() {
        return $this->hasMany('App\Models\BookingConfirmedDetail', 'booking_header_id', 'id')->where('post_type', 'trip');
    }

    public function merchantBookingConfirmedDetails() {
        return $this->hasMany('App\Models\BookingConfirmedDetail', 'booking_header_id', 'id')->where('post_type', 'merchant');
    }

    public function user() {
        return $this->hasOne('App\User', 'id', 'user_id');
    }

    public function bookingStatus() {
        return $this->hasOne('App\Models\BookingStatus', 'id', 'status_id');
    }

    public function paymentConfirmation() {
        return $this->hasOne('App\Models\BookingConfirmation', 'booking_id', 'id');
    }

}
