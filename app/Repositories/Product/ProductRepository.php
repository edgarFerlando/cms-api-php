<?php namespace App\Repositories\Product;

use App\Product;
use App\ProductVariation;
use App\ProductSku;
use App\User;
use Config;
use Response;
//use App\Tag;
//use App\ProductCategory;
use Str;
//use Event;
//use Image;
//use File;
use App\Repositories\RepositoryAbstract;
use App\Repositories\CrudableInterface as CrudableInterface;
use App\Repositories\RepositoryInterface as RepositoryInterface;
use App\Repositories\Product\ProductInterface;
//use App\Repositories\ProductVariation\ProductVariationInterface;
use App\Exceptions\Validation\ValidationException;
use App\Repositories\AbstractValidator as Validator;
use LaravelLocalization;
use Notification;

use App\ProductAttribute;
use App\Repositories\ProductAttribute\ProductAttributeRepository;

use App\ProductAttributeOption;
use App\Repositories\ProductAttributeOption\ProductAttributeOptionRepository;

use App\Models\ProductMeta;
use App\Repositories\ProductMeta\ProductMetaRepository;

use App\Taxonomy;
use App\Repositories\Taxonomy\TaxonomyRepository;

use App\Models\ProductImage;
use App\Models\ProductSpecialOffer;

use Input;
use DB;

use Auth;

class ProductRepository extends RepositoryAbstract implements ProductInterface, CrudableInterface {

/*protected $width;
protected $height;
protected $thumbWidth;
protected $thumbHeight;
protected $imgDir;*/
protected $perPage;
protected $product;
protected $productAttribute;
protected $productAttributeOption;
protected $productMeta;
protected $productImage;
//protected $productVariation;
/**
* Rules
*
* @var array
*/
protected static $rules;/* = [
   'title'   => 'required',
   'content' => 'required'
   ];*/
   protected static $attributeNames;

/**
* @param Article $article
*/
public function __construct(Product $product) {
  $this->perPage = Config::get('holiday.per_page');
  $this->product = $product;
  $this->productCategory = new TaxonomyRepository(new Taxonomy);
  $this->productAttribute = new ProductAttributeRepository(new ProductAttribute);
  $this->productAttributeOption = new ProductAttributeOptionRepository(new ProductAttributeOption);
  $this->productMeta = new ProductMeta;
  $this->productImage = new ProductImage;
  $rules_n_attributeNames = $this->rules();
  self::$rules = $rules_n_attributeNames['rules'];
  self::$attributeNames = $rules_n_attributeNames['attributeNames'];
}

public function rules_hotel(){
  $_rules = array();
  $setAttributeNames = array();
  switch (Input::method()) {
    case 'PATCH': 
    $id = Input::segment(4);
    $_rules['title'] = 'required|max:255|unique:product_translations,title,'.$id.',product_id';
    $_rules['slug'] = 'required|alpha_dash|max:255|unique:product_translations,slug,'.$id.',product_id';
    break;
    default:
    $_rules['title'] = 'required|max:255|unique:product_translations,title';
    $_rules['slug'] = 'required|alpha_dash|max:255|unique:product_translations,slug';
    break;
}
$_rules['hotel_address'] = 'required';
$_rules['product_category'] = 'required';

$setAttributeNames['title'] = trans('app.name');
   if(!is_null(Input::get('price_single'))){// && is_null(Input::get('variant_datarow'))){
    $_rules['price_single'] = 'required';
    $_rules['weekend_price_single'] = 'required';
    $_rules['qty_single'] = 'required';

    $setAttributeNames['price_single'] = trans('app.price');
    $setAttributeNames['weekend_price_single'] = trans('app.weekend_price');
    $setAttributeNames['qty_single'] = trans('app.qty');
}

foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
    $_rules['meta_keywords.'.$locale] = 'required';
    $_rules['meta_description.'.$locale] = 'required';

    $setAttributeNames['product_category'] = trans('app.product_category_hotel');
    $setAttributeNames['title'] = trans('app.hotel_name');
    $setAttributeNames['hotel_address'] = trans('app.address');
    $setAttributeNames['meta_keywords.' . $locale] = trans('app.meta_keywords').' [ ' . $properties['native'].' ]';
    $setAttributeNames['meta_description.' . $locale] = trans('app.meta_description').' [ ' . $properties['native'].' ]';
}
return [
'rules' => $_rules,
'attributeNames' => $setAttributeNames
];
}

public function rules_playground(){
    $_rules = array();
    $setAttributeNames = array();
    switch (Input::method()) {
        case 'PATCH': 
        $id = Input::segment(4);
        $_rules['title'] = 'required|max:255|unique:product_translations,title,'.$id.',product_id';
        $_rules['slug'] = 'required|alpha_dash|max:255|unique:product_translations,slug,'.$id.',product_id';
        break;
        default:
        $_rules['title'] = 'required|max:255|unique:product_translations,title';
        $_rules['slug'] = 'required|alpha_dash|max:255|unique:product_translations,slug';
        break;
    }
    $_rules['playground_address'] = 'required';
    $_rules['product_category'] = 'required';

    $setAttributeNames['title'] = trans('app.name');
   if(!is_null(Input::get('price_single'))){// && is_null(Input::get('variant_datarow'))){
    $_rules['price_single'] = 'required';
    $_rules['weekend_price_single'] = 'required';
    $_rules['qty_single'] = 'required';

    $setAttributeNames['price_single'] = trans('app.price');
    $setAttributeNames['weekend_price_single'] = trans('app.weekend_price');
    $setAttributeNames['qty_single'] = trans('app.qty');
}

foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
    $_rules['meta_keywords.'.$locale] = 'required';
    $_rules['meta_description.'.$locale] = 'required';

    $setAttributeNames['product_category'] = trans('app.product_category_hotel');
    $setAttributeNames['title'] = trans('app.hotel_name');
    $setAttributeNames['playground_address'] = trans('app.address');
    $setAttributeNames['meta_keywords.' . $locale] = trans('app.meta_keywords').' [ ' . $properties['native'].' ]';
    $setAttributeNames['meta_description.' . $locale] = trans('app.meta_description').' [ ' . $properties['native'].' ]';
}
return [
'rules' => $_rules,
'attributeNames' => $setAttributeNames
];
}

public function rules_trip(){
    $_rules = array();
    $setAttributeNames = array();
    switch (Input::method()) {
        case 'PATCH': 
        $id = Input::segment(4);
        $_rules['title'] = 'required|max:255|unique:product_translations,title,'.$id.',product_id';
        $_rules['slug'] = 'required|alpha_dash|max:255|unique:product_translations,slug,'.$id.',product_id';
        break;
        default:
        $_rules['title'] = 'required|max:255|unique:product_translations,title';
        $_rules['slug'] = 'required|alpha_dash|max:255|unique:product_translations,slug';
        break;
    }
    //$_rules['product_category'] = 'required';
    $_rules['start_date'] = 'required|date';
    $_rules['end_date'] = 'required|date';

    $setAttributeNames['title'] = trans('app.name');
    $setAttributeNames['start_date'] = trans('app.start_date');
    $setAttributeNames['end_date'] = trans('app.end_date');

   if(!is_null(Input::get('price_single'))){// && is_null(Input::get('variant_datarow'))){
    $_rules['price_single'] = 'required';
    $_rules['weekend_price_single'] = 'required';
    $_rules['qty_single'] = 'required';

    $setAttributeNames['price_single'] = trans('app.price');
    $setAttributeNames['weekend_price_single'] = trans('app.weekend_price');
    $setAttributeNames['qty_single'] = trans('app.qty');
}

foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
    $_rules['meta_keywords.'.$locale] = 'required';
    $_rules['meta_description.'.$locale] = 'required';

    $setAttributeNames['product_category'] = trans('app.product_category_hotel');
    $setAttributeNames['title'] = trans('app.hotel_name');
    $setAttributeNames['meta_keywords.' . $locale] = trans('app.meta_keywords').' [ ' . $properties['native'].' ]';
    $setAttributeNames['meta_description.' . $locale] = trans('app.meta_description').' [ ' . $properties['native'].' ]';
}
return [
'rules' => $_rules,
'attributeNames' => $setAttributeNames
];
}

public function rules_merchant(){
    $_rules = array();
    $setAttributeNames = array();
    switch (Input::method()) {
        case 'PATCH': 
        $id = Input::segment(4);
        $_rules['title'] = 'required|max:255|unique:product_translations,title,'.$id.',product_id';
        $_rules['slug'] = 'required|alpha_dash|max:255|unique:product_translations,slug,'.$id.',product_id';
        break;
        default:
        $_rules['title'] = 'required|max:255|unique:product_translations,title';
        $_rules['slug'] = 'required|alpha_dash|max:255|unique:product_translations,slug';
        break;
    }
    $_rules['product_category'] = 'required';

    $setAttributeNames['title'] = trans('app.merchant_name');
   if(!is_null(Input::get('price_single'))){// && is_null(Input::get('variant_datarow'))){
    $_rules['price_single'] = 'required';
    $_rules['weekend_price_single'] = 'required';
    $_rules['qty_single'] = 'required';

    $setAttributeNames['price_single'] = trans('app.price');
    $setAttributeNames['weekend_price_single'] = trans('app.weekend_price');
    $setAttributeNames['qty_single'] = trans('app.qty');
}

foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
    $_rules['meta_keywords.'.$locale] = 'required';
    $_rules['meta_description.'.$locale] = 'required';

    $setAttributeNames['product_category'] = trans('app.product_category_hotel');
    $setAttributeNames['title'] = trans('app.hotel_name');
    $setAttributeNames['merchant_address'] = trans('app.address');
    $setAttributeNames['meta_keywords.' . $locale] = trans('app.meta_keywords').' [ ' . $properties['native'].' ]';
    $setAttributeNames['meta_description.' . $locale] = trans('app.meta_description').' [ ' . $properties['native'].' ]';
}
return [
'rules' => $_rules,
'attributeNames' => $setAttributeNames
];
}

public function rules(){
    switch (Input::get('post_type')) {
        case 'hotel':
        return $this->rules_hotel();
        break;
        case 'playground':
        return $this->rules_playground();
        break;
        case 'trip':
        return $this->rules_trip();
        break;
        case 'merchant':
        return $this->rules_merchant();
        break;
    }
}

/**
* @return mixed
*/
public function all() {

   //return $this->article->with('tags')->orderBy('created_at', 'DESC')->where('is_published', 1)->get();
  return $this->product->orderBy('created_at', 'DESC')->where('is_published', 1)->get();
}

/**
* @return mixed
*/
public function allByUser($filter) {
  //\DB::enableQueryLog();
   //return $this->article->with('tags')->orderBy('created_at', 'DESC')->where('is_published', 1)->get();
  return $this->product->with(['productCategory'])->whereIn('created_by', $filter['user_id'] )->get();
  //dd(\DB::getQueryLog());
}

/**
* @param $limit
* @return mixed
*/
public function getLastProduct($limit) {

  return $this->product->orderBy('created_at', 'desc')->take($limit)->offset(0)->get();
}

/**
* @param $slug
* @return mixed
*/
public function getSlugsByID($id) {
  $product_with_attrs =  $this->product
  ->select('locale', 'slug')
  ->join('product_translations', 'products.id', '=', 'product_translations.product_id')
  ->where( 'products.id', $id)->get()->toArray();
  $params = Input::all();
  $param_attrs = '';
  if($product_with_attrs){
    $attrs = [];

    if($params){
      $param_attr = [];
      foreach ($params as $key => $value) {
        $param_attr[] = $key.'='.$value;
    }
    $param_attrs = '?'.implode('&', $param_attr);
}

foreach ($product_with_attrs as $attr) { 
   $attrs[$attr['locale']]['url'] = $attr['url'].$param_attrs;
}
return $attrs;
}
}

/**
* @return mixed
*/
public function lists() {

   //return $this->article->get()->lists('title', 'id');
  return $this->product->all()->lists('title', 'id');
}

/**
* Get paginated articles
*
* @param int $page Number of articles per page
* @param int $limit Results per page
* @param boolean $all Show published or all
* @return StdClass Object with $items and $totalItems for pagination
*/
public function paginate($page = 1, $limit = 10, $filter = array()){//$all = false) {

    $result = new \StdClass;
    $result->page = $page;
    $result->limit = $limit;
    $result->totalItems = 0;
    $result->items = array();

   //$query = $this->article->with('tags')->orderBy('created_at', 'DESC');
    $query = $this->product->with(['productCategory.taxonomyTranslation', 'productMetas.productMetaTranslation', 'translations', 'productVariations.productAttributeOption.productAttributeOptionTranslation', 'productSkus.productVariation', 'productSkus.productSpecialOffer', 'productImages', 'productSpecialOffers' ])->orderBy('created_at', 'DESC');

   /*if(!$all) {
       $query->where('is_published', 1);
   }*/

   if(is_array($filter)){
       foreach($filter as $ff => $term){
         if(trim($term) != ''){
           switch ($ff) {
             case 'with' :
             $query->whereHas('productSpecialOffers', function($q) {
              $q->havingRaw('COUNT(DISTINCT `product_id`) > 0');
          });
             break;
             case 'product_category':
             $query->where('product_category_id', $term);
             break;
             case 'title':
             $query->whereHas('productTranslation', function($q) use ($term) {
              $q->where('title', 'like', '%'.$term.'%');
          });
             break;
             case 'post_type':
             $query->where('post_type', $term);
             break;
         }
     }
 }
}

$user = User::with('roles')->find(Auth::user()->id);
$userRole = '';

foreach ($user->roles as $role) {
  if($role->name == 'admin'){
    $userRole = $role->name;
  }
  else
  {
    $userRole = 'user';
  }
}

//dd($role);
if ($userRole != 'admin') {
  $query->where('created_by', Auth::user()->id);
}


$products = $limit == 0? $query->get():$query->skip($limit * ($page - 1))->take($limit)->get();

$result->totalItems = $this->totalProducts($filter);
$result->items = $products->all();
return $result;
}

public function paginateAll($page = 1, $limit = 10, $filter = array()){//$all = false) {

    $result = new \StdClass;
    $result->page = $page;
    $result->limit = $limit;
    $result->totalItems = 0;
    $result->items = array();

   //$query = $this->article->with('tags')->orderBy('created_at', 'DESC');
    $query = $this->product->with(['productCategory.taxonomyTranslation', 'productMetas.productMetaTranslation', 'translations', 'productVariations.productAttributeOption.productAttributeOptionTranslation', 'productSkus.productVariation', 'productSkus.productSpecialOffer', 'productImages', 'productSpecialOffers' ])->orderBy('created_at', 'DESC');

   /*if(!$all) {
       $query->where('is_published', 1);
   }*/

   if(is_array($filter)){
       foreach($filter as $ff => $term){
         if(trim($term) != ''){
           switch ($ff) {
             case 'with' :
             $query->whereHas('productSpecialOffers', function($q) {
              $q->havingRaw('COUNT(DISTINCT `product_id`) > 0');
          });
             break;
             case 'product_category':
             $query->where('product_category_id', $term);
             break;
             case 'title':
             $query->whereHas('productTranslation', function($q) use ($term) {
              $q->where('title', 'like', '%'.$term.'%');
          });
             break;
             case 'post_type':
             $query->where('post_type', $term);
             break;
         }
     }
 }
}

$products = $limit == 0? $query->get():$query->skip($limit * ($page - 1))->take($limit)->get();

$result->totalItems = $this->totalProducts($filter);
$result->items = $products->all();
return $result;
}

//public function frontend_paginate_allByRegion($page = 1, $limit = 10, $all = false) {
public function allByRegion($region_id = null, $paginate = false, $page = 1, $limit = 10, $filter = array()){
    if(!is_null($region_id)){
     $q_where = $this->product->where('product_category_id', $region_id);
     $q = $q_where->with(['productCategory.taxonomyTranslation', 'productMetas.productMetaTranslation', 'productSkus'])->orderBy('created_at', 'DESC');

     if(is_array($filter)){
       foreach($filter as $ff => $term){
         if(trim($term) != ''){
           switch ($ff) {
             case 'star': 
             $q->whereHas('productMetas', function($q1) use ($term) {
              $q1->where('meta_key', 'hotel_star')
              ->whereHas('productMetaTranslation', function($q2) use ($term) {
                $q2->whereIn('meta_value', explode(',',$term));
            });
          });
             break;
             case 'region':
             $q->whereHas('productCategory', function($q1) use ($term) {
              $q1->whereHas('taxonomyTranslation', function($q2) use ($term) {
                $q2->whereIn('slug', explode(',',$term));
            });
          });
             break;
         }
     }
 }
}

if($paginate){ 
    $result = new \StdClass;
    $result->page = $page;
    $result->limit = $limit;
    $result->totalItems = 0;
    $result->items = array();
    $result->totalItems = $q_where->count();
    $products = $q->skip($limit * ($page - 1))->take($limit)->get();


    $result->items = $products->all();
    return $result;
}
return $q->get();
}
   /*$result = new \StdClass;
   $result->page = $page;
   $result->limit = $limit;
   $result->totalItems = 0;
   $result->items = array();*/

   //$query = $this->product->where('product_category_id', 45)->with(['productCategory.taxonomyTranslation'])->orderBy('created_at', 'DESC');

   //if(!$all) {
   //    $query->where('is_published', 1);
  // }

   /*$products = $q->skip($limit * ($page - 1))->take($limit)->get();
dd($products);
   $result->totalItems = $this->totalProducts($all);
   $result->items = $products->all();

   return $result;*/
}

public function getFilterByCountry($filter = null){//$country_slug = null){
   if(!is_null($filter)){//$country_slug)){
      if(is_null($filter['country']))
      {
        $regions_raw = $this->productCategory->findByPost($filter['post_type']); 
      }
      else
      {
        $regions_raw = $this->productCategory->findBySlug($filter['country'], $filter['post_type'])->getDescendantsAndSelf(); 
      }

    $regions = [];

    if(is_null($filter['country']))
    {
      foreach ($regions_raw as $region) {
        $region->getDescendantsAndSelf();
        $regions[] = $region->id;
      }
    }
    else
    {
      foreach ($regions_raw as $region) {
        $regions[] = $region->id;
      }
    }

    $items = $this->product->whereIn('product_category_id', $regions)
    ->with(['productCategory', 'productMetas', 'productMetas.productMetaTranslations', 'productSkus'])->get();

    if($items){
        $star = [];
        $regions_name = [];
        foreach ($items as $item) {

          $meta = productMeta($item->productMetas);
          if($meta && isset($meta->hotel_star) && $meta->hotel_star != ''){
            if( !isset($star[$meta->hotel_star])){

              $star[$meta->hotel_star] = 1;
                       //$regions_name[$item->productCategory->id]['num'] = 1;
          }else{
              $star[$meta->hotel_star]++;
                       //$regions_name[$item->productCategory->id]['num']++;
          }
      }
      if(isset($regions_name[$item->productCategory->id]['num']))
         $regions_name[$item->productCategory->id]['num']++;
     else
         $regions_name[$item->productCategory->id]['num'] = 1;
     $regions_name[$item->productCategory->id]['title'] = $item->productCategory->title;
     $regions_name[$item->productCategory->id]['slug'] = $item->productCategory->slug;
 }
}
//dd($region);
return [
'star' => $star,
'region' => $regions_name
];
}
}

public function getFilterByPost($filter = null){//$country_slug = null){
   if(!is_null($filter)){//$country_slug)){

    $regions_raw = $this->productCategory->findByPost($filter['post_type']); 
      

    $regions = [];

    foreach ($regions_raw as $region) {
      $region->getDescendantsAndSelf();
      $regions[] = $region->id;
    }

    $items = $this->product->whereIn('product_category_id', $regions)
    ->with(['productCategory', 'productMetas', 'productMetas.productMetaTranslations', 'productSkus'])->get();

    if($items){
        $star = [];
        $regions_name = [];
        foreach ($items as $item) {

          $meta = productMeta($item->productMetas);
          if($meta && isset($meta->hotel_star) && $meta->hotel_star != ''){
            if( !isset($star[$meta->hotel_star])){

              $star[$meta->hotel_star] = 1;
                       //$regions_name[$item->productCategory->id]['num'] = 1;
          }else{
              $star[$meta->hotel_star]++;
                       //$regions_name[$item->productCategory->id]['num']++;
          }
      }
      if(isset($regions_name[$item->productCategory->id]['num']))
         $regions_name[$item->productCategory->id]['num']++;
     else
         $regions_name[$item->productCategory->id]['num'] = 1;
     $regions_name[$item->productCategory->id]['title'] = $item->productCategory->title;
     $regions_name[$item->productCategory->id]['slug'] = $item->productCategory->slug;
 }
}
//dd($region);
return [
'star' => $star,
'region' => $regions_name
];
}
}

/*
public function getFilterByCategory($filter = null){//$country_slug = null){
  $ids = [];
  $filterPost = $this->productCategory->getTermsByPostType($filter['post_type']);

  foreach ($filterPost as $key => $value) {
    $ids[$key] = $value->id;
  }

  $items = [];
  $items_raw = $this->product->whereIn('product_category_id', $ids)
    ->with(['productCategory.taxonomyTranslation', 'productMetas', 'productMetas.productMetaTranslations', 'productSkus'])->get();

  dd($items);
  return ;
}
*/

public function getFilterByCountryDate($filter = null){//$country_slug = null){
   if(!is_null($filter)){//$country_slug)){
    //dd($filter['start_date']);
    $start_date = $filter['start_date'];

    $regions_raw = $this->productCategory->findBySlug($filter['country'], $filter['post_type'], $filter['start_date'])->getDescendantsAndSelf(); 
    //dd($regions_raw);
    $regions = [];

    foreach ($regions_raw as $region) {
        $regions[] = $region->id;

    }

    //dd($regions);
    //\DB::enableQueryLog();


    $items = $this->product->whereIn('product_category_id', $regions)
    ->with(['productCategory', 'productMetas', 'productMetas.productMetaTranslations', 'productSkus'])
    ->whereHas('productMetas', function($q1) use ($start_date) {
      $q1->where('meta_key', 'start_date')
      ->whereHas('productMetaTranslations', function($q2) use ($start_date) {
          $q2->where('meta_value', '>', $start_date);
      });
    })->get();

    //dd(\DB::getQueryLog());
    //dd($items);

    if($items){
        $star = [];
        $regions_name = [];
        foreach ($items as $item) {

          $meta = productMeta($item->productMetas);
          if($meta && isset($meta->hotel_star) && $meta->hotel_star != ''){
            if( !isset($star[$meta->hotel_star])){

              $star[$meta->hotel_star] = 1;
                       //$regions_name[$item->productCategory->id]['num'] = 1;
          }else{
              $star[$meta->hotel_star]++;
                       //$regions_name[$item->productCategory->id]['num']++;
          }
      }
      if(isset($regions_name[$item->productCategory->id]['num']))
         $regions_name[$item->productCategory->id]['num']++;
     else
         $regions_name[$item->productCategory->id]['num'] = 1;
     $regions_name[$item->productCategory->id]['title'] = $item->productCategory->title;
     $regions_name[$item->productCategory->id]['slug'] = $item->productCategory->slug;
 }
}
//dd($region);
return [
'star' => $star,
'region' => $regions_name
];
}
}

public function getFilterByRegion($region_slug = null){
    if(!is_null($region_slug)){
        $region = $this->productCategory->findBySlug($region_slug, 'hotel'); 
        $items = $this->product->where('product_category_id', $region->id)
        ->with(['productCategory', 'productMetas', 'productSkus'])->get();
        if($items){
            $star = [];
           //$regions_name = [];
            foreach ($items as $item) {
              $meta = productMeta($item->productMetas);
              if(!isset($star[$meta->hotel_star])){
                $star[$meta->hotel_star] = 1;
                   //$regions_name[$item->productCategory->id]['num'] = 1;
            }else{
                $star[$meta->hotel_star]++;
                   //$regions_name[$item->productCategory->id]['num']++;
            }
               //$regions_name[$item->productCategory->id]['title'] = $item->productCategory->title;
               //$regions_name[$item->productCategory->id]['slug'] = $item->productCategory->slug;
        }
    }
    return [
    'star' => $star
    ];
}
}

public function allByCity($city_slug = null, $paginate = false, $page = 1, $limit = 10, $filter = array(), $sortBy = ''){
    if(!is_null($city_slug)){
        $post_type = $filter['post_type'];

        $arr_city = explode(',', $city_slug);

        //dd($arr_city);

        if(is_array($arr_city))
        {
          $regions_raw = $this->productCategory->findBySlugs($arr_city, $post_type);
        }
        else
        {
          //$regions_raw = $this->productCategory->findBySlugs(['shoes', 'bag'], $post_type);
          $regions_raw = $this->productCategory->findBySlug($city_slug, $post_type)->getDescendantsAndSelf();
        }

        //dd($regions_raw);
        $regions = [];
        if(is_array($arr_city))
        {
          foreach ($regions_raw as $region) {
              $region->getDescendantsAndSelf();
              $regions[] = $region->id;
          }
        }
        else
        {
          foreach ($regions_raw as $region) {
              $regions[] = $region->id;
          }
        }

        $q_where = $this->product->whereIn('product_category_id', $regions);

        //\DB::enableQueryLog();

        $q = $q_where->with(['translations', 'productImages', 'productCategory.taxonomyTranslation', 'productMetas.productMetaTranslation', 'productMetas.productMetaTranslations', 'productSkus']);
        //->leftJoin(DB::raw('(SELECT distinct pm.product_id,pmt.meta_value FROM product_metas pm LEFT JOIN product_meta_translations pmt ON pm.id = pmt.product_meta_id WHERE pm.meta_key="hotel_star" ORDER BY pmt.meta_value) as p_m_t'),'products.id', '=', 'p_m_t.product_id')
        //->orderBy('p_m_t.meta_value', 'DESC')
        //->orderBy('created_at', 'DESC');
        
        //$q->get(); //hanya untuk get last query
        //dd(\DB::getQueryLog());
        //dd($sortBy);
        if($sortBy != ''){
          foreach(explode(',', $sortBy) as $sort){
            $sort_split = explode('_', $sort);
            switch ($sort_split[0]) {
              case 'star': 
                $q->leftJoin(DB::raw('(SELECT distinct pm.product_id,pmt.meta_value FROM product_metas pm LEFT JOIN product_meta_translations pmt ON pm.id = pmt.product_meta_id WHERE pm.meta_key="hotel_star" ORDER BY pmt.meta_value) as p_m_t'),'products.id', '=', 'p_m_t.product_id')
                ->orderBy('p_m_t.meta_value', $sort_split[1]);
              break;
              case 'price': 

/*SELECT *,p_vars.min_price  FROM `products` LEFT JOIN (
SELECT ps.product_id, MIN(ps.price) AS min_price
FROM product_variations pv LEFT JOIN `product_skus` ps ON pv.product_sku_id = ps.id
#where ps.product_id in('395','368') 
GROUP BY ps.product_id
) AS p_vars ON `products`.`id` = `p_vars`.`product_id` 
WHERE `product_category_id` IN (44)  ORDER BY `p_vars`.`min_price` DESC,`created_at` DESC*/

                $q->leftJoin(DB::raw('(SELECT ps.product_id, MIN(ps.price) AS min_price FROM product_variations pv LEFT JOIN product_skus ps ON pv.product_sku_id = ps.id GROUP BY ps.product_id) as p_vars'),'products.id', '=', 'p_vars.product_id')
                ->orderBy('p_vars.min_price', $sort_split[1]);
              break;

            }
          }
        }
        $q->orderBy('created_at', 'DESC');
        //dd($q);
        //dd($filter);
        if(is_array($filter)){
            foreach($filter as $ff => $term){
              if(trim($term) != ''){
                switch ($ff) {
                  case 'star': 
                    $q->whereHas('productMetas', function($q1) use ($term) {
                      $q1->where('meta_key', 'hotel_star')
                      ->whereHas('productMetaTranslation', function($q2) use ($term) {
                        $q2->whereIn('meta_value', explode(',',$term));
                      });
                    });

                  /*$q->load(['productMetas' => function($q3)
                  {   
                    $q3->where('meta_key', 'hotel_star')
                    ->load('productMetaTranslation', function($q4) {
                      $q4->orderBy('meta_value', 'asc');
                    });
                  }]);*/
                  break;
                  case 'region':
                    $q->whereHas('productCategory', function($q1) use ($term) {
                      $q1->whereHas('taxonomyTranslation', function($q2) use ($term) {
                        $q2->whereIn('slug', explode(',',$term));
                      });
                    });
                  break;
              }
          }
      }
  }

  //dd($q);
  if($paginate){ 
    $result = new \StdClass;
    $result->page = $page;
    $result->limit = $limit;
    $result->totalItems = 0;
    $result->items = array();
    $result->totalItems = $q_where->count();
    $products = $q->skip($limit * ($page - 1))->take($limit)->get();


    $result->items = $products->all();
    return $result;
  }

  return $q->get();
}
}

public function allByCityPost($city_slug = null, $paginate = false, $page = 1, $limit = 10, $filter = array(), $sortBy = ''){
if(is_null($city_slug) && $filter['post_type'] == 'merchant'){
        $post_type = $filter['post_type'];

        if(is_null($city_slug))
        {
          $regions_raw = $this->productCategory->findByPost($filter['post_type']); 
        }
        else
        {
          $regions_raw = $this->productCategory->findBySlug($city_slug, $filter['post_type'])->getDescendantsAndSelf(); 
        }
        
        //dd($regions_raw);
        $regions = [];

        if(is_null($filter['country']))
        {
          foreach ($regions_raw as $region) {
            $region->getDescendantsAndSelf();
            $regions[] = $region->id;
          }
        }
        else
        {
          foreach ($regions_raw as $region) {
            $regions[] = $region->id;
          }
        }
        
        $q_where = $this->product->whereIn('product_category_id', $regions);

        //\DB::enableQueryLog();

        $q = $q_where->with(['translations', 'productImages', 'productCategory.taxonomyTranslation', 'productMetas.productMetaTranslation', 'productMetas.productMetaTranslations', 'productSkus']);
        //->leftJoin(DB::raw('(SELECT distinct pm.product_id,pmt.meta_value FROM product_metas pm LEFT JOIN product_meta_translations pmt ON pm.id = pmt.product_meta_id WHERE pm.meta_key="hotel_star" ORDER BY pmt.meta_value) as p_m_t'),'products.id', '=', 'p_m_t.product_id')
        //->orderBy('p_m_t.meta_value', 'DESC')
        //->orderBy('created_at', 'DESC');
        
        //$q->get(); //hanya untuk get last query
        //dd(\DB::getQueryLog());
        //dd($sortBy);
        if($sortBy != ''){
          foreach(explode(',', $sortBy) as $sort){
            $sort_split = explode('_', $sort);
            switch ($sort_split[0]) {
              case 'star': 
                $q->leftJoin(DB::raw('(SELECT distinct pm.product_id,pmt.meta_value FROM product_metas pm LEFT JOIN product_meta_translations pmt ON pm.id = pmt.product_meta_id WHERE pm.meta_key="hotel_star" ORDER BY pmt.meta_value) as p_m_t'),'products.id', '=', 'p_m_t.product_id')
                ->orderBy('p_m_t.meta_value', $sort_split[1]);
              break;
              case 'price': 

/*SELECT *,p_vars.min_price  FROM `products` LEFT JOIN (
SELECT ps.product_id, MIN(ps.price) AS min_price
FROM product_variations pv LEFT JOIN `product_skus` ps ON pv.product_sku_id = ps.id
#where ps.product_id in('395','368') 
GROUP BY ps.product_id
) AS p_vars ON `products`.`id` = `p_vars`.`product_id` 
WHERE `product_category_id` IN (44)  ORDER BY `p_vars`.`min_price` DESC,`created_at` DESC*/

                $q->leftJoin(DB::raw('(SELECT ps.product_id, MIN(ps.price) AS min_price FROM product_variations pv LEFT JOIN product_skus ps ON pv.product_sku_id = ps.id GROUP BY ps.product_id) as p_vars'),'products.id', '=', 'p_vars.product_id')
                ->orderBy('p_vars.min_price', $sort_split[1]);
              break;

            }
          }
        }
        $q->orderBy('created_at', 'DESC');
        //dd($q);
        //dd($filter);
        if(is_array($filter)){
            foreach($filter as $ff => $term){
              if(trim($term) != ''){
                switch ($ff) {
                  case 'star': 
                    $q->whereHas('productMetas', function($q1) use ($term) {
                      $q1->where('meta_key', 'hotel_star')
                      ->whereHas('productMetaTranslation', function($q2) use ($term) {
                        $q2->whereIn('meta_value', explode(',',$term));
                      });
                    });

                  /*$q->load(['productMetas' => function($q3)
                  {   
                    $q3->where('meta_key', 'hotel_star')
                    ->load('productMetaTranslation', function($q4) {
                      $q4->orderBy('meta_value', 'asc');
                    });
                  }]);*/
                  break;
                  case 'region':
                    $q->whereHas('productCategory', function($q1) use ($term) {
                      $q1->whereHas('taxonomyTranslation', function($q2) use ($term) {
                        $q2->whereIn('slug', explode(',',$term));
                      });
                    });
                  break;
              }
          }
      }
  }

  if($paginate){ 
    $result = new \StdClass;
    $result->page = $page;
    $result->limit = $limit;
    $result->totalItems = 0;
    $result->items = array();
    $result->totalItems = $q_where->count();
    $products = $q->skip($limit * ($page - 1))->take($limit)->get();


    $result->items = $products->all();
    return $result;
  }

  return $q->get();
}
}

public function allByCityDate($city_slug = null, $paginate = false, $page = 1, $limit = 10, $filter = array(), $sortBy = ''){
    if(!is_null($city_slug)){
        $post_type = $filter['post_type'];
        $regions_raw = $this->productCategory->findBySlug($city_slug, $post_type)->getDescendantsAndSelf();
        $regions = [];
        foreach ($regions_raw as $region) {
            $regions[] = $region->id;
        }
        $q_where = $this->product->whereIn('product_category_id', $regions);

        //\DB::enableQueryLog();

        $q = $q_where->with(['translations', 'productImages', 'productCategory.taxonomyTranslation', 'productMetas.productMetaTranslation', 'productMetas.productMetaTranslations', 'productSkus']);
        //->leftJoin(DB::raw('(SELECT distinct pm.product_id,pmt.meta_value FROM product_metas pm LEFT JOIN product_meta_translations pmt ON pm.id = pmt.product_meta_id WHERE pm.meta_key="hotel_star" ORDER BY pmt.meta_value) as p_m_t'),'products.id', '=', 'p_m_t.product_id')
        //->orderBy('p_m_t.meta_value', 'DESC')
        //->orderBy('created_at', 'DESC');
        
        //$q->get(); //hanya untuk get last query
        //dd(\DB::getQueryLog());
        //dd($sortBy);
        if($sortBy != ''){
          foreach(explode(',', $sortBy) as $sort){
            $sort_split = explode('_', $sort);
            switch ($sort_split[0]) {
              case 'star': 
                $q->leftJoin(DB::raw('(SELECT distinct pm.product_id,pmt.meta_value FROM product_metas pm LEFT JOIN product_meta_translations pmt ON pm.id = pmt.product_meta_id WHERE pm.meta_key="hotel_star" ORDER BY pmt.meta_value) as p_m_t'),'products.id', '=', 'p_m_t.product_id')
                ->orderBy('p_m_t.meta_value', $sort_split[1]);
              break;
              case 'price': 

/*SELECT *,p_vars.min_price  FROM `products` LEFT JOIN (
SELECT ps.product_id, MIN(ps.price) AS min_price
FROM product_variations pv LEFT JOIN `product_skus` ps ON pv.product_sku_id = ps.id
#where ps.product_id in('395','368') 
GROUP BY ps.product_id
) AS p_vars ON `products`.`id` = `p_vars`.`product_id` 
WHERE `product_category_id` IN (44)  ORDER BY `p_vars`.`min_price` DESC,`created_at` DESC*/

                $q->leftJoin(DB::raw('(SELECT ps.product_id, MIN(ps.price) AS min_price FROM product_variations pv LEFT JOIN product_skus ps ON pv.product_sku_id = ps.id GROUP BY ps.product_id) as p_vars'),'products.id', '=', 'p_vars.product_id')
                ->orderBy('p_vars.min_price', $sort_split[1]);
              break;

            }
          }
        }
        $q->orderBy('created_at', 'DESC');
        //dd($q);
        //dd($filter);
        $date_now = date('Y-m-d');

        if(is_array($filter)){
            foreach($filter as $ff => $term){
              
              if(trim($term) != ''){
                switch ($ff) {
                  case 'star': 
                    $q->whereHas('productMetas', function($q1) use ($term) {
                      $q1->where('meta_key', 'hotel_star')
                      ->whereHas('productMetaTranslation', function($q2) use ($term) {
                        $q2->whereIn('meta_value', explode(',',$term));
                      });
                    });
                  break;
                  case 'start_date': 

                    //dd($date_now);
                    $q->whereHas('productMetas', function($q1) use ($term, $date_now) {
                      $q1->where('meta_key', 'start_date')
                      ->whereHas('productMetaTranslation', function($q2) use ($term, $date_now) {
                        $q2->where('meta_value', '>', $term)->where('meta_value', '>', $date_now);
                      });
                    });
                  
                    //dd($q->get());
                  /*$q->load(['productMetas' => function($q3)
                  {   
                    $q3->where('meta_key', 'hotel_star')
                    ->load('productMetaTranslation', function($q4) {
                      $q4->orderBy('meta_value', 'asc');
                    });
                  }]);*/
                  break;
                  case 'region':
                    $q->whereHas('productCategory', function($q1) use ($term) {
                      $q1->whereHas('taxonomyTranslation', function($q2) use ($term) {
                        $q2->whereIn('slug', explode(',',$term));
                      });
                    });
                  break;
              }
          }
      }
  }

  //dd($q_where);
  if($paginate){ 
    $result = new \StdClass;
    $result->page = $page;
    $result->limit = $limit;
    $result->totalItems = 0;
    $result->items = array();
    $result->totalItems = $q_where->count();
    $products = $q->skip($limit * ($page - 1))->take($limit)->get();


    $result->items = $products->all();
    return $result;
  }

  return $q->get();
}
}

public function allByCityUser($city_slug = null, $paginate = false, $page = 1, $limit = 10, $filter = array(), $sortBy = ''){
    if(!is_null($city_slug)){
        $post_type = $filter['post_type'];
        $regions_raw = $this->productCategory->findBySlugMulti($city_slug, $post_type);
        //dd($regions_raw);
        $regions = [];
        foreach ($regions_raw as $region) {
            $regionParent = $region->getDescendantsAndSelf();
            $regions[] = $region->id;
        }

        $q_where = $this->product->whereIn('product_category_id', $regions)->where('created_by', $filter['user_id'] );
        //$q_where = $this->product->whereIn('product_category_id', $regions);
        //\DB::enableQueryLog();

        $q = $q_where->with(['translations', 'productImages', 'productCategory.taxonomyTranslation', 'productMetas.productMetaTranslation', 'productMetas.productMetaTranslations', 'productSkus']);
        //->leftJoin(DB::raw('(SELECT distinct pm.product_id,pmt.meta_value FROM product_metas pm LEFT JOIN product_meta_translations pmt ON pm.id = pmt.product_meta_id WHERE pm.meta_key="hotel_star" ORDER BY pmt.meta_value) as p_m_t'),'products.id', '=', 'p_m_t.product_id')
        //->orderBy('p_m_t.meta_value', 'DESC')
        //->orderBy('created_at', 'DESC');
        
        //$q->get(); //hanya untuk get last query
        //dd(\DB::getQueryLog());
        //dd($q->get());

        if($sortBy != ''){
          foreach(explode(',', $sortBy) as $sort){
            $sort_split = explode('_', $sort);
            switch ($sort_split[0]) {
              case 'star': 
                $q->leftJoin(DB::raw('(SELECT distinct pm.product_id,pmt.meta_value FROM product_metas pm LEFT JOIN product_meta_translations pmt ON pm.id = pmt.product_meta_id WHERE pm.meta_key="hotel_star" ORDER BY pmt.meta_value) as p_m_t'),'products.id', '=', 'p_m_t.product_id')
                ->orderBy('p_m_t.meta_value', $sort_split[1]);
              break;
              case 'price': 

                $q->leftJoin(DB::raw('(SELECT ps.product_id, MIN(ps.price) AS min_price FROM product_variations pv LEFT JOIN product_skus ps ON pv.product_sku_id = ps.id GROUP BY ps.product_id) as p_vars'),'products.id', '=', 'p_vars.product_id')
                ->orderBy('p_vars.min_price', $sort_split[1]);
              break;
            }
          }
        }
        $q->orderBy('created_at', 'DESC');
        //dd($filter);
        if(is_array($filter)){
            foreach($filter as $ff => $term){
              //dd($ff);
              if(!is_array($term)){
                if(trim($term) != ''){
                  switch ($ff) {
                    case 'star': 
                      $q->whereHas('productMetas', function($q1) use ($term) {
                        $q1->where('meta_key', 'hotel_star')
                        ->whereHas('productMetaTranslation', function($q2) use ($term) {
                          $q2->whereIn('meta_value', explode(',',$term));
                        });
                      });

                    break;
                    case 'region':
                      $q->whereHas('productCategory', function($q1) use ($term) {
                        $q1->whereHas('taxonomyTranslation', function($q2) use ($term) {
                          $q2->whereIn('slug', explode(',',$term));
                        });
                      });
                    break;
                    case 'start_date': 
                      $q->whereHas('productMetas', function($q1) use ($term) {
                        $q1->where('meta_key', 'start_date')
                        ->whereHas('productMetaTranslation', function($q2) use ($term) {
                          $q2->where('meta_value', '>', $term);
                        });
                      });
                    break;
                  }
                }
              }
      }
  }


  if($paginate){ 
    $result = new \StdClass;
    $result->page = $page;
    $result->limit = $limit;
    $result->totalItems = 0;
    $result->items = array();
    $result->totalItems = $q_where->count();
    $products = $q->skip($limit * ($page - 1))->take($limit)->get();


    $result->items = $products->all();
    return $result;
  }
  return $q->get();
}
}

/**
* @param $id
* @return mixed
*/
public function find($id) {
  return $this->product->with(['productMetas.productMetaTranslation', 'productMetas.productMetaTranslations', 'productCategory.taxonomyTranslation', 'productVariations.productAttributeOption.productAttributeOptionTranslation', 'productSkus.productVariation', 'productImages'])->find($id);
   //return $this->article->with(['tags', 'category'])->findOrFail($id);
}

public function featuredProducts() {
  return $this->product
  ->with(['productCategory.taxonomyTranslation', 'productVariations', 'productSkus'])
  ->where('is_featured', true)
  ->get();
   //return $this->article->with(['tags', 'category'])->findOrFail($id);
}

public function findAllByTitleLike($slug, $post_type){
  if($post_type == 'trip')
  {
    $start_date = date('Y-m-d');

    return $this->product->with(['productTranslation', 'productCategory', 'productMetas', 'productMetas.productMetaTranslations'])->where('post_type', $post_type)->whereHas('productTranslation', function($q) use ($slug){
        $q->where('title', 'like', '%'.$slug.'%');
    })->whereHas('productMetas', function($q1) use ($start_date) {
      $q1->where('meta_key', 'start_date')
      ->whereHas('productMetaTranslations', function($q2) use ($start_date) {
          $q2->where('meta_value', '>', $start_date);
      });
    })->get();
  }
  else
  {
    return $this->product->with(['productTranslation', 'productCategory'])->where('post_type', $post_type)->whereHas('productTranslation', function($q) use ($slug){
        $q->where('title', 'like', '%'.$slug.'%');
    })->get();
  }
  
}

public function findAllByTitle($title, $post_type){
  return $this->product->with(['productTranslation', 'productCategory'])->where('post_type', $post_type)->whereHas('productTranslation', function($q) use ($title){
    $q->where('title', $title);
})->first();
}

public function findWithVariations($id) {
  $product = $this->find($id);
  $product_skus = $product->productSkus;

   //grouping product variations by product_sku_id
  $product_variations = $product->productVariations;  
  $product_variation_by_sku = [];
  foreach($product_variations as $variation){
    $product_attribute_option = $this->productAttributeOption->find($variation->product_attribute_option_id);
       //$variations[$idx]['product_attribute_name'] = $this->productAttribute->find($variation->product_attribute_id)->name;
       //$attr[$variation->sku_id]['attribute'][$product_attribute_option->productAttribute->name] = $product_attribute_option->name;
       //$variations[$sku->id]['attribute']['product_attribute_option_name'] = $product_attribute_option->name;
       //$product_variation_by_sku[$variation->product_sku_id][] = $product_attribute_option;
    $product_variation_by_sku[$variation->product_sku_id][$product_attribute_option->productAttribute->name] = $product_attribute_option->name;
}
   //dd($product_variation_by_sku);

$variations = [];
foreach($product_skus as $idx => $sku){
    $variations[$sku->id]['price'] = $sku->price;
    $variations[$sku->id]['weekend_price'] = $sku->weekend_price;
    $variations[$sku->id]['qty'] = $sku->qty;
    $variations[$sku->id]['attributes'] = $product_variation_by_sku[$sku->id];

}
$product->variations = $variations;
   //dd($variations);
return $product;
}

public function findWithVariations2($id) {
   $product = $this->find($id); //dd($product);
   $product_skus = $product->productSkus;
   //dd($product_skus);
   //grouping product variations by product_sku_id
   $product_variations = $product->productVariations;
   $product_variation_by_sku = [];
   foreach($product_variations as $variation){ 
       $product_attribute_option = $variation->productAttributeOption;//$this->productAttributeOption->find($variation->product_attribute_option_id);
       $product_variation_by_sku[$variation->product_sku_id][$product_attribute_option->productAttribute->id]['id'] = $product_attribute_option->id;
       $product_variation_by_sku[$variation->product_sku_id][$product_attribute_option->productAttribute->id]['val'] = $product_attribute_option->name;
   }
   //dd($product_variation_by_sku);

   $variations = [];
   foreach($product_skus as $idx => $sku){ 
       $variations[$sku->id]['variation_id'] = $sku->productVariation->id;
       $variations[$sku->id]['price'] = $sku->price;
       $variations[$sku->id]['weekend_price'] = $sku->weekend_price;
       $variations[$sku->id]['qty'] = $sku->qty;
       $variations[$sku->id]['room_image'] = $sku->room_image;
       $variations[$sku->id]['room_info'] = $sku->room_info;
       $variations[$sku->id]['attributes'] = $product_variation_by_sku[$sku->id];

   }
   $product->variations = $variations;
   //dd($variations);
   return $product;
}

public function getBySlugWithVariations($slug) {
  $product = $this->product->with(['productTranslation', 'productMetas', 'productImages', 'productVariations.productSku', 'productVariations.productAttributeOption.productAttributeOptionTranslation'])->whereHas('productTranslation', function($q) use ($slug){
    $q->where('slug', $slug)
    ->where('locale', getLang());
})->firstOrFail();
   //dd($product);
   /*$product_skus = $product->productSkus;
   //dd($product_skus);
   //grouping product variations by product_sku_id
   $product_variations = $product->productVariations;  //dd($product_variations);
   $product_variation_by_sku = [];
   foreach($product_variations as $variation){
       $product_attribute_option = $this->productAttributeOption->find($variation->product_attribute_option_id);
       //dd($product_attribute_option);
       $product_variation_by_sku[$variation->product_sku_id][$product_attribute_option->productAttribute->product_attribute_key] = $product_attribute_option->name;
   }
   //dd($product_variation_by_sku);
   $variations = [];//ini sebenernya adalah grouping SKU , cuman penamaannya saja pake variation
   foreach($product_skus as $idx => $sku){
       $variations[$sku->id] = $sku;
       //$variations[$sku->id]['price'] = $sku->price;
       //$variations[$sku->id]['qty'] = $sku->qty;
       //$variations[$sku->id]['room_image'] = $sku->room_image;
       //$variations[$sku->id]['room_info'] = $sku->room_info;
       $variations[$sku->id]['attrs'] = $product_variation_by_sku[$sku->id];
       
   }*/
   //dd($variations);
   //$product->variations = $product_variations;//$variations;
   $product->productMeta = productMeta($product->productMetas);
   

   //dd($product);
   return $product;
}

/**
* @param $slug
* @return mixed
*/
/*public function getBySlug($slug) {
   //return $this->article->with(['tags', 'category'])->where('slug', $slug)->first();
   return $this->article->with(['category'])->where('slug', $slug)->first();
}*/
public function getBySlug($slug, $isPublished = false) {
  if($isPublished === true)
     return $this->product->select('products.id', 'product_translations.slug')
 ->join('product_translations', 'products.id', '=', 'product_translations.product_id')
 ->where('slug', $slug)->where('is_published', true)->firstOrFail();

 return $this->product->select('products.id', 'product_translations.slug')
 ->join('product_translations', 'products.id', '=', 'product_translations.product_id')
 ->where('slug', $slug)->firstOrFail();
}



/**
* @param $attributes
* @return bool|mixed
* @throws \App\Exceptions\Validation\ValidationException
*/

public function create($attributes) {
  switch ($attributes['post_type']) {
    case 'hotel':
    $this->create_hotel($attributes);
    break;
    case 'playground':
    $this->create_playground($attributes);
    break;
    case 'trip':
    $this->create_trip($attributes);
    break;
    case 'merchant':
    $this->create_merchant($attributes);
    break;
}

}

function create_hotel($attributes){
   //separate variation & sku post
 $variant = [];
 $variants = [];
 $sku = [];
 $skus = [];

 $product_images = [];
   //dd($attributes);
 if(isset($attributes['variant_datarow'])){ 
   $variant_datarow = buildPOST_fromJS($attributes['variant_datarow']);
       //dd($variant_datarow);
   foreach($variant_datarow as $datarow){
     foreach($datarow as $ff_name => $ff){
       $pos = strpos($ff_name, 'variant');
       if ($pos === false) {
         $sku[$ff_name] = $ff['val'];

                   //is image
         if(in_array($ff_name, [ 'room_image' ]))
           $sku[$ff_name] = getImagePath($ff['val']);

                   //is money
       if(in_array($ff_name, [ 'price', 'weekend_price' ]))
           $sku[$ff_name] = str_replace(',', '.', $ff['val']);

   }else{

                   preg_match_all("/\[([^\]]*)\]/", $ff_name, $attr_id);//attr id
                   //debug
                   //if($ff_name == 'additional_information')
                    //   dd($attr_id);
                   $variant[$attr_id[1][0]] = $ff['val'];
               }
           }
           $skus[] = $sku;
           $variants[] = $variant;
       }
   }
   //dd($skus);

   if($this->isValid($attributes)) {
       //$t_attributes['title'] = $attributes['title'];
       //$t_attributes['slug'] = $attributes['slug'];
      $t_attributes['post_type'] = $attributes['post_type'];
      $t_attributes['product_category_id'] = $attributes['product_category'];
      $t_attributes['is_published'] = isset($attributes['is_published']) ? true : false;
      $t_attributes['created_by'] = Auth::user()->id;
      
      if(is_null(Input::get('price_single')) && empty($skus) || !empty($skus)){
        $t_attributes['price'] = 0;
        $t_attributes['weekend_price'] = 0;
        $t_attributes['qty'] = 0;
    }else{
        $t_attributes['price'] = $attributes['price_single'];
        $t_attributes['weekend_price'] = $attributes['weekend_price_single'];
        $t_attributes['qty'] = $attributes['qty_single'];
    }

    foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
        $t_attributes[$locale] = [
               'title' => $attributes['title'],//[$locale],
               'slug' => $attributes['slug'],//[$locale],
               'body' => $attributes['body'][$locale],
               'meta_keywords' => $attributes['meta_keywords'][$locale],
               'meta_description' => $attributes['meta_description'][$locale]
               ];

           /*$t_meta_attributes[$locale] = [
               'hotel_address' => $attributes['hotel_address'][$locale]
               ];*/
           }
           $product = $this->product->create($t_attributes);

       //save product meta

           $product_meta_keys = [ 'hotel_address', 'hotel_main_facilities', 'hotel_star', 'hotel_latitude', 'hotel_longitude' ];
           $t_product_meta_keys = [ 'hotel_main_facilities' ];
           foreach($product_meta_keys as $meta_key){
               $t_metaAttributes['post_type'] = $attributes['post_type'];
               $t_metaAttributes['product_id'] = $product->id;
               $t_metaAttributes['meta_key'] = $meta_key;
           //reset t_meta
               foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
                 unset($t_metaAttributes[$locale]);
             }
           //if(in_array($meta_key, $t_product_meta_keys)){
               //has translations
             foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
                 $t_metaAttributes[$locale] = [
                 'meta_value' => in_array($meta_key, $t_product_meta_keys)?$attributes[$meta_key][$locale]:$attributes[$meta_key]
                 ];
             }
           //}else{
           //    $t_metaAttributes['meta_value'] = $attributes[$meta_key];
           //}
             $this->productMeta->create($t_metaAttributes);
         }

       //save product images
         if(isset($attributes['product_image_datarow'])){
            $product_image_datarow = buildPOST_fromJS($attributes['product_image_datarow']);
           //dd($product_image_datarow);
            foreach($product_image_datarow as $datarow){
               //$foo = '<img class="foo bar test" title="test image" src="http://example.com/img/image.jpg" alt="test image" width="100" height="100" />';
              $image_path = [];
               //preg_match( '/src="([^"]*)"/i', $foo, $array ) ;
               //print_r( $array[1] ) ;
              $image_path_raw = $datarow['product_image']['val'];
               //preg_match( '/src="([^"]*)"/i', $image_path_raw, $image_path ) ;
              $product_images[] = [
              'post_type' => $attributes['post_type'],
              'product_id' => $product->id,
                   'image_path' => getImagePath($image_path_raw)//str_replace(url('/').'/', '', $image_path[1] )
                   ];
               }
               $this->productImage->insert($product_images);
           }

       //save sku
       //$sku_attributes
           if(count($skus)){
              $variant_attributes_count = 0;
               foreach($skus as $idx => $sku_attributes){//dd($sku_attributes);
                $sku_attributes['product_id'] = $product->id;
                $sku_id = ProductSku::create($sku_attributes)->id;
                foreach($variants[$idx] as $product_attribute_id => $product_attribute_option_id){
                  $variant_attributes[$variant_attributes_count]['product_id'] = $product->id;
                  $variant_attributes[$variant_attributes_count]['product_sku_id'] = $sku_id;
                  $variant_attributes[$variant_attributes_count]['product_attribute_id'] = $product_attribute_id;
                  $variant_attributes[$variant_attributes_count]['product_attribute_option_id'] = $product_attribute_option_id;
                  $variant_attributes_count++;
              }
          }
          ProductVariation::insert($variant_attributes);
      }
      return true;
  }
  throw new ValidationException('Product validation failed', $this->getErrors());
}

function create_playground($attributes){
   //separate variation & sku post
    $variant = [];
    $variants = [];
    $sku = [];
    $skus = [];

    $product_images = [];
   //dd($attributes);
    if(isset($attributes['variant_datarow'])){ 
      $variant_datarow = buildPOST_fromJS($attributes['variant_datarow']);
       //dd($variant_datarow);
      foreach($variant_datarow as $datarow){
        foreach($datarow as $ff_name => $ff){
          $pos = strpos($ff_name, 'variant');
          if ($pos === false) {
            $sku[$ff_name] = $ff['val'];

                   //is image
                    //   if(in_array($ff_name, [ 'room_image' ]))
                     //      $sku[$ff_name] = getImagePath($ff['val']);

                   //is money
            if(in_array($ff_name, [ 'price', 'weekend_price' ]))
              $sku[$ff_name] = str_replace(',', '.', $ff['val']);

      }else{

                   preg_match_all("/\[([^\]]*)\]/", $ff_name, $attr_id);//attr id
                   //debug
                   //if($ff_name == 'additional_information')
                    //   dd($attr_id);
                   $variant[$attr_id[1][0]] = $ff['val'];
               }
           }
           $skus[] = $sku;
           $variants[] = $variant;
       }
   }
   //dd($variants);

   if($this->isValid($attributes)) {
       //$t_attributes['title'] = $attributes['title'];
       //$t_attributes['slug'] = $attributes['slug'];
      $t_attributes['post_type'] = $attributes['post_type'];
      $t_attributes['product_category_id'] = $attributes['product_category'];
      $t_attributes['is_published'] = isset($attributes['is_published']) ? true : false;
      $t_attributes['created_by'] = Auth::user()->id;
      if(is_null(Input::get('price_single')) && empty($skus) || !empty($skus)){
        $t_attributes['price'] = 0;
        $t_attributes['weekend_price'] = 0;
        $t_attributes['qty'] = 0;
    }else{
        $t_attributes['price'] = $attributes['price_single'];
        $t_attributes['weekend_price'] = $attributes['weekend_price_single'];
        $t_attributes['qty'] = $attributes['qty_single'];
    }

    foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
        $t_attributes[$locale] = [
               'title' => $attributes['title'],//[$locale],
               'slug' => $attributes['slug'],//[$locale],
               'body' => $attributes['body'][$locale],
               'meta_keywords' => $attributes['meta_keywords'][$locale],
               'meta_description' => $attributes['meta_description'][$locale]
               ];

           /*$t_meta_attributes[$locale] = [
               'hotel_address' => $attributes['hotel_address'][$locale]
               ];*/
           }
           $product = $this->product->create($t_attributes);

       //save product meta

           $product_meta_keys = [ 'playground_address', 'playground_latitude', 'playground_longitude' ];
           $t_product_meta_keys = [];
           foreach($product_meta_keys as $meta_key){
               $t_metaAttributes['post_type'] = $attributes['post_type'];
               $t_metaAttributes['product_id'] = $product->id;
               $t_metaAttributes['meta_key'] = $meta_key;
           //reset t_meta
               foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
                 unset($t_metaAttributes[$locale]);
             }
           //if(in_array($meta_key, $t_product_meta_keys)){
               //has translations
             foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
                 $t_metaAttributes[$locale] = [
                 'meta_value' => in_array($meta_key, $t_product_meta_keys)?$attributes[$meta_key][$locale]:$attributes[$meta_key]
                 ];
             }
           //}else{
           //    $t_metaAttributes['meta_value'] = $attributes[$meta_key];
           //}
             $this->productMeta->create($t_metaAttributes);
         }

       //save product images
         if(isset($attributes['product_image_datarow'])){
            $product_image_datarow = buildPOST_fromJS($attributes['product_image_datarow']);
           //dd($product_image_datarow);
            foreach($product_image_datarow as $datarow){
               //$foo = '<img class="foo bar test" title="test image" src="http://example.com/img/image.jpg" alt="test image" width="100" height="100" />';
              $image_path = [];
               //preg_match( '/src="([^"]*)"/i', $foo, $array ) ;
               //print_r( $array[1] ) ;
              $image_path_raw = $datarow['product_image']['val'];
               //preg_match( '/src="([^"]*)"/i', $image_path_raw, $image_path ) ;
              $product_images[] = [
              'post_type' => $attributes['post_type'],
              'product_id' => $product->id,
                   'image_path' => getImagePath($image_path_raw)//str_replace(url('/').'/', '', $image_path[1] )
                   ];
               }
               $this->productImage->insert($product_images);
           }


       //save sku
       //$sku_attributes
           if(count($skus)){
              $variant_attributes_count = 0;
           foreach($skus as $idx => $sku_attributes){//dd($sku_attributes);
              $sku_attributes['product_id'] = $product->id;
              $sku_id = ProductSku::create($sku_attributes)->id;
              foreach($variants[$idx] as $product_attribute_id => $product_attribute_option_id){
                $variant_attributes[$variant_attributes_count]['product_id'] = $product->id;
                $variant_attributes[$variant_attributes_count]['product_sku_id'] = $sku_id;
                $variant_attributes[$variant_attributes_count]['product_attribute_id'] = $product_attribute_id;
                $variant_attributes[$variant_attributes_count]['product_attribute_option_id'] = $product_attribute_option_id;
                $variant_attributes_count++;
            }
        }
        ProductVariation::insert($variant_attributes);
    }
    return true;
}
throw new ValidationException('Product validation failed', $this->getErrors());
}

function create_trip($attributes){
   //separate variation & sku post
    $variant = [];
    $variants = [];
    $sku = [];
    $skus = [];

    $product_images = [];
   //dd($attributes);
    if(isset($attributes['variant_datarow'])){ 
      $variant_datarow = buildPOST_fromJS($attributes['variant_datarow']);
       //dd($variant_datarow);
      foreach($variant_datarow as $datarow){
        foreach($datarow as $ff_name => $ff){
          $pos = strpos($ff_name, 'variant');
          if ($pos === false) {
            $sku[$ff_name] = $ff['val'];

                   //is image
                    //   if(in_array($ff_name, [ 'room_image' ]))
                     //      $sku[$ff_name] = getImagePath($ff['val']);

                   //is money
            if(in_array($ff_name, [ 'price', 'weekend_price' ]))
              $sku[$ff_name] = str_replace(',', '.', $ff['val']);

      }else{

                   preg_match_all("/\[([^\]]*)\]/", $ff_name, $attr_id);//attr id
                   //debug
                   //if($ff_name == 'additional_information')
                    //   dd($attr_id);
                   $variant[$attr_id[1][0]] = $ff['val'];
               }
           }
           $skus[] = $sku;
           $variants[] = $variant;
       }
   }
   //dd($variants);

   if($this->isValid($attributes)) {
       //$t_attributes['title'] = $attributes['title'];
       //$t_attributes['slug'] = $attributes['slug'];
      $t_attributes['post_type'] = $attributes['post_type'];
      $t_attributes['product_category_id'] = $attributes['product_category'];
      $t_attributes['is_published'] = isset($attributes['is_published']) ? true : false;
      $t_attributes['created_by'] = Auth::user()->id;
      if(is_null(Input::get('price_single')) && empty($skus) || !empty($skus)){
        $t_attributes['price'] = 0;
        $t_attributes['weekend_price'] = 0;
        $t_attributes['qty'] = 0;
    }else{
        $t_attributes['price'] = $attributes['price_single'];
        $t_attributes['weekend_price'] = $attributes['weekend_price_single']; 
        $t_attributes['qty'] = $attributes['qty_single'];
    }

    foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
        $t_attributes[$locale] = [
               'title' => $attributes['title'],//[$locale],
               'slug' => $attributes['slug'],//[$locale],
               'body' => $attributes['body'][$locale],
               'meta_keywords' => $attributes['meta_keywords'][$locale],
               'meta_description' => $attributes['meta_description'][$locale]
               ];

           /*$t_meta_attributes[$locale] = [
               'hotel_address' => $attributes['hotel_address'][$locale]
               ];*/
           }
           $product = $this->product->create($t_attributes);

       //save product meta

           $product_meta_keys = ['start_date', 'end_date' ];
           $t_product_meta_keys = [];
           foreach($product_meta_keys as $meta_key){
               $t_metaAttributes['post_type'] = $attributes['post_type'];
               $t_metaAttributes['product_id'] = $product->id;
               $t_metaAttributes['meta_key'] = $meta_key;
           //reset t_meta
               foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
                 unset($t_metaAttributes[$locale]);
             }
           //if(in_array($meta_key, $t_product_meta_keys)){
               //has translations
             foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
                 $t_metaAttributes[$locale] = [
                 'meta_value' => in_array($meta_key, $t_product_meta_keys)?$attributes[$meta_key][$locale]:$attributes[$meta_key]
                 ];
             }
           //}else{
           //    $t_metaAttributes['meta_value'] = $attributes[$meta_key];
           //}
             $this->productMeta->create($t_metaAttributes);
         }

       //save product images
         if(isset($attributes['product_image_datarow'])){
            $product_image_datarow = buildPOST_fromJS($attributes['product_image_datarow']);
           //dd($product_image_datarow);
            foreach($product_image_datarow as $datarow){
               //$foo = '<img class="foo bar test" title="test image" src="http://example.com/img/image.jpg" alt="test image" width="100" height="100" />';
              $image_path = [];
               //preg_match( '/src="([^"]*)"/i', $foo, $array ) ;
               //print_r( $array[1] ) ;
              $image_path_raw = $datarow['product_image']['val'];
               //preg_match( '/src="([^"]*)"/i', $image_path_raw, $image_path ) ;
              $product_images[] = [
              'post_type' => $attributes['post_type'],
              'product_id' => $product->id,
                   'image_path' => getImagePath($image_path_raw)//str_replace(url('/').'/', '', $image_path[1] )
                   ];
               }
               $this->productImage->insert($product_images);
           }


       //save sku
       //$sku_attributes
           if(count($skus)){
              $variant_attributes_count = 0;
           foreach($skus as $idx => $sku_attributes){//dd($sku_attributes);
              $sku_attributes['product_id'] = $product->id;
              $sku_id = ProductSku::create($sku_attributes)->id;
              //$variant_attributes[$variant_attributes_count]['product_id'] = $product->id;
              //$variant_attributes[$variant_attributes_count]['product_sku_id'] = $sku_id;
            
              foreach($variants[$idx] as $product_attribute_id => $product_attribute_option_id){
                $variant_attributes[$variant_attributes_count]['product_id'] = $product->id;
                $variant_attributes[$variant_attributes_count]['product_sku_id'] = $sku_id;
                $variant_attributes[$variant_attributes_count]['product_attribute_id'] = $product_attribute_id;
                $variant_attributes[$variant_attributes_count]['product_attribute_option_id'] = $product_attribute_option_id;
                $variant_attributes_count++;
              
            }
            
        }
        ProductVariation::insert($variant_attributes);
    }
    return true;
}
throw new ValidationException('Product validation failed', $this->getErrors());
}

function create_merchant($attributes){
   //separate variation & sku post
    $variant = [];
    $variants = [];
    $sku = [];
    $skus = [];

    $product_images = [];
   //dd($attributes);
    if(isset($attributes['variant_datarow'])){ 
      $variant_datarow = buildPOST_fromJS($attributes['variant_datarow']);
       //dd($variant_datarow);
      foreach($variant_datarow as $datarow){
        foreach($datarow as $ff_name => $ff){
          $pos = strpos($ff_name, 'variant');
          if ($pos === false) {
            $sku[$ff_name] = $ff['val'];

                   //is image
                    //   if(in_array($ff_name, [ 'room_image' ]))
                     //      $sku[$ff_name] = getImagePath($ff['val']);

                   //is money
            if(in_array($ff_name, [ 'price', 'weekend_price' ]))
              $sku[$ff_name] = str_replace(',', '.', $ff['val']);

      }else{

                   preg_match_all("/\[([^\]]*)\]/", $ff_name, $attr_id);//attr id
                   //debug
                   //if($ff_name == 'additional_information')
                    //   dd($attr_id);
                   $variant[$attr_id[1][0]] = $ff['val'];
               }
           }
           $skus[] = $sku;
           $variants[] = $variant;
       }
   }
   //dd($variants);

   if($this->isValid($attributes)) {
       //$t_attributes['title'] = $attributes['title'];
       //$t_attributes['slug'] = $attributes['slug'];
      $t_attributes['post_type'] = $attributes['post_type'];
      $t_attributes['product_category_id'] = $attributes['product_category'];
      $t_attributes['is_published'] = isset($attributes['is_published']) ? true : false;
      $t_attributes['created_by'] = Auth::user()->id;
      if(is_null(Input::get('price_single')) && empty($skus) || !empty($skus)){
        $t_attributes['price'] = 0;
        $t_attributes['weekend_price'] = 0;
        $t_attributes['qty'] = 0;
    }else{
        $t_attributes['price'] = $attributes['price_single'];
        $t_attributes['weekend_price'] = $attributes['weekend_price_single'];
        $t_attributes['qty'] = $attributes['qty_single'];
    }

    foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
        $t_attributes[$locale] = [
               'title' => $attributes['title'],//[$locale],
               'slug' => $attributes['slug'],//[$locale],
               'body' => $attributes['body'][$locale],
               'meta_keywords' => $attributes['meta_keywords'][$locale],
               'meta_description' => $attributes['meta_description'][$locale]
               ];

           /*$t_meta_attributes[$locale] = [
               'hotel_address' => $attributes['hotel_address'][$locale]
               ];*/
           }
           $product = $this->product->create($t_attributes);

       //save product meta
           
           $product_meta_keys = [];
           $t_product_meta_keys = [];
           foreach($product_meta_keys as $meta_key){
               $t_metaAttributes['post_type'] = $attributes['post_type'];
               $t_metaAttributes['product_id'] = $product->id;
               $t_metaAttributes['meta_key'] = $meta_key;
           
               foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
                 unset($t_metaAttributes[$locale]);
             }

           //if(in_array($meta_key, $t_product_meta_keys)){
               //has translations
             foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
                 $t_metaAttributes[$locale] = [
                 'meta_value' => in_array($meta_key, $t_product_meta_keys)?$attributes[$meta_key][$locale]:$attributes[$meta_key]
                 ];
             }
           //}else{
           //    $t_metaAttributes['meta_value'] = $attributes[$meta_key];
           //}
             $this->productMeta->create($t_metaAttributes);
         }

       //save product images
         if(isset($attributes['product_image_datarow'])){
            $product_image_datarow = buildPOST_fromJS($attributes['product_image_datarow']);
           //dd($product_image_datarow);
            foreach($product_image_datarow as $datarow){
               //$foo = '<img class="foo bar test" title="test image" src="http://example.com/img/image.jpg" alt="test image" width="100" height="100" />';
              $image_path = [];
               //preg_match( '/src="([^"]*)"/i', $foo, $array ) ;
               //print_r( $array[1] ) ;
              $image_path_raw = $datarow['product_image']['val'];
               //preg_match( '/src="([^"]*)"/i', $image_path_raw, $image_path ) ;
              $product_images[] = [
              'post_type' => $attributes['post_type'],
              'product_id' => $product->id,
                   'image_path' => getImagePath($image_path_raw)//str_replace(url('/').'/', '', $image_path[1] )
                   ];
               }
               $this->productImage->insert($product_images);
           }


       //save sku
       //$sku_attributes
           if(count($skus)){
              $variant_attributes_count = 0;
           foreach($skus as $idx => $sku_attributes){//dd($sku_attributes);
              $sku_attributes['product_id'] = $product->id;
              $sku_id = ProductSku::create($sku_attributes)->id;
              foreach($variants[$idx] as $product_attribute_id => $product_attribute_option_id){
                $variant_attributes[$variant_attributes_count]['product_id'] = $product->id;
                $variant_attributes[$variant_attributes_count]['product_sku_id'] = $sku_id;
                $variant_attributes[$variant_attributes_count]['product_attribute_id'] = $product_attribute_id;
                $variant_attributes[$variant_attributes_count]['product_attribute_option_id'] = $product_attribute_option_id;
                $variant_attributes_count++;
            }
        }
        ProductVariation::insert($variant_attributes);
    }
    return true;
}
throw new ValidationException('Product validation failed', $this->getErrors());
}

public function update($id, $attributes) {

  switch ($attributes['post_type']) {
    case 'hotel':
    $this->update_hotel($id, $attributes);
    break;
    case 'playground':
    $this->update_playground($id, $attributes);
    break;
    case 'trip':
    $this->update_trip($id, $attributes);
    break;
    case 'merchant':
    $this->update_merchant($id, $attributes);
    break;
}
}

public function update_hotel($id, $attributes) {
   //separate variation & sku post
    $variant = [];
    $variants = [];
    $sku = [];
    $skus = [];
    $still_exist_sku_id = []; 
    if(isset($attributes['variant_datarow'])){
       $variant_datarow = buildPOST_fromJS($attributes['variant_datarow']); //dd($variant_datarow);
       foreach($variant_datarow as $datarow){
         foreach($datarow as $ff_name => $ff){
           $pos = strpos($ff_name, 'variant');
           if ($pos === false) {
             $sku[$ff_name] = $ff['val'];

                   //is image
             if(in_array($ff_name, [ 'room_image' ]))
               $sku[$ff_name] = getImagePath($ff['val']);

                   //is money
           if(in_array($ff_name, [ 'price', 'weekend_price' ]))
               $sku[$ff_name] = str_replace(',', '.', $ff['val']);

                   //sku yang tetap digunakan atau tidak didelete
           if($ff_name == 'sku_id')
               $still_exist_sku_id[] = $sku[$ff_name];
       }else{
                   preg_match_all("/\[([^\]]*)\]/", $ff_name, $attr_id);//attr id
                   $variant[$attr_id[1][0]] = $ff['val'];
               }
           }
           $skus[] = $sku;
           $variants[] = $variant;
       }
   }


   //dd($still_exist_sku_id);
   //dd($skus);
   //dd($variants);
   //dd($this->find($id)->productSkus()->where('product_id', $id)->whereNotIn('id', $still_exist_sku_id)->get());

   //dd(ProductVariation::where('product_id', $id)->whereNotIn('product_sku_id', $still_exist_sku_id)->get());
   //$applied_skus_rawx = $this->find($id)->productVariations()->whereNotIn('product_sku_id', $still_exist_sku_id)->get();
   //dd($applied_skus_rawx);
   //dd($skus);
   if($this->isValid($attributes)) {
      $this->product = $this->find($id);
      $t_attributes['post_type'] = $attributes['post_type'];
      $t_attributes['product_category_id'] = $attributes['product_category'];
      $t_attributes['is_published'] = isset($attributes['is_published']) ? true : false;
      $t_attributes['updated_by'] = Auth::user()->id;

      if(is_null(Input::get('price_single')) && empty($skus) || !empty($skus)){
        $t_attributes['price'] = 0;
        $t_attributes['weekend_price'] = 0;
        $t_attributes['qty'] = 0;
    }else{
        $t_attributes['price'] = $attributes['price_single'];
        $t_attributes['weekend_price'] = $attributes['weekend_price_single'];
        $t_attributes['qty'] = $attributes['qty_single'];
    }

    foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
        $t_attributes[$locale] = [
        'title' => $attributes['title'],
        'slug' => $attributes['slug'],
        'body' => $attributes['body'][$locale],
        'meta_keywords' => $attributes['meta_keywords'][$locale],
        'meta_description' => $attributes['meta_description'][$locale]
        ];
    }
    $product = $this->product->update($t_attributes);

       //save product meta
    $this->product->productMetas()->delete();
    $product_meta_keys = [ 'hotel_address', 'hotel_main_facilities', 'hotel_star', 'hotel_latitude', 'hotel_longitude' ];
    $t_product_meta_keys = [ 'hotel_main_facilities' ];
    foreach($product_meta_keys as $meta_key){
        $t_metaAttributes['post_type'] = $attributes['post_type'];
        $t_metaAttributes['product_id'] = $id;
        $t_metaAttributes['meta_key'] = $meta_key;
           //reset t_meta
        foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
          unset($t_metaAttributes[$locale]);
      }

           //has translations
      foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
          $t_metaAttributes[$locale] = [
          'meta_value' => in_array($meta_key, $t_product_meta_keys)?$attributes[$meta_key][$locale]:$attributes[$meta_key]
          ];
      }
      $this->productMeta->create($t_metaAttributes);
  }

  $this->product->productSkus()->where('product_id', $id)->update(['is_deleted' => 1 ]);

  $applied_skus_raw = $this->product->productSkus()->where('product_id', $id)->whereNotIn('id', $still_exist_sku_id)->get();
  $remove_skus = [];
       //remove product_variations yg berhubungan dengan sku yg akan diremove ini.
       //sebab jika tidak, maka akan error jika langsung hapus sku nya
  foreach($applied_skus_raw as $applied_sku){
     $remove_skus[] = $applied_sku->id;
 }
       //dd($remove_skus);
 if(count($skus)){
     foreach($skus as $idx => $sku_attributes){
       $sku_attributes['product_id'] = $id;
       $sku_id = ProductSku::create($sku_attributes)->id;
       foreach($variants[$idx] as $product_attribute_id => $product_attribute_option_id){
                   $variant_attributes = [];//reset array
                   $variant_attributes['product_id'] = $id;
                   $variant_attributes['product_sku_id'] = $sku_id;
                   $variant_attributes['product_attribute_id'] = $product_attribute_id;
                   $variant_attributes['product_attribute_option_id'] = $product_attribute_option_id;
                   
                   if(isset($sku_attributes['sku_id']) && $sku_attributes['sku_id'] != '' ){
                       //karena bisa saja lebih dari satu attribute, tidak seperti hotel yang hanya satu attribute
                       //$prodVariations = ProductVariation::where('product_sku_id', $sku_attributes['sku_id'])->get();
                      // if($prodVariations){
                           //foreach ($prodVariations as $prodVariation) {
                               //$variant_attributes['id'] = $prodVariation->id;
                               //$prodVariation = ProductVariation::find($sku_attributes['variation_id']);
                     $prodVariation = ProductVariation::where([ 
                       'product_sku_id' => $sku_attributes['sku_id'], 
                       'product_attribute_id' => $product_attribute_id ])->first();
                     if(!is_null($prodVariation)){
                       $variant_attributes['id'] = $prodVariation->id;
                       $prodVariation->fill($variant_attributes);
                       $prodVariation->save();
                   }else{
                       ProductVariation::insert($variant_attributes);
                   }
                        //   }
                       //}
               }else{
                  ProductVariation::insert($variant_attributes);
              }
          }
      }

  }
       ProductVariation::where('product_id', $id)->whereIn('product_sku_id', $remove_skus)->delete();//harus ini hapus duluan, kalo tidak akan terjadi error
       $this->product->productSkus()->where('is_deleted', 1)->delete();

       //save product images
       $this->product->productImages()->delete($id);
       if(isset($attributes['product_image_datarow'])){
         $product_image_datarow = buildPOST_fromJS($attributes['product_image_datarow']);
         foreach($product_image_datarow as $datarow){
           $image_path = [];
           $image_path_raw = $datarow['product_image']['val'];

           $product_images[] = [
           'post_type' => $attributes['post_type'],
           'product_id' => $id,
           'image_path' => getImagePath($image_path_raw)
           ];
       }
       $this->productImage->insert($product_images);
   }
   return true;
}
throw new ValidationException('Product validation failed', $this->getErrors());
}

public function update_playground($id, $attributes) {
   //separate variation & sku post
  $variant = [];
  $variants = [];
  $sku = [];
  $skus = [];
  $still_exist_sku_id = []; 
  if(isset($attributes['variant_datarow'])){
       $variant_datarow = buildPOST_fromJS($attributes['variant_datarow']); //dd($variant_datarow);
       foreach($variant_datarow as $datarow){
         foreach($datarow as $ff_name => $ff){
           $pos = strpos($ff_name, 'variant');
           if ($pos === false) {
             $sku[$ff_name] = $ff['val'];

                   //is image
                   //if(in_array($ff_name, [ 'room_image' ]))
                   //    $sku[$ff_name] = getImagePath($ff['val']);

                   //is money
             if(in_array($ff_name, [ 'price', 'weekend_price' ]))
               $sku[$ff_name] = str_replace(',', '.', $ff['val']);

                   //sku yang tetap digunakan atau tidak didelete
           if($ff_name == 'sku_id')
               $still_exist_sku_id[] = $sku[$ff_name];
       }else{
                   preg_match_all("/\[([^\]]*)\]/", $ff_name, $attr_id);//attr id
                   $variant[$attr_id[1][0]] = $ff['val'];
               }
           }
           $skus[] = $sku;
           $variants[] = $variant;
       }
   }

   if($this->isValid($attributes)) {
      $this->product = $this->find($id);
      $t_attributes['post_type'] = $attributes['post_type'];
      $t_attributes['product_category_id'] = $attributes['product_category'];
      $t_attributes['is_published'] = isset($attributes['is_published']) ? true : false;
      $t_attributes['updated_by'] = Auth::user()->id;

      if(is_null(Input::get('price_single')) && empty($skus) || !empty($skus)){
        $t_attributes['price'] = 0;
        $t_attributes['weekend_price'] = 0;
        $t_attributes['qty'] = 0;
    }else{
        $t_attributes['price'] = $attributes['price_single'];
        $t_attributes['weekend_price'] = $attributes['weekend_price_single'];
        $t_attributes['qty'] = $attributes['qty_single'];
    }

    foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
        $t_attributes[$locale] = [
        'title' => $attributes['title'],
        'slug' => $attributes['slug'],
        'body' => $attributes['body'][$locale],
        'meta_keywords' => $attributes['meta_keywords'][$locale],
        'meta_description' => $attributes['meta_description'][$locale]
        ];
    }
    $product = $this->product->update($t_attributes);

       //save product meta
    $this->product->productMetas()->delete();
    $product_meta_keys = [ 'playground_address', 'playground_latitude', 'playground_longitude' ];
    $t_product_meta_keys = [];
    foreach($product_meta_keys as $meta_key){
        $t_metaAttributes['post_type'] = $attributes['post_type'];
        $t_metaAttributes['product_id'] = $id;
        $t_metaAttributes['meta_key'] = $meta_key;
           //reset t_meta
        foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
          unset($t_metaAttributes[$locale]);
      }

           //has translations
      foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
          $t_metaAttributes[$locale] = [
          'meta_value' => in_array($meta_key, $t_product_meta_keys)?$attributes[$meta_key][$locale]:$attributes[$meta_key]
          ];
      }
      $this->productMeta->create($t_metaAttributes);
  }

  $this->product->productSkus()->where('product_id', $id)->update(['is_deleted' => 1 ]);

  $applied_skus_raw = $this->product->productSkus()->where('product_id', $id)->whereNotIn('id', $still_exist_sku_id)->get();
  $remove_skus = [];
       //remove product_variations yg berhubungan dengan sku yg akan diremove ini.
       //sebab jika tidak, maka akan error jika langsung hapus sku nya
  foreach($applied_skus_raw as $applied_sku){
     $remove_skus[] = $applied_sku->id;
 }
       //dd($remove_skus);
 if(count($skus)){
     foreach($skus as $idx => $sku_attributes){ 
       $sku_attributes['product_id'] = $id;
       $sku_id = ProductSku::create($sku_attributes)->id;
       foreach($variants[$idx] as $product_attribute_id => $product_attribute_option_id){
                   $variant_attributes = [];//reset array
                   $variant_attributes['product_id'] = $id;
                   $variant_attributes['product_sku_id'] = $sku_id;
                   $variant_attributes['product_attribute_id'] = $product_attribute_id;
                   $variant_attributes['product_attribute_option_id'] = $product_attribute_option_id;
                   
                   if(isset($sku_attributes['sku_id']) && $sku_attributes['sku_id'] != '' ){
                     $prodVariation = ProductVariation::where([ 
                       'product_sku_id' => $sku_attributes['sku_id'], 
                       'product_attribute_id' => $product_attribute_id ])->first();
                     if(!is_null($prodVariation)){
                       $variant_attributes['id'] = $prodVariation->id;
                       $prodVariation->fill($variant_attributes);
                       $prodVariation->save();
                   }else{
                       ProductVariation::insert($variant_attributes);
                   }
                        //   }
                       //}
               }else{
                  ProductVariation::insert($variant_attributes);
              }
          }
      }

  }
       ProductVariation::where('product_id', $id)->whereIn('product_sku_id', $remove_skus)->delete();//harus ini hapus duluan, kalo tidak akan terjadi error
       $this->product->productSkus()->where('is_deleted', 1)->delete();

       //save product images
       $this->product->productImages()->delete($id);
       if(isset($attributes['product_image_datarow'])){
         $product_image_datarow = buildPOST_fromJS($attributes['product_image_datarow']);
         foreach($product_image_datarow as $datarow){
           $image_path = [];
           $image_path_raw = $datarow['product_image']['val'];

           $product_images[] = [
           'post_type' => $attributes['post_type'],
           'product_id' => $id,
           'image_path' => getImagePath($image_path_raw)
           ];
       }
       $this->productImage->insert($product_images);
   }
   return true;
}
throw new ValidationException('Product validation failed', $this->getErrors());
}

public function update_trip($id, $attributes) {
   //separate variation & sku post
  $variant = [];
  $variants = [];
  $sku = [];
  $skus = [];
  $still_exist_sku_id = []; 
  if(isset($attributes['variant_datarow'])){
       $variant_datarow = buildPOST_fromJS($attributes['variant_datarow']); //dd($variant_datarow);
       foreach($variant_datarow as $datarow){
         foreach($datarow as $ff_name => $ff){
           $pos = strpos($ff_name, 'variant');
           if ($pos === false) {
             $sku[$ff_name] = $ff['val'];

                   //is image
                   //if(in_array($ff_name, [ 'room_image' ]))
                   //    $sku[$ff_name] = getImagePath($ff['val']);

                   //is money
             if(in_array($ff_name, [ 'price', 'weekend_price' ]))
               $sku[$ff_name] = str_replace(',', '.', $ff['val']);

                   //sku yang tetap digunakan atau tidak didelete
           if($ff_name == 'sku_id')
               $still_exist_sku_id[] = $sku[$ff_name];
       }else{
                   preg_match_all("/\[([^\]]*)\]/", $ff_name, $attr_id);//attr id
                   $variant[$attr_id[1][0]] = $ff['val'];
               }
           }
           $skus[] = $sku;
           $variants[] = $variant;
       }
   }

   if($this->isValid($attributes)) {
      $this->product = $this->find($id);
      $t_attributes['post_type'] = $attributes['post_type'];
      $t_attributes['product_category_id'] = $attributes['product_category'];
      $t_attributes['is_published'] = isset($attributes['is_published']) ? true : false;
      $t_attributes['updated_by'] = Auth::user()->id;
      if(is_null(Input::get('price_single')) && empty($skus) || !empty($skus)){
        $t_attributes['price'] = 0;
        $t_attributes['weekend_price'] = 0;
        $t_attributes['qty'] = 0;
    }else{
        $t_attributes['price'] = $attributes['price_single'];
        $t_attributes['weekend_price'] = $attributes['weekend_price_single'];
        $t_attributes['qty'] = $attributes['qty_single'];
    }

    foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
        $t_attributes[$locale] = [
        'title' => $attributes['title'],
        'slug' => $attributes['slug'],
        'body' => $attributes['body'][$locale],
        'meta_keywords' => $attributes['meta_keywords'][$locale],
        'meta_description' => $attributes['meta_description'][$locale]
        ];
    }
    $product = $this->product->update($t_attributes);

       //save product meta
    $this->product->productMetas()->delete();
    $product_meta_keys = [ 'start_date', 'end_date' ];
    $t_product_meta_keys = [];
    foreach($product_meta_keys as $meta_key){
        $t_metaAttributes['post_type'] = $attributes['post_type'];
        $t_metaAttributes['product_id'] = $id;
        $t_metaAttributes['meta_key'] = $meta_key;
           //reset t_meta
        foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
          unset($t_metaAttributes[$locale]);
      }

           //has translations
      foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
          $t_metaAttributes[$locale] = [
          'meta_value' => in_array($meta_key, $t_product_meta_keys)?$attributes[$meta_key][$locale]:$attributes[$meta_key]
          ];
      }
      $this->productMeta->create($t_metaAttributes);
  }

  $this->product->productSkus()->where('product_id', $id)->update(['is_deleted' => 1 ]);

  $applied_skus_raw = $this->product->productSkus()->where('product_id', $id)->whereNotIn('id', $still_exist_sku_id)->get();
  $remove_skus = [];
       //remove product_variations yg berhubungan dengan sku yg akan diremove ini.
       //sebab jika tidak, maka akan error jika langsung hapus sku nya
  foreach($applied_skus_raw as $applied_sku){
     $remove_skus[] = $applied_sku->id;
 }
       //dd($remove_skus);
 if(count($skus)){
     foreach($skus as $idx => $sku_attributes){ 
       $sku_attributes['product_id'] = $id;
       $sku_id = ProductSku::create($sku_attributes)->id;
       foreach($variants[$idx] as $product_attribute_id => $product_attribute_option_id){
                   $variant_attributes = [];//reset array
                   $variant_attributes['product_id'] = $id;
                   $variant_attributes['product_sku_id'] = $sku_id;
                   $variant_attributes['product_attribute_id'] = $product_attribute_id;
                   $variant_attributes['product_attribute_option_id'] = $product_attribute_option_id;
                   
                   if(isset($sku_attributes['sku_id']) && $sku_attributes['sku_id'] != '' ){
                     $prodVariation = ProductVariation::where([ 
                       'product_sku_id' => $sku_attributes['sku_id'], 
                       'product_attribute_id' => $product_attribute_id ])->first();
                     if(!is_null($prodVariation)){
                       $variant_attributes['id'] = $prodVariation->id;
                       $prodVariation->fill($variant_attributes);
                       $prodVariation->save();
                   }else{
                       ProductVariation::insert($variant_attributes);
                   }
                        //   }
                       //}
               }else{
                  ProductVariation::insert($variant_attributes);
              }
          }
      }

  }
       ProductVariation::where('product_id', $id)->whereIn('product_sku_id', $remove_skus)->delete();//harus ini hapus duluan, kalo tidak akan terjadi error
       $this->product->productSkus()->where('is_deleted', 1)->delete();

       //save product images
       $this->product->productImages()->delete($id);
       if(isset($attributes['product_image_datarow'])){
         $product_image_datarow = buildPOST_fromJS($attributes['product_image_datarow']);
         foreach($product_image_datarow as $datarow){
           $image_path = [];
           $image_path_raw = $datarow['product_image']['val'];

           $product_images[] = [
           'post_type' => $attributes['post_type'],
           'product_id' => $id,
           'image_path' => getImagePath($image_path_raw)
           ];
       }
       $this->productImage->insert($product_images);
   }
   return true;
}
throw new ValidationException('Product validation failed', $this->getErrors());
}

public function update_merchant($id, $attributes) {
   //separate variation & sku post
  $variant = [];
  $variants = [];
  $sku = [];
  $skus = [];
  $still_exist_sku_id = []; 
  if(isset($attributes['variant_datarow'])){
       $variant_datarow = buildPOST_fromJS($attributes['variant_datarow']); //dd($variant_datarow);
       foreach($variant_datarow as $datarow){
         foreach($datarow as $ff_name => $ff){
           $pos = strpos($ff_name, 'variant');
           if ($pos === false) {
             $sku[$ff_name] = $ff['val'];

                   //is image
                   //if(in_array($ff_name, [ 'room_image' ]))
                   //    $sku[$ff_name] = getImagePath($ff['val']);

                   //is money
             if(in_array($ff_name, [ 'price', 'weekend_price' ]))
               $sku[$ff_name] = str_replace(',', '.', $ff['val']);

                   //sku yang tetap digunakan atau tidak didelete
           if($ff_name == 'sku_id')
               $still_exist_sku_id[] = $sku[$ff_name];
       }else{
                   preg_match_all("/\[([^\]]*)\]/", $ff_name, $attr_id);//attr id
                   $variant[$attr_id[1][0]] = $ff['val'];
               }
           }
           $skus[] = $sku;
           $variants[] = $variant;
       }
   }

   if($this->isValid($attributes)) {
      $this->product = $this->find($id);
      $t_attributes['post_type'] = $attributes['post_type'];
      $t_attributes['product_category_id'] = $attributes['product_category'];
      $t_attributes['is_published'] = isset($attributes['is_published']) ? true : false;
      $t_attributes['updated_by'] = Auth::user()->id;

      if(is_null(Input::get('price_single')) && empty($skus) || !empty($skus)){
        $t_attributes['price'] = 0;
        $t_attributes['weekend_price'] = 0;
        $t_attributes['qty'] = 0;
    }else{
        $t_attributes['price'] = $attributes['price_single'];
        $t_attributes['weekend_price'] = $attributes['weekend_price_single'];
        $t_attributes['qty'] = $attributes['qty_single'];
    }

    foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
        $t_attributes[$locale] = [
        'title' => $attributes['title'],
        'slug' => $attributes['slug'],
        'body' => $attributes['body'][$locale],
        'meta_keywords' => $attributes['meta_keywords'][$locale],
        'meta_description' => $attributes['meta_description'][$locale]
        ];
    }
    $product = $this->product->update($t_attributes);

       //save product meta
    $this->product->productMetas()->delete();
    $product_meta_keys = [];
    $t_product_meta_keys = [];
    foreach($product_meta_keys as $meta_key){
        $t_metaAttributes['post_type'] = $attributes['post_type'];
        $t_metaAttributes['product_id'] = $id;
        $t_metaAttributes['meta_key'] = $meta_key;
           //reset t_meta
        foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
          unset($t_metaAttributes[$locale]);
      }

           //has translations
      foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
          $t_metaAttributes[$locale] = [
          'meta_value' => in_array($meta_key, $t_product_meta_keys)?$attributes[$meta_key][$locale]:$attributes[$meta_key]
          ];
      }
      $this->productMeta->create($t_metaAttributes);
  }

  $this->product->productSkus()->where('product_id', $id)->update(['is_deleted' => 1 ]);

  $applied_skus_raw = $this->product->productSkus()->where('product_id', $id)->whereNotIn('id', $still_exist_sku_id)->get();
  $remove_skus = [];
       //remove product_variations yg berhubungan dengan sku yg akan diremove ini.
       //sebab jika tidak, maka akan error jika langsung hapus sku nya
  foreach($applied_skus_raw as $applied_sku){
     $remove_skus[] = $applied_sku->id;
 }
       //dd($remove_skus);
 if(count($skus)){
     foreach($skus as $idx => $sku_attributes){ 
       $sku_attributes['product_id'] = $id;
       $sku_id = ProductSku::create($sku_attributes)->id;
       foreach($variants[$idx] as $product_attribute_id => $product_attribute_option_id){
                   $variant_attributes = [];//reset array
                   $variant_attributes['product_id'] = $id;
                   $variant_attributes['product_sku_id'] = $sku_id;
                   $variant_attributes['product_attribute_id'] = $product_attribute_id;
                   $variant_attributes['product_attribute_option_id'] = $product_attribute_option_id;
                   
                   if(isset($sku_attributes['sku_id']) && $sku_attributes['sku_id'] != '' ){
                     $prodVariation = ProductVariation::where([ 
                       'product_sku_id' => $sku_attributes['sku_id'], 
                       'product_attribute_id' => $product_attribute_id ])->first();
                     if(!is_null($prodVariation)){
                       $variant_attributes['id'] = $prodVariation->id;
                       $prodVariation->fill($variant_attributes);
                       $prodVariation->save();
                   }else{
                       ProductVariation::insert($variant_attributes);
                   }
                        //   }
                       //}
               }else{
                  ProductVariation::insert($variant_attributes);
              }
          }
      }

  }
       ProductVariation::where('product_id', $id)->whereIn('product_sku_id', $remove_skus)->delete();//harus ini hapus duluan, kalo tidak akan terjadi error
       $this->product->productSkus()->where('is_deleted', 1)->delete();

       //save product images
       $this->product->productImages()->delete($id);
       if(isset($attributes['product_image_datarow'])){
         $product_image_datarow = buildPOST_fromJS($attributes['product_image_datarow']);
         foreach($product_image_datarow as $datarow){
           $image_path = [];
           $image_path_raw = $datarow['product_image']['val'];

           $product_images[] = [
           'post_type' => $attributes['post_type'],
           'product_id' => $id,
           'image_path' => getImagePath($image_path_raw)
           ];
       }
       $this->productImage->insert($product_images);
   }
   return true;
}
throw new ValidationException('Product validation failed', $this->getErrors());
}
/**
* @param $id
* @return mixed|void
*/
public function delete($id) {

  $this->product = $this->product->findOrFail($id);
  $this->product->productVariations()->delete($id);
  $this->product->productSkus()->delete($id);
  $this->product->productMetas()->delete($id);
  $this->product->productImages()->delete($id);
  $this->product->delete();
}

/**
* @param $id
* @return mixed
*/
public function togglePublish($id) {

  $product = $this->product->find($id);

  $product->is_published = ($product->is_published) ? false : true;
  $product->save();

  return Response::json(array('result' => 'success', 'changed' => ($product->is_published) ? 1 : 0));
}

/**
* @param $id
* @return string
*/
function getUrl($id) {

  $product = $this->product->findOrFail($id);
  return url('product/' . $id . '/' . $product->slug, $parameters = array(), $secure = null);
}

/**
* Get total article count
* @param bool $all
* @return mixed
*/
protected function totalProducts($filter = array()) {
  $query = $this->product->orderBy('created_at', 'DESC');
   //if(!$all) {
    //   return $this->product->where('is_published', 1)->count();
   //}

  if(is_array($filter)){
    foreach($filter as $ff => $term){
      if(trim($term) != ''){
        switch ($ff) {
          case 'with' :
          $query->whereHas('productSpecialOffers', function($q) {
           $q->havingRaw('COUNT(DISTINCT `product_id`) >= 1');
       });
          break;
          case 'product_category':
          $query->where('product_category_id', $term);
          break;
          case 'title':
          $query->whereHas('productTranslation', function($q) use ($term) {
           $q->where('title', 'like', '%'.$term.'%');
       });
          break;
          case 'post_type':
          $query->where('post_type', $term);
          break;
      }
  }
}
}

return $query->count();
}

public function bulkMaster_create($items) {
    if(count($items)){
        foreach($items as $item_idx => $attributes){
            $t_attributes['post_type'] = $attributes['post_type'];
           $t_attributes['product_category_id'] = $attributes['location_id'];//product_category

           $t_attributes['is_published'] = $attributes['is_published']; 
           foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
              $t_attributes[$locale] = [
              'title' => $attributes['hotel_name'],
              'slug' => str_slug($attributes['hotel_name']),
              'body' => $attributes['description'][$locale]
              ];
          }

          $product = $this->product->create($t_attributes);

          //save product meta
          $product_meta_keys = [ 'hotel_address', 'hotel_main_facilities', 'hotel_star', 'hotel_latitude', 'hotel_longitude' ];
          $t_product_meta_keys = [ 'hotel_main_facilities' ];
          foreach($product_meta_keys as $meta_key){
              $t_metaAttributes['post_type'] = $attributes['post_type'];
              $t_metaAttributes['product_id'] = $product->id;
              $t_metaAttributes['meta_key'] = $meta_key;
               //reset t_meta
              foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
                unset($t_metaAttributes[$locale]);
            }
               //if(in_array($meta_key, $t_product_meta_keys)){
                   //has translations
            foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
                $t_metaAttributes[$locale] = [
                'meta_value' => in_array($meta_key, $t_product_meta_keys)?$attributes[$meta_key][$locale]:$attributes[$meta_key]
                ];
            }
               //}else{
               //    $t_metaAttributes['meta_value'] = $attributes[$meta_key];
               //}
            $this->productMeta->create($t_metaAttributes);
        }
    } 
    return true;
}else{
   throw new ValidationException('Product Master validation failed', $this->getErrors());
}

}


/*public function bulkMaster_update($items) {
   if(count($items)){
       foreach($items as $item_idx => $attributes){
           $t_attributes['post_type'] = $attributes['post_type'];
           $t_attributes['product_category_id'] = $attributes['location_id'];//product_category

           $t_attributes['is_published'] = $attributes['is_published']; 
           foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
               $t_attributes[$locale] = [
                   'title' => $attributes['hotel_name'],
                   'slug' => str_slug($attributes['hotel_name']),
                   'body' => $attributes['description'][$locale]
               ];
           }
          $this->product = $this->find($attributes['id']); 
          $product = $this->product->update($t_attributes);

          //save product meta
           $this->product->productMetas()->delete();
           $product_meta_keys = [ 'hotel_address', 'hotel_main_facilities', 'hotel_star', 'hotel_latitude', 'hotel_longitude' ];
           $t_product_meta_keys = [ 'hotel_main_facilities' ];
           foreach($product_meta_keys as $meta_key){
               $t_metaAttributes['post_type'] = $attributes['post_type'];
               $t_metaAttributes['product_id'] = $attributes['id'];
               $t_metaAttributes['meta_key'] = $meta_key;
               //reset t_meta
               foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
                   unset($t_metaAttributes[$locale]);
               }
               //has translations
               foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
                   $t_metaAttributes[$locale] = [
                       'meta_value' => in_array($meta_key, $t_product_meta_keys)?$attributes[$meta_key][$locale]:$attributes[$meta_key]
                   ];
               }
               $this->productMeta->create($t_metaAttributes);
           }
       } 
       return true;
   }else{
       throw new ValidationException('Product Master validation failed', $this->getErrors());
   }
   
}*/
public function bulkMasterHotel_process($cell_map, $items) {
  if(count($items)){
    $errors = [];
    $debug = [];
    foreach($items as $item_idx => $attributes){
           //$errors['rows'][$item_idx] = [];
           //if empty langsung skip aja
           /*if($attributes['hotel_name'] == ''){
               $errors['rows'][$item_idx][] = 'C';//karena kolom milik hotel_name
               $errors['data'][$item_idx] = $attributes;
               continue;
           }*/
           //dd($attributes);
           /*foreach($attributes as $ff => $attr){
               if(in_array($ff, [ 'hotel_main_facilities', 'description', 'meta_keywords', 'meta_description' ])){
                   //keep value before re arrange
                   $is_published = $attributes['is_published'];
                   unset($attributes['is_published']);
                   unset($attributes['hotel_main_facilities']);
                   unset($attributes['description']);
                   unset($attributes['meta_keywords']);
                   unset($attributes['meta_description']);

                   foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
                       $attributes[$ff.'_'.$locale] = isset($attr[$locale])?$attr[$locale]:'';
                   }

                   //rearrage
                   $attributes['is_published'] = $is_published;
               }
           }
           dd($attributes);*/

           /*$nonArrAttributes = [];
           foreach($attributes as $ff => $attr){
               if(in_array($ff, [ 'hotel_main_facilities', 'description', 'meta_keywords', 'meta_description' ])){
                   foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
                       $nonArrAttributes[$ff.'_'.$locale] = isset($attr[$locale])?$attr[$locale]:'';
                   }

                   //rearrage
                   $nonArrAttributes['is_published'] = $attributes['is_published'];
               }
           }
           $nonArrAttributes = $attributes;
           foreach($nonArrAttributes as $ff => $attr){
               if(in_array($ff, [ 'hotel_main_facilities', 'description', 'meta_keywords', 'meta_description' ])){
                   //keep value before re arrange
                   $is_published = $nonArrAttributes['is_published'];
                   unset($nonArrAttributes['is_published']);
                   unset($nonArrAttributes['hotel_main_facilities']);
                   unset($nonArrAttributes['description']);
                   unset($nonArrAttributes['meta_keywords']);
                   unset($nonArrAttributes['meta_description']);

                   foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
                       $nonArrAttributes[$ff.'_'.$locale] = isset($attr[$locale])?$attr[$locale]:'';
                   }

                   //rearrage
                   $nonArrAttributes['is_published'] = $is_published;
               }
           }*/
           //dd($nonArrAttributes);
           $t_attributes['post_type'] = 'hotel';
           $product_category = $this->productCategory->findByTitle($attributes['location'], 'hotel'); //jika upload via xlxs maka akan melihat ke yg parent_id nya tidak null
           if(!$product_category){ // location harus ditemukan
               $errors['rows'][$item_idx][] = [ 'cell' => $cell_map['location'], 'error' => 'not_exist' ];//'B';//karena kolom milik location
               //$errors['data'][$item_idx] = $attributes;
           }else{
              $t_attributes['product_category_id'] = $product_category->id;
          }

           //dd($errors['rows'][$item_idx]);    
          $t_attributes['is_published'] = $attributes['is_published']; 
          foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
              $t_attributes[$locale] = [
              'title' => $attributes['hotel_name'],
              'slug' => str_slug($attributes['hotel_name']),
              'body' => $attributes['description'][$locale],
              'meta_keywords' => $attributes['meta_keywords'][$locale],
              'meta_description' => $attributes['meta_description'][$locale]
              ];
          }

          if(empty($errors['rows'][$item_idx])){ 
               if(isset($attributes['id']) && $attributes['id'] != ''){//update
                 $this_product = $this->find($attributes['id']);
                 if($this_product){//check, kemungkinan id sudah dihapus
                    $this_product->productMetas()->delete(); 
                    $product = $this_product->update($t_attributes);
                    $product_id = $attributes['id']; 
                 }else{
                    $errors['rows'][$item_idx][] = [ 'cell' => $cell_map['id'], 'error' => 'not_exist' ];
                 }
               }else{//create new


                   //$product_id = ''; //pasti kosong
                   //pastikan nama produk unique
                $check_product = $this->findAllByTitle($attributes['hotel_name'], 'hotel');
                   if($check_product){ //exist
                       $errors['rows'][$item_idx][] = [ 'cell' => $cell_map['hotel_name'], 'error' => 'exist' ];//'C';//karena kolom milik hotel_name

                       //jika dideteksi lebih dari satu bahasa
                       /*foreach($attributes as $ff => $attr){
                           if(in_array($ff, [ 'hotel_main_facilities', 'description', 'meta_keywords', 'meta_description' ])){
                               //keep value before re arrange
                               $is_published = $attributes['is_published'];
                               unset($attributes['is_published']);
                               unset($attributes['hotel_main_facilities']);
                               unset($attributes['description']);
                               unset($attributes['meta_keywords']);
                               unset($attributes['meta_description']);

                               foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
                                   $attributes[$ff.'_'.$locale] = isset($attr[$locale])?$attr[$locale]:'';
                               }

                               //rearrage
                               $attributes['is_published'] = $is_published;
                           }
                       }*/
                       //dd('emang mask sini');
                       //$errors['data'][$item_idx] = $attributes;
                   }else{//harus unique title , seharusnya sih harus unique slug juga
                       //dd($t_attributes);

                     $product = $this->product->create($t_attributes);
                     $product_id = $product->id;
                 }
             }
               //if(empty($errors['rows'][$item_idx])){
             $product_meta_keys = [ 'hotel_address', 'hotel_main_facilities', 'hotel_star', 'hotel_latitude', 'hotel_longitude' ];
             $t_product_meta_keys = [ 'hotel_main_facilities' ];
                   if(!empty($product_meta_keys) && isset($product_id)){ //kalau exist maka variable product_id tidak diset
                     foreach($product_meta_keys as $meta_key){
                       $t_metaAttributes['post_type'] = 'hotel';
                       $t_metaAttributes['product_id'] = $product_id;
                       $t_metaAttributes['meta_key'] = $meta_key;
                           //reset t_meta
                           //foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
                           //    unset($t_metaAttributes[$locale]);
                           //}
                           //has translations
                       foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
                         $t_metaAttributes[$locale] = [
                         'meta_value' => in_array($meta_key, $t_product_meta_keys)?$attributes[$meta_key][$locale]:$attributes[$meta_key]
                         ];
                     }
                           //dd($t_metaAttributes);
                     $this->productMeta->create($t_metaAttributes);
                 }
             }
               //}
         }

           //if(count($errors['data'][$item_idx]) == 0){
              //save product meta
               /*$product_meta_keys = [ 'hotel_address', 'hotel_main_facilities', 'hotel_star', 'hotel_latitude', 'hotel_longitude' ];
               $t_product_meta_keys = [ 'hotel_main_facilities' ];
               if(!empty($product_meta_keys)){
                   foreach($product_meta_keys as $meta_key){
                       $t_metaAttributes['post_type'] = 'hotel';
                       $t_metaAttributes['product_id'] = $product_id;
                       $t_metaAttributes['meta_key'] = $meta_key;
                       //reset t_meta
                       foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
                           unset($t_metaAttributes[$locale]);
                       }
                       //has translations
                       foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
                           $t_metaAttributes[$locale] = [
                           'meta_value' => in_array($meta_key, $t_product_meta_keys)?$attributes[$meta_key][$locale]:$attributes[$meta_key]
                           ];
                       }

                       $this->productMeta->create($t_metaAttributes);
                   }
               }*/
           //}
           } 
       //return true;
           return $errors;
       }else{
         throw new ValidationException('Product Master validation failed', $this->getErrors());
     }

 }

 public function bulkMasterPlayground_process($cell_map, $items) {
    if(count($items)){
      $errors = [];
      $debug = [];
      foreach($items as $item_idx => $attributes){
        $t_attributes['post_type'] = 'playground';
        $product_category = $this->productCategory->findByTitle($attributes['location'], 'playground'); //jika upload via xlxs maka akan melihat ke yg parent_id nya tidak null
           if(!$product_category){ // location harus ditemukan
               $errors['rows'][$item_idx][] = [ 'cell' => $cell_map['location'], 'error' => 'not_exist' ];//'B';//karena kolom milik location
               //$errors['data'][$item_idx] = $attributes;
           }else{
              $t_attributes['product_category_id'] = $product_category->id;
          }

           //dd($errors['rows'][$item_idx]);    
          $t_attributes['is_published'] = $attributes['is_published']; 
          foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
              $t_attributes[$locale] = [
              'title' => $attributes['playground_name'],
              'slug' => str_slug($attributes['playground_name']),
              'body' => $attributes['description'][$locale],
              'meta_keywords' => $attributes['meta_keywords'][$locale],
              'meta_description' => $attributes['meta_description'][$locale]
              ];
          }

          if(empty($errors['rows'][$item_idx])){ 
               if(isset($attributes['id']) && $attributes['id'] != ''){//update
                 $this->product = $this->find($attributes['id']);
                 $this->product->productMetas()->delete(); 
                 $product = $this->product->update($t_attributes);
                 $product_id = $attributes['id']; 
               }else{//create new
                   //$product_id = ''; //pasti kosong
                   //pastikan nama produk unique
                $check_product = $this->findAllByTitle($attributes['playground_name'], 'playground');
                   if($check_product){ //exist
                       $errors['rows'][$item_idx][] = [ 'cell' => $cell_map['playground_name'], 'error' => 'exist' ];//'C';//karena kolom milik hotel_name

                   }else{//harus unique title , seharusnya sih harus unique slug juga
                     $product = $this->product->create($t_attributes);
                     $product_id = $product->id;
                 }
             }
               //if(empty($errors['rows'][$item_idx])){
             $product_meta_keys = [ 'playground_address', 'playground_latitude', 'playground_longitude' ];
             $t_product_meta_keys = [];
                   if(!empty($product_meta_keys) && isset($product_id)){ //kalau exist maka variable product_id tidak diset
                     foreach($product_meta_keys as $meta_key){
                       $t_metaAttributes['post_type'] = 'playground';
                       $t_metaAttributes['product_id'] = $product_id;
                       $t_metaAttributes['meta_key'] = $meta_key;
                           //has translations
                       foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
                         $t_metaAttributes[$locale] = [
                         'meta_value' => in_array($meta_key, $t_product_meta_keys)?$attributes[$meta_key][$locale]:$attributes[$meta_key]
                         ];
                     }
                           //dd($t_metaAttributes);
                     $this->productMeta->create($t_metaAttributes);
                 }
             }
               //}
         }
     } 
     return $errors;
 }else{
  throw new ValidationException('Product Master validation failed', $this->getErrors());
}

}

public function bulkMaster_process($items) {
 if(count($items)){
   foreach($items as $item_idx => $attributes){
     $t_attributes['post_type'] = 'hotel';
           $t_attributes['product_category_id'] = $attributes['location_id'];//product_category

           $t_attributes['is_published'] = $attributes['is_published']; 
           foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
              $t_attributes[$locale] = [
              'title' => $attributes['hotel_name'],
              'slug' => str_slug($attributes['hotel_name']),
              'body' => $attributes['description'][$locale]
              ];
          }


          if(isset($attributes['id']) && $attributes['id'] != ''){
            $this->product = $this->find($attributes['id']);
            $this->product->productMetas()->delete(); 
            $product = $this->product->update($t_attributes);
            $product_id = $attributes['id'];
        }else{
           $product = $this->product->create($t_attributes);
           $product_id = $product->id;
       }

          //save product meta

       $product_meta_keys = [ 'hotel_address', 'hotel_main_facilities', 'hotel_star', 'hotel_latitude', 'hotel_longitude' ];
       $t_product_meta_keys = [ 'hotel_main_facilities' ];
           //if($product_id == '2')
           //    dd($product_id);
       foreach($product_meta_keys as $meta_key){


           $t_metaAttributes['post_type'] = 'hotel';
           $t_metaAttributes['product_id'] = $product_id;
           $t_metaAttributes['meta_key'] = $meta_key;
               //reset t_meta
           foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
             unset($t_metaAttributes[$locale]);
         }
               //has translations
         foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
             $t_metaAttributes[$locale] = [
             'meta_value' => in_array($meta_key, $t_product_meta_keys)?$attributes[$meta_key][$locale]:$attributes[$meta_key]
             ];
         }

         $this->productMeta->create($t_metaAttributes);
     }
 } 
 return true;
}else{
    throw new ValidationException('Product Master validation failed', $this->getErrors());
}

}


//product gallery
/*public function bulkProductGallery_create($items) {
   if(count($items)){
       foreach($items as $item_idx => $attributes){
           $t_attributes['post_type'] = 'hotel';
           $t_attributes['product_id'] = $attributes['product_id'];
           $t_attributes['image_path'] = $attributes['image_path'];
           $product = $this->productImage->create($t_attributes);
       } 
       return true;
   }else{
       throw new ValidationException('Product Gallery validation failed', $this->getErrors());
   }
   
}*/

public function bulkProductGalleryHotel_process($cell_map, $items) {
  if(count($items)){
    $errors = [];
    foreach($items as $item_idx => $attributes){
      $t_attributes = [];
      $product = $this->product->whereHas('productTranslation', function($q) use ($attributes){
        $q->where('title', $attributes['hotel_name'])
        ->where('locale', getLang());
    })->first();
      if($product){
        if(isset($attributes['id']) && $attributes['id'] != ''){
          $t_attributes['product_id'] = $product->id;
          $t_attributes['image_path'] = $attributes['image_path'];
          $this_productImage = $this->productImage->find($attributes['id']);
          if($this_productImage)
            $this_productImage->update($t_attributes);
          else{//failed id
               $errors['rows'][$item_idx][] = [ 'cell' => $cell_map['id'], 'error' => 'not_exist' ];//karena kolom milik id
           }
    }else{ 
       $t_attributes['post_type'] = 'hotel';
       $t_attributes['product_id'] = $product->id;
       $t_attributes['image_path'] = $attributes['image_path'];
       $this->productImage->create($t_attributes);
   }
           }else{//failed 
               $errors['rows'][$item_idx][] = [ 'cell' => $cell_map['hotel_name'], 'error' => 'not_exist' ];//karena kolom milik product_name
               //$errors['data'][$item_idx] = $attributes;
           }

       } 
       return $errors;
   }else{
       throw new ValidationException('Product Gallery validation failed', $this->getErrors());
   }

}

public function bulkProductGalleryPlayground_process($cell_map, $items) {
  if(count($items)){
    $errors = [];
    foreach($items as $item_idx => $attributes){
      $t_attributes = [];
      $product = $this->product->whereHas('productTranslation', function($q) use ($attributes){
        $q->where('title', $attributes['playground_name'])
        ->where('locale', getLang());
    })->first();
      if($product){
        if(isset($attributes['id']) && $attributes['id'] != ''){
          $t_attributes['product_id'] = $product->id;
          $t_attributes['image_path'] = $attributes['image_path'];
          $this_productImage = $this->productImage->find($attributes['id']);
          if($this_productImage)
            $this_productImage->update($t_attributes);
          else{//failed id
               $errors['rows'][$item_idx][] = [ 'cell' => $cell_map['id'], 'error' => 'not_exist' ];//karena kolom milik id
           }
    }else{ 
       $t_attributes['post_type'] = 'playground';
       $t_attributes['product_id'] = $product->id;
       $t_attributes['image_path'] = $attributes['image_path'];
       $this->productImage->create($t_attributes);
   }
           }else{//failed 
               $errors['rows'][$item_idx][] = [ 'cell' => $cell_map['playground_name'], 'error' => 'not_exist' ];//karena kolom milik product_name
               //$errors['data'][$item_idx] = $attributes;
           }

       } 
       return $errors;
   }else{
       throw new ValidationException('Product Gallery validation failed', $this->getErrors());
   }

}

public function bulkProductGallery_processJUNK($items) {
  if(count($items)){
    foreach($items as $item_idx => $attributes){
      $t_attributes = [];
      if(isset($attributes['id']) && $attributes['id'] != ''){
        $t_attributes['product_id'] = $attributes['product_id'];
        $t_attributes['image_path'] = $attributes['image_path'];
        $this->productImage = $this->productImage->find($attributes['id']);
        $this->productImage->update($t_attributes);
    }else{ 
        $t_attributes['post_type'] = 'hotel';
        $t_attributes['product_id'] = $attributes['product_id'];
        $t_attributes['image_path'] = $attributes['image_path'];
        $this->productImage->create($t_attributes);
    }
} 
return true;
}else{
 throw new ValidationException('Product Gallery validation failed', $this->getErrors());
}

}

public function bulkVariant_process($items) {
    if(count($items)){
        $variants = [];
        $skus = [];
       //$product_ids = []; 
        foreach($items as $item_idx => $attributes){
            if($attributes['product_attribute_option_id'] != ''){
              $product_attribute_option =  $this->productAttributeOption->find($attributes['product_attribute_option_id']);
               //$product_ids[] = $attributes['product_id'];
          }

          /* $t_attributes = [];
           if(isset($attributes['variation_id']) && $attributes['variation_id'] != ''){
               $t_attributes['product_id'] = $attributes['product_id'];
               $t_attributes['room_image'] = $attributes['image_path'];
               $this->productImage = $this->productImage->find($attributes['id']);
               $this->productImage->update($t_attributes);
           }else{ 
               */
              if($attributes['product_attribute_option_id'] != ''){
                $skus[$attributes['product_id']][$item_idx]['variation_id'] = $attributes['variation_id'];
                $skus[$attributes['product_id']][$item_idx]['product_id'] = $attributes['product_id'];
                $skus[$attributes['product_id']][$item_idx]['room_image'] = $attributes['room_image'];
                $skus[$attributes['product_id']][$item_idx]['price'] = $attributes['price'];
                $skus[$attributes['product_id']][$item_idx]['weekend_price'] = $attributes['weekend_price'];
                $skus[$attributes['product_id']][$item_idx]['special_offer'] = $attributes['special_offer'];
                $skus[$attributes['product_id']][$item_idx]['qty'] = $attributes['qty'];
                $skus[$attributes['product_id']][$item_idx]['room_info'] = $attributes['room_info'];   

                $variants[$attributes['product_id']][$item_idx] = [ $product_attribute_option->product_attribute_id => $attributes['product_attribute_option_id'] ];
            }
          // }
        } 
       //foreach($products as $product_id){
        if(count($skus)){
            foreach($skus as $product_id => $skus_by_product_id){
              ProductSku::where('product_id', $product_id)->update(['is_deleted' => 1 ]);
               ProductSpecialOffer::where('product_id', $product_id)->delete();//delete all special offer by product_id
               foreach($skus_by_product_id as $idx => $sku_attributes){
                if(count($variants[$product_id][$idx])){
                  $special_offer = $sku_attributes['special_offer'];
                  unset($sku_attributes['special_offer']);
                  $sku_id = ProductSku::create($sku_attributes)->id;
                  foreach($variants[$product_id][$idx] as $product_attribute_id => $product_attribute_option_id){
                    $variant_attributes['product_id'] = $sku_attributes['product_id'];
                    $variant_attributes['product_sku_id'] = $sku_id;
                    $variant_attributes['product_attribute_id'] = $product_attribute_id;
                    $variant_attributes['product_attribute_option_id'] = $product_attribute_option_id;

                    if(isset($sku_attributes['variation_id']) && $sku_attributes['variation_id'] != '' ){
                      $prodVariation = ProductVariation::find($sku_attributes['variation_id']); 
                      if($prodVariation){
                        $prodVariation->fill($variant_attributes);
                        $prodVariation->save();
                    }else{
                        ProductVariation::insert($variant_attributes);
                    }
                }else{
                               //balikin productsku yg is_deleted nya 1, karena variation_id nya kosong / bukan edit
                   $prod_variations = ProductVariation::where('product_id', $product_id)->lists('id', 'product_sku_id');
                   ProductSku::whereIn('id', array_keys($prod_variations))->update(['is_deleted' => 0 ]);
                   ProductVariation::insert($variant_attributes);
               }

                           //create special offer
               if($special_offer != '' && $special_offer != '0' && $special_offer != 0)
                   ProductSpecialOffer::create([
                     'product_id' => $product_id,
                     'product_sku_id' => $sku_id,
                     'special_offer' => $special_offer
                     ]);
           }
       }
   }
   ProductSku::where('product_id', $product_id)->where('is_deleted', 1)->delete();
}
}
       //}

return true;
}else{
 throw new ValidationException('Product Variant validation failed', $this->getErrors());
}

}

    public function bulkVariantHotel_process($cell_map, $items) {
        $errors = [];
        if(count($items)){
            $variants = [];
            $skus = [];
           //$errors = [];
            $still_exist_sku_id = []; 
            $cekgajelas = []; //dd($items);
            foreach($items as $item_idx => $attributes){
                $product = $this->product->with(['productSkus'])->whereHas('productTranslation', function($q) use ($attributes){
                    $q->where('title', $attributes['hotel_name'])
                    ->where('locale', 'en');
                })->first();

                if($product){
                    $product_id = $product->id;
                    if($product->productSkus){//sku ditemukan
                        $upload_skus = [];
                        $upload_sku = [];
                        $upload_variation = [];
                        $upload_variations = [];
                        $variants = [];

                        //list semua sku beserta variantnya
                        $sku_variants = [];//ini yg seharusnya variant-variant dimiliki produk ini
                        // $sku_ids = [];
                        $sku_ids_by_variation_id = [];
                        //echo '<pre>';
                        //var_dump($product->productSkus);
                        //echo '</pre>';
                        foreach($product->productSkus as $sku_idx => $sku){ 
                          
                            $sku_id = $sku->id;
                            $sku_variants[$sku_idx] = [];

                            foreach($sku->productVariations as $prodVariation){
                                $prod_attr_id = $prodVariation->productAttribute->id;
                                $prod_attr_key = $prodVariation->productAttribute->product_attribute_key;
                                $prod_attr_opt_name = $prodVariation->productAttributeOption->name;
                                $prod_attr_opt_id = $prodVariation->productAttributeOption->id;
                                $sku_variants[$sku_idx][] = $prod_attr_opt_name;

                                $upload_variations[$sku_id][$prod_attr_id] = $prod_attr_opt_id;

                                //simpan sku variants berdasarkan nomor variation_id nya, agar bisa di get sku id nya saat mau di edit(update)
                                $sku_ids_by_variation_id[$prodVariation->id] = $sku_id;
                            }

                            $sku_variants[$sku_idx] = strtolower(implode('_', $sku_variants[$sku_idx]));//hasilnya seperti S_Yellow
                            $still_exist_sku_id[] = $sku_id;
                            //old sku juga harus masuk kesini, karena saat update, semua sku akan berubah id
                            //nanti direplace lg sku tertentu, sesuai variasi yg diupload xlxs
                            $upload_skus[$sku_id] = [
                               'sku_id' => $sku_id,
                               'room_image' => $sku->room_image,
                               'room_info' => $sku->room_info,
                               'price' => $sku->price,
                               'weekend_price' => $sku->weekend_price,
                               'weight' => $sku->weight,
                               'qty' => $sku->qty
                            ];
                        }

                    }

                    $upload_sku_variant = [];//ini yg variant-variant yang diupload
                    //if( $item_idx == 2) dd($attributes);
                    foreach($attributes as $ff_name => $ff_val){//cari yg berhubungan dengan variant
                        $pos = strpos($ff_name, 'variant_');//harus cari nama
                        $product_attribute_key = str_replace('variant_', '', $ff_name);
                        if ($pos !== false) {//variant ditemukan di header xls
                            $prodAttr = ProductAttribute::where('product_attribute_key', $product_attribute_key)->first();
                            if($prodAttr){//variant ditemukan di database
                                $attr_id = $prodAttr->id;
                                $prodAttrOpt = ProductAttributeOption::where('product_attribute_id', $attr_id)->whereHas('ProductAttributeOptionTranslation', function($q) use ($ff_val){
                                    $q->where('name', $ff_val)->where('locale', 'en');
                                })->first();//check product attribute option is exist

                                if(!is_null($prodAttrOpt)){//product attr opt ditemukan
                                    $upload_sku_variant[] = $ff_val;
                                    $upload_variation[$attr_id] = $prodAttrOpt->id;
                                }else{
                                    $errors['rows'][$item_idx][] = [ 'cell' => $cell_map[$ff_name], 'error' => 'not_exist' ];//$xls_column;
                                }
                            }
                        }
                    }

                    //if( $item_idx == 2) dd($attributes);
                    if(!empty($upload_sku_variant)){
                        $upload_sku_variant = strtolower(implode('_', $upload_sku_variant)); //hasilnya seperti s_yellow
                        $replaced_sku = [
                            'room_image' => $attributes['room_image_path'], 
                            'room_info' => $attributes['room_info'],
                            'price' => $attributes['price'],
                            'weekend_price' => $attributes['weekend_price'],
                            'qty' => $attributes['availability']
                        ];

                        //$cekgajelas[] = $attributes['hotel_name'].' '.$upload_sku_variant.' '.in_array($upload_sku_variant, $sku_variants);
                        if(in_array($upload_sku_variant, $sku_variants) || trim($attributes['variation_id']) != ''){ // cek ada variant ini di produk ini ? atau variation_id nya tidak kosong ( edit mode )
                            
                            //jika ketemu, tapi xls yg diupload tidak ada variant id, maka dibuat error, karena akan nimpa value lama
                            if(trim($attributes['variation_id']) == ''){ 
                                foreach($attributes as $ff_name => $ff_val){//cari yg berhubungan dengan variant
                                    $pos = strpos($ff_name, 'variant_');//harus cari nama
                                    if ($pos !== false) {//buat jadi error "exist"
                                        $errors['rows'][$item_idx][] = [ 'cell' => $cell_map[$ff_name], 'error' => 'exist' ];
                                    }
                                }
                                //echo 'masuk sini';
                                //echo 'error';
                                //$cekgajelas[] = $attributes['hotel_name'];
                            }else{//edit variation
                                //jika ketemu maka kita ambil sku nya untuk diupdate
                                //tadi kita harus get dahulu variation sebelumnya. 
                                //misal L_red mau diubah ke XL_red, maka kita perlu dapatkan sku variant : L_red dari variation_id nya
                                $upload_sku_id = $sku_ids_by_variation_id[$attributes['variation_id']];//$sku_ids[$upload_sku_variant];
                                //$still_exist_sku_id[] = $upload_sku_id;//$sku_ids[$upload_sku_variant];

                                //replace variant jika sku id terdeteksi ditemukan
                                $upload_variations[$upload_sku_id] = $upload_variation;

                                $replaced_sku['sku_id'] = $upload_sku_id;
                                //echo '<pre>';
                                //var_dump($replaced_sku);
                                //echo '</pre>';

                                $upload_skus[$upload_sku_id] = $replaced_sku;

                            }

                        }else{//variant dari file upload tidak ditemukan
                            //buat sku baru, setelah input ini array akan melanjutkan sku yg sudah ada, jadi sku id nya sudah tidak valid
                            $upload_variations[] = $upload_variation;
                            $upload_skus[] = $replaced_sku;
                            //$cekgajelas[] = $attributes['hotel_name'];
                            //echo 'masuk sini';
                        }
                        //echo '<pre>';
                        //var_dump($upload_skus);
                        //echo '</pre>';
                        
                    }
                    
                    if(empty($errors['rows'][$item_idx])){//check error lagi setelah pengecekan variation udah ada atau belum
                        //if(empty($errors)){ //check error lagi setelah pengecekan variation udah ada atau belum
                        $this->product = $this->find($product_id);
                        $this->product->productSkus()->where('product_id', $product_id)->update(['is_deleted' => 1 ]);

                        $applied_skus_raw = $this->product->productSkus()->where('product_id', $product_id)->whereNotIn('id', $still_exist_sku_id)->get();
                        $remove_skus = [];
                        //remove product_variations yg berhubungan dengan sku yg akan diremove ini.
                        //sebab jika tidak, maka akan error jika langsung hapus sku nya
                        foreach($applied_skus_raw as $applied_sku){
                            $remove_skus[] = $applied_sku->id;
                        }
                        //$cekgajelas[] = $attributes['hotel_name'];
                        //echo '<pre>';
                        //var_dump($upload_skus);
                        //echo '</pre>';
                        if(count($upload_skus)){ 
                            //if( $item_idx == 9) dd($upload_skus);
                            foreach($upload_skus as $idx => $sku_attributes){
                                $sku_attributes['product_id'] = $product_id;
                                 //echo '<pre>';
                                  //var_dump($sku_attributes);
                                  //echo '</pre>';
                                $sku_id = ProductSku::create($sku_attributes)->id;
                                foreach($upload_variations[$idx] as $product_attribute_id => $product_attribute_option_id){
                                    $variant_attributes = [];//reset array
                                    $variant_attributes['product_id'] = $product_id;
                                    $variant_attributes['product_sku_id'] = $sku_id;
                                    $variant_attributes['product_attribute_id'] = $product_attribute_id;
                                    $variant_attributes['product_attribute_option_id'] = $product_attribute_option_id;
                               
                                    if(isset($sku_attributes['sku_id']) && $sku_attributes['sku_id'] != '' ){
                                        //karena bisa saja lebih dari satu attribute, tidak seperti hotel yang hanya satu attribute
                                        $prodVariation = ProductVariation::where([ 
                                           'product_sku_id' => $sku_attributes['sku_id'], 
                                           'product_attribute_id' => $product_attribute_id ]
                                        )->first();
                                        
                                        if(!is_null($prodVariation)){
                                            $variant_attributes['id'] = $prodVariation->id;
                                            $prodVariation->fill($variant_attributes);
                                            $prodVariation->save();
                                        }else{
                                            ProductVariation::insert($variant_attributes);
                                        }
                                    }else{
                                        ProductVariation::insert($variant_attributes);
                                    }
                                }
                            }
                        }
                        ProductVariation::where('product_id', $product_id)->whereIn('product_sku_id', $remove_skus)->delete();//harus ini hapus duluan, kalo tidak akan terjadi error
                        $this->product->productSkus()->where('is_deleted', 1)->delete();
                    }//end if not error
                   
                }else{//failed 
                    $errors['rows'][$item_idx][] = [ 'cell' => $cell_map['hotel_name'], 'error' => 'not_exist' ];//karena kolom milik product_name
                    //$errors['data'][$item_idx] = $attributes;
                }   
            }  //dd('test');
            //dd($cekgajelas);
            //return $errors;
        }
        return $errors;
    }


    public function bulkVariantPlayground_process($cell_map, $items) { // seharusnya milik hotel bisa dijadikan satu dengan playground
        $errors = [];
        if(count($items)){
            $variants = [];
            $skus = [];
           //$errors = [];
            $still_exist_sku_id = []; 
            $cekgajelas = []; 
            foreach($items as $item_idx => $attributes){
                $product = $this->product->with(['productSkus'])->whereHas('productTranslation', function($q) use ($attributes){
                    $q->where('title', $attributes['playground_name'])
                    ->where('locale', 'en');
                })->first();

                if($product){
                    $product_id = $product->id;
                    if($product->productSkus){//sku ditemukan
                        $upload_skus = [];
                        $upload_sku = [];
                        $upload_variation = [];
                        $upload_variations = [];
                        $variants = [];

                        //list semua sku beserta variantnya
                        $sku_variants = [];//ini yg seharusnya variant-variant dimiliki produk ini
                        // $sku_ids = [];
                        $sku_ids_by_variation_id = [];

                        foreach($product->productSkus as $sku_idx => $sku){
                            $sku_id = $sku->id;
                            $sku_variants[$sku_idx] = [];

                            foreach($sku->productVariations as $prodVariation){
                                $prod_attr_id = $prodVariation->productAttribute->id;
                                $prod_attr_key = $prodVariation->productAttribute->product_attribute_key;
                                $prod_attr_opt_name = $prodVariation->productAttributeOption->name;
                                $prod_attr_opt_id = $prodVariation->productAttributeOption->id;
                                $sku_variants[$sku_idx][] = $prod_attr_opt_name;

                                $upload_variations[$sku_id][$prod_attr_id] = $prod_attr_opt_id;

                                //simpan sku variants berdasarkan nomor variation_id nya, agar bisa di get sku id nya saat mau di edit(update)
                                $sku_ids_by_variation_id[$prodVariation->id] = $sku_id;
                            }

                            $sku_variants[$sku_idx] = strtolower(implode('_', $sku_variants[$sku_idx]));//hasilnya seperti S_Yellow
                            $still_exist_sku_id[] = $sku_id;
                            //old sku juga harus masuk kesini, karena saat update, semua sku akan berubah id
                            //nanti direplace lg sku tertentu, sesuai variasi yg diupload xlxs
                            $upload_skus[$sku_id] = [
                               'sku_id' => $sku_id,
                               //'sku_image' => $sku->sku_image,
                               'price' => $sku->price,
                               'weekend_price' => $sku->weekend_price,
                               //'weight' => $sku->weight,
                               'qty' => $sku->qty
                            ];
                        }
                    }

                    $upload_sku_variant = [];//ini yg variant-variant yang diupload
                    //dd($attributes);
                    foreach($attributes as $ff_name => $ff_val){//cari yg berhubungan dengan variant
                        $pos = strpos($ff_name, 'variant_');//harus cari nama
                        $product_attribute_key = str_replace('variant_', '', $ff_name);
                        if ($pos !== false) {//variant ditemukan di header xls
                            $prodAttr = ProductAttribute::where('product_attribute_key', $product_attribute_key)->first();
                            if($prodAttr){//variant ditemukan di database
                                $attr_id = $prodAttr->id;
                                $prodAttrOpt = ProductAttributeOption::where('product_attribute_id', $attr_id)->whereHas('ProductAttributeOptionTranslation', function($q) use ($ff_val){
                                    $q->where('name', $ff_val)->where('locale', 'en');
                                })->first();//check product attribute option is exist

                                if(!is_null($prodAttrOpt)){//product attr opt ditemukan
                                    $upload_sku_variant[] = $ff_val;
                                    $upload_variation[$attr_id] = $prodAttrOpt->id;
                                }else{
                                    $errors['rows'][$item_idx][] = [ 'cell' => $cell_map[$ff_name], 'error' => 'not_exist' ];//$xls_column;
                                }
                            }
                        }
                    }

                    //dd($upload_sku_variant);

                    if(!empty($upload_sku_variant)){
                        $upload_sku_variant = strtolower(implode('_', $upload_sku_variant)); //hasilnya seperti s_yellow
                        $replaced_sku = [
                            //'room_image' => $attributes['room_image_path'], 
                            //'room_info' => $attributes['room_info'],
                            'price' => $attributes['price'],
                            'weekend_price' => $attributes['weekend_price'],
                            'qty' => $attributes['availability']
                        ];

                        //$cekgajelas[] = $attributes['hotel_name'].' '.$upload_sku_variant.' '.in_array($upload_sku_variant, $sku_variants);
                        if(in_array($upload_sku_variant, $sku_variants) || trim($attributes['variation_id']) != ''){ // cek ada variant ini di produk ini ? atau variation_id nya tidak kosong ( edit mode )
                           //jika ketemu, tapi xls yg diupload tidak ada variant id, maka dibuat error, karena akan nimpa value lama
                            if(trim($attributes['variation_id']) == ''){
                                foreach($attributes as $ff_name => $ff_val){//cari yg berhubungan dengan variant
                                    $pos = strpos($ff_name, 'variant_');//harus cari nama
                                    if ($pos !== false) {//buat jadi error "exist"
                                        $errors['rows'][$item_idx][] = [ 'cell' => $cell_map[$ff_name], 'error' => 'exist' ];
                                    }
                                }
                                //$cekgajelas[] = $attributes['hotel_name'];
                            }else{//edit variation
                                //jika ketemu maka kita ambil sku nya untuk diupdate
                                //tadi kita harus get dahulu variation sebelumnya. 
                                //misal L_red mau diubah ke XL_red, maka kita perlu dapatkan sku variant : L_red dari variation_id nya
                                $upload_sku_id = $sku_ids_by_variation_id[$attributes['variation_id']];//$sku_ids[$upload_sku_variant];
                                //$still_exist_sku_id[] = $upload_sku_id;//$sku_ids[$upload_sku_variant];

                                //replace variant jika sku id terdeteksi ditemukan
                                $upload_variations[$upload_sku_id] = $upload_variation;

                                $replaced_sku['sku_id'] = $upload_sku_id;
                                $upload_skus[$upload_sku_id] = $replaced_sku;
                            }
                        }else{//variant dari file upload tidak ditemukan
                            //buat sku baru, setelah input ini array akan melanjutkan sku yg sudah ada, jadi sku id nya sudah tidak valid
                            $upload_variations[] = $upload_variation;
                            $upload_skus[] = $replaced_sku;
                            //$cekgajelas[] = $attributes['hotel_name'];
                        }
                    }

                    if(empty($errors['rows'][$item_idx])){//check error lagi setelah pengecekan variation udah ada atau belum
                        //if(empty($errors)){ //check error lagi setelah pengecekan variation udah ada atau belum
                        $this->product = $this->find($product_id);
                        $this->product->productSkus()->where('product_id', $product_id)->update(['is_deleted' => 1 ]);

                        $applied_skus_raw = $this->product->productSkus()->where('product_id', $product_id)->whereNotIn('id', $still_exist_sku_id)->get();
                        $remove_skus = [];
                        //remove product_variations yg berhubungan dengan sku yg akan diremove ini.
                        //sebab jika tidak, maka akan error jika langsung hapus sku nya
                        foreach($applied_skus_raw as $applied_sku){
                            $remove_skus[] = $applied_sku->id;
                        }
                        //$cekgajelas[] = $attributes['hotel_name'];
                        if(count($upload_skus)){
                            foreach($upload_skus as $idx => $sku_attributes){
                                $sku_attributes['product_id'] = $product_id;
                                $sku_id = ProductSku::create($sku_attributes)->id;
                                foreach($upload_variations[$idx] as $product_attribute_id => $product_attribute_option_id){
                                    $variant_attributes = [];//reset array
                                    $variant_attributes['product_id'] = $product_id;
                                    $variant_attributes['product_sku_id'] = $sku_id;
                                    $variant_attributes['product_attribute_id'] = $product_attribute_id;
                                    $variant_attributes['product_attribute_option_id'] = $product_attribute_option_id;
                               
                                    if(isset($sku_attributes['sku_id']) && $sku_attributes['sku_id'] != '' ){
                                        //karena bisa saja lebih dari satu attribute, tidak seperti hotel yang hanya satu attribute
                                        $prodVariation = ProductVariation::where([ 
                                           'product_sku_id' => $sku_attributes['sku_id'], 
                                           'product_attribute_id' => $product_attribute_id ]
                                        )->first();

                                        if(!is_null($prodVariation)){
                                            $variant_attributes['id'] = $prodVariation->id;
                                            $prodVariation->fill($variant_attributes);
                                            $prodVariation->save();
                                        }else{
                                            ProductVariation::insert($variant_attributes);
                                        }
                                    }else{
                                        ProductVariation::insert($variant_attributes);
                                    }
                                }
                            }
                        }
                        ProductVariation::where('product_id', $product_id)->whereIn('product_sku_id', $remove_skus)->delete();//harus ini hapus duluan, kalo tidak akan terjadi error
                        $this->product->productSkus()->where('is_deleted', 1)->delete();
                    }//end if not error
                }else{//failed 
                    $errors['rows'][$item_idx][] = [ 'cell' => $cell_map['playground_name'], 'error' => 'not_exist' ];//karena kolom milik product_name
                }   
            }
        }
        return $errors;
    }
}
