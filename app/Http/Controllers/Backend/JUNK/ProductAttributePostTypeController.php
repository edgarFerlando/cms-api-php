<?php namespace App\Http\Controllers\backend;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Redirect;
use View;
use Input;
use Validator;
use Response;
use Request;
use Notification;
use App\Exceptions\Validation\ValidationException;
//use App\Repositories\ProductAttributePostType\ProductAttributePostTypeInterface;
//use App\Repositories\ProductAttributePostType\ProductAttributePostTypeRepository as ProductAttributePostType;
use App\Repositories\ProductAttribute\ProductAttributeInterface;
use App\Repositories\ProductAttribute\ProductAttributeRepository as ProductAttribute;
use App\ProductAttributePostType;

class ProductAttributePostTypeController extends Controller {

    protected $productAttribute;

    public function __construct(ProductAttributeInterface $productAttribute) {
        $this->productAttribute = $productAttribute;
    }

	public function edit($post_type) {
        $productAttributePostType = ProductAttributePostType::where('post_type', $post_type)->get();
        $productAttribute_selected = [];
        if($productAttributePostType){
            foreach($productAttributePostType as $product_attribute){
                $productAttribute_selected[] = $product_attribute->product_attribute_id;
            }
        }

        $productAttribute_options = $this->productAttribute->lists('name', 'id');
        return view('backend.productAttributePostType.index', compact('post_type', 'productAttribute_options', 'productAttribute_selected'));
    }

    public function update() {
    	$post_type = Input::get('post_type');
    	ProductAttributePostType::where('post_type', $post_type)->delete();
    	if(Input::get('product_attribute')){
	        foreach(Input::get('product_attribute') as $productAttributeID ){
		        //\DB::enableQueryLog();
		        ProductAttributePostType::create([
		            'post_type' => $post_type,
		            'product_attribute_id' => $productAttributeID
		        ]);
		        //dd(\DB::getQueryLog());
		    }
		}
		Notification::success(trans('app.product_attribute_'.$post_type.'_updated'));
        return Redirect::route('admin.product_attribute_posttype', [ 'post_type' => $post_type ]);
    }


}
