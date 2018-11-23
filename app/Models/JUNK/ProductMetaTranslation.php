<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductMetaTranslation extends Model {

	public $timestamps = false;
    protected $fillable = [ 'meta_value' ];

}