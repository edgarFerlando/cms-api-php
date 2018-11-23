<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class ArticleTranslation extends Model {

	public $timestamps = false;
    protected $fillable = [ 'title', 'slug', 'body', 'meta_title', 'meta_keywords', 'meta_description' ];

}
