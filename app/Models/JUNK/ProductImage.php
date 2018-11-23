<?php namespace App\Models;

use App\BaseModel;

class ProductImage extends BaseModel {
	public $table = 'product_images';
    protected $fillable = [ 'post_type', 'product_id', 'image_path' ];
    public $timestamps = false;
}