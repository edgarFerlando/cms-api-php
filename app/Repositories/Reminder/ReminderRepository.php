<?php namespace App\Repositories\Reminder;

use Config;
use App\Models\Reminder;
use App\Repositories\RepositoryAbstract;
use App\Repositories\CrudableInterface;
use App\Repositories\RepositoryInterface as RepositoryInterface;
use App\Exceptions\Validation\ValidationException;
use App\Repositories\AbstractValidator as Validator;
use LaravelLocalization;
use URL;
use App\ProductAttributeTaxonomy;
use Auth;
use App\Models\ReminderMeta;
use DB;
use Input;
use Route;
use Request;
use Carbon\Carbon;

class ReminderRepository extends RepositoryAbstract implements ReminderInterface, CrudableInterface {

    /**
     * @var
     */
    protected $perPage;
    /**
     * @var \Taxonomy
     */
    protected $reminder;
    /**
     * Rules
     *
     * @var array
     */
    protected static $rules;
    protected static $attributeNames;

    /**
     * @param Category $taxonomy
     */
    public function __construct(Reminder $reminder) {

        $this->reminder = $reminder;
        //$this->perPage = Config::get('holiday.per_page');
        $rules_n_attributeNames = $this->rules();
        self::$rules = $rules_n_attributeNames['rules'];
        self::$attributeNames = $rules_n_attributeNames['attributeNames'];
    }

    public function rules(){
        $_rules = array();
        $setAttributeNames = array();
        $_rules['module_name'] = 'required';
        $_rules['screen_name'] = 'required';
        $_rules['reminder_datetime'] = 'required';

        switch (Input::get('module_name')) {
            case 'free':
                $method = Request::method();
                $_rules['about'] = 'required';
                switch($method)
                {
                    case 'POST'://create
                    break;
                    case 'PATCH'://update
                    break;
                    default:
                    break;
                }
                break;
            case 'schedule' : 
                $method = Request::method();
                $_rules['cfp_schedule_id'] = 'required';
                switch($method)
                {
                    case 'POST'://create
                    break;
                    case 'PATCH'://update
                    break;
                    default:
                    break;
                }
                break;
            break;
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
        return $this->reminder->find($id);
    }

    public function createBulk($reminders) {
        foreach ($reminders as $reminder) {
            $this->create($reminder);
        }
    }

    /**
     * @param $attributes
     * @return bool|mixed
     * @throws \App\Exceptions\Validation\ValidationException
     */
    public function create($attributes) {
        if($this->isValid($attributes)) {
            DB::beginTransaction();
            $user_id = $attributes['user_id'];
            $t_attributes = [ 
                'user_id' => $user_id,
                'module_name' => $attributes['module_name'],
                'screen_name' => $attributes['screen_name'],
                'reminder_datetime' => Carbon::parse($attributes['reminder_datetime'])->format('Y-m-d H:i:s'),
                'is_repeated' => (isset($attributes['is_repeated']) && $attributes['is_repeated'] != '' )?$attributes['is_repeated']:'does_not_repeat',
                'created_by' => $user_id,
                'created_at' => Carbon::now(),
                'updated_by' => $user_id,
                'updated_at' => Carbon::now(),
                'record_flag' => 'N',
                'is_predefined' => (isset($attributes['is_predefined']) && $attributes['is_predefined'] != '' )?$attributes['is_predefined']:0
            ];
            //dd($t_attributes);
            $reminder = $this->reminder->create($t_attributes);

            $meta_map = [
                'next_reminder_datetime' => [ 'meta_key' => 'next_reminder_datetime', 'type' => 'text' ]
            ];
            switch ($attributes['module_name']) {
                case 'free':
                    $meta_map += [
                        'about' => [ 'meta_key' => 'about', 'type' => 'text' ],
                        'note' => [ 'meta_key' => 'note', 'type' => 'text' ]
                    ];
                    break;
                case 'schedule' : 
                    $meta_map += [
                        'cfp_schedule_id' => [ 'meta_key' => 'cfp_schedule_id', 'type' => 'text' ]
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

                ReminderMeta::insert([
                    'reminder_id' => $reminder->id,
                    'meta_key' => $meta_attr['meta_key'],
                    'meta_value' => $attributes[$ff_name] 
                ]);
            }

            DB::commit();
            return $reminder;
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
            $user_id = $attributes['user_id'];
            $t_attributes = [ 
                'user_id' => $user_id,
                'module_name' => $attributes['module_name'],
                'screen_name' => $attributes['screen_name'],
                'reminder_datetime' => Carbon::parse($attributes['reminder_datetime'])->format('Y-m-d H:i:s'),
                'is_repeated' => (isset($attributes['is_repeated']) && $attributes['is_repeated'] != '' )?$attributes['is_repeated']:'does_not_repeat',
                'updated_by' => $user_id,
                'updated_at' => Carbon::now(),
                'record_flag' => 'U'
            ];

            $existing_metas_q = ReminderMeta::where('reminder_id', $id)->get();
            $existing_metas = userMeta($existing_metas_q);
            $this->reminder = $this->find($id);
/*
            $t_attributes['parent_id'] = $attributes['parent']?$attributes['parent']:null;
            $t_attributes['image'] = isset($attributes['image'])?getImagePath($attributes['image']):'';
            
            $t_attributes['title'] = $attributes['title'];
            $t_attributes['slug'] = $attributes['slug'];
            $t_attributes['updated_by'] = Auth::user()->id;*/
            /*$langs = LaravelLocalization::getSupportedLocales();
            foreach ($langs as $localeCode => $properties) {
                $t_attributes[$localeCode] = [
                    'title' => $attributes['title'][$localeCode],
                    'slug' => $attributes['slug'][$localeCode],
                ];
            }*/
            $this->reminder->fill($t_attributes)->save();

            $meta_map = [
                'next_reminder_datetime' => [ 'meta_key' => 'next_reminder_datetime', 'type' => 'text' ]
            ];
            switch ($attributes['module_name']) {
                case 'free':
                    $meta_map += [
                        'about' => [ 'meta_key' => 'about', 'type' => 'text' ],
                        'note' => [ 'meta_key' => 'note', 'type' => 'text' ]
                    ];
                    break;
                case 'schedule' : 
                    $meta_map += [
                        'cfp_schedule_id' => [ 'meta_key' => 'cfp_schedule_id', 'type' => 'text' ]
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

                $meta_key = $meta_attr['meta_key']; //dd(isset($existing_metas->$meta_key));
                if(isset($existing_metas->$meta_key)){ 
                    ReminderMeta::where('reminder_id', $id)
                    ->where('meta_key', $meta_key)
                    ->update(['meta_value' => $attributes[$ff_name]]);
                }else{
                    ReminderMeta::insert([
                        'reminder_id' => $id,
                        'meta_key' => $meta_key,
                        'meta_value' => $attributes[$ff_name] 
                    ]);
                }
            }

            DB::commit();
            return $this->reminder;
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