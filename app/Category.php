<?php namespace App;

use App\Interfaces\ModelInterface as ModelInterface;
use Baum\Node;

class Category extends Node implements ModelInterface {
    use \App\classes\TranslatableSupportBaum;

    public $table = 'categories';
    public $translatedAttributes = [ 'title', 'slug' ];
    protected $fillable = [ 'parent_id', 'lft', 'rgt', 'depth', 'order', 'title', 'slug' ];
    protected $appends = ['url'];

    public function setUrlAttribute($value) {

        $this->attributes['url'] = $value;
    }

    public function getUrlAttribute() {

        return "article/category/" . $this->attributes['slug'];
    }

    public function articles() {

        return $this->hasMany('App\Article');
    }

}