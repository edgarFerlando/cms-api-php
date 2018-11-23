<?php namespace App\Repositories\ProductAttributeOption;

use App\ProductAttributeOption;
use Config;
use Response;
//use App\Tag;
use App\ProductAttribute;
use App\Repositories\ProductAttribute\ProductAttributeRepository;

use Str;
//use Event;
//use Image;
//use File;
use App\Repositories\RepositoryAbstract;
use App\Repositories\CrudableInterface as CrudableInterface;
use App\Repositories\RepositoryInterface as RepositoryInterface;
use App\Exceptions\Validation\ValidationException;
use App\Repositories\AbstractValidator as Validator;
use LaravelLocalization;
use Notification;


class ProductAttributeOptionRepository extends RepositoryAbstract implements ProductAttributeOptionInterface, CrudableInterface {

    protected $perPage;
    protected $productAttributeOption;
    /**
     * Rules
     *
     * @var array
     */
    protected static $rules;
    protected static $attributeNames;

    /**
     * @param ProductAttributeOption $productAttributeOption
     */
    public function __construct(ProductAttributeOption $productAttributeOption) {

        $config = Config::get('holiday');
        $this->perPage = $config['per_page'];
        $this->productAttributeOption = $productAttributeOption;
        $this->productAttribute = new ProductAttributeRepository(new ProductAttribute);
        $rules_n_attributeNames = $this->rules();
        self::$rules = $rules_n_attributeNames['rules'];
        self::$attributeNames = $rules_n_attributeNames['attributeNames'];
    }

    public function rules(){
        $_rules = array();
        $setAttributeNames = array();
        $_rules['product_attribute'] = 'required';
        $setAttributeNames['product_attribute'] = trans('app.product_attribute');
        foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
            $_rules['name.'.$locale] = 'required|max:255';
            

            $setAttributeNames['name.' . $locale] = trans('app.name').' [ ' . $properties['native'].' ]';
        }
        return [
            'rules' => $_rules,
            'attributeNames' => $setAttributeNames
        ];
    }

    /**
     * @return mixed
     */
    public function all() {

        return $this->productAttributeOption
        ->orderBy('product_attribute_options.created_at', 'DESC')->get();

        /*return $this->productAttributeOption->select('product_attribute_options.*', 'product_attributes.name as product_attribute_name')
            ->join('product_attributes', 'product_attributes.id', '=', 'product_attribute_options.product_attribute_id')
            ->orderBy('product_attribute_options.created_at', 'DESC')
            ->get();*/
    }

    /**
     * @param $limit
     * @return mixed
     */
    public function getLastProductAttributeOption($limit) {
        return $this->productAttributeOption->orderBy('created_at', 'desc')->take($limit)->offset(0)->get();
    }

    /**
     * @return mixed
     */
    public function lists() {
        return $this->productAttributeOption->all()->lists('name', 'id');
    }

    /**
     * Get paginated productAttributeOptions
     *
     * @param int $page Number of productAttributeOptions per page
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
        //\DB::enableQueryLog();



        $query = $this->productAttributeOption->with(['translations', 'productAttribute.translations'])->orderBy('created_at', 'DESC');
        /*$query = $this->productAttributeOption->select('product_attribute_options.*', 'product_attributes.name as product_attribute_name')
            ->join('product_attributes', 'product_attributes.id', '=', 'product_attribute_options.product_attribute_id')
            ->join('product_attribute_translations', 'product_attribute_translations.product_attribute_id', '=', 'product_attributes.id')
            ->orderBy('product_attribute_options.created_at', 'DESC');*/

        /*if(!$all) {
            $query->where('is_published', 1);}*/

                
        if(is_array($filter)){
            foreach($filter as $ff => $term){
                if(trim($term) != ''){
                    switch ($ff) {
                        case 'product_attribute':
                            $query->where('product_attribute_id', $term);
                            /*$query->whereHas('productAttribute', function($q) use ($term) {
                                $q->where('product_attribute_key', $term);
                            });*/
                            break;
                        case 'name':
                            $query->whereHas('productAttributeOptionTranslations', function($q) use ($term) {
                                $q->where('name', 'like', '%'.$term.'%');
                            });
                            break;
                    }
                }
            }
        }
        
        $productAttributeOptions = $limit == 0? $query->get():$query->skip($limit * ($page - 1))->take($limit)->get();
        //dd(\DB::getQueryLog());
        $result->totalItems = $this->totalProductAttributeOptions($filter);
        $result->items = $productAttributeOptions->all();
        return $result;

/*




        $productAttributeOptions = $query->skip($limit * ($page - 1))->take($limit)->get();
        $result->totalItems = $this->totalProductAttributeOptions($all);
        $result->items = $productAttributeOptions->all();

        return $result;*/
    }

    /**
     * @param $id
     * @return mixed
     */
    public function find($id) {
        //dd($this->productAttributeOption->with(['productAttribute'])->findOrFail($id)->toArray());
        return $this->productAttributeOption->with(['productAttribute'])->findOrFail($id);
    }

    public function findAllByNameLike($name){
        return $this->productAttributeOption->with(['productAttributeOptionTranslation', 'productAttribute'])->whereHas('productAttributeOptionTranslations', function($q) use ($name){
            $q->where('name', 'like', '%'.$name.'%');
        })->get();
    }

    public function findAllByName($name){
        return $this->productAttributeOption->with(['productAttributeOptionTranslation', 'productAttribute'])->whereHas('productAttributeOptionTranslations', function($q) use ($name){
            $q->where('name', $name);
        })->first();
    }

    /**
     * @param $attributes
     * @return bool|mixed
     * @throws \App\Exceptions\Validation\ValidationException
     */
    public function create($attributes) {
        if($this->isValid($attributes)) {
            $t_attributes = array();
            $t_attributes['product_attribute_id'] = $attributes['product_attribute'];
            foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
                $t_attributes[$locale] = [
                    'name' => $attributes['name'][$locale]
                ];
            }
            $this->productAttributeOption->fill($t_attributes)->save();

            return true;
        }
        throw new ValidationException('Product attribute option validation failed', $this->getErrors());
    }

    /**
     * @param $id
     * @param $attributes
     * @return bool|mixed
     * @throws \App\Exceptions\Validation\ValidationException
     */
    public function update($id, $attributes) {
        if($this->isValid($attributes)) {
            $t_attributes = array();
            $t_attributes['product_attribute_id'] = $attributes['product_attribute'];
            $this->productAttributeOption = $this->find($id);
            foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
                $t_attributes[$locale] = [
                    'name' => $attributes['name'][$locale]
                ];
            }
            $this->productAttributeOption->fill($t_attributes)->save();

            return true;
        }

        throw new ValidationException('Product attribute option validation failed', $this->getErrors());
    }

    /**
     * @param $id
     * @return mixed|void
     */
    public function delete($id) {

        $productAttributeOption = $this->productAttributeOption->findOrFail($id);
        $productAttributeOption->delete();
    }

    /**
     * @param $id
     * @return mixed
     */
    public function togglePublish($id) {

        $productAttributeOption = $this->productAttributeOption->find($id);

        $productAttributeOption->is_published = ($productAttributeOption->is_published) ? false : true;
        $productAttributeOption->save();

        return Response::json(array('result' => 'success', 'changed' => ($productAttributeOption->is_published) ? 1 : 0));
    }

    /**
     * @param $id
     * @return string
     */
    function getUrl($id) {

        $productAttributeOption = $this->productAttributeOption->findOrFail($id);
        return url('productAttributeOption/' . $id . '/' . $productAttributeOption->slug, $parameters = array(), $secure = null);
    }

    /**
     * Get total productAttributeOption count
     * @param bool $all
     * @return mixed
     */
    protected function totalProductAttributeOptions($filter = array()) {
        $query = $this->productAttributeOption->orderBy('created_at', 'DESC');
        /*if(!$all) {
            return $this->productAttributeOption->where('is_published', 1)->count();
        }*/

        if(is_array($filter)){
            foreach($filter as $ff => $term){
                if(trim($term) != ''){
                    switch ($ff) {
                        case 'product_attribute':
                            $query->where('product_attribute_id', $term);
                            break;
                        case 'name':
                            $query->whereHas('productAttributeOptionTranslation', function($q) use ($term) {
                                $q->where('name', 'like', '%'.$term.'%');
                            });
                            break;
                    }
                }
            }
        }

        return $query->count();
    }


    public function bulkProductAttributeOption_process($cell_map, $items) {
      if(count($items)){
        $errors = [];
        $debug = [];
        foreach($items as $item_idx => $attributes){
               $product_attribute = $this->productAttribute->findByName($attributes['product_attribute']);
               if(!$product_attribute){ // location harus ditemukan
                   $errors['rows'][$item_idx][] = [ 'cell' => $cell_map['product_attribute'], 'error' => 'not_exist' ];//'B';//karena kolom milik location
              
               }else{
                  $t_attributes['product_attribute_id'] = $product_attribute->id;
              }
  
              //$t_attributes['is_published'] = $attributes['is_published']; 
              foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
                  $t_attributes[$locale] = [
                      'name' => $attributes['name'][$locale],
                      //'slug' => str_slug($attributes['hotel_name']),
                      //'body' => $attributes['description'][$locale],
                      //'meta_keywords' => $attributes['meta_keywords'][$locale],
                      //'meta_description' => $attributes['meta_description'][$locale]
                  ];
              }

              if(empty($errors['rows'][$item_idx])){
                   if(isset($attributes['id']) && $attributes['id'] != ''){//update
                     $this->productAttributeOption = $this->find($attributes['id']);
                     //$this->product->productMetas()->delete(); 
                     //$productAttributeOption = 
                     $this->productAttributeOption->update($t_attributes);
                     //$productAttributeOption_id = $attributes['id']; 
                   }else{//create new
                        //pastikan nama product attribute option unique
                        foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
                            $check_product = $this->findAllByName($attributes['name'][$locale]);
                            if($check_product){ //exist
                                $errors['rows'][$item_idx][] = [ 'cell' => $cell_map['name_'.$locale], 'error' => 'exist' ];
                            }//else{//harus unique title , seharusnya sih harus unique slug juga
                                   // $productAttributeOption = $this->productAttributeOption->create($t_attributes);
                                   // $productAttributeOption_id = $productAttributeOption->id;
                            //}
                        }
                        
                        if(isset($errors['rows']) && isset($errors['rows'][$item_idx]) && count($errors['rows'][$item_idx])){ 
                            //echo '<pre>';
                            //var_dump('tes');
                            //echo '</ pre>';

                            //do nothing
                            //$productAttributeOption = 
                            //$this->productAttributeOption->create($t_attributes);
                            //$productAttributeOption_id = $productAttributeOption->id;
                        }else{
                            //echo '<pre>';
                            //var_dump($t_attributes);
                            //echo '</ pre>';
                            $this->productAttributeOption->create($t_attributes);
                        }
                    }

                 /*
                 $product_meta_keys = [ 'hotel_address', 'hotel_main_facilities', 'hotel_star', 'hotel_latitude', 'hotel_longitude' ];
                 $t_product_meta_keys = [ 'hotel_main_facilities' ];
                       if(!empty($product_meta_keys) && isset($product_id)){ //kalau exist maka variable product_id tidak diset
                         foreach($product_meta_keys as $meta_key){
                           $t_metaAttributes['post_type'] = 'hotel';
                           $t_metaAttributes['product_id'] = $product_id;
                           $t_metaAttributes['meta_key'] = $meta_key;
                           //has translations
                           foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
                             $t_metaAttributes[$locale] = [
                             'meta_value' => in_array($meta_key, $t_product_meta_keys)?$attributes[$meta_key][$locale]:$attributes[$meta_key]
                             ];
                         }
                         $this->productMeta->create($t_metaAttributes);
                     }*/
                 }
             } //dd('stop');
               return $errors;
           }else{
             throw new ValidationException('Product Attribute Option validation failed', $this->getErrors());
         }

     }
}
