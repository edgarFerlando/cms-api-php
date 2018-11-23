<?php namespace App\Models;

use App\BaseModel;

class Banner extends BaseModel{

	use \Dimsav\Translatable\Translatable;

    public $table = 'banners';
    public $translatedAttributes = [ 'title', 'url', 'image' ];
    protected $fillable = [ 'banner_group_id', 'type', 'option', 'is_published','title', 'url', 'image' ];
    protected $appends = ['url'];

    public function bannerGroup() {
        return $this->hasOne('App\Models\BannerGroup', 'id', 'banner_group_id');
    }


}
