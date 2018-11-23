<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingStatus extends Model {

	use \Dimsav\Translatable\Translatable;

	public $table = 'booking_statuses';
    public $translatedAttributes = [ 'name' ];
    protected $fillable = [ 'name' ];

	public function bookingStatusTranslation() {
        return $this->hasOne('App\Models\BookingStatusTranslation')->where('locale', '=', getLang());
    }

}
