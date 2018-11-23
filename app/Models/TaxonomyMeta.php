<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaxonomyMeta extends Model {

	public $timestamps = false;
	protected $fillable = ['taxonomy_id', 'meta_key', 'meta_value'];

}