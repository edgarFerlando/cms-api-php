<?php namespace App\Repositories\FinancialHealth;

use Config;
use App\Models\FinancialHealth;
use App\Repositories\RepositoryAbstract;
use App\Repositories\CrudableInterface;
use App\Repositories\RepositoryInterface as RepositoryInterface;
use App\Exceptions\Validation\ValidationException;
use App\Repositories\AbstractValidator as Validator;
use LaravelLocalization;
use URL;
use App\ProductAttributeTaxonomy;
use Auth;
use App\Models\FinancialHealthMeta;
use DB;
use Input;
use Route;
use Request;
use Carbon\Carbon;

use App\Taxonomy;
use App\Repositories\Taxonomy\TaxonomyRepository;

use App\User;
use App\Repositories\User\UserRepository;

class FinancialHealthRepository extends RepositoryAbstract implements FinancialHealthInterface, CrudableInterface {

    /**
     * @var
     */
    protected $perPage;
    /**
     * @var \Taxonomy
     */
    protected $FinancialHealth;
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
    public function __construct(FinancialHealth $FinancialHealth) {

        $this->FinancialHealth = $FinancialHealth;
        $rules_n_attributeNames = $this->rules();
        self::$rules = $rules_n_attributeNames['rules'];
        self::$attributeNames = $rules_n_attributeNames['attributeNames'];
        $this->taxonomy = new TaxonomyRepository(new Taxonomy);
        $this->user = new UserRepository(new User);
    }

    public function rules(){
        $_rules = array();
        $setAttributeNames = array();
        $_rules['user_id'] = 'required';
        $_rules['mail_to'] = 'required|email';

        $setAttributeNames['user_id'] = trans('app.user');
        $setAttributeNames['mail_to'] = trans('app.email');

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
        return $this->FinancialHealth->with('metas')->find($id);
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
            $user_dtl = $this->user->findWithMetas($user_id);
            if(is_null($user_dtl)){
                throw new ValidationException('Financial health validation failed', [
                       'user' => 'not found',
                ]);
            }

            //structure
            $items_raw = $this->taxonomy->getTermsByPostType('financial_health_structure')->toHierarchy(); 
            $items = $this->taxonomy->buildWithMetas($items_raw); //    dd($items);
            $input_names_required = renderTaxoInputName($items, 'slug');

            $input_names_titles = renderTaxoTitle($items,'slug', 'id');
            $input_names_not_set = [];
            $input_names_submited = [];
            $attributes_data_safe = [];
            if(isset($attributes['data'])){
                foreach ($attributes['data'] as $type => $input_names) {
                    foreach ($input_names as $input_key => $input_value) {
                        $input_names_submited[] = $type.'_'.$input_key;
                        $attributes_data_safe[$type.'_'.$input_key] = $input_value;
                    }
                }
            }

            //replace attribute data dengan format yang baru
            $attributes['data'] = $attributes_data_safe;
            //dd($attributes);

            //dd($input_names_submited);
            foreach ($input_names_required as $input_name_safe) {
                if(!in_array($input_name_safe, $input_names_submited)){
                    $input_name_safe_split = explode('_', $input_name_safe);
                    $input_name_form = str_replace($input_name_safe_split[0].'_', '', $input_name_safe);
                    
                    $input_names_not_set[$input_name_safe_split[0]][str_replace($input_name_safe_split[0].'_', '', $input_name_safe)] = 'Input name '.$input_name_form.' is required';
                }
            }

            if(count($input_names_not_set)){
                throw new ValidationException('Financial health validation failed', $input_names_not_set);
            }

            
            
            $t_attributes = [ 
                'user_id' => $user_id,
                'mail_to' => $attributes['mail_to'],
                'created_by' => $user_id,
                'created_at' => Carbon::now(),
                'updated_by' => $user_id,
                'updated_at' => Carbon::now(),
                'record_flag' => 'N'
            ];
            //dd($t_attributes);
            $financialHealth = $this->FinancialHealth->create($t_attributes);
            
            /*
                "pemasukan" => array:2 [
                "bulanan_rutin" => 111
                "tambahan" => 222
                ]
                "pengeluaran" => array:6 [
                "bulanan_rutin" => 333
                "makan" => 444
                "transportasi" => 555
                "hiburan" => 666
                "cicilan" => 777
                "hutang" => 888
                ]
            */
            $meta_map = [
                'pemasukan_bulanan_rutin' => [ 'meta_key' => 'pemasukan_bulanan_rutin', 'type' => 'text' ],
                'pemasukan_tambahan' => [ 'meta_key' => 'pemasukan_tambahan', 'type' => 'text' ],
                'pengeluaran_bulanan_rutin' => [ 'meta_key' => 'pengeluaran_bulanan_rutin', 'type' => 'text' ],
                'pengeluaran_makan' => [ 'meta_key' => 'pengeluaran_makan', 'type' => 'text' ],
                'pengeluaran_transportasi' => [ 'meta_key' => 'pengeluaran_transportasi', 'type' => 'text' ],
                'pengeluaran_hiburan' => [ 'meta_key' => 'pengeluaran_hiburan', 'type' => 'text' ],
                'pengeluaran_cicilan' => [ 'meta_key' => 'pengeluaran_cicilan', 'type' => 'text' ],
                'pengeluaran_hutang' => [ 'meta_key' => 'pengeluaran_hutang', 'type' => 'text' ]
            ];

            foreach($meta_map as $ff_name => $meta_attr){

                switch ($meta_attr['type']) {
                    case 'image' :
                            $attributes[$ff_name] = getImagePath($attributes['data'][$ff_name]);
                        break;
                    default:
                        $attributes[$ff_name] = $attributes['data'][$ff_name];
                        break;
                }

                FinancialHealthMeta::insert([
                    'financial_health_id' => $financialHealth->id,
                    'meta_key' => $meta_attr['meta_key'],
                    'meta_value' => $attributes['data'][$ff_name] 
                ]);
            }

            //send email
            $item = $this->find($financialHealth->id);
            $item_metas = userMeta($item->metas);
            $email_web_path = md5($user_dtl['email'].'|financialHealth|'.$financialHealth->id);
            //$data_html_all = []; //dd($item_metas);
            $data_pendapatan_count = 0;
            //$data_pendapatan_html = '<table class="row incomes-wrapper" style="border-collapse:collapse;border-spacing:0;display:table;padding:0;position:relative;text-align:left;vertical-align:top;width:100%"><tbody>';
            $data_pengeluaran_count = 0;
            //$data_pengeluaran_html = '<table class="row expenses-wrapper" style="border-collapse:collapse;border-spacing:0;display:table;padding:0;position:relative;text-align:left;vertical-align:top;width:100%"><tbody>';
            //$asset_email_url = 'http://mylife.whiteopendev.com/img/';//asset('img')
            $total_pendapatan = 0;
            $total_pengeluaran = 0;
            if($item_metas){
                foreach ($item_metas as $item_meta_key => $item_meta) {
                    if(isset($input_names_titles[$item_meta_key])){
                        $input_name_raw = explode('_', $item_meta_key);
                        $input_type_form = $input_name_raw[0];
                        $input_name_title = $input_names_titles[$item_meta_key];
                        //$data_html_all[$input_type_form][$input_name_title] = $item_meta;
                        switch ($input_type_form) {
                            case 'pemasukan':
                                /*$data_pendapatan_html .= '<tr style="padding:0;text-align:left;vertical-align:top"><td class="small-6 large-6 columns small-padding" style="-moz-hyphens:auto;-webkit-hyphens:auto;Margin:0 auto;border-collapse:collapse!important;color:#575759;font-family:Arial!important;font-size:16px;font-weight:700;hyphens:auto;line-height:34px;margin:0 auto;padding:0 0 10px!important;padding-bottom:16px;padding-left:0!important;padding-right:0!important;text-align:left;vertical-align:top;width:50%;word-wrap:break-word">'.
                                ($data_pendapatan_count+1).'. '.$input_name_title
                                .'</td><td class="small-6 large-6 columns small-padding" style="-moz-hyphens:auto;-webkit-hyphens:auto;Margin:0 auto;border-collapse:collapse!important;color:#575759;font-family:Arial!important;font-size:16px;font-weight:700;hyphens:auto;line-height:34px;margin:0 auto;padding:0 0 10px!important;padding-bottom:16px;padding-left:0!important;padding-right:0!important;text-align:left;vertical-align:top;width:50%;word-wrap:break-word"><table class="row" style="border-collapse:collapse;border-spacing:0;display:table;padding:0;position:relative;text-align:left;vertical-align:top;width:100%"><tbody><tr style="padding:0;text-align:left;vertical-align:top"><td class="field-start" style="-moz-hyphens:auto;-webkit-hyphens:auto;Margin:0;background:transparent url('.$asset_email_url.'email/field-start.png) no-repeat;background-position:0 0;border-collapse:collapse!important;color:#575759;font-family:Arial!important;font-size:16px;font-weight:700;height:34px;hyphens:auto;line-height:34px;margin:0;object-fit:none;padding:0;text-align:left;vertical-align:top;width:18px;word-wrap:break-word"></td><td class="field-body text-uppercase" style="-moz-hyphens:auto;-webkit-hyphens:auto;Margin:0;background-color:#E9E9EB;border-collapse:collapse!important;color:#555;font-family:Arial!important;font-size:16px;font-weight:700;height:34px;hyphens:auto;line-height:34px!important;margin:0;min-width:100px;padding:0 15px;text-align:center;text-transform:uppercase;vertical-align:top;word-wrap:break-word">'.
                                money($item_meta, 2)
                                .'</td><td class="field-end" style="-moz-hyphens:auto;-webkit-hyphens:auto;Margin:0;background:transparent url('.$asset_email_url.'email/field-end.png) no-repeat;background-position:0 0;border-collapse:collapse!important;color:#575759;font-family:Arial!important;font-size:16px;font-weight:700;height:34px;hyphens:auto;line-height:34px;margin:0;object-fit:none;padding:0;text-align:left;vertical-align:top;width:18px;word-wrap:break-word"></td></tr></tbody></table></td></tr>'; 
                                */
                                $total_pendapatan +=$item_meta;
                                $data_pendapatan_count++;
                                break;
                            case 'pengeluaran':
                                /*$data_pengeluaran_html .= '<tr style="padding:0;text-align:left;vertical-align:top"><td class="small-6 large-6 columns small-padding" style="-moz-hyphens:auto;-webkit-hyphens:auto;Margin:0 auto;border-collapse:collapse!important;color:#575759;font-family:Arial!important;font-size:16px;font-weight:700;hyphens:auto;line-height:34px;margin:0 auto;padding:0 0 10px!important;padding-bottom:16px;padding-left:0!important;padding-right:0!important;text-align:left;vertical-align:top;width:50%;word-wrap:break-word">'.
                                ($data_pengeluaran_count+1).'. '.$input_name_title
                                .'</td><td class="small-6 large-6 columns small-padding" style="-moz-hyphens:auto;-webkit-hyphens:auto;Margin:0 auto;border-collapse:collapse!important;color:#575759;font-family:Arial!important;font-size:16px;font-weight:700;hyphens:auto;line-height:34px;margin:0 auto;padding:0 0 10px!important;padding-bottom:16px;padding-left:0!important;padding-right:0!important;text-align:left;vertical-align:top;width:50%;word-wrap:break-word"><table class="row" style="border-collapse:collapse;border-spacing:0;display:table;padding:0;position:relative;text-align:left;vertical-align:top;width:100%"><tbody><tr style="padding:0;text-align:left;vertical-align:top"><td class="field-start" style="-moz-hyphens:auto;-webkit-hyphens:auto;Margin:0;background:transparent url('.$asset_email_url.'email/field-start.png) no-repeat;background-position:0 0;border-collapse:collapse!important;color:#575759;font-family:Arial!important;font-size:16px;font-weight:700;height:34px;hyphens:auto;line-height:34px;margin:0;object-fit:none;padding:0;text-align:left;vertical-align:top;width:18px;word-wrap:break-word"></td><td class="field-body text-uppercase" style="-moz-hyphens:auto;-webkit-hyphens:auto;Margin:0;background-color:#E9E9EB;border-collapse:collapse!important;color:#555;font-family:Arial!important;font-size:16px;font-weight:700;height:34px;hyphens:auto;line-height:34px!important;margin:0;min-width:100px;padding:0 15px;text-align:center;text-transform:uppercase;vertical-align:top;word-wrap:break-word">'.
                                money($item_meta, 2)
                                .'</td><td class="field-end" style="-moz-hyphens:auto;-webkit-hyphens:auto;Margin:0;background:transparent url('.$asset_email_url.'email/field-end.png) no-repeat;background-position:0 0;border-collapse:collapse!important;color:#575759;font-family:Arial!important;font-size:16px;font-weight:700;height:34px;hyphens:auto;line-height:34px;margin:0;object-fit:none;padding:0;text-align:left;vertical-align:top;width:18px;word-wrap:break-word"></td></tr></tbody></table></td></tr>'; 
                                */
                                $total_pengeluaran +=$item_meta;
                                $data_pengeluaran_count++;
                                break;
                        }
                    }
                    
                }
            }
            //dd($total_pendapatan.' - '.$total_pengeluaran.' = '.($total_pendapatan-$total_pengeluaran));
            //$data_pendapatan_html .= '</tbody></table>';
            //$data_pengeluaran_html .= '</tbody></table>';
            //dd($data_pengeluaran_html);
            $x = $total_pendapatan-$total_pengeluaran;
            $x_percent = $x !== 0?($x/$total_pendapatan)*100:0;
            //dd($x_percent);

            $result = ''; 
            if($x_percent > 20){
                $result = 'Baik sekali';
            }else if($x_percent == 20){
                $result = 'Cukup baik';
            }else if($x_percent >= 1 && $x_percent < 20){
                $result = 'Kurang baik';
            }else if($x_percent < 1){
                $result = 'Gawat';
            }
            //dd($result);
            $update_data = [
                'report' => $x_percent,
                'result' => $result
            ];

            //build html

            if(sendEmailWithTemplate([
                'email_template_module_id' => 4,//financial health
                'to' => $attributes['mail_to'],
                'replace_vars' => [
                    //'{asset_email_url}' => $asset_email_url, 
                    //'{current_datetime}' => Carbon::now()->format('d M Y'),
                    '{client_name}' => $user_dtl['name'],
                    '{email_web_path}' => '<a href="'.url('email/financial-health-checkup/'.$email_web_path).'">Cek kesehatan finansial</a>',
                    //'{incomes}' => $data_pendapatan_html,
                    //'{expenses}' => $data_pengeluaran_html,
                    //'{result}' => $result,
                    //'{result_filename}' => str_slug($result, '-')
                ]
            ])){
                $update_data += [
                    'is_email_sent' => 1,
                    'email_web_path' => $email_web_path
                ];
                
            }
            $this->FinancialHealth->where([ 'id' => $financialHealth->id ])->update($update_data);
            DB::commit();
            return $financialHealth;
        }
        throw new ValidationException('Financial health validation failed', $this->getErrors());
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
                'FinancialHealth_datetime' => Carbon::parse($attributes['FinancialHealth_datetime'])->format('Y-m-d H:i:s'),
                'is_repeated' => (isset($attributes['is_repeated']) && $attributes['is_repeated'] != '' )?$attributes['is_repeated']:'does_not_repeat',
                'updated_by' => $user_id,
                'updated_at' => Carbon::now(),
                'record_flag' => 'U'
            ];

            $existing_metas_q = FinancialHealthMeta::where('FinancialHealth_id', $id)->get();
            $existing_metas = userMeta($existing_metas_q);
            $this->FinancialHealth = $this->find($id);
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
            $this->FinancialHealth->fill($t_attributes)->save();

            $meta_map = [
                'next_FinancialHealth_datetime' => [ 'meta_key' => 'next_FinancialHealth_datetime', 'type' => 'text' ]
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
                    FinancialHealthMeta::where('FinancialHealth_id', $id)
                    ->where('meta_key', $meta_key)
                    ->update(['meta_value' => $attributes[$ff_name]]);
                }else{
                    FinancialHealthMeta::create([
                        'FinancialHealth_id' => $id,
                        'meta_key' => $meta_key,
                        'meta_value' => $attributes[$ff_name] 
                    ]);
                }
            }

            DB::commit();
            return $this->FinancialHealth;
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