<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model {

	use \Dimsav\Translatable\Translatable;

	public $table = 'testimonials';
	public $translatedAttributes = [ 'name' ];
	protected $fillable = ['user_id', 'testimonial', 'name'];

	public function testimonialTranslation() {
        return $this->hasOne('App\Models\TestimonialTranslation')->where('locale', '=', getLang());
    }

}
