<?php namespace App;

use App\Interfaces\ModelInterface as ModelInterface;
use Baum\Node;

class Taxonomy extends Node implements ModelInterface {
    //use \App\classes\TranslatableSupportBaum;

    public $table = 'taxonomies';
    //public $translatedAttributes = [ 'title', 'slug' ];
    protected $fillable = [ 
        'post_type', 
        'image', 
        'parent_id', 
        'lft', 
        'rgt', 
        'depth', 
        'order', 
        'title', 
        'slug', 
        'color', 
        'is_hidden',
        'is_required',
        'budged_percentage',
        'description',
        'created_by', 
        'updated_by' 
    ];
    
    protected $appends = ['url'];

    public function setUrlAttribute($value) {

        $this->attributes['url'] = $value;
    }

    public function getUrlAttribute() {

        return "taxonomy/";
    }

    public function articles() {

        return $this->hasMany('App\Article');
    }

    /*public function productAttributeTaxonomy() {

        return $this->hasMany('App\ProductAttributeTaxonomy', 'taxonomy_id', 'id');
    }*/

    public function taxonomyTranslation() {
        return $this->hasOne('App\TaxonomyTranslation')->where('locale', '=', getLang());
    }

    public function trips()
    {
        return $this->hasMany('App\Product', 'product_category_id', 'id');
    }

    public function parentDetail(){
        return $this->hasOne('App\Taxonomy', 'id', 'parent_id');
    }

    public function taxoMetas() {

        return $this->hasMany('App\Models\TaxonomyMeta', 'taxonomy_id', 'id');
    }

    public function getimageAttribute($image)
    {   
        if($image == '' || is_null($image))
            return '';
        else
            return url($image);  
    }

}