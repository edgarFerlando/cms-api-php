<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class TaxonomyTranslation extends Model {

	public $timestamps = false;
    protected $fillable = [ 'title', 'slug'];

}