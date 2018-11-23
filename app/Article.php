<?php namespace App;

//use Illuminate\Database\Eloquent\Model;
use App\Interfaces\ModelInterface as ModelInterface;

class Article extends BaseModel implements ModelInterface{
	public $table = 'articles';
    protected $fillable = [ 'category_id', 'is_published', 'title', 'slug', 'source_name',  'source_url', 'body', 'featured_image', 'meta_title', 'meta_keywords', 'meta_description', 'record_flag', 'created_by', 'updated_by' ];
    protected $appends = ['url'];

    public function category() {
        return $this->hasOne('App\Taxonomy', 'id', 'category_id');
    }

    public function setUrlAttribute($value) {

        $this->attributes['url'] = $value;
    }

    public function getUrlAttribute() {
        return $this->attributes['slug'];
    }

    public function getfeaturedImageAttribute($image)
    {   
        if($image == '' || is_null($image))
            return '';
        else
            return url($image);  
    }
}
