<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserMeta extends Model {

	public $timestamps = false;
	protected $fillable = ['user_id', 'meta_key', 'meta_value'];

	public function branch()
    {
    	return $this->hasOne('App\Taxonomy', 'id', 'meta_value');//->whereHas('meta_value', '!=', '');
    }

}
