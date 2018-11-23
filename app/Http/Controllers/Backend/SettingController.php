<?php namespace App\Http\Controllers\Backend;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

//use App\Repositories\ContactUs\ContactUsInterface;
use Redirect;
use View;
use Input;
use Validator;
use Response;
use Notification;
//use Illuminate\Pagination\LengthAwarePaginator;
//use Illuminate\Pagination\Paginator;
//use App\Repositories\ContactUs\ContactUsRepository as ContactUs;
use App\Exceptions\Validation\ValidationException;
use Config;
use App\Repositories\Role\RoleRepository as Role;
use Entrust;
use Cache;

use App\Models\WeekendDays;
use App\Repositories\Taxonomy\TaxonomyInterface;


class SettingController extends Controller {
    protected $taxonomy;
    protected $role;

    public function __construct(Role $role, TaxonomyInterface $taxonomy) {
        $this->role = $role;
        $this->taxonomy = $taxonomy;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function general() {
        $attr = [ 
                'title' => trans('app.general_settings')
            ];
        if(!Entrust::can(['update_general_setting'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }


        $role_options[' '] = '-';
        $role_options += $this->role->lists('display_name', 'id');
        return view('backend.setting.general', compact('role_options'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function generalStoreJUNK() {
        try {
            $config_map = [
                'site_title' => 'site_title',
                //'new_user_default_role' => 'new_user_default_role'
            ];

            foreach($config_map as $ff_name => $config_key){
                $config_key_fix = 'settings::'.$config_key;
                $config_val = Input::get($ff_name);
                config_db()->set($config_key_fix, $config_val);
                Cache::forever($config_key_fix, $config_val);
            }
            
            Notification::success( trans('app.general_settings_updated'));
            return Redirect::route('admin.settings.general');
        } catch (ValidationException $e) {
            return Redirect::route('admin.settings.general')->withInput()->withErrors($e->getErrors());
        }
    }

    public function generalStore() {
        try {

            $rules = [ 
                'site_title' => 'required',
                'default_role_id_client' => 'required',
                'default_role_id_cfp' => 'required'
            ];

            $config_map = [
                'site_title' => 'site_title',
                'default_role_id_client' => 'default_role_id_client',
                'default_role_id_cfp' => 'default_role_id_cfp'
            ];

            $input = [];
            foreach($config_map as $ff_name => $config_key){
                $input[$ff_name] = unformat_money_raw(Input::get($ff_name));
            }

            //dd($input);

            $v = Validator::make($input, $rules);
            $setAttributeNames['site_title'] = trans('app.site_title');
            $setAttributeNames['default_role_id_client'] = trans('app.default_role_client');
            $setAttributeNames['default_role_id_cfp'] = trans('app.default_role_cfp');
            $v->setAttributeNames($setAttributeNames);
            if ($v->fails())
            {  
                return Redirect::Route('admin.settings.general')->withInput()->withErrors($v->errors());
            }

            foreach($config_map as $ff_name => $config_key){
                $config_key_fix = 'settings::'.$config_key;
                $config_val = $input[$ff_name]; //  echo $config_key;
                config_db()->set($config_key_fix, $config_val);
                Cache::forever($config_key_fix, $config_val);
            }
            
            Notification::success( trans('app.general_settings_updated'));
            return Redirect::route('admin.settings.general');
        } catch (ValidationException $e) {
            return Redirect::route('admin.settings.general')->withInput()->withErrors($e->getErrors());
        }
    }

    public function reading() {
        $attr = [ 
                'title' => trans('app.reading_settings')
            ];
        if(!Entrust::can(['update_reading_setting'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        return view('backend.setting.reading');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function readingStore() {
        try {
            $config_map = [
                'per_page' => 'per_page',
                'backend_per_page' => 'backend_per_page',
                //'scroller_newest_products' => 'scroller_newest_products',
                //'scroller_last_shipping_products' => 'scroller_last_shipping_products',
                //'scroller_best_seller_products' => 'scroller_best_seller_products',
                //'scroller_best_seller_products_period' => 'scroller_best_seller_products_period'
            ];

            foreach($config_map as $ff_name => $config_key){
                //config_db()->set('settings::'.$config_key, Input::get($ff_name));

                $config_key_fix = 'settings::'.$config_key;
                $config_val = Input::get($ff_name);
                config_db()->set($config_key_fix, $config_val);
                Cache::forever($config_key_fix, $config_val);
            }
            
            Notification::success( trans('app.reading_settings_updated'));
            return Redirect::route('admin.settings.reading');
        } catch (ValidationException $e) {
            return Redirect::route('admin.settings.reading')->withInput()->withErrors($e->getErrors());
        }
    }

    public function notification() {
        $attr = [ 
                'title' => trans('app.notification_settings')
            ];
        if(!Entrust::can(['update_notification_setting'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        return view('backend.setting.notification');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function notificationStore() {
        try {
            $config_map = [
                'client_max_number_notifications' => 'client_max_number_notifications',
                'cfp_max_number_notifications' => 'cfp_max_number_notifications'
            ];

            foreach($config_map as $ff_name => $config_key){
                $config_key_fix = 'settings::'.$config_key;
                $config_val = Input::get($ff_name);
                config_db()->set($config_key_fix, $config_val);
                Cache::forever($config_key_fix, $config_val);
            }
            
            Notification::success( trans('app.notification_settings_updated'));
            return Redirect::route('admin.settings.notification');
        } catch (ValidationException $e) {
            return Redirect::route('admin.settings.notification')->withInput()->withErrors($e->getErrors());
        }
    }

    public function commerce() {
        $attr = [ 
                'title' => trans('app.commerce_settings')
            ];
        if(!Entrust::can(['update_commerce_setting'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        return view('backend.setting.commerce');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function commerceStoreJUNK() {
        try {
            $config_map = [
                'per_page' => 'per_page',
                'backend_per_page' => 'backend_per_page',
            ];

            foreach($config_map as $ff_name => $config_key){
                //config_db()->set('settings::'.$config_key, Input::get($ff_name));

                $config_key_fix = 'settings::'.$config_key;
                $config_val = Input::get($ff_name);
                config_db()->set($config_key_fix, $config_val);
                Cache::forever($config_key_fix, $config_val);
            }
            
            Notification::success( trans('app.commerce_settings_updated'));
            return Redirect::route('admin.settings.commerce');
        } catch (ValidationException $e) {
            return Redirect::route('admin.settings.commerce')->withInput()->withErrors($e->getErrors());
        }
    }

    public function commerceStore() {
        try {
            $rules = [ 
                'payment_deadline' => 'required|numeric'
            ];

            $v = Validator::make(Input::all(), $rules);
            $setAttributeNames['payment_deadline'] = trans('app.payment_deadline');
            $v->setAttributeNames($setAttributeNames);
            if ($v->fails())
            {  
                return Redirect::Route('admin.settings.commerce')->withInput()->withErrors($v->errors());
            }
            $config_map = [
                'payment_deadline' => 'payment_deadline'
            ];

            foreach($config_map as $ff_name => $config_key){
                $config_key_fix = 'settings::'.$config_key;
                $config_val = Input::get($ff_name);
                config_db()->set($config_key_fix, $config_val);
                Cache::forever($config_key_fix, $config_val);
            }
            
            Notification::success( trans('app.commerce_settings_updated'));
            return Redirect::route('admin.settings.commerce');
        } catch (ValidationException $e) {
            return Redirect::route('admin.settings.commerce')->withInput()->withErrors($e->getErrors());
        }
    }


    public function seo() {
        $attr = [ 
                'title' => trans('app.seo_settings')
            ];
        if(!Entrust::can(['update_seo_setting'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        return view('backend.setting.seo');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function seoStore() {
        try {
            $config_map = [
            ];

            foreach (config('translatable.locales') as $locale) {
                $config_map += [
                    'default_meta_keywords.'.$locale => 'default_meta_keywords['.$locale.']',
                    'default_meta_description.'.$locale => 'default_meta_description['.$locale.']'
                ];
            }

            foreach($config_map as $ff_name => $config_key){
                //config_db()->set('settings::'.$config_key, Input::get($ff_name));
                $config_key_fix = 'settings::'.$config_key;
                $config_val = Input::get($ff_name);
                config_db()->set($config_key_fix, $config_val);
                Cache::forever($config_key_fix, $config_val);
            }
            
            Notification::success( trans('app.seo_settings_updated'));
            return Redirect::route('admin.settings.seo');
        } catch (ValidationException $e) {
            return Redirect::route('admin.settings.seo')->withInput()->withErrors($e->getErrors());
        }
    }

    public function weekendDays_injectWeekend($from, $to) {
        //format : 2016-01-01
        $data = getWeekendDatesWithDesc($from, $to);
        foreach($data as $row){
            $exist = WeekendDays::where('weekend_date', $row['weekend_date'])->count();
            if($exist == 0){
                $new_data = new WeekendDays;
                $new_data->weekend_date = $row['weekend_date'];
                $new_data->description = $row['description'];
                $new_data->save();
            }
        }
        return Redirect::route('admin.settings.weekend-days');
    }

    public function weekendDays() {
        $attr = [ 
                'title' => trans('app.weekend_days_settings')
            ];
        if(!Entrust::can(['update_weekend_days_setting'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        //dd(getWeekendDates('2016-01-01', '2016-2-31'));
        
        //dd($weekend_days);
        $weekend_days = WeekendDays::where(\DB::raw('DATE_FORMAT(weekend_date, "%Y")'), carbon_now_format('Y'))->get();
        //dd($weekend_days);
        return view('backend.setting.weekend-days', compact('weekend_days'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function weekendDaysStore() {
        try {
            $rules = [ 
                'weekend_date' => 'required|date_format:Y-m-d|unique:weekend_days,weekend_date', 
                'description' => 'required' 
            ];

            $v = Validator::make(Input::all(), $rules);
            $setAttributeNames['weekend_date'] = trans('app.weekend_date');
            $setAttributeNames['description'] = trans('app.description');
            $v->setAttributeNames($setAttributeNames);
            if ($v->fails())
            {  
                return Redirect::Route('admin.settings.weekend-days')->withInput()->withErrors($v->errors());
            }
            $new_data = new WeekendDays;
            $new_data->weekend_date = Input::get('weekend_date');
            $new_data->description = Input::get('description');
            $new_data->save();
            
            Notification::success( trans('app.data_updated'));
            return Redirect::route('admin.settings.weekend-days');
        } catch (ValidationException $e) {
            return Redirect::route('admin.settings.weekend-days')->withInput()->withErrors($e->getErrors());
        }
    }

    public function finance() {
        $attr = [ 
                'title' => trans('app.finance_settings')
            ];
        if(!Entrust::can(['update_finance_setting'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        return view('backend.setting.finance');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function financeStore() {
        try {

            $rules = [ 
                'rate_inflation' => 'required|numeric',
                'rate_property_inflation' => 'required|numeric',
                'rate_deposit' => 'required|numeric',
                'price_life_insurance' => 'required|numeric',
                'price_critical_insurance' => 'required|numeric',
                'tenor_plan_protection' => 'required|numeric'
            ];

            $config_map = [
                'rate_inflation' => 'rate_inflation',
                'rate_property_inflation' => 'rate_property_inflation',
                'rate_deposit' => 'rate_deposit',
                'price_life_insurance' => 'price_life_insurance',
                'price_critical_insurance' => 'price_critical_insurance',
                'tenor_plan_protection' => 'tenor_plan_protection'
            ];

            $input = [];
            foreach($config_map as $ff_name => $config_key){
                $input[$ff_name] = unformat_money_raw(Input::get($ff_name));
            }

            //dd($input);

            $v = Validator::make($input, $rules);
            $setAttributeNames['rate_inflation'] = trans('app.inflation');
            $setAttributeNames['rate_property_inflation'] = trans('app.property_inflation');
            $setAttributeNames['rate_deposit'] = trans('app.deposit');
            $setAttributeNames['price_life_insurance'] = trans('app.price_life_insurance');
            $setAttributeNames['price_critical_insurance'] = trans('app.price_critical_insurance');
            $setAttributeNames['tenor_plan_protection'] = trans('app.tenor_plan_protection');
            $v->setAttributeNames($setAttributeNames);
            if ($v->fails())
            {  
                return Redirect::Route('admin.settings.finance')->withInput()->withErrors($v->errors());
            }

            


            foreach($config_map as $ff_name => $config_key){
                $config_key_fix = 'settings::'.$config_key;
                $config_val = $input[$ff_name]; //  echo $config_key;
                config_db()->set($config_key_fix, $config_val);
                Cache::forever($config_key_fix, $config_val);
            }
            
            Notification::success( trans('app.finance_settings_updated'));
            return Redirect::route('admin.settings.finance');
        } catch (ValidationException $e) {
            return Redirect::route('admin.settings.finance')->withInput()->withErrors($e->getErrors());
        }
    }

    public function inflation() {
        $attr = [ 
                'title' => trans('app.inflation_settings')
            ];
        if(!Entrust::can(['update_inflation_setting'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        return view('backend.setting.inflation');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function inflationStore() {
        try {

            $rules = [ 
                'rate_inflation' => 'required|numeric',
                'rate_property_inflation' => 'required|numeric'
            ];

            $config_map = [
                'rate_inflation' => 'rate_inflation',
                'rate_property_inflation' => 'rate_property_inflation'
            ];

            $input = [];
            foreach($config_map as $ff_name => $config_key){
                $input[$ff_name] = unformat_money_raw(Input::get($ff_name));
            }

            //dd($input);

            $v = Validator::make($input, $rules);
            $setAttributeNames['rate_inflation'] = trans('app.inflation');
            $setAttributeNames['rate_property_inflation'] = trans('app.property_inflation');
            $v->setAttributeNames($setAttributeNames);
            if ($v->fails())
            {  
                return Redirect::Route('admin.settings.inflation')->withInput()->withErrors($v->errors());
            }

            


            foreach($config_map as $ff_name => $config_key){
                $config_key_fix = 'settings::'.$config_key;
                $config_val = $input[$ff_name]; //  echo $config_key;
                config_db()->set($config_key_fix, $config_val);
                Cache::forever($config_key_fix, $config_val);
            }
            
            Notification::success( trans('app.finance_settings_updated'));
            return Redirect::route('admin.settings.inflation');
        } catch (ValidationException $e) {
            return Redirect::route('admin.settings.inflation')->withInput()->withErrors($e->getErrors());
        }
    }

    public function investment() {
        $attr = [ 
                'title' => trans('app.investment_settings')
            ];
        if(!Entrust::can(['update_investment_setting'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        return view('backend.setting.investment');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function investmentStore() {
        try {

            $rules = [ 
                'rate_deposit' => 'required|numeric'
            ];

            $config_map = [
                'rate_deposit' => 'rate_deposit'
            ];

            $input = [];
            foreach($config_map as $ff_name => $config_key){
                $input[$ff_name] = unformat_money_raw(Input::get($ff_name));
            }

            //dd($input);

            $v = Validator::make($input, $rules);
            $setAttributeNames['rate_deposit'] = trans('app.deposit');
            $v->setAttributeNames($setAttributeNames);
            if ($v->fails())
            {  
                return Redirect::Route('admin.settings.investment')->withInput()->withErrors($v->errors());
            }

            foreach($config_map as $ff_name => $config_key){
                $config_key_fix = 'settings::'.$config_key;
                $config_val = $input[$ff_name]; //  echo $config_key;
                config_db()->set($config_key_fix, $config_val);
                Cache::forever($config_key_fix, $config_val);
            }
            
            Notification::success( trans('app.finance_settings_updated'));
            return Redirect::route('admin.settings.investment');
        } catch (ValidationException $e) {
            return Redirect::route('admin.settings.investment')->withInput()->withErrors($e->getErrors());
        }
    }

    public function insurance() {
        $attr = [ 
                'title' => trans('app.insurance_settings')
            ];
        if(!Entrust::can(['update_insurance_setting'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        return view('backend.setting.insurance');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function insuranceStore() {
        try {

            $rules = [ 
                'price_life_insurance' => 'required|numeric',
                'price_critical_insurance' => 'required|numeric',
                'tenor_plan_protection' => 'required|numeric'
            ];

            $config_map = [
                'price_life_insurance' => 'price_life_insurance',
                'price_critical_insurance' => 'price_critical_insurance',
                'tenor_plan_protection' => 'tenor_plan_protection'
            ];

            $input = [];
            foreach($config_map as $ff_name => $config_key){
                $input[$ff_name] = unformat_money_raw(Input::get($ff_name));
            }

            //dd($input);

            $v = Validator::make($input, $rules);
            $setAttributeNames['price_life_insurance'] = trans('app.price_life_insurance');
            $setAttributeNames['price_critical_insurance'] = trans('app.price_critical_insurance');
            $setAttributeNames['tenor_plan_protection'] = trans('app.tenor_plan_protection');
            $v->setAttributeNames($setAttributeNames);
            if ($v->fails())
            {  
                return Redirect::Route('admin.settings.insurance')->withInput()->withErrors($v->errors());
            }

            


            foreach($config_map as $ff_name => $config_key){
                $config_key_fix = 'settings::'.$config_key;
                $config_val = $input[$ff_name]; //  echo $config_key;
                config_db()->set($config_key_fix, $config_val);
                Cache::forever($config_key_fix, $config_val);
            }
            
            Notification::success( trans('app.insurance_settings_updated'));
            return Redirect::route('admin.settings.insurance');
        } catch (ValidationException $e) {
            return Redirect::route('admin.settings.insurance')->withInput()->withErrors($e->getErrors());
        }
    }

    public function cfp() {
        $attr = [ 
                'title' => trans('app.cfp_settings')
            ];
        if(!Entrust::can(['update_cfp_setting'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        return view('backend.setting.cfp');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function cfpStore() {
        try {

            $rules = [ 
                'cfp_working_hour_start' => 'required|date_format:H:i',
                'cfp_working_hour_end' => 'required|date_format:H:i',
                'cfp_remind_x_minutes_before_schedule' => 'required|numeric'
            ];

            $config_map = [
                'cfp_working_hour_start' => 'cfp_working_hour_start',
                'cfp_working_hour_end' => 'cfp_working_hour_end',
                'cfp_remind_x_minutes_before_schedule' => 'cfp_remind_x_minutes_before_schedule'
            ];

            $input = [];
            foreach($config_map as $ff_name => $config_key){
                $input[$ff_name] = unformat_money_raw(Input::get($ff_name));
            }

            //dd($input);

            $v = Validator::make($input, $rules);
            $setAttributeNames['cfp_working_hour_start'] = trans('app.working_hour_start');
            $setAttributeNames['cfp_working_hour_end'] = trans('app.working_hour_end');
            $setAttributeNames['cfp_remind_x_minutes_before_schedule'] = trans('app.remind_x_minutes_before_schedule');
            $v->setAttributeNames($setAttributeNames);
            if ($v->fails())
            {  
                return Redirect::Route('admin.settings.cfp')->withInput()->withErrors($v->errors());
            }

            


            foreach($config_map as $ff_name => $config_key){
                $config_key_fix = 'settings::'.$config_key;
                $config_val = $input[$ff_name]; //  echo $config_key;
                config_db()->set($config_key_fix, $config_val);
                Cache::forever($config_key_fix, $config_val);
            }
            
            Notification::success( trans('app.cfp_settings_updated'));
            return Redirect::route('admin.settings.cfp');
        } catch (ValidationException $e) {
            return Redirect::route('admin.settings.cfp')->withInput()->withErrors($e->getErrors());
        }
    }

    public function wallet() {
        $attr = [ 
                'title' => trans('app.wallet_settings')
            ];
        if(!Entrust::can(['update_wallet_setting'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $wallet_category_options[' '] = '-';
        $wallet_category_options += renderLists($this->taxonomy->getTermsByPostType_n_parent('wallet', 'expense')->toHierarchy());
        return view('backend.setting.wallet', compact('wallet_category_options'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function walletStore() {
        try {

            $rules = [ 
                'default_taxo_wallet_id_on_reminder_action' => 'required|numeric'
            ];

            $config_map = [
                'default_taxo_wallet_id_on_reminder_action' => 'default_taxo_wallet_id_on_reminder_action'
            ];


            $v = Validator::make(Input::all(), $rules);
            $setAttributeNames['default_taxo_wallet_id_on_reminder_action'] = trans('app.default_taxo_wallet_id_on_reminder_action');
            $v->setAttributeNames($setAttributeNames);
            if ($v->fails())
            {  
                return Redirect::Route('admin.settings.wallet')->withInput()->withErrors($v->errors());
            }

            foreach($config_map as $ff_name => $config_key){
                $config_key_fix = 'settings::'.$config_key;
                $config_val = Input::get($ff_name);
                config_db()->set($config_key_fix, $config_val);
                Cache::forever($config_key_fix, $config_val);
            }
            
            Notification::success( trans('app.wallet_settings_updated'));
            return Redirect::route('admin.settings.wallet');
        } catch (ValidationException $e) {
            return Redirect::route('admin.settings.wallet')->withInput()->withErrors($e->getErrors());
        }
    }

    public function subscription() {
        $attr = [ 
                'title' => trans('app.subscription_settings')
            ];
        if(!Entrust::can(['update_subscription_setting'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        return view('backend.setting.subscription');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function subscriptionStore() {
        try {

            $rules = [ 
                'free_consultation_limit' => 'required|numeric'
            ];

            $config_map = [
                'free_consultation_limit' => 'free_consultation_limit'
            ];

            $input = [];
            foreach($config_map as $ff_name => $config_key){
                $input[$ff_name] = unformat_money_raw(Input::get($ff_name));
            }

            //dd($input);

            $v = Validator::make($input, $rules);
            $setAttributeNames['free_consultation_limit'] = trans('app.free_consultation_limit');
            $v->setAttributeNames($setAttributeNames);
            if ($v->fails())
            {  
                return Redirect::Route('admin.settings.subscription')->withInput()->withErrors($v->errors());
            }

            


            foreach($config_map as $ff_name => $config_key){
                $config_key_fix = 'settings::'.$config_key;
                $config_val = $input[$ff_name]; //  echo $config_key;
                config_db()->set($config_key_fix, $config_val);
                Cache::forever($config_key_fix, $config_val);
            }
            
            Notification::success( trans('app.data_updated'));
            return Redirect::route('admin.settings.subscription');
        } catch (ValidationException $e) {
            return Redirect::route('admin.settings.subscription')->withInput()->withErrors($e->getErrors());
        }
    }
}