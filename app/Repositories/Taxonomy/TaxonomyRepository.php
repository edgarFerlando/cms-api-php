<?php namespace App\Repositories\Taxonomy;

use Config;
use App\Taxonomy;
use App\Repositories\RepositoryAbstract;
use App\Repositories\CrudableInterface;
use App\Repositories\RepositoryInterface as RepositoryInterface;
use App\Exceptions\Validation\ValidationException;
use App\Repositories\AbstractValidator as Validator;
use LaravelLocalization;
use URL;
use App\ProductAttributeTaxonomy;
use Auth;
use App\Models\TaxonomyMeta;
use DB;
use Input;
use Route;
use Request;

class TaxonomyRepository extends RepositoryAbstract implements TaxonomyInterface, CrudableInterface {

    /**
     * @var
     */
    protected $perPage;
    /**
     * @var \Taxonomy
     */
    protected $taxonomy;
    /**
     * Rules
     *
     * @var array
     */
    protected static $rules;/* = [
        'title' => 'required|min:3|unique:taxonomies'
    ];*/
    protected static $attributeNames;

    /**
     * @param Category $taxonomy
     */
    public function __construct(Taxonomy $taxonomy) {

        $this->taxonomy = $taxonomy;
        $this->perPage = Config::get('holiday.per_page');
        $rules_n_attributeNames = $this->rules();
        self::$rules = $rules_n_attributeNames['rules'];
        self::$attributeNames = $rules_n_attributeNames['attributeNames'];
    }

    public function rules(){
        $_rules = array();
        $setAttributeNames = array();
        //$_rules['image'] = 'required';
        $_rules['title'] = 'required|max:255|min:3';
        $_rules['slug'] = 'required|alpha_dash|max:255';

        switch (Input::get('post_type')) {
            case 'branch':
                $method = Request::method();
                switch($method)
                {
                    case 'POST'://create
                        //unique branch code dari tabel taxonomy_metas saja
                        $_rules['branch_code'] = 'required|unique:taxonomy_metas,meta_value|min:3|max:3';
                    break;
                    case 'PATCH'://update
                        $taxo_id = Route::current()->getParameter('id');
                        $old_data = TaxonomyMeta::where('taxonomy_id', $taxo_id)->first();
                        if(!is_null($old_data) && $old_data->meta_value == Input::get('branch_code')){
                            $_rules['branch_code'] = 'required|min:3|max:3';
                        }else{
                            $_rules['branch_code'] = 'required|unique:taxonomy_metas,meta_value|min:3|max:3';
                        }
                    break;
                    default:
                        $_rules['branch_code'] = 'required|unique:taxonomy_metas,meta_value|min:3|max:3';
                    break;
                }
            break;
            case 'financial_health_structure':
                $method = Request::method();
                switch($method)
                {
                    case 'POST':
                    case 'PATCH':
                    default:
                        $_rules['description'] = 'required';
                        $_rules['input_name'] = 'required';
                    break;
                }
            break;
        }

        //$setAttributeNames['title.' . $locale] = 'Title [ ' . $properties['native'].' ]';
        //$setAttributeNames['slug.' . $locale] = 'Slug [ ' . $properties['native'].' ]';
        
        return [
            'rules' => $_rules,
            'attributeNames' => $setAttributeNames
        ];
    }

    /**
     * @return mixed
     */
    public function all() {
        return $this->taxonomy->orderBy('order', 'asc')->get();
    }

    /**
     * @param int $page
     * @param int $limit
     * @param bool $all
     * @return mixed|\StdClass
     */
    public function paginate($page = 1, $limit = 10, $all = false) {

        $result = new \StdClass;
        $result->page = $page;
        $result->limit = $limit;
        $result->totalItems = 0;
        $result->items = array();

        $query = $this->taxonomy;//->orderBy('title');

        $taxonomies = $query->skip($limit * ($page - 1))->take($limit)->get();

        $result->totalItems = $this->totalCategories();
        $result->items = $taxonomies->all();

        return $result;
    }

    /**
     * @return mixed
     */
    public function lists() {

        return $this->taxonomy->all()->lists('title', 'id');
    }

    /**
     * @param $id
     * @return mixed
     */
    public function find($id) {
        //return $this->taxonomy->with(['productAttributeTaxonomy.productAttribute.productAttributeTranslation'])->findOrFail($id);
        return $this->taxonomy->findOrFail($id);
    }

    public function findWithParentDetail($id) {
        //return $this->taxonomy->with(['productAttributeTaxonomy.productAttribute.productAttributeTranslation'])->findOrFail($id);
        return $this->taxonomy->with('parentDetail')->findOrFail($id);
    }

    public function findWithMetas($id) {
        return $this->taxonomy->with('taxoMetas')->findOrFail($id);
    }

    public function findBySlug($slug, $post_type){
        return $this->taxonomy->where('post_type', $post_type)->where('slug', $slug)->first();
    }

    public function findBySlugs($slug, $post_type){
        return $this->taxonomy->where('post_type', $post_type)->whereIn('slug', $slug)->get();
    }

    public function findByPost($post_type){
        return $this->taxonomy->where('post_type', $post_type)->get();
    }

    public function findByDate($slug, $post_type, $start_date){
        return $this->taxonomy->with('trips.productMetas')->where('post_type', $post_type)->where('slug', $slug)
        ->whereHas('trips.productMetas', function($q1) use ($start_date) {
                      $q1->where('meta_key', 'start_date')
                      ->whereHas('productMetaTranslation', function($q2) use ($start_date) {
                        $q2->where('meta_value', '>=', $start_date);
                      });
        })->first();
    }

    public function findBySlugMulti($slug, $post_type){
        return $this->taxonomy->where('post_type', $post_type)->whereIn('slug', $slug)->get();
    }

    public function findByTitle($title, $post_type){
        return $this->taxonomy->where('post_type', $post_type)->where('title', $title)->first();
    }

    public function findByTitle_ParentNotNull($title, $post_type){
        return $this->taxonomy->where('post_type', $post_type)->whereNotNull('parent_id')->where('title', $title)->first();
    }

    public function findAllByTitleLike($title, $post_type){
        return $this->taxonomy->where('post_type', $post_type)->where('title', 'like', '%'.$title.'%')
        ->orderBy('depth')->get();
    }

    public function findAllByTitleUser($id, $post_type){
        return $this->taxonomy->where('post_type', $post_type)->whereHas('product', function($q) use ($id){
            $q->where('created_by', $id);
        })->orderBy('depth')->get();
    }

    /**
     * @param $slug
     * @return mixed
     */
    public function getTermsByPostType($post_type) {
        return $this->taxonomy->where('post_type', $post_type)->get();
        
        //return $this->taxonomy->with(['translations'])->where('post_type', $post_type)->get()->toHierarchy();// tanpa ->toHierarchy() akan ada 140 query
    }

    public function getTermsByPostType2($post_type) {
        return $this->taxonomy->where('post_type', $post_type)->where('title', '=', 'Simple')->first()->getDescendantsAndSelf();
    }

    public function getTermsByPostType_n_parent($post_type, $parent_slug) {
        $taxo = $this->taxonomy->where('post_type', $post_type)->where('slug', $parent_slug)->first();
        return is_null($taxo)?null:$taxo->getDescendants();
    }







    /**
     | Self financial checkup ...
     | Financila Checkup Client ...
     | ----------------------------------------------
     | 29 Agustus 2018
     | Gugun DP
     |
     | Function ini copy dari function 'getTermsByPostType_n_parent'
     | dimana funcsi 'getTermsByPostType_n_parent' digunakan untuk financial checkup oleh CFP
     | sedangkan funcsi ini 'createSelf' digunakan supaya Client bisa melakukan financial checkup sendiri
     */

    public function getCategoryPengeluaranPendapatan($post_type, $parent_slug) {
        
        $taxo = $this->taxonomy->where('post_type', $post_type)->where('slug', $parent_slug)->first();

        return is_null($taxo)?null:$taxo->getDescendants();
    }






    

    /*public function getTermsByPostType_compact($post_type) {//hanya select kolom tertentu saja
        return $this->taxonomy
        ->select('id', 'title', 'slug')
        ->where('post_type', $post_type)->get();
        
        //return $this->taxonomy->with(['translations'])->where('post_type', $post_type)->get()->toHierarchy();// tanpa ->toHierarchy() akan ada 140 query
    }*/

    public function getTermsBy($filter) {
        //\DB::enableQueryLog();
        $query = $this->taxonomy->orderBy('id');//->with('translations');
        if(is_array($filter)){
            foreach($filter as $ff => $term){
                if(trim($term) != ''){
                    switch ($ff) {
                        case 'level' :
                            $query->where('depth', $term);
                            
                        break;
                        case 'post_type':
                            $query->where('post_type', $term);
                        break;
                    }
                }
            }
        }
        //$query->get();
        //dd(\DB::getQueryLog());
        return $query->get();
        //return $this->taxonomy->with(['translations'])->where('post_type', $post_type)->get()->toHierarchy();// tanpa ->toHierarchy() akan ada 140 query
    }

    public function getMaxOrder() {
        $taxonomy = $this->taxonomy->orderBy('order', 'desc')->first();
        if(isset($taxonomy))
            return $taxonomy->order;
        return 0;
    }

    /**
     * @param $attributes
     * @return bool|mixed
     * @throws \App\Exceptions\Validation\ValidationException
     */
    public function create($attributes) {
        if($this->isValid($attributes)) {

            DB::beginTransaction();
            $t_attributes['post_type'] = $attributes['post_type'];
            $t_attributes['image'] = isset($attributes['image'])?getImagePath($attributes['image']):'';
            $t_attributes['color'] = $attributes['color'][0];
            $t_attributes['description'] = $attributes['description'];
            $t_attributes['parent_id'] = $attributes['parent']?$attributes['parent']:null;
            $t_attributes['order'] = $this->getMaxOrder() + 1;
            $t_attributes['title'] = $attributes['title'];
            $t_attributes['slug'] = $attributes['slug'];
            $t_attributes['created_by'] = Auth::user()->id;
            /*$langs = LaravelLocalization::getSupportedLocales();
            foreach ($langs as $localeCode => $properties) {
                $t_attributes[$localeCode] = [
                    'title' => $attributes['title'][$localeCode],
                    'slug' => $attributes['slug'][$localeCode],
                ];
            }*/
            $taxonomy = $this->taxonomy->create($t_attributes);
            $meta_map = [];
            switch ($attributes['post_type']) {
                case 'branch':
                    $meta_map += [
                        'branch_code' => [ 'meta_key' => 'branch_code', 'type' => 'text' ]
                    ];
                    break;
                case 'financial_health_structure':
                    $meta_map += [
                        'description' => [ 'meta_key' => 'description', 'type' => 'text' ],
                        'input_name' => [ 'meta_key' => 'input_name', 'type' => 'text' ]
                    ];
                    break;
            }

            //structure
            //$meta_map = [
            //    'branch_code' => [ 'meta_key' => 'branch_code', 'type' => 'text', 'ff_name_alias' => 'xxx' ]
            //];
            foreach($meta_map as $ff_name => $meta_attr){

                switch ($meta_attr['type']) {
                    case 'image' :
                            $attributes[$ff_name] = getImagePath($attributes[$ff_name]);
                        break;
                    default:
                        $attributes[$ff_name] = $attributes[$ff_name];
                        break;
                }

                /*if($meta_key == 'user_thumbnail')
                {
                    $attributes[$ff_name] = getImagePath($attributes['user_thumbnail']);
                }

                if($meta_key == 'user_image')
                {
                    $attributes[$ff_name] = getImagePath($attributes['user_image']);
                }

                if($meta_key == 'ktp_image')
                {
                    $attributes[$ff_name] = getImagePath($attributes['ktp_image']);
                }

                if($meta_key == 'name')
                {
                    $attributes['first_name'] = $attributes['name'];
                }*/

                TaxonomyMeta::create([
                    'taxonomy_id' => $taxonomy->id,
                    'meta_key' => $meta_attr['meta_key'],
                    'meta_value' => $attributes[$ff_name] 
                ]);
                
            }

            /*if(isset($attributes['product_attribute']) && in_array($attributes['post_type'], Config::get('holiday.postType_has_productAttribute'))){
                foreach($attributes['product_attribute'] as $productAttributeID ){
                    //\DB::enableQueryLog();
                    ProductAttributeTaxonomy::create([
                        'taxonomy_id' => $taxonomy->id,
                        'product_attribute_id' => $productAttributeID
                    ]);
                    //dd(\DB::getQueryLog());
                }
                
            }*/

            /*if(in_array($attributes['post_type'], Config::get('holiday.postType_has_productAttribute'))){
                foreach($attributes['product_attribute'] as $count => $productAttributeID){
                    $variant_attributes[$count]['product_id'] = $product->id;
                    $variant_attributes[$count]['product_sku_id'] = $sku_id;
                }
                ProductVariation::insert($variant_attributes);
            }*/


            DB::commit();
            return true;
        }
        throw new ValidationException('Category validation failed', $this->getErrors());
    }

    /**
     * @param $id
     * @param $attributes
     * @return bool|mixed
     * @throws \App\Exceptions\Validation\ValidationException
     */
    public function update($id, $attributes) {
        if($this->isValid($attributes)) {
            DB::beginTransaction();
            $existing_metas_q = TaxonomyMeta::where('taxonomy_id', $id)->get();
            $existing_metas = userMeta($existing_metas_q);
            $t_attributes['parent_id'] = $attributes['parent']?$attributes['parent']:null;
            $t_attributes['image'] = isset($attributes['image'])?getImagePath($attributes['image']):'';
            $this->taxonomy = $this->find($id);
            $t_attributes['title'] = $attributes['title'];
            $t_attributes['slug'] = $attributes['slug'];
            $t_attributes['updated_by'] = Auth::user()->id;
            $t_attributes['color'] = '#'.$attributes['color'];
            $t_attributes['description'] = $attributes['description'];
            /*$langs = LaravelLocalization::getSupportedLocales();
            foreach ($langs as $localeCode => $properties) {
                $t_attributes[$localeCode] = [
                    'title' => $attributes['title'][$localeCode],
                    'slug' => $attributes['slug'][$localeCode],
                ];
            }*/
            $this->taxonomy->fill($t_attributes)->save();
            $meta_map = [];
            switch ($attributes['post_type']) {
                case 'branch':
                    $meta_map += [
                        'branch_code' => [ 'meta_key' => 'branch_code', 'type' => 'text' ]
                    ];
                    break;
                case 'financial_health_structure':
                    $meta_map += [
                        'description' => [ 'meta_key' => 'description', 'type' => 'text' ],
                        'input_name' => [ 'meta_key' => 'input_name', 'type' => 'text' ]
                    ];
                    break;
            }

            foreach($meta_map as $ff_name => $meta_attr){
                switch ($meta_attr['type']) {
                    case 'image' :
                            $attributes[$ff_name] = getImagePath($attributes[$ff_name]);
                        break;
                    default:
                        $attributes[$ff_name] = $attributes[$ff_name];
                        break;
                }
                $meta_key = $meta_attr['meta_key'];
                if(isset($existing_metas->$meta_key)){
                    TaxonomyMeta::where('taxonomy_id', $id)
                    ->where('meta_key', $meta_key)
                    ->update(['meta_value' => $attributes[$ff_name]]);
                }else{
                    TaxonomyMeta::create([
                        'taxonomy_id' => $id,
                        'meta_key' => $meta_key,
                        'meta_value' => $attributes[$ff_name] 
                    ]);
                }
            }
            DB::commit();
            return true;
        }
        throw new ValidationException('Category validation failed', $this->getErrors());
    }

    /**
     * @param $id
     * @return mixed|void
     */
    public function delete($id) {

        $this->taxonomy = $this->taxonomy->find($id);
        $this->taxonomy->delete();
    }

    /**
     * Get total taxonomy count
     * @return mixed
     */
    protected function totalCategories() {

        return $this->taxonomy->count();
    }

    /* nested structure */
    public function generateMenu($post_type, $taxonomies, $parentId = 0) {

        $result = null;
        foreach($taxonomies as $item) {

            if($item->parent_id == $parentId) {
                $iconClass = ($item->is_published) ?'fa fa-eye' : 'fa fa-eye-slash';
                $result .= "<li class='dd-item' data-id='{$item->id}'>
                <button type='button' data-action='collapse'><i class='fa fa-minus-square-o'></i></button>
                <button type='button' data-action='expand' style='display: none;'><i class='fa fa-plus-square-o'></i></button>
                <div class='dd-handle'></div>
                <div class='dd-content'><a href='" . URL::route('admin.taxonomy.show', [ $post_type, $item->id ]) . "'>{$item->title}</a>
                <div class='ns-actions'>
                <a title='Edit Menu' class='edit-menu' href='" . URL::route('admin.taxonomy.edit', [ $post_type, $item->id ]) . "'><i class='fa fa-pencil'></i></a>
                <a class='delete-menu' href='" . URL::route('admin.taxonomy.delete', [ $post_type, $item->id ]) . "'><i class='fa fa-trash-o'></i></a>
                <input type='hidden' value='1' name='menu_id'>
                </div>
                </div>" . $this->generateMenu($post_type, $taxonomies, $item->id) . "
                </li>";
            }
        }
        return $result ? "\n<ol class=\"dd-list\">\n$result</ol>\n" : null;
    }

    public function getMenuHTML($post_type, $items) {
        return $this->generateMenu($post_type, $items);
    }

    function htmlHieOffCanvas($nodes){
        $html = '<ul class="off-canvas-list"><li><label>'.trans('app.category').'</label></li>';
            foreach($nodes as $node):
            $html .= $this->renderNodeOffCanvas($node);
            endforeach;
        $html .= '</ul>';
        return $html;
    }

    function renderNodeOffCanvas($node) {
        LaravelLocalization::transRoute('routes.product_category_slug');
      if(is_null($node->parent_id) && count($node->children) == 0){
        return '<li class="'.setActive($node->url).'"><a href="'.trans_url_locale('product-category/{category}', [ 'category' => $node->slug ]).'">' . $node->title . '</a></li>';
      }elseif( $node->isLeaf()) {
        return '<li class="'.setActive($node->url).'"><a href="'.trans_url_locale('product-category/{category}', [ 'category' => $node->slug ]).'">' . $node->title . '</a></li>';
      }else {
        $html = '<li class="'.setActive($node->url).' has-submenu"><a href="#">'.$node->title.'</a>';

        $html .= '<ul class="left-submenu"><li class="back"><a href="#">Back</a></li>';

        foreach($node->children as $child)
          $html .= $this->renderNodeOffCanvas($child);

        $html .= '</ul>';

        $html .= '</li>';
      }

      return $html;
    }

    public function parseJsonArray($jsonArray, $parentID = 0) {

        $return = array();

        foreach($jsonArray as $subArray) {

            $returnSubArray = array();

            if(isset($subArray['children'])) {
                $returnSubArray = $this->parseJsonArray($subArray['children'], $subArray['id']);
            }

            $return[] = array('id' => $subArray['id'], 'parentID' => $parentID);
            $return = array_merge($return, $returnSubArray);
        }

        return $return;
    }

    //include taxo meta hierarchy
    public function buildWithMetas($jsonArray, $parentID = 0) {

        $return = array();
        $idx = 0;
        foreach($jsonArray as $subArray) {

            $returnSubArray = array();

            if(isset($subArray['children'])) {
                $returnSubArray['children'] = $this->buildWithMetas($subArray['children'], $subArray['id']);
            }

            $safe_item = [
                'id' => $subArray['id'], 
                'parentID' => $parentID,
                'post_type'=> $subArray['post_type'], 
                'image'=> $subArray['image'], 
                'title'=> $subArray['title'], 
                'slug'=> $subArray['slug'], 
                'url'=> $subArray['url'], 
            ];

            $taxoMeta = (array)userMeta($subArray->taxoMetas);
			$safe_item = array_merge($safe_item, $taxoMeta);
            $return[$idx] = array_merge($safe_item, $returnSubArray);
            $idx++;
        }

        return $return;
    }

    //untuk keperluan penamaan input_name API financial health checkup
   /* public function buildInputNameWithMetas($jsonArray, $parentID = 0) {

        $return = array();
        $idx = 0;
        foreach($jsonArray as $subArray) {

            $returnSubArray = array();

            if(isset($subArray['children'])) {
                $returnSubArray['children'] = $this->buildInputNameWithMetas($subArray['children'], $subArray['id']);
            }

            $safe_item = [
                'id' => $subArray['id'], 
                'parentID' => $parentID,
                'post_type'=> $subArray['post_type'], 
                'image'=> $subArray['image'], 
                'title'=> $subArray['title'], 
                'slug'=> $subArray['slug'], 
                'url'=> $subArray['url'], 
            ];

            $taxoMeta = (array)userMeta($subArray->taxoMetas);
			$safe_item = array_merge($safe_item, $taxoMeta);
            $return[$idx] = array_merge($safe_item, $returnSubArray);
            $idx++;
        }

        return $return;
    }*/
    
    public function changeParentById($data) {
        foreach($data as $k => $v) {
            $item = $this->taxonomy->find($v['id']);
            $item->parent_id = $v['parentID'] == 0?null:$v['parentID'];
            $item->order = $k + 1;
            $item->save();
        }
    }

    public function roots($post_type){
        return $this->taxonomy->where('post_type', $post_type)->where('depth', 0)->get();
    }

    /*public function testingcolumn(){
        return $this->taxonomy->with('trips.productMetas')->where('post_type', $post_type)->where('slug', $slug)
        ->whereHas('trips.productMetas', function($q1) use ($start_date) {
                      $q1->where('meta_key', 'start_date')
                      ->whereHas('productMetaTranslation', function($q2) use ($start_date) {
                        $q2->where('meta_value', '>=', $start_date);
                      });
        })->first();
    }*/
}