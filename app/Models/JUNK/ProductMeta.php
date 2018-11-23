<?php namespace App\Models;

use App\BaseModel;

class ProductMeta extends BaseModel {

	use \Dimsav\Translatable\Translatable;

	public $table = 'product_metas';
    public $translatedAttributes = [ 'meta_value' ];
    protected $fillable = [ 'post_type', 'product_id', 'meta_key', 'meta_value' ];
    public $timestamps = false;

    public function productMetaTranslation() {
        return $this->hasOne('App\Models\ProductMetaTranslation')->where('locale', '=', getLang());
    }

    public function productMetaTranslations() {
        return $this->hasMany('App\Models\ProductMetaTranslation', 'product_meta_id', 'id');
    }
}