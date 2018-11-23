<?php

if (!function_exists('gratavarUrl')) {
    /**
     * Gravatar URL from Email address
     *
     * @param string $email Email address
     * @param string $size Size in pixels
     * @param string $default Default image [ 404 | mm | identicon | monsterid | wavatar ]
     * @param string $rating Max rating [ g | pg | r | x ]
     *
     * @return string
     */
    function gratavarUrl($email, $size = 60, $default = 'mm', $rating = 'g') {

        return 'http://www.gravatar.com/avatar/' . md5(strtolower(trim($email))) . "?s={$size}&d={$default}&r={$rating}";
    }
}


/**
 * Backend menu active
 * @param $path
 * @param string $active
 * @return string
 */
function setActive($path, $active = 'active') {

    if (is_array($path)) {

        foreach ($path as $k => $v) {
            $path[$k] = getLang() . "/" . $v;
        }
    } else {
        $path = getLang() . "/" . $path;
    }

    return call_user_func_array('Request::is', (array)$path) ? $active : '';
}

//including parameter and must match
function setActive2($path, $args, $active = 'active') {
    //check argument
    $args_path = '';
    if (is_array($args)) {
        $args_path_arr = [];
        foreach ($args as $arg) {
            $args_path_arr[] = $arg.'='.Input::get($arg);
        }

        $args_path .= implode('&', $args_path_arr);
    } else {
        $args_path .= $arg.'='.Input::get($arg);
    }
    $current_url = Request::url().'?'.$args_path;
    return $current_url == langUrl($path) ? $active : '';
}

/**
 * @return mixed
 */
 
function getLang() {

    return LaravelLocalization::getCurrentLocale();
}

/**
 * @param null $url
 * @return mixed
 */
function langURL($url = null) {

    //return LaravelLocalization::getLocalizedURL(getLang(), $url);

    return url(getLang().$url);
}

/**
 * @param $route
 * @return mixed
 */
function langRoute($route, $parameters = array()) {

    return URL::route(getLang() . "." . $route, $parameters);
}

/**
 * @param $route
 * @return mixed
 */
function langRedirectRoute($route, $args = []) {

    return Redirect::route(getLang() . "." . $route, $args);
}

function changekeyname($array, $newkey)
{
   $newArray = [];
   foreach ($array as $key => $value) 
   {
       
      $newArray[$value->$newkey] = $value;

   }     
   return $newArray;   
}

function changekeyandval($array, $newkey, $newvalue)
{
   $newArray = [];
   foreach ($array as $key => $value) 
   {
       
      $newArray[$value->$newkey] = $value->$newvalue;

   }     
   return $newArray;   
}

function array_multisort_by($arrTags, $first_sort = SORT_DESC, $second_sort = SORT_ASC)
{
    $tag = array(); 
    $num = array();

    foreach($arrTags as $key => $value){ 
    $tag[] = $key; 
    $num[] = $value; 
    }

    array_multisort($num, $first_sort, $tag, $second_sort, $arrTags);
    return $arrTags;
}

if (!function_exists('val')) {
    function val($model, $key, $key_form = null) {
        if(is_null($key_form))
            $key_form = $key;
        if(Input::old($key_form))
            return Input::old($key_form);
        if(isset($model->$key))
            return $model->$key;
        return '';
    }
}

/**
 * Language values form
 * @param object $model model
 * @param string $key field table database
 * @return string $key_form field form
 */
if (!function_exists('lang_val')) {
    function lang_val($model, $key, $key_form = null) {
        if(is_null($key_form))
            $key_form = $key;
        if(Input::old($key_form))
            return Input::old($key_form);
        $key_values = [];
        //$langs = LaravelLocalization::getSupportedLocales();
        //foreach ($langs as $localeCode => $properties) {
        foreach (config('translatable.locales') as $locale) {
            $key_values[$locale] = $model->translate($locale)->$key;
        }
        //}
        return $key_values;
    }
}

if (!function_exists('lang_val_img')) {
    function lang_val_img($model, $key, $key_form = null) {
        if(is_null($key_form))
            $key_form = $key;
        //if(Input::old($key_form))
        //    return Input::old($key_form);
        $key_values = [];
        if(Input::old($key_form)){
            $old = [];
            foreach(Input::old($key_form) as $locale_old_image => $old_image){
                $old[$locale_old_image] = getImagePath($old_image);
            }
            foreach (config('translatable.locales') as $locale) {
                $key_values[$locale] = $old[$locale];
            }
        }else{
            foreach (config('translatable.locales') as $locale) {
                $key_values[$locale] = $model->translate($locale)->$key;
            }
        }
        return $key_values;
    }
}

if (!function_exists('lang_val_config_db')) {
    function lang_val_config_db($group, $key, $key_form = null) {
        if(is_null($key_form))
            $key_form = $key;
        $key_values = [];
        if(Input::old($key_form)){
            $old = [];
            foreach(Input::old($key_form) as $locale_old => $input_old){
                $old[$locale_old] = $input_old;
            }
            foreach (config('translatable.locales') as $locale) {
                $key_values[$locale] = $old[$locale];
            }
        }else{
            foreach (config('translatable.locales') as $locale) {
                $key_values[$locale] = config_db_cached($group.'::'.$key.'['.$locale.']');
            }
        }
        return $key_values;
    }
}

if (!function_exists('old_input_img')) {
    function old_input_img($model) {
        $key_values = [];
        if(!is_null($model)){
            foreach($model as $locale => $old_image){
                $key_values[$locale] = $old_image?getImagePath($old_image):'';
            }
        }
        
        return $key_values;
    }
}

if (!function_exists('old_input_img_singlelang')) {
    function old_input_img_singlelang($old_image) {
        if(!is_null($old_image)){
            return  $old_image?getImagePath($old_image):'';
        }
    }
}

if (!function_exists('lang_val_meta')) {
    function lang_val_meta($model, $key, $key_form = null) {
        if(is_null($key_form))
            $key_form = $key;
        if(Input::old($key_form))
            return Input::old($key_form);
        $key_values = [];
        foreach (config('translatable.locales') as $locale) { 
            $model_key_locale = '';
            if(isset($model->$key)){
                $model_key = $model->$key;
                $model_key_locale = $model_key[$locale];
            }
            $key_values[$locale] = $model_key_locale;
        }
        //}
        return $key_values;
    }
}

if (!function_exists('lang_errors')) {
    function lang_errors($errors, $name){
        if (!empty($errors) && $errors->first($name))
            return '<span class="help-block">'. $errors->first($name) .'</span>';
    }
}

if (!function_exists('lang_errors_has')) {
    function lang_errors_has($errors, $name){
        $has_errors = [];
        foreach (config('translatable.locales') as $locale) {
            if (!empty($errors) && $errors->first($name.'.'.$locale))
                $has_errors[$locale] = true;
        }
        return $has_errors;
    }
}

if (!function_exists('lang_show')) {
    function lang_show($model = null, $field){
        $default_locale = Config::get('app.locale');
        $html = '<ul class="nav nav-pills lang-tab hide">';
        $langs = LaravelLocalization::getSupportedLocales();
        foreach ($langs as $locale => $properties) {
            $is_active = $default_locale == $locale ? 'active' : '';
            $html .= '<li class="' . $is_active . '"><a href="#" class="lang-' . $locale . '">' . strtoupper($locale) . '</a></li>';
        }
        $html .='</ul>';
        foreach ($langs as $locale => $properties) {
            $is_hidden = $default_locale == $locale ? '' : 'hide';
            $lang_value = isset($model[$locale]) ? $model[$locale] : '';
            $html .= '<div class="lang-' . $locale . ' ' . $is_hidden.'">';
            $html .= $model->translate($locale)->$field;
            $html .= '</div>';
        }
        return $html;
    }
}

if (!function_exists('lang_show_img')) {
    function lang_show_img($model = null, $field){
        $default_locale = Config::get('app.locale');
        $html = '<ul class="nav nav-pills lang-tab hide">';
        $langs = LaravelLocalization::getSupportedLocales();
        foreach ($langs as $locale => $properties) {
            $is_active = $default_locale == $locale ? 'active' : '';
            $html .= '<li class="' . $is_active . '"><a href="#" class="lang-' . $locale . '">' . strtoupper($locale) . '</a></li>';
        }
        $html .='</ul>';
        foreach ($langs as $locale => $properties) {
            $is_hidden = $default_locale == $locale ? '' : 'hide';
            $lang_value = isset($model[$locale]) ? $model[$locale] : '';
            $html .= '<div class="lang-' . $locale . ' ' . $is_hidden.'">';
            $html .= '<img width="300" src="'.$model->translate($locale)->$field.'" />';
            $html .= '</div>';
        }
        return $html;
    }
}

if (!function_exists('renderLists')) {
    function renderLists($root, $val = 'title', $key = 'id'){
        $arr = [];
        foreach($root as $r){
            $arr += renderListOption($r, $val, $key);
        }
        return $arr;
    }
}

if (!function_exists('renderListOption')) {
    function renderListOption($node, $val = 'title', $key = 'id'){
        $lists = []; 
        $level = $node->depth;//$node->getLevel();
        $indent = ""; 
        for($i = 1; $i <= $level; $i++){
            //if($i == $level)
            //  $indent .= '└─';
            //else
                $indent .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
        }
        
        $lists[$node->$key] = $indent . $node->$val;
        foreach($node->children as $child){
                $lists += renderListOption($child);
        }
        return $lists;
    }
}

if (!function_exists('renderLists_antijsonparse')) {
    function renderLists_antijsonparse($root, $val = 'title', $key = 'id'){
        $arr = [];
        foreach($root as $r){
            $arr += renderListOption_antijsonparse($r, $val, $key);
        }
        return $arr;
    }
}

if (!function_exists('renderListOption_antijsonparse')) {
    function renderListOption_antijsonparse($node, $val = 'title', $key = 'id'){
        $lists = []; 
        $level = $node->depth;//$node->getLevel();
        $indent = ""; 
        for($i = 1; $i <= $level; $i++){
            //if($i == $level)
            //  $indent .= '└─';
            //else
                $indent .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
        }
        
        $lists['_'.$node->$key] = $indent . $node->$val;//di kasi underscore
        foreach($node->children as $child){
                $lists += renderListOption_antijsonparse($child);
        }
        return $lists;
    }
}

if (!function_exists('renderTaxosWithMeta')) {
    function renderTaxosWithMeta($root, $val = 'title', $key = 'id'){
        $arr = [];
        $idx_r = 0; 
        return $root;
        foreach($root as $r){
            $arr[$idx_r] = $r;
        }
        return $arr;
    }
}

if (!function_exists('renderTaxosWithMetaDtl')) {
    function renderTaxosWithMetaDtl($node, $val = 'title', $key = 'id'){
        $lists = []; 
        $lists[$node->$key] = $node;
        foreach($node->children as $child){
                //$lists['children'][]= renderTaxosWithMetaDtl($child);
                //$lists += renderTaxosWithMetaDtl($child);
        }
        return $lists;
    }
}

if (!function_exists('renderTaxoInputName')) {
    function renderTaxoInputName($root, $val = 'title', $key = 'id'){
        $arr = [];
        foreach($root as $r){
            $arr += renderTaxoInputNameDtl($r, $val, $key);
        }
        return array_values($arr);
    }
}

if (!function_exists('renderTaxoInputNameDtl')) {
    function renderTaxoInputNameDtl($node, $val = 'title', $key = 'id'){
        $lists = []; 
        $indent = ""; 
        if(isset($node['parentID']) && $node['parentID'] == 0){
            
        }else{
            $lists = $node['input_name'];//$indent . $node[$val];
        }
        foreach($node['children'] as $child){
                $lists[$child[$key]] = $node[$val].'_'.renderTaxoInputNameDtl($child);
        }
        return $lists;
    }
}

if (!function_exists('renderTaxoTitle')) {
    function renderTaxoTitle($root, $val = 'title', $key = 'id', $val2 = 'input_name'){
        $arr = [];
        foreach($root as $r){
            $arr += renderTaxoTitleDtl($r, $val, $key, $val2);
        }
        return $arr;
    }
}

if (!function_exists('renderTaxoTitleDtl')) {
    function renderTaxoTitleDtl($node, $val = 'title', $key = 'id', $val2 = 'input_name'){
        $lists = []; 
        if(isset($node['parentID']) && $node['parentID'] == 0){
            
        }else{
            $lists = $node[$val2];
        }
        foreach($node['children'] as $child){
                $lists[$node[$val].'_'.renderTaxoTitleDtl($child, 'title', 'id', 'input_name')] = renderTaxoTitleDtl($child, 'title', 'id', 'title');
        }
        return $lists;
    }
}

if (!function_exists('parametersLocalize')) {
    function parametersLocalize($ori, $localized){
        return $localized;
    }
}

if (!function_exists('money')) {
    function money($val, $dec = 0) {
        return $val == 0 ? 0 : number_format($val, $dec, ',', '.');
    }
}

if (!function_exists('unformat_money')) {
    function unformat_money($val) {
        return intval(str_replace('.', '', $val));
    }
}

if (!function_exists('unformat_money_raw')) {
    function unformat_money_raw($val, $dec = 0) {
        return str_replace(',', '.', str_replace('.', '', $val));
    }
}


if (!function_exists('buildPOST_fromJS')) {
    function buildPOST_fromJS($raw_post, $extra_request = '') {
        $data = [];
        $filter_is_money = array('price', 'weekend_price'); //field name
        if ($extra_request != '' && is_array($extra_request)) {
            $akumulasi_stok = array();
            $akumulasi_total_harga = array();
        }
        foreach ($raw_post as $idx => $datarow) { 
            $datarow = json_decode(urldecode($datarow)); //dd($datarow);
            foreach ($datarow as $key => $value) {
                //do filter
                if (in_array($value->alias, $filter_is_money))
                    $value->val = intval(str_replace('.', '', $value->val));

                //do extra request
                if ($extra_request != '' && is_array($extra_request)) {
                    foreach ($extra_request as $req) {
                        switch ($req) {
                            case 'xxx':
                                if ($value->alias == 'yyy') {
                                    $akumulasi_stok[] = $value->val;
                                }
                                break;
                        }
                    }
                }

                $data[$idx][$value->alias]['val'] = $value->val;
                $data[$idx][$value->alias]['text'] = $value->text;
            }
        }

    //extra request
        if ($extra_request != '' && is_array($extra_request)) {
            foreach ($raw_post as $idx => $datarow) {
                foreach ($extra_request as $req) {
                    switch ($req) {
                        case 'xxx':
                            $data[$idx]['xxx'] = $akumulasi_stok;
                            break;
                    }
                }
            }
        }
        return $data;
    }

}

function mapping_fieldType($form, $data) {
    $hidden_vals = [];//new stdClass();
    switch ($form) {
        case 'variant':
            foreach($data as $ff_name => $attr){
                $ff_type = 'input';
                //foreach($map as $contain_ff_name as $ff_type){
                if (strpos($ff_name, 'variant')!==false)
                    $ff_type = 'select';
                //}

                if (strpos($ff_name, 'room_image')!==false || strpos($ff_name, 'room_info')!==false)
                    $ff_type = 'textarea';
                
                $hidden_vals[$ff_type.'[name="'.$ff_name.'"]'] = [
                    'val' => $data[$ff_name]['val'],
                    'text' => $data[$ff_name]['text'],
                    'alias' => $ff_name
                ];
            }
            break;
        case 'capability':
            foreach($data as $ff_name => $attr){
                $ff_type = 'input';
                if (strpos($ff_name, 'is_able')!==false)
                    $ff_type = 'select';
                
                $hidden_vals[$ff_type.'[name="'.$ff_name.'"]'] = [
                    'val' => $data[$ff_name]['val'],
                    'text' => $data[$ff_name]['text'],
                    'alias' => $ff_name
                ];
            }
            break;
        case 'product_image' :
            foreach($data as $ff_name => $attr){
                $ff_type = 'input';

                if (strpos($ff_name, 'product_image')!==false)// || strpos($ff_name, 'room_image')!==false || strpos($ff_name, 'room_info')!==false)
                    $ff_type = 'textarea';
                
                $hidden_vals[$ff_type.'[name="'.$ff_name.'"]'] = [
                    'val' => $data[$ff_name]['val'],
                    'text' => $data[$ff_name]['text'],
                    'alias' => $ff_name
                ];
            }
            break;
        case 'provider' :
            foreach($data as $ff_name => $attr){
                $ff_type = 'input';
                
                $hidden_vals[$ff_type.'[name="'.$ff_name.'"]'] = [
                    'val' => $data[$ff_name]['val'],
                    'text' => $data[$ff_name]['text'],
                    'alias' => $ff_name
                ];
            }
            break;
    }
    return rawurlencode(json_encode($hidden_vals));
}

function trans_uc($text){
    return strtoupper(trans($text));
}

function trans_url($locale, $url, $attributes_raw = []){
    $attributes = [];
    if(!empty($attributes_raw))
        foreach($attributes_raw as $attr_key => $attr_val){
            $attributes[$attr_key] = $attr_val[$locale];
        }
    return LaravelLocalization::getLocalizedURL($locale, $url, $attributes);
}

function trans_url_locale($url, $attributes = []){
    return LaravelLocalization::getLocalizedURL(getLang(), $url, $attributes);
}

function trans_route_locale($transRoute = null, $args = []){
    return trans_route(getLang(), $transRoute, $args);
}

function trans_route($locale, $transRoute = null, $args = []){
    if(!is_null($transRoute)){
        $transRoute = 'routes.'.$transRoute;
        $curr_locale = LaravelLocalization::getCurrentLocale();
        LaravelLocalization::setLocale(Config::get('app.fallback_locale'));
        $URL_locale_fallback = trans($transRoute);
        LaravelLocalization::setLocale($curr_locale);
        LaravelLocalization::transRoute($transRoute);
        $http_query = [];
        $transRoute_args = [];
        if(is_array($args) && !empty($args)){
            foreach($args as $key_arg => $arg){
                $transRoute_args[$key_arg] = isset($arg[$locale])?$arg[$locale]:$arg;
                if (strpos($URL_locale_fallback, '{'.$key_arg.'}') === false) {
                    $http_query[$key_arg] = $arg;
                }
            } 
        }

        $url = LaravelLocalization::getLocalizedURL($locale, $URL_locale_fallback, $transRoute_args);
        if(!empty($http_query)){ 
            $url .= '?'.http_build_query($http_query);
        }
        return $url;
    }
    return '';
}

function trans_get_only($key, $items){
    $res = [];
    foreach ($items as $item) {
        $res[$item->locale] = $item->$key;
    }
    return $res;
}

function stars($stars = 0, $max_stars = 5, $empty_star = false){
    $html = '';
    for($i = 0; $i < $stars; $i++){
        $html .= '<i class="fa fa-star"></i>';
    }
    
    if($empty_star){
        $remaining_stars = $max_stars - $stars;
        if($remaining_stars > 0){
            for ($i = 0; $i < $remaining_stars; $i++){
                $html .= '<i class="fa fa-star-o"></i>';
            }
        }
    }
    return $html;
}

function stars_char($stars = 0){
    $html = '';
    for($i = 0; $i < $stars; $i++){
        $html .= '&#9733;';
    }
    return $html;
}

function build_meta_input($data_raw){
    $data = [];
    foreach($data_raw as $item){
        $data[$item->meta_key] = $item->meta_value;
    }
    return (object)$data;
}

function build_t_meta_input($data_raw){
    $data = [];
    foreach($data_raw as $item){
        foreach ($item->productMetaTranslations as $productMetaTranslation) {
            $data[$item->meta_key][$productMetaTranslation->locale] = $productMetaTranslation->meta_value;
        }
        
    }
    return (object)$data;
}

function productMeta($productMetas){
    $productMeta_t = build_t_meta_input($productMetas);
    $productMeta = [];
    foreach($productMeta_t as $meta_key => $meta){
      $productMeta[$meta_key] = $meta[getLang()];
    }
    return  (object)$productMeta;
}

function userMeta($userMetas, $types = array()){ 
    $userMeta = [];
    foreach($userMetas as $existing_meta){
        $meta_value = $existing_meta->meta_value;
        if(!empty($types)){
            if(in_array($existing_meta->meta_key, array_keys($types))){
                switch ($types[$existing_meta->meta_key]['type']) {
                    case 'image' :
                            $meta_value = url($meta_value);
                        break;
                }
            }
        }
        $userMeta[$existing_meta->meta_key] = $meta_value;
    }
    return  (object)$userMeta;
}

function userMeta_storeJUNK($user_id, $post){
    $existing_metas_q = UserMeta::where('user_id', $user_id)->get();
    $existing_metas = userMeta($existing_metas_q);
    $meta_map = [
        'full_name' => 'full_name',
        'phone' => 'phone'
    ];
    //$post = $request->all();
    foreach($meta_map as $ff_name => $meta_key){
        if(isset($existing_metas->$meta_key)){
            UserMeta::where('user_id', $user_id)
            ->where('meta_key', $meta_key)
            ->update(['meta_value' => $post[$ff_name]]);

            if($meta_key == 'full_name')
                User::where('id', $user_id)
                ->update(['name' => $post[$ff_name]]);
        }else{
            UserMeta::create([
                'user_id' => $user_id,
                'meta_key' => $meta_key,
                'meta_value' => $post[$ff_name] 
            ]);
        }
    }
}

function userMeta_store($user_id, $meta_map, $is_update = true){
    /*  sample 
        $meta_map = [
            'user_code' => [ 'meta_key' => 'user_code', 'meta_value' => 'xxx', 'type' => 'text' ]
        ];
    */ 
    if($is_update === true){
        $existing_metas_q = App\Models\UserMeta::where('user_id', $user_id)->get();
        $existing_metas = userMeta($existing_metas_q);
    }

    foreach($meta_map as $ff_name => $meta_attr){
        $meta_key = $meta_attr['meta_key'];
        $meta_value = $meta_attr['meta_value'];
        $meta_value_safe = '';
        switch ($meta_attr['type']) {
            case 'image' :
                    $meta_value_safe = getImagePath($meta_value);
                break;
            case 'dateFormatYmd' :
                    if($meta_value != '')
                        $meta_value_safe = Carbon\Carbon::parse($meta_value)->format('Y-m-d');
                break;
            default:
                    $meta_value_safe = $meta_value;
                break;
        }
        
        if(isset($existing_metas->$meta_key)){
            App\Models\UserMeta::where('user_id', $user_id)
                ->where('meta_key', $meta_key)
                ->update(['meta_value' => $meta_value_safe]);
            if($meta_key == 'name')
                App\User::where('id', $user_id)
                    ->update(['name' => $meta_value_safe]);
        }else{
            App\Models\UserMeta::insert([
                'user_id' => $user_id,
                'meta_key' => $meta_key,
                'meta_value' => $meta_value_safe 
            ]);
        }
    }
}

function fulldate_trans($input = null){//, $format = 'm/d/Y H:i:s'){ 
    if(is_null($input) || $input == '' || $input == '0000-00-00 00:00:00'){
        return '';
    }

    $timestamp = strtotime($input);
    $date_time = explode(' ', $input);
    list($y,$m,$d) = explode('-', $date_time[0]);
    list($h,$i,$s) = explode(':', $date_time[1]);

    $formated_date = '';
    switch (getLang()) {
        case 'en':
            $formated_date = strftime('%d %B %Y %H:%M:%S', mktime($h, $i, $s, $m, $d, $y));;
            break;
        case 'id':
            setlocale(LC_ALL, 'id_ID.UTF8', 'id_ID.UTF-8', 'id_ID.8859-1', 'id_ID', 'IND.UTF8', 'IND.UTF-8', 'IND.8859-1', 'IND', 'Indonesian.UTF8', 'Indonesian.UTF-8', 'Indonesian.8859-1', 'Indonesian', 'Indonesia', 'id', 'ID');
            //++$formated_date = strftime('%A, %d %B %Y %H:%M:%S', mktime($h, $i, $s, $m, $d, $y));//Rabu, 20 Mei 2015 12:47:29
            $formated_date = strftime('%d %B %Y %H:%M:%S', mktime($h, $i, $s, $m, $d, $y));
            break;
    }
   return $formated_date;
}

function dateonly_trans($input = null){//, $format = 'm/d/Y H:i:s'){ 
    if(is_null($input) || $input == '' || $input == '0000-00-00'){
        return '';
    }

    $timestamp = strtotime($input);
    $formated_date = '';
    switch (getLang()) {
        case 'en':
            $formated_date = strftime('%d %B %Y', $timestamp);
            break;
        case 'id':
            setlocale(LC_ALL, 'id_ID.UTF8', 'id_ID.UTF-8', 'id_ID.8859-1', 'id_ID', 'IND.UTF8', 'IND.UTF-8', 'IND.8859-1', 'IND', 'Indonesian.UTF8', 'Indonesian.UTF-8', 'Indonesian.8859-1', 'Indonesian', 'Indonesia', 'id', 'ID');
            //++$formated_date = strftime('%A, %d %B %Y %H:%M:%S', mktime($h, $i, $s, $m, $d, $y));//Rabu, 20 Mei 2015 12:47:29
            $formated_date = strftime('%d %B %Y', $timestamp);
            break;
    }
   return $formated_date;
}

function date_trans($input = null){
    if(is_null($input) || $input == '' || $input == '0000-00-00'){
        return '';
    }

    $date = strtotime($input);
    list($y,$m,$d) = explode('-', $input);

    $formated_date = '';
    switch (getLang()) {
        case 'en':
            $formated_date = strftime('%d %B %Y', mktime($m, $d, $y));;
            break;
        case 'id':
            setlocale(LC_ALL, 'id_ID.UTF8', 'id_ID.UTF-8', 'id_ID.8859-1', 'id_ID', 'IND.UTF8', 'IND.UTF-8', 'IND.8859-1', 'IND', 'Indonesian.UTF8', 'Indonesian.UTF-8', 'Indonesian.8859-1', 'Indonesian', 'Indonesia', 'id', 'ID');
            //++$formated_date = strftime('%A, %d %B %Y', mktime($m, $d, $y));//Thursday, 17 December 2015
            $formated_date = strftime('%d %B %Y', mktime($m, $d, $y));//Thursday, 17 December 2015
            break;
    }
   return $formated_date;
}

function getImagePath($image_path_raw, $withoutBaseUrl = true){
    preg_match( '/src="([^"]*)"/i', $image_path_raw, $image_path );
    $image_path_clean = count($image_path) && isset($image_path[1])?$image_path[1]:'';
    return $withoutBaseUrl?str_replace(url('/').'/', '', $image_path_clean):$image_path_clean;
}

function extractComma($texts = null){
    $data = [];
    if(!is_null($texts)){
        foreach(explode(',', trim($texts)) as $text){
            $data[] = trim($text);
        }
    }
    return $data;
}

function builtCheckLists($texts, $grid_num = 1){
    $lists = '';
    $lists .= '<ul class="small-block-grid-'.$grid_num.'">';
    foreach(extractComma($texts) as $text){
        $lists .= '<li><i class="fa fa-check"></i> '.$text.'</li>';
    } 
    $lists .= '</ul>';
    return $lists;
}

function buildEmailTemplateLists($item_arr){
    $data = [];
    //$data[''] = '';
    foreach($item_arr as $item){
        $data[$item->id] = $item->subject;
    }
    return $data;
}

function sendEmailWithTemplate($data) {
    //$email_tpl_raw = App\Models\EmailTemplateModuleTemplate::with(['emailTemplate.emailTemplateTranslation'])->where('email_template_module_id', $data['email_template_module_id'])->first();
    $email_tpl_raw = App\Models\EmailTemplateModuleTemplate::with(['emailTemplate'])->where('email_template_module_id', $data['email_template_module_id'])->first();
    if(is_null($email_tpl_raw))
        return false;//jika tidak memiliki email template pada module tersebut
        
    $email_tpl = $email_tpl_raw->emailTemplate;
    $data['subject'] = $email_tpl->subject;
    $data['cc'] = explode(',', trim($email_tpl_raw->cc));
    if(isset($data['init_bcc']))
        $data['cc'] = array_merge($data['init_bcc'], $data['cc']);
   
    $data['reply_to'] = [];

    $message = $email_tpl->body;
    $send_message = nl2br($message); 
    foreach($data['replace_vars'] as $key => $val){
        $send_message = str_replace($key, $val, $send_message);
    }

    Mail::send(['html' => 'emails.blank'], ['message_text' => $send_message], function($message) use ($data) {
        $message->to($data['to'])->subject($data['subject']);
        if(count($data['reply_to']))
            $message->setReplyTo($data['reply_to']);
        if(count($data['cc']))
            $message->setBcc($data['cc']);
    });

    if( count(Mail::failures()) > 0 ) {
        return false;
     } else {
        return true;
     }
}

function paymentInfoHTML($info){
    $bank_account = App\Models\BankAccount::find($info['bank_account'])->name;
    $customer_bank_account = App\Models\BankAccount::find($info['customer_bank_account'])->name;

    return '<strong>'.trans('app.email').'</strong><br />
      '.$info['email'].'<br />
      <strong>'.trans('app.booking_no').'</strong><br />
      '.$info['booking_no'].'<br />
      <strong>'.trans('app.amount').'</strong><br />
      '.$info['amount'].'<br />
      <strong>'.$bank_account.'</strong><br />
      '.$info['bank_account'].'<br />
      <strong>'.trans('app.customer_bank_account').'</strong><br />
      '.$customer_bank_account.'<br />
      <strong>'.trans('app.account_name').'</strong><br />
      '.$info['account_name'].'<br />
      <strong>'.trans('app.account_no').'</strong><br />
      '.$info['account_no'];
}

function get_email_template_module_id($booking_status_id = 1){
    $module_id = 0;
    switch ($booking_status_id) {
        case '1': //on request
            $module_id = 2;
            break;
        case '2': //confirmed
            $module_id = 3;
            break;
        case '3': //paid
            $module_id = 4;
            break;
    }
    return $module_id;
}

function carbon_format($input = null, $format = 'd M Y', $from_format = 'm/d/Y'){
    if(is_null($input) || $input == '')
        return '';
   return Carbon\Carbon::createFromFormat($from_format , $input)->format($format);
}

function carbon_format_store($input = null, $format = 'Y-m-d', $from_format = 'm/d/Y'){
    if(is_null($input) || $input == '')
        return '';
   return Carbon\Carbon::createFromFormat($from_format , $input)->format($format);
}

function carbon_format_view($input = null, $format = 'm/d/Y', $from_format = 'Y-m-d'){
    if(is_null($input) || $input == '' || $input == '0000-00-00' )
        return '';
   return Carbon\Carbon::createFromFormat($from_format , $input)->format($format);
}

function carbon_parse_view($input = null, $format = 'm/d/Y'){
    if(is_null($input) || $input == '' || $input == '0000-00-00' )
        return '';
   return Carbon\Carbon::parse($input)->format($format);
}

function carbon_diff($date_after, $date_before){
    $after = new Carbon\Carbon($date_after);
    $before = new Carbon\Carbon($date_before);
    return $after->diff($before)->days;
}

function thumb_path($image_path, $size = 'thumb70x70'){
    $thumb = explode('/', $image_path);
    $idx_img_name = count($thumb)-1;
    $img_name = $thumb[$idx_img_name];
    $img_file = explode('.', $img_name);
    $file_name = $img_file[0];
    $file_ext = $img_file[1];
    $thumb[$idx_img_name] = 'thumb';
    $thumb[($idx_img_name+1)] = $file_name.'_'.$size.'.'.$file_ext;
    return implode('/', $thumb);
}

function build_get_param($params){
    if($params){
        $param_attr = [];
        foreach ($params as $key => $value) {
            $param_attr[] = $key.'='.$value;
        }
        return '?'.implode('&', $param_attr);
    }
    return '';
}                                                                                   

function get_depth_as($depth){
    $as = [
        0 => 'country',
        1 => 'city',
        2 => 'region'
    ];
    return $as[$depth];
}

function is_cell_excel_contain_locale($search_arr, $keyword){ 

    foreach ($search_arr as $search) {
        if( substr($keyword, 0, strlen($search)) == $search ) {
            return str_replace($search.'_', '', $keyword);//locale
        }
    }
    return false;
}

function build_t_product($data_raw){
    $data = [];
    $translation_keys = ['id','product_id','locale','title','slug','body','meta_title','meta_keywords','meta_description'];
    foreach($data_raw as $item){
        foreach ($translation_keys as $key) {
            $data[$key][$item->locale] = $item->$key;
        }
        
    }
    return (object)$data;
}

function num2char($num){
    return chr(64+$num);
}

function config_db_cached($key = null, $default = null) {
    if (\Cache::has($key))
    {
       $setting = \Cache::get($key);
    }
    else{ 
       $setting = config_db($key, $default);

        if ($setting){
            \Cache::forever($key, $setting);
        }
    } 

  return $setting;
}

function data_YearMonth_trans($input = null){//, $format = 'm/d/Y H:i:s'){ 
    if(is_null($input) || $input == '' || $input == '0000-00-00 00:00:00'){
        return '';
    }

    $timestamp = strtotime($input);
    $date_time = explode(' ', $input);
    list($y,$m,$d) = explode('-', $date_time[0]);
    list($h,$i,$s) = explode(':', $date_time[1]);

    $formated_date = '';
    switch (getLang()) {
        case 'en':
            $formated_date = strftime('%B %Y', mktime($h, $i, $s, $m, $d, $y));;
            break;
        case 'id':
            setlocale(LC_ALL, 'id_ID.UTF8', 'id_ID.UTF-8', 'id_ID.8859-1', 'id_ID', 'IND.UTF8', 'IND.UTF-8', 'IND.8859-1', 'IND', 'Indonesian.UTF8', 'Indonesian.UTF-8', 'Indonesian.8859-1', 'Indonesian', 'Indonesia', 'id', 'ID');
            //++$formated_date = strftime('%A, %d %B %Y %H:%M:%S', mktime($h, $i, $s, $m, $d, $y));//Rabu, 20 Mei 2015 12:47:29
            $formated_date = strftime('%B %Y', mktime($h, $i, $s, $m, $d, $y));
            break;
    }
   return $formated_date;
}

if (!function_exists('renderListsForMenuOption')) {
    function renderListsForMenuOption($root, $module, $val = 'title', $key = 'id'){
        $arr = [];
        foreach($root as $r){
            $arr += renderListMenuOption($r, $module, $val, $key);
        }
        return $arr;
    }
}

if (!function_exists('renderListMenuOption')) {
    function renderListMenuOption($node, $module, $val = 'title', $key = 'id'){
        $lists = [];
        $level = $node->getLevel();
        $indent = ""; 
        for($i = 1; $i <= $level; $i++){
            $indent .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
        }
        $lists[$module.'-'.$node->$key] = $indent . $node->$val;
        foreach($node->children as $child){
                $lists += renderListMenuOption($child, $module);
        }
        return $lists;
    }
}

function in_array_strpos($word, $array){
    foreach($array as $a){
        if (strpos($word,$a) !== false) {
            return true;
        }
    }
    return false;
}

function carbon_now_format($format = 'd/m/Y'){
   return Carbon\Carbon::now()->format($format);
}

function getWeekendDates($from, $to){
    $from = strtotime($from);
    $to = strtotime($to);
    $weekendDays = [];
    while (date("Y-m-d", $from) != date("Y-m-d", $to)) {
        $day_index = date("w", $from);
        if ($day_index == 0 || $day_index == 6 ) {
            $weekendDays[] = date('Y-m-d', $from);
        }
        $from = strtotime(date("Y-m-d", $from) . "+1 day");
    }
    return $weekendDays;
}

function getWeekendDatesWithDesc($from, $to, $desc = 'weekend'){
    $from = strtotime($from);
    $to = strtotime($to);
    $weekendDays = [];
    while (date("Y-m-d", $from) != date("Y-m-d", $to)) {
        $day_index = date("w", $from);
        if ($day_index == 0 || $day_index == 6 ) {
            $weekendDays[] = [ 'weekend_date' => date('Y-m-d', $from), 'description' => $desc ];
        }
        $from = strtotime(date("Y-m-d", $from) . "+1 day");
    }
    return $weekendDays;
}

function isWeekendDates($date){
    $count = App\Models\WeekendDays::where('weekend_date', $date)->count();
    if($count > 0)
        return true;
    return false;
}

function addDayswithdate($date,$days,$format='Y-m-d'){
    $date = strtotime("+".$days." days", strtotime($date));
    return  date($format, $date);

}

function getTest(){
    //$controller = app()->make('App\Http\Controllers\Backend\Auth\AuthController');
    //$arguments = []; // you need to put the arguments that the "create" method requires in here
    //$role = app()->call([$controller, 'getRole'], $arguments);
    //$role = App\Http\Controllers\Backend\Auth\AuthController::getRole();
    //dd($role);
    //'admin';//Auth::user()->name;
    $role = 'admin';
    return $role;
}

function getDatesFromRange($date_time_from, $date_time_to, $duration, $blocked_slots)
{   //echo 'masuk get date from range helper'; var_dump($blocked_slots); exit;
    $tmp_raw = [];
    $start = Carbon\Carbon::parse($date_time_from);
    $end = Carbon\Carbon::parse($date_time_to);
    $diff = $end->diffInMinutes($start); //dd($diff/$duration);
    $tmp = Carbon\Carbon::parse($start);
    //init
    $Hi_format_init = $start->format('H:i');
    $tmp_raw[''] = 'available';
    $tmp_raw[$Hi_format_init] = in_array($Hi_format_init, $blocked_slots)?'not_available':'available';
    for($i = 0; $i < ($diff/$duration); $i++){
        $tmp_next = $tmp->addMinutes($duration);
        $Hi_format = $tmp_next->format('H:i');
        //$tmp_raw[] = $tmp_next->format('H:i');
        $tmp_raw[$Hi_format] = in_array($Hi_format, $blocked_slots)?'not_available':'available';
    }
    return $tmp_raw;
}

function getDatesFromRange_raw($date_time_from, $date_time_to, $duration)
{   //var_dump($blocked_slots); exit;
    $tmp_raw = [];
    $start = Carbon\Carbon::parse($date_time_from);
    $end = Carbon\Carbon::parse($date_time_to);
    $diff = $end->diffInMinutes($start); //dd($diff/$duration);
    $tmp = Carbon\Carbon::parse($start);
    //init
    $Hi_format_init = $start->format('H:i');
    //$tmp_raw[''] = 'available';
    $tmp_raw[] = $Hi_format_init;//in_array($Hi_format_init, $blocked_slots)?'not_available':'available';
    for($i = 0; $i < ($diff/$duration); $i++){
        $tmp_next = $tmp->addMinutes($duration);
        $Hi_format = $tmp_next->format('H:i');
        //$tmp_raw[] = $tmp_next->format('H:i');
        //$tmp_raw[$Hi_format] = in_array($Hi_format, $blocked_slots)?'not_available':'available';
        $tmp_raw[] = $Hi_format;
    }
    return $tmp_raw;
}

function getDatesFromRange_api($date_time_from, $date_time_to, $duration, $blocked_slots){
    $tmp_raw = [];
    $start = Carbon\Carbon::parse($date_time_from);                 // 31 July 2018 09:00
    $end = Carbon\Carbon::parse($date_time_to);                     // 31 July 2018 21:00
    $diff = $end->diffInMinutes($start); // dd($diff/$duration);    // 24
    $tmp = Carbon\Carbon::parse($start);

//    Carbon {#1001
//      +"date": "2018-07-31 09:00:00.000000"
//      +"timezone_type": 3
//      +"timezone": "Africa/Bamako"
//    }

    //init
    $Hi_format_init = $start->format('H:i');
    //$tmp_raw[''] = 'available';

    $blocked_slots_list = []; // dd($blocked_slots);
    foreach($blocked_slots as $idx_blocked_slot => $blocked_slot){
        $start_blocked = Carbon\Carbon::parse($start->format('Y-m-d').' '.$blocked_slot[0]);
        $end_blocked = Carbon\Carbon::parse($start->format('Y-m-d').' '.$blocked_slot[1]); //dd($end_time);
        $diff_blocked = $end_blocked->diffInMinutes($start_blocked)-$duration; //dd($diff_time);

        $Hi_format_blocked_init = $start_blocked->format('H:i');
        if(!in_array($Hi_format_blocked_init, $blocked_slots_list))
            $blocked_slots_list[] = $Hi_format_blocked_init;
        for($i = 0; $i < ($diff_blocked/$duration); $i++){
            $tmp_next_blocked = $start_blocked->addMinutes($duration);
            $Hi_format_blocked = $tmp_next_blocked->format('H:i');
            //$tmp_raw[] = $tmp_next->format('H:i');
            if(!in_array($Hi_format_blocked, $blocked_slots_list))
                $blocked_slots_list[] = $Hi_format_blocked;
        }
    } // dd($blocked_slots_list);

    $tmp_raw[$Hi_format_init] = in_array($Hi_format_init, $blocked_slots_list)?'not_available':'available';
    for($i = 0; $i < ($diff/$duration)-2; $i++){
        $tmp_next = $tmp->addMinutes($duration);
        $Hi_format = $tmp_next->format('H:i');
        //$tmp_raw[] = $tmp_next->format('H:i');
        $tmp_raw[$Hi_format] = in_array($Hi_format, $blocked_slots_list)?'not_available':'available';
    }
    return $tmp_raw;
}

function getDatesFromRange_notAvailable($date_time_from, $date_time_to, $duration, $blocked_slots = array())
{
    $tmp_raw = [];
    $start = Carbon\Carbon::parse($date_time_from);
    $end = Carbon\Carbon::parse($date_time_to);
    $diff = $end->diffInMinutes($start); //dd($diff/$duration);
    $tmp = Carbon\Carbon::parse($start);
    //init
    $Hi_format_init = $start->format('H:i');
    //$tmp_raw[''] = 'available';


    $blocked_slots_list = []; //dd($blocked_slots);
    foreach($blocked_slots as $idx_blocked_slot => $blocked_slot){
        $start_blocked = Carbon\Carbon::parse($start->format('Y-m-d').' '.$blocked_slot[0]);
        $end_blocked = Carbon\Carbon::parse($start->format('Y-m-d').' '.$blocked_slot[1]); //dd($end_time);
        $diff_blocked = $end_blocked->diffInMinutes($start_blocked)-$duration; //dd($diff_time);

        $Hi_format_blocked_init = $start_blocked->format('H:i');
        if(!in_array($Hi_format_blocked_init, $blocked_slots_list))
            $blocked_slots_list[] = $Hi_format_blocked_init;
        for($i = 0; $i < ($diff_blocked/$duration); $i++){
            $tmp_next_blocked = $start_blocked->addMinutes($duration);
            $Hi_format_blocked = $tmp_next_blocked->format('H:i');
            //$tmp_raw[] = $tmp_next->format('H:i');
            if(!in_array($Hi_format_blocked, $blocked_slots_list))
                $blocked_slots_list[] = $Hi_format_blocked;
        }
    }//dd($blocked_slots_list);
    if(in_array($Hi_format_init, $blocked_slots_list))
        $tmp_raw[] = $Hi_format_init;
    for($i = 0; $i < ($diff/$duration)-1; $i++){
        $tmp_next = $tmp->addMinutes($duration);
        $Hi_format = $tmp_next->format('H:i');
        //$tmp_raw[] = $tmp_next->format('H:i');
        //$tmp_raw[$Hi_format] = in_array($Hi_format, $blocked_slots_list)?'not_available':'available';
        if(in_array($Hi_format, $blocked_slots_list))
            $tmp_raw[] = $Hi_format;
    }
    //var_dump($tmp_raw);
    return $tmp_raw;
}

//update dokumentasi jika ada update Snippet gitlab $1692058
function notAvailableTimeSlot_time($params = array()){
    $notAvailableTimeSlot_time = [];
    if(empty($params)) return $notAvailableTimeSlot_time;

    $duration = $params['duration'];                                                // 30 di set di file "myli_1/app/Http/Controllers/API/CfpScheduleController.php"
    $schedule_start_date_formated = $params['schedule_start_date_formated'];        // 2018-08-01       inputan
    $notAvailableTimeSlots = $params['notAvailableTimeSlots'];                      // 09:00 - 09:30    yang sudah ter booking
    //$notAvailableTimeSlots_now = $params['notAvailableTimeSlots_now'];
    $cfp_working_hour_start = $params['cfp_working_hour_start'];                    // 09:00:00         jam mulai kerja
    $cfp_working_hour_end = $params['cfp_working_hour_end'];                        // 17:00:00         jam selesai kerja
    $schedule_weekend = date('N', strtotime($schedule_start_date_formated));        // hari (1 = Senin, 2 = Selasa, dst)


    $path_holiday_json = storage_path() . "/json/national_holiday.json";            // json hari libur nasional
    $json_holiday = json_decode(file_get_contents($path_holiday_json), true); 
    $data_holiday = array();                                                        

    foreach($json_holiday['items'] as $holidays){
        $nested_data['start_holiday'] = $holidays['start']['date'];
        $data_holiday[] = $nested_data;
    }
    $date_holiday = array_column($data_holiday, 'start_holiday');
    $holiday_date = in_array(date('Y-m-d', strtotime($schedule_start_date_formated)), $date_holiday);

    $today_date = Carbon\Carbon::now()->format('Y-m-d');

    /**
     | ----------------------------------------------------------------------------------------------
     | jika tanggal pertemuan lebih kecil dari tanggal saat ini (jadwal pertemuan sudah terlewat).
     | jika hari sabtu
     | jika hari minggu
     | jika hari libur nasional
     |
     */

    if(strtotime($schedule_start_date_formated) < strtotime($today_date) || $holiday_date == true){ // kemarin dan sebelumnya serta sabtu,minggu " $schedule_weekend == 6 || $schedule_weekend == 7 || "
        $notAvailableTimeSlot_time[] = [ $cfp_working_hour_start, Carbon\Carbon::parse($cfp_working_hour_end)->subMinutes(30)->format('H:i'), 'Hari libur / kelewat' ];
    } else {
        $notAvailableTimeSlot_time = [];
        if(count($notAvailableTimeSlots) > 0){
            foreach ($notAvailableTimeSlots as $notAvailableTimeSlot) {
                $notAvailableTimeSlot_time[] = [ Carbon\Carbon::parse($notAvailableTimeSlot->schedule_start_date_plus_spare)->format('H:i'), Carbon\Carbon::parse($notAvailableTimeSlot->schedule_end_date)->format('H:i'), 'Jam sudah terbooking' ];
            }
        }

        $now_datetime_formated = Carbon\Carbon::now()->format('Y-m-d H:i:s');       //'2018-07-31 10:20' - Hari dan jam sekarang
        
        /**
         | ------------------------------------------------------
         | Tambahkan jika tanggal nya adalah HARI INI
         |
         */

        if(strtotime($schedule_start_date_formated) == strtotime($today_date)) {     // jika waktu start_date_schedule = hari ini


            // jika jam sekarang sudah jam 09:00
            // Start diambil dari jam kerja CFP 
            if(Carbon\Carbon::now()->format('H:i') >= Carbon\Carbon::parse($cfp_working_hour_start)->format('H:i')) {
                $today_time_start = $cfp_working_hour_start;
            } else {
                // jika belum jam 09:00
                // Start diambil dari jam saat itu
                $today_time_start = Carbon\Carbon::parse($now_datetime_formated)->format('H:i'); //$cfp_working_hour_start;                            // 09:00:00
            }

            $today_time_end = '';
            // $now_datetime_formated = Carbon\Carbon::now()->format('Y-m-d H:i:s');

            // jika hari dan jam sekarang menitnya lebih besar dari 0
            // jika hari dan jam sekarang menitnya <= dengan dari duration (30 menit)
            if(Carbon\Carbon::parse($now_datetime_formated)->format('i') > 0 && Carbon\Carbon::parse($now_datetime_formated)->format('i') <= $duration ) {
                // waktu akhir hari = jam ini + duration (30 menit)
                $today_time_end = Carbon\Carbon::parse(Carbon\Carbon::parse($now_datetime_formated)->format('H:00'))->addMinutes($duration)->format('H:i');
            
                // jika hari dan jam sekarang menitnya > 30 menit
            } else if(Carbon\Carbon::parse($now_datetime_formated)->format('i') > $duration ){
                // waktu akhir hari ini = hari jam sekarang H:duration (30menit) tambah 30 menit
                $today_time_end = Carbon\Carbon::parse(Carbon\Carbon::parse($now_datetime_formated)->format('H:'.$duration))->addMinutes($duration)->format('H:i');
            } else {
                $today_time_end = Carbon\Carbon::parse(Carbon\Carbon::parse($now_datetime_formated)->format('H:'.$duration))->addMinutes($duration)->format('H:i');	
            }

            $notAvailableTimeSlot_time[] = [ $today_time_start, Carbon\Carbon::parse($today_time_end)->format('H:i'), 'Not available karna hari ini jamnya sudah terlewat' ];
        }
    }
    return $notAvailableTimeSlot_time;
}

function calc_inf_fv($name, $pv, $rate, $lama_tahun_investasi, $income_is_monthly){ //dd(unformat_money_raw(money(95721117.1875,2)));
  $lama_investasi = $lama_tahun_investasi; 
  //$pv = unformat_money($pv);
  $next_pv = $income_is_monthly === true?$pv*12:$pv; //tahunan
  $html_head_simulasi = '<tr class="simul-text-wrap"><th class="simul-text" colspan="6"><span class="simul-text-type"></span>&nbsp;<span class="simul-text-name"></span></th></tr>';
  $html_head_simulasi .= '<tr><th class="text-center">Year</th><th class="text-center">Payment</th><th class="text-center">Annual Inflation rate [ % ]</th><th class="text-center">Interest</th><th class="text-center">FV</th></tr>';
  //++$html_simulasi = '';
  $simulasi_inf = [];
  $interest_percent = $rate;//tahunan
  $interest_percent_decimal = $interest_percent/100;
  $cek_interest = [];
  for($i=1;$i<=$lama_investasi;$i++){
    $current_payment = $next_pv;//pv + next_pv;
    $interest = $interest_percent_decimal*$current_payment;
    $fv_inf = $current_payment + unformat_money_raw(money($interest,2)); 
    //++$html_simulasi .= '<tr><td class="text-right">'.$i.'</td><td class="text-right">'.money($current_payment,2).'</td><td class="text-right">'.money($interest_percent,2).'</td><td class="text-right">'.money($interest,2).'</td><td class="text-right">'.money($fv_inf,2).'</td></tr>';
    $simulasi_inf[] = [
        'month' => $i,
        'current_payment' => money($current_payment,2),
        'interest_percent' => money($interest_percent,2),
        'interest' => money($interest,2),
        'fv_inf' => money($fv_inf,2)
    ];
    $next_pv = $fv_inf;
  }

  $data[$name] = [
    //++'html_simulasi_inf_'.$name.'_head' => $html_head_simulasi,
    //++'html_simulasi_inf_'.$name => $html_simulasi,
    //++'view_simulasi_inf_'.$name => $simulasi_inf,//versi tampilan, karena money nya sudah diformat
    'fv_inf_'.$name => $next_pv,
    'rate_inf_'.$name => $rate,
    'pv_inf_'.$name => $pv
  ];
  $data['ori'] = [
    'rate_inf_ori' => $rate
  ];

  return $data;
}

function calc_ins($name, $data){ //dd($data);
    //$rate_inv_needinv = $data['rate_inv_needinv'];
    $need_inv = $data['needinv']['pv_inf_needinv'];
    $inf_rate = $data['ori']['rate_inf_ori'];
    $tenor = config_db_cached('settings::tenor_plan_protection');//untuk asuransi pasti 5 tahun
    $name = strtolower($name);

    $data_inf = calc_inf_fv($name, $need_inv, $inf_rate, $tenor, false);//inflasi seharusnya dihitung dalam tahun
    $fv_need_inv = $data_inf[$name]['fv_inf_'.$name];

    /*
        *Kritis per 1m 340rb perbulan*
         (1.914.422.343,75 / 1.000.000.000) x 340.000
         
        *Jiwa per 1m 4jt pertahun*
        ( (1.914.422.343,75 / 1.000.000.000) x 4.000.000 ) / 12
         
        *Kesehatan*
        biaya tergantung asuransi nya
    */
    $premi_dasar_jiwa = config_db_cached('settings::price_life_insurance');//1M, 4jt pertahun
    $premi_dasar_kritis = config_db_cached('settings::price_critical_insurance');// 1M, 340rb perbulan

    //++$html_head_simulasi = '<tr class="simul-text-wrap"><th class="simul-text" colspan="4"><span class="simul-text-type"></span>&nbsp;<span class="simul-text-name"></span></th></tr>';
    //++$html_head_simulasi .= '<tr><th class="text-center" colspan="4">Asuransi</th></tr>';
    //++$html_head_simulasi .= '<tr><th class="text-center">Tenor [ tahun ]</th><th>Inflasi [ % ]</th><th class="text-center">PV</th><th class="text-center">FV</th></tr>';
    //++$html_simulasi = '<tr><td class="text-right">'.$tenor.'</td><td class="text-right">'.money($inf_rate,2).'</td><td class="text-right">'.money($need_inv,2).'</td><td class="text-right">'.money($fv_need_inv,2).'</td></tr>';
    $simulasi_ins = [
        'tenor' => $tenor,
        'inf_rate' => money($inf_rate,2),
        'need_inv' => money($need_inv,2),
        'fv_need_inv' => money($fv_need_inv,2)
    ];

    $premi_bulanan = [
        'jiwa' => ( ($fv_need_inv / 1000000000) * $premi_dasar_jiwa ) / 12,
        'kritis' => ($fv_need_inv / 1000000000) * $premi_dasar_kritis,
        'kesehatan' => 0
    ];

    $premi_dasar_jiwa = config_db_cached('settings::price_life_insurance');//1M, 4jt pertahun
    $premi_dasar_kritis = config_db_cached('settings::price_critical_insurance');// 1M, 340rb perbulan

    $data[$name] = [
        //++'html_simulasi_ins_'.$name.'_head' => $html_head_simulasi,
        //++'html_simulasi_ins_'.$name => $html_simulasi,
        'tenor_'.$name => $tenor,
        'view_simulasi_ins_'.$name => $simulasi_ins,
        'fv_inf_'.$name => $fv_need_inv,
        'rate_inf_'.$name => $inf_rate,
        'premi_bulanan' => $premi_bulanan,
        'premi_dasar_jiwa' => $premi_dasar_jiwa,
        'premi_dasar_kritis' => $premi_dasar_kritis
    ];

    return $data;
}

function add_ins_html($name, $data){
    $name = strtolower($name); //dd($data);
    $rate_ins = $data['needinv']['rate_inv_needinv'];
    $fv_need_inv = $data[$name]['fv_inf_'.$name];
    $inf_rate = $data['ori']['rate_inf_ori'];
    if($name != '' && ( isset($rate_ins) && $rate_ins != '') && ( isset($fv_need_inv) && $fv_need_inv != '') ){
        $html = '<dl">';
        $html .='<dt style="text-transform:capitalize;">'.$name.' &nbsp;&nbsp;<a href="#" simulasi="html_simulasi_ins_'.$name.'" class="load-simulasi"><i class="fa fa-list"></i></a></dt>';
        $html .='<dt>Durasi</dt>';
        $html .='<dd>5 tahun</dd>';
        $html .='<dt>FV inf '.money($inf_rate, 2).'%  &nbsp;&nbsp;<a href="#" simulasi="html_simulasi_inf_'.$name.'" class="load-simulasi"><i class="fa fa-list"></i></a></dt>';
        $html .='<dd>Rp '.money($fv_need_inv, 2).'</dd>';
        $html .='</dl>';

        $res[$name] = [
            'ins_html' => $html
        ];
        return $res;
    }
}

function pmt($rate_per_period, $number_of_payments, $present_value, $future_value, $type){
    if($rate_per_period != 0.0){
      // Interest rate exists
      $q = pow(1 + $rate_per_period, $number_of_payments);
      return -($rate_per_period * ($future_value + ($q * $present_value))) / ((-1 + $q) * (1 + $rate_per_period * ($type)));

    } else if($number_of_payments != 0.0){
      // No interest rate, but number of payments exists
      return -($future_value + $present_value) / $number_of_payments;
    }

    return 0;
}

function calc_inv_pv_getPayment($name, $rate, $data){
    $lama_bulan_investasi = $data['lama_bulan_investasi'];
    $fv_inv = $data['needinv']['fv_inf_needinv'];
    $next_inv = 0;
    $html_head_simulasi = '<tr class="simul-text-wrap"><th class="simul-text" colspan="6"><span class="simul-text-type"></span>&nbsp;<span class="simul-text-name"></span></th></tr>';
    $html_head_simulasi += '<tr><th class="text-center">Month</th><th class="text-center">Payment</th><th class="text-center">Monthly Interest [ % ]</th><th class="text-center">Interest</th><th class="text-center">FV</th></tr>';
    $html_simulasi = '';
    $interest_reverse = (($rate/100)/12)*$fv_inv; //echo $fv_inv; exit;
    $payment_reverse = -pmt(($rate/100) / 12, $lama_bulan_investasi, $fv_inv, 0, 0);//jika kelebihan investasinya maka bulannya tambahin satu aja
    $monthly_payment = $payment_reverse-$interest_reverse; 
    $monthly_payment = unformat_money_raw(money($monthly_payment, 2));

    $res = calc_inv_fv($name, $monthly_payment, $rate, $data);

    return $res[$name];
}

function calc_inv_pv_getPayment2($name, $rate, $data){
    $lama_bulan_investasi = $data['lama_bulan_investasi'];
    $fv_inv = $data['needinv']['fv_inf_needinv'];
    $next_inv = 0;
    $html_head_simulasi = '<tr class="simul-text-wrap"><th class="simul-text" colspan="6"><span class="simul-text-type"></span>&nbsp;<span class="simul-text-name"></span></th></tr>';
    $html_head_simulasi += '<tr><th class="text-center">Month</th><th class="text-center">Payment</th><th class="text-center">Monthly Interest [ % ]</th><th class="text-center">Interest</th><th class="text-center">FV</th></tr>';
    $html_simulasi = '';
    $interest_reverse = (($rate/100)/12)*$fv_inv; //echo $fv_inv; exit;
    $payment_reverse = -pmt(($rate/100) / 12, $lama_bulan_investasi, $fv_inv, 0, 0);//jika kelebihan investasinya maka bulannya tambahin satu aja
    $monthly_payment = $payment_reverse-$interest_reverse; 
    $monthly_payment = unformat_money_raw(money($monthly_payment, 2));

    $res = calc_inv_fv2($name, $monthly_payment, $rate, $data);

    return $res[$name];
}

function calc_inv_fv2($name, $jumlah_investasi, $rate, $data){
      $lama_bulan_investasi = $data['lama_bulan_investasi'];
      $inv = $jumlah_investasi;
      $next_inv = 0;
      //++$html_head_simulasi = '<tr class="simul-text-wrap"><th class="simul-text" colspan="6"><span class="simul-text-type"></span>&nbsp;<span class="simul-text-name"></span></th></tr>';
      //++$html_head_simulasi .= '<tr><th class="text-center">Month</th><th class="text-center">Payment</th><th class="text-center">Monthly Interest [ % ]</th><th class="text-center">Interest</th><th class="text-center">FV</th></tr>';
      //++$html_simulasi = '';
      $simulasi_inv = [];
      $simulasi_inv_plain = [];
      $interest_percent = ($rate/12);
      $interest_percent_decimal = $interest_percent/100;
      
      for($i=1;$i<=$lama_bulan_investasi;$i++){
        $current_payment = $inv + $next_inv;
        $interest = $interest_percent_decimal*$current_payment;
        $fv_inv = $current_payment + unformat_money_raw(money($interest,2));
        //++$html_simulasi .= '<tr><td class="text-right">'.$i.'</td><td class="text-right">'.money($current_payment, 2).'</td><td class="text-right">'.money($interest_percent,2).'</td><td class="text-right">'.money($interest,2).'</td><td class="text-right">'.money($fv_inv,2).'</td></tr>';
        $simulasi_inv[] = [
            'month' =>  $i,
            'current_payment' => money($current_payment, 2),
            'interest_percent' => money($interest_percent,2),
            'interest' => money($interest,2),
            'fv_inv' => money($fv_inv,2)
        ];
        $simulasi_inv_plain[] = [
            'month' =>  $i,
            'current_payment' => $current_payment,
            'interest_percent' => $interest_percent,
            'interest' => $interest,
            'fv_inv' => $fv_inv
        ];
        $next_inv = $fv_inv;
      }

      $name = strtolower($name);
      $res[$name] = [
        //++'html_simulasi_inv_'.$name.'_head' => $html_head_simulasi,
        //++'html_simulasi_inv_'.$name => $html_simulasi,
        //'view_simulasi_inv_'.$name => $simulasi_inv,
        'simulasi_inv_'.$name => $simulasi_inv_plain,
        'fv_inv_'.$name => $next_inv,
        'rate_inv_'.$name => $rate,
        'pv_inv_'.$name => floatval($inv)
      ];

      return $res;
}

//tanpa name
function calc_inv_dynamicRate( $jumlah_investasi, $rates, $data){
    $rates_safe = array_values($rates);
    $inv_month_safe = array_keys($rates);
    $lama_bulan_investasi = $data['lama_bulan_investasi'];
    $inv = $jumlah_investasi;
    $next_inv = 0;
    //++$html_head_simulasi = '<tr class="simul-text-wrap"><th class="simul-text" colspan="6"><span class="simul-text-type"></span>&nbsp;<span class="simul-text-name"></span></th></tr>';
    //++$html_head_simulasi .= '<tr><th class="text-center">Month</th><th class="text-center">Payment</th><th class="text-center">Monthly Interest [ % ]</th><th class="text-center">Interest</th><th class="text-center">FV</th></tr>';
    //++$html_simulasi = '';
    $simulasi_inv = [];
    $simulasi_inv_plain = [];
    //$interest_percent = ($rate/12);
    //$interest_percent_decimal = $interest_percent/100;
    //$interest_percent = 18;
    //$interest_percent_decimal = 18/100;
    for($i=1;$i<=$lama_bulan_investasi;$i++){
        $interest_percent = $rates_safe[($i-1)];
        $inv_month = $inv_month_safe[($i-1)];
        $interest_percent_decimal = $interest_percent/100;

        $current_payment = $inv + $next_inv;
        $interest = $interest_percent_decimal*$current_payment;
        $fv_inv = $current_payment + unformat_money_raw(money($interest,2));
        //++$html_simulasi .= '<tr><td class="text-right">'.$i.'</td><td class="text-right">'.money($current_payment, 2).'</td><td class="text-right">'.money($interest_percent,2).'</td><td class="text-right">'.money($interest,2).'</td><td class="text-right">'.money($fv_inv,2).'</td></tr>';
        $simulasi_inv[] = [
            'month' =>  $i,
            'inv_month' => $inv_month,
            'current_payment' => money($current_payment, 2),
            'interest_percent' => money($interest_percent,2),
            'interest' => money($interest,2),
            'fv_inv' => money($fv_inv,2)
        ];
        $simulasi_inv_plain[] = [
            'month' =>  $i,
            'inv_month' => $inv_month,
            'current_payment' => $current_payment,
            'interest_percent' => $interest_percent,
            'interest' => $interest,
            'fv_inv' => $fv_inv
        ];
        $next_inv = $fv_inv;
    }

    //$name = strtolower($name);
    $res = [
      //++'html_simulasi_inv_'.$name.'_head' => $html_head_simulasi,
      //++'html_simulasi_inv_'.$name => $html_simulasi,
      //'view_simulasi_inv_'.$name => $simulasi_inv,
      'simulasi_inv' => $simulasi_inv_plain,
      'fv_inv' => $next_inv,
      //'rate_inv_'.$name => $rate,
      'pv_inv' => floatval($inv)
    ];

    return $res;
}

//tanpa simulasi_inv_, karena cfp app udah keburu pakai helper ini tanpa menampilkan key simulasi_inv_
function calc_inv_fv($name, $jumlah_investasi, $rate, $data){
      $lama_bulan_investasi = $data['lama_bulan_investasi'];
      $inv = $jumlah_investasi;
      $next_inv = 0;
      //++$html_head_simulasi = '<tr class="simul-text-wrap"><th class="simul-text" colspan="6"><span class="simul-text-type"></span>&nbsp;<span class="simul-text-name"></span></th></tr>';
      //++$html_head_simulasi .= '<tr><th class="text-center">Month</th><th class="text-center">Payment</th><th class="text-center">Monthly Interest [ % ]</th><th class="text-center">Interest</th><th class="text-center">FV</th></tr>';
      //++$html_simulasi = '';
      $simulasi_inv = [];
      $simulasi_inv_plain = [];
      $interest_percent = ($rate/12);
      $interest_percent_decimal = $interest_percent/100;
      
      for($i=1;$i<=$lama_bulan_investasi;$i++){
        $current_payment = $inv + $next_inv;
        $interest = $interest_percent_decimal*$current_payment;
        $fv_inv = $current_payment + unformat_money_raw(money($interest,2));
        //++$html_simulasi .= '<tr><td class="text-right">'.$i.'</td><td class="text-right">'.money($current_payment, 2).'</td><td class="text-right">'.money($interest_percent,2).'</td><td class="text-right">'.money($interest,2).'</td><td class="text-right">'.money($fv_inv,2).'</td></tr>';
        $simulasi_inv[] = [
            'month' =>  $i,
            'current_payment' => money($current_payment, 2),
            'interest_percent' => money($interest_percent,2),
            'interest' => money($interest,2),
            'fv_inv' => money($fv_inv,2)
        ];
        // $simulasi_inv_plain[] = [
        //     'month' =>  $i,
        //     'current_payment' => $current_payment,
        //     'interest_percent' => $interest_percent,
        //     'interest' => $interest,
        //     'fv_inv' => $fv_inv
        // ];
        $next_inv = $fv_inv;
      }

      $name = strtolower($name);
      $res[$name] = [
        //++'html_simulasi_inv_'.$name.'_head' => $html_head_simulasi,
        //++'html_simulasi_inv_'.$name => $html_simulasi,
        //'view_simulasi_inv_'.$name => $simulasi_inv,
        //'simulasi_inv_'.$name => $simulasi_inv_plain,
        'fv_inv_'.$name => $next_inv,
        'rate_inv_'.$name => $rate,
        'pv_inv_'.$name => floatval($inv)
      ];

      return $res;
}

function slugify ($string, $separator = '-') {
    $string = utf8_encode($string);
    $string = iconv('UTF-8', 'ASCII//TRANSLIT', $string);   
    $string = preg_replace('/[^a-z0-9- ]/i', '', $string);
    $string = str_replace(' ', $separator, $string);
    $string = trim($string, $separator);
    $string = strtolower($string);

    if (empty($string)) {
        return 'n-a';
    }

    return $string;
}

function income_simulation_rate_optionsx($inv_rate_options, $res){
    $arr_rate_options = [];
    $arr_rate_idx = 0;
    foreach ($inv_rate_options as $inv_rate_option) {
        foreach ($inv_rate_option['rates'] as $idx_rate => $rate) {
            $arr_rate_options[$arr_rate_idx]['product'] = $inv_rate_option['product'];
            $arr_rate_options[$arr_rate_idx]['bgcolor'] = $inv_rate_option['bgcolor'];
            $arr_rate_options[$arr_rate_idx]['bgcolor2'] = $inv_rate_option['bgcolor2'];
            $arr_rate_options[$arr_rate_idx]['rate'] = $rate;
            $arr_rate_options[$arr_rate_idx]['taxo_wallet_asset_id'] = $inv_rate_option['taxo_wallet_asset_id'];
            $arr_rate_options[$arr_rate_idx]['details'] = calc_inv_pv_getPayment(slugify($inv_rate_option['product'], '_').'_'.$rate, $rate, $res);
            $arr_rate_idx++;
        }
    }

    return $arr_rate_options;
}

function income_simulation_rate_options($inv_rate_options, $res){
    $arr_rate_options = [];
    //$arr_rate_idx = 0;
    //dd($inv_rate_options);
    foreach ($inv_rate_options as $rate_idx => $inv_rate_option) {
        $arr_rate_options[$rate_idx]['interest_rate_id'] = $inv_rate_option->id;
        $arr_rate_options[$rate_idx]['product'] = $inv_rate_option->product->title;
        $arr_rate_options[$rate_idx]['bgcolor'] = $inv_rate_option->bgcolor;
        $arr_rate_options[$rate_idx]['bgcolor2'] = $inv_rate_option->bgcolor2;
        $arr_rate_options[$rate_idx]['rate'] = $inv_rate_option->rate;
        $arr_rate_options[$rate_idx]['taxo_wallet_asset_id'] = $inv_rate_option['taxo_wallet_asset_id'];
        $arr_rate_options[$rate_idx]['details'] = calc_inv_pv_getPayment(slugify($inv_rate_option->product->title, '_').'_'.$inv_rate_option->rate, $inv_rate_option->rate, $res);
    }

    return $arr_rate_options;
}

//$tesdate = new Carbon\Carbon('13-1-2017');
//dd(gen_client_code('001', $tesdate, '1'));
//hasilnya "001130120171"
function gen_client_code($branch_code, $date_code, $number){
    //$date = $date->format('dmY');
    return $branch_code.$date_code.$number;
}

function gen_cfp_code($branch_code, $number){
    return $branch_code.$number;
}

function is_active_title($code){
    return $code == 0 ? trans('app.not_active'):trans('app.active');
}

function gender($gender_code){
    $gender = [
        'M' => trans('app.male'),
        'F' => trans('app.female')
    ];

    return $gender[$gender_code];
}

if (!function_exists('array_column')) {
    function array_column(array $array, $columnKey, $indexKey = null)
    {
        $result = array();
        foreach ($array as $subArray) {
            if (!is_array($subArray)) {
                continue;
            } elseif (is_null($indexKey) && array_key_exists($columnKey, $subArray)) {
                $result[] = $subArray[$columnKey];
            } elseif (array_key_exists($indexKey, $subArray)) {
                if (is_null($columnKey)) {
                    $result[$subArray[$indexKey]] = $subArray;
                } elseif (array_key_exists($columnKey, $subArray)) {
                    $result[$subArray[$indexKey]] = $subArray[$columnKey];
                }
            }
        }
        return $result;
    }
}

if (!function_exists('getModuleVersion')) {
    function getModuleVersion($attributes) {

        /**
         | --------------------------------------------
         | Ini daftar parameter yang diterima
         | --------------------------------------------
         
         $attributes['user_id'];                    -> 242
         $attributes['transaction_date_until'];     -> "2018-08-01"     -> tanggal sekarang
         $attributes['status'];                     -> "approved"
         $attributes['active_version_key'];         -> financialCheckup_cashflowAnalysis
         $attributes['client_cutoff_date'];         -> 31               -> di set di file "app/Repositories/CashflowAnalysis/CashflowAnalysisRepository.php"

         | --------------------------------------------
         |
         */


        $user_id = $attributes['user_id'];
        $transaction_date_until = !isset($attributes['transaction_date_until'])? Carbon\Carbon::now()->format('Y-m-d'): Carbon\Carbon::parse($attributes['transaction_date_until'])->format('Y-m-d'); // dd($transaction_date_until); "2018-08-01"

        $status = !isset($attributes['status'])?'approved':$attributes['status'];
        $active_version_key = !isset($attributes['active_version_key'])?'':$attributes['active_version_key'];
        if($active_version_key === '') return '';

        /**
         | ------------------------------------------
         | Membuat awal bulan
         | ------------------------------------------
         | $start_date          -> {tahun sekarang}-{bulan sekarang}-01 00:00:00
         |
         */

        $transaction_date_until_Ym =  Carbon\Carbon::parse($transaction_date_until)->format('Y-m'); 
        $dmY_api =  Carbon\Carbon::createFromFormat('Y-m-d' , $transaction_date_until_Ym.'-01');
        $start_date = $dmY_api->format('Y-m-d').' 00:00:00'; // awal bulan dari api // dd($start_date); // "2018-08-01 00:00:00"
        
        $client_cutoff_date = !isset($attributes['client_cutoff_date']) || $attributes['client_cutoff_date'] === 0?'31':$attributes['client_cutoff_date']; // jika kosong akan merujuk ke tanggal 31 atau akhir bulan, meskipun ada akhr bulan yang hanya sampai 28 saja

        /**
         | ----------------------------------------------
         | Tanggal terakhir dari bulan sekarang
         | ----------------------------------------------
         | $eom_of_transaction_date_until   -> menampilkan sampai ke H:i:s
         | $client_cutoff_Ymd               -> menampilkan hanya sampai tanggal
         |
         |
         */

        $eom_of_transaction_date_until = Carbon\Carbon::parse($transaction_date_until)->endOfMonth();
        $client_cutoff_Ymd = $eom_of_transaction_date_until->format('Y').'-'.$eom_of_transaction_date_until->format('m').'-'.$client_cutoff_date; // harus manual. jika tidak maka carbon akan mengenerate salah jika tanggal tidak ditemuakan dibulan tersebut
        // $cutoff_date = Carbon\Carbon::parse($eom_of_transaction_date_until->format('Y-m-'.$client_cutoff_date));
        

        $cutoff_date_Ymd_safe = returnValidDate($client_cutoff_Ymd);
        
        // ++$end_date_check_version =  Carbon\Carbon::today()->format('Y-m-d') == $transaction_date_until?$end_date_baseon_cutoff_date->format('Y-m-d H:i:s'):$transaction_date_until.' 23:59:59';
        
        // dd('from '.$start_date.' until '.$end_date_check_version);
        // dd('transaction date until : '.$transaction_date_until.' < cut off date Ymd safe : '.$cutoff_date_Ymd_safe);
        // dd(Carbon\Carbon::today()->format('Y-m-d').' == '.Carbon\Carbon::parse($transaction_date_until)->format('Y-m-d'));

        /** 
         | -------------------------------------------
         | Waktu sekarang sampai ke detik
         | -------------------------------------------
         */

        $end_date_check_version = Carbon\Carbon::now()->format('Y-m-d') == Carbon\Carbon::parse($transaction_date_until)->format('Y-m-d')?Carbon\Carbon::now()->format('Y-m-d H:i:s'):Carbon\Carbon::parse($transaction_date_until)->format('Y-m-d 23:59:59');


        // jika tanggal sekarang < dari tanggal terakhir di bulan ini
        if($transaction_date_until < $cutoff_date_Ymd_safe){
            
            /**
             | ----------------------------------------------
             | Menampilkan tanggal terakhir dari bulan kemarin
             | ----------------------------------------------
             | Tanggal sekarang     2018-08-01
             | Hasil                2018-07-31 00:00:00
             |
             */

            $start_date = Carbon\Carbon::parse($cutoff_date_Ymd_safe)->subMonthNoOverflow()->format('Y-m-d 00:00:00');
            // dd($start_date); "2018-07-31 00:00:00"

        }else{
            $start_date = Carbon\Carbon::parse($cutoff_date_Ymd_safe)->format('Y-m-d 00:00:00');
        }

        // Log::debug($start_date.' sampai '.$end_date_check_version);

        // do check if there are many active version details with status approved within date ranges ( start of month until today or specific day )

        /**
         | ------------------------------------
         | Ini adalah script lama, 
         | Dimana pada script ini hanya menampilkna 
         | yang created_at nya di bulan ini.
         | Pada awal bulan script ini menyebabkan 
         | cashflow-analysis kosong
         | ------------------------------------
        $checkApprovedActiveVersion_details = App\Models\ActiveVersionDetail::where('created_at', '>=', $start_date)
        ->where('created_at', '<=', $end_date_check_version)
        ->where('active_version_key', $active_version_key)
        ->where('user_id', $user_id)
        ->where('status', $status)->get();
         |
         |
         | ------------------------------------
         | Cek dari tabel Active Version Detail dengan user = $user, status = $status
         | Ambil dengan version dan id yang terbaru
         | Jika ada ambil datanya dan masukan ke $approvedActiveVersion
         |
         */

        $checkApprovedActiveVersion_details = App\Models\ActiveVersionDetail::where('active_version_key', $active_version_key)
            ->where('user_id', $user_id)
            ->where('status', $status)
            ->max('version', 'id');
       
        if(count($checkApprovedActiveVersion_details)){
            
            // if active version details founded
            // get biggest version within date ranges ( start of month until today or specific day)

            /**
             |
            $approvedActiveVersion = App\Models\ActiveVersionDetail::where('created_at', '>=', $start_date)
                ->where('created_at', '<=', $end_date_check_version)
                ->where('active_version_key', $active_version_key)
                ->where('user_id', $user_id)
                ->where('status', $status)->max('version');
             |
             */

             $approvedActiveVersion = App\Models\ActiveVersionDetail::where('active_version_key', $active_version_key)
                ->where('user_id', $user_id)
                ->where('status', $status)
                ->max('version', 'id');

        } else {

            // if active version details not founded
            // get smallest version within this month

            $end_date_check_version =  Carbon\Carbon::parse($transaction_date_until)->endOfMonth()->format('Y-m-d H:i:s');
            
            /**
             |            
            $approvedActiveVersion = App\Models\ActiveVersionDetail::where('created_at', '>=', $start_date)
                ->where('created_at', '<=', $end_date_check_version)
                ->where('active_version_key', $active_version_key)
                ->where('user_id', $user_id)
                ->where('status', $status)->min('version');
             |
             */

             $approvedActiveVersion = App\Models\ActiveVersionDetail::where('active_version_key', $active_version_key)
                ->where('user_id', $user_id)
                ->where('status', $status)
                ->max('version', 'id');

            // dd(\DB::getQueryLog());
        }

        return is_null($approvedActiveVersion)?'':$approvedActiveVersion;
    }
}

/*
return valid date. 
misalnya : 2018-02-31 akan return tanggal akhir bulan yaitu 2018-02-28. sehingga tidak akan bisa return tanggal yang invalid
param $date_raw harus berformat Y-m-d
*/
function returnValidDate($date_raw){
    if(!isset($date_raw)){
        $date_safe = \Carbon\Carbon::today()->format('Y-m-d');
    }else{
        $date_prepare = \Carbon\Carbon::parse($date_raw)->format('Y-m-d');
		\Log::debug('Helper line 22756 : from '.$date_raw.' formated become '.$date_prepare);
        //breakdown date. jika tidak dibreak maka akan menghasilkan data yang salah. misalnya : 2018-02-31 akan menghasilkan 2018-03-03 karena mungkin tidak ketemu
        preg_match('#^(\d{4})-(\d{2})-(\d{2})$#', $date_prepare, $results);
        $year = $results[1];
        $month   = $results[2];
        $day  = $results[3]; //dd($day.' '.$month.' '.$year);
        if(checkdate($month, $day, $year)){//jika true / exist pada bulan tersebut
            $date_safe = $date_prepare;//\Carbon\Carbon::parse($date_raw)->format('Y-m-d');
        }else{//tidak exist pada bulan tersebut, maka akan diubah menjadi akhir akhir bulan secara otomatis
            $date_safe = \Carbon\Carbon::parse($year.'-'.$month.'-01')->endOfMonth()->format('Y-m-d');
        }
    }
    return $date_safe;
}

function sendPushNotifViaFCM($firebase_payload){
    $client = new GuzzleHttp\Client();    
    try { 
        $request = $client->createRequest('POST',
            "https://fcm.googleapis.com/fcm/send", 
            [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'key=AAAAiv9ap7U:APA91bFbJ9LwY-qGbESllnlfvDYRyUIuK0Fg9S8ZndT3KYBKxkecww8mXTYMhVpV4SK_VSM4rtjuTOzP7OrznRE8nLi1TTJdo60KrbX13Lq_uzzFsjloEeletJTdnCbJifZ3XV40seyk'
                ],
                'body' => json_encode($firebase_payload)
            ]
        );
        //dd($request);
        //exit;
        $response = $client->send($request);
        return $response;
        // $resp = $response->json();
    } catch(\Exception $e){
        $resp = $e->getResponse(); 
        return $resp;
        //$statusCode = $resp->getStatusCode();        
    }
}