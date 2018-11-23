<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductSpecialOffer extends Model {

	public $timestamps = false;
    protected $fillable = [ 'product_id', 'product_sku_id', 'special_offer' ];

    public function product() {
        return $this->hasOne('App\Product', 'id', 'product_id');
    }

}
