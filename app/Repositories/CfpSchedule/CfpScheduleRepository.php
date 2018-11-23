<?php namespace App\Repositories\CfpSchedule;

use App\Models\CfpSchedule;

use Auth;
use Config;
use Response;
use Illuminate\Support\Str;
use App\Repositories\RepositoryAbstract;
use App\Repositories\CrudableInterface as CrudableInterface;
use App\Repositories\RepositoryInterface as RepositoryInterface;
use App\Exceptions\Validation\ValidationException;
use App\Repositories\AbstractValidator as Validator;
use LaravelLocalization;
use Notification;
use App\User;
use Carbon\Carbon;
use Validator as Valid;
use App\Repositories\CfpScheduleType\CfpScheduleTypeRepository;
use App\Models\CfpScheduleType;
use App\Models\CfpScheduleReminder;
use Illuminate\Support\MessageBag;
use Input;
use App\Models\Reminder;
use App\Models\ReminderMeta;
use DB;
use Request;
use Route;
use App\Repositories\Cycle\CycleRepository;
use App\Models\Cycle;


class CfpScheduleRepository extends RepositoryAbstract implements CfpScheduleInterface, CrudableInterface {

    protected $perPage;
    protected $cfpSchedule;
    protected $cfpScheduleType;
    protected $cycle;
    /**
     * Rules
     *
     * @var array
     */
    protected static $rules;
    protected static $attributeNames;

    /**
     * @param ProductAttribute $productAttribute
     */
    public function __construct(CfpSchedule $cfpSchedule) {

        $config = Config::get('holiday');
        $this->perPage = $config['per_page'];
        $this->cfpSchedule = $cfpSchedule;
        $this->cfpScheduleType = new CfpScheduleTypeRepository(new CfpScheduleType);
        $this->cycle = new CycleRepository(new Cycle);

        $rules_n_attributeNames = $this->rules();
        self::$rules = $rules_n_attributeNames['rules'];
        self::$attributeNames = $rules_n_attributeNames['attributeNames'];
    }

    public function rules(){
        $_rules = array();
        $setAttributeNames = array();
        
        $_rules['schedule_type'] = 'required';
        $_rules['title'] = 'required';
        $_rules['client_id'] = 'required';//|exists:users,id';
        $_rules['cfp_id'] = 'required';//|exists:users,id';
        $_rules['schedule_start_date'] = 'required|date_format:"d M Y"|after:today|cannot_booking_national_day_indonesia';

        /*$method = Request::method(); dd($method);
        switch($method)
        {
            case 'POST'://create
            break;
            case 'PATCH'://update
            break;
            default:
            break;
        }*/

        //$id = Input::has('id') ? Input::get('id') : Route::current()->getParameter('schedule');//jika ada id diset di param api, jika tidak maka akan pakai parameter dari url admin
        $id = Input::get('id');
        //dd($id);
        $_rules['schedule_available_time_slot'] = 'required|date_format:"d M Y H:i"|after:today-1|working_hours:schedule_type|timeslot_available:cfp_id,schedule_type,'.$id;
        
        if(Input::has('id') && Input::get('id') != '' ){
            if(!is_null($this->find($id))){ 
                $_rules['schedule_available_time_slot'] = 'required|date_format:"d M Y H:i"|is_schedule_expired:'.$id.'|after:today-1|working_hours:schedule_type|timeslot_available:cfp_id,schedule_type,'.$id;
            }
        }
        
        if(Input::has('schedule_type') && Input::get('schedule_type') == 'meet_up' && strtotime(Input::get('schedule_start_date')) == strtotime(Carbon::now()->format('d M Y'))){
            $_rules['schedule_start_date'] .= '|cannot_booking_same_day';
        }

        //weekend achmad andriansyah
        // $daily = date('N', strtotime(Input::get('schedule_start_date')));
        // if($daily == 6){
        //     $_rules['schedule_start_date'] .= '|cannot_booking_saturday';
        // }
        // if($daily == 7){
        //     $_rules['schedule_start_date'] .= '|cannot_booking_sunday';
        // }

        //var_dump(date('N', strtotime($input['schedule_start_date'])));

        //$_rules['schedule_end_date'] = 'required';

        $setAttributeNames['title'] = trans('app.title');
        $setAttributeNames['client_id'] = trans('app.client_name');
        $setAttributeNames['cfp_id'] = trans('app.cfp_name');
        $setAttributeNames['schedule_start_date'] = trans('app.schedule_start_date'); //dd(Input::get('schedule_start_date'));
        $setAttributeNames['schedule_end_date'] = trans('app.schedule_end_date');
        $setAttributeNames['schedule_available_time_slot'] = trans('app.time_slot');
        if(Input::has('is_reminder_activated') && Input::get('is_reminder_activated') == 1 && Input::has('reminder') ){
            $_rules['reminder.module_name'] = 'required';
            $_rules['reminder.screen_name'] = 'required';
            $_rules['reminder.reminder_datetime'] = 'required';

            switch (Input::get('reminder.module_name')) {
                case 'free':
                    $_rules['reminder.about'] = 'required';
                    $setAttributeNames['reminder.about'] = trans('app.about');
                    break;
                case 'schedule' : 
                    //$_rules['reminder.cfp_schedule_id'] = 'required';
                    //$setAttributeNames['reminder.cfp_schedule_id'] = trans('app.schedule_id');
                break;
            }

            $setAttributeNames['reminder.module_name'] = trans('app.module_name');
            $setAttributeNames['reminder.screen_name'] = trans('app.screen_name');
            $setAttributeNames['reminder.reminder_datetime'] = trans('app.datetime');
        }
        


        /*
        foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
            $_rules['name.'.$locale] = 'required|max:255';

            $setAttributeNames['name.' . $locale] = trans('app.name').' [ ' . $properties['native'].' ]';
        }
        */



        return [
            'rules' => $_rules,
            'attributeNames' => $setAttributeNames
        ]; 

    }

    public function all() {
        return $this->cfpSchedule->orderBy('created_by', 'DESC')->get();
    }

    public function paginate($page = 1, $limit = 10, $filter = array()) {

        $result = new \StdClass;
        $result->page = $page;
        $result->limit = $limit;
        $result->totalItems = 0;
        $result->items = array();

        $query = $this->cfpSchedule->select('cfp_schedules.*', 'c.name as client_name', 'cfp.name as cfp_name','uc.name as created_by_name', 'uu.name as updated_by_name', 'st.type_display_name');
        $query->orderBy('created_at', 'DESC');
        //dd($query);
        /*if(!$all) {
            $query->where('is_published', 1);
        }*/

        $query->join('cfp_schedule_types as st', 'st.id', '=', 'cfp_schedules.schedule_type_id', 'left');
        $query->join('users as c', 'c.id', '=', 'cfp_schedules.client_id', 'left');
        $query->join('users as cfp', 'cfp.id', '=', 'cfp_schedules.cfp_id', 'left');
        $query->join('users as uc', 'uc.id', '=', 'cfp_schedules.created_by', 'left');
        $query->join('users as uu', 'uu.id', '=', 'cfp_schedules.updated_by', 'left');

        //\DB::enableQueryLog();
        if(is_array($filter)){
            foreach($filter as $ff => $term){
                if(trim($term) != ''){
                    switch ($ff) {
                        case 'record_flag_is_not':
                            $query->where('cfp_schedules.record_flag', '!=', $term);
                        break;
                        case 'schedule_type':
                            $query->where('schedule_type_id', $term);
                        break;
                        case 'client_name':
                            $query->whereHas('client', function($q) use ($term) {
                                $q
                                ->whereRaw('LOWER(name) like ?', [ '%'.strtolower($term).'%' ]);
                            });
                        break;
                        case 'cfp_name':
                            $query->whereHas('cfp', function($q) use ($term) {
                                $q
                                ->whereRaw('LOWER(name) like ?', [ '%'.strtolower($term).'%' ]);
                            });
                        break;
                    }
                }
            }
        }

        $cfpSchedules = $query->skip($limit * ($page - 1))->take($limit)->get();

        //dd(\DB::getQueryLog());
        //dd($categoryCodes);

        $result->totalItems = $this->totalCfpSchedules($filter);
        $result->items = $cfpSchedules->all();
/*
        foreach ($result->items as $key => $item) {
            $result->items[$key]->cfpSchedules = $item;

            $customer_user = User::find($item->customer_id);
            $cfp_user = User::find($item->cfp_id);
            $userCreate = User::find($item->created_by);

            $result->items[$key]->customerName = $customer_user->name;
            $result->items[$key]->cfpName = $cfp_user->name;
            $result->items[$key]->userCreate = $userCreate->name;

            if(!is_null($item->updated_by))
            {
                $userUpdate = User::find($item->updated_by);
                $result->items[$key]->userUpdate = $userUpdate->name;
            }
        };*/
        
        //dd($result);

        return $result;
    }

    public function findJUNK($id) {

        $rules['id'] = 'required|exists:mylife_trc_schedule_cfp,internalid';

        $data['id'] = $id;

        $validator = Valid::make($data, $rules);

        if ($validator->fails())
        { 
            //dd($validator->errors()->first('id'));
            $data['id'] = $validator->errors()->first('id');
            return $data;
        }

        return $this->cfpSchedule->findOrFail($id);
    }

    public function find($id) {
        return $this->cfpSchedule
        ->select('cfp_schedules.*','uc.name as created_by_name', 'uu.name as updated_by_name', 'st.type_name')
        ->with(['client.userMetas', 'cfp.userMetas'])
        ->join('cfp_schedule_types as st', 'st.id', '=', 'cfp_schedules.schedule_type_id', 'left')
        ->join('users as uc', 'uc.id', '=', 'cfp_schedules.created_by', 'left')
        ->join('users as uu', 'uu.id', '=', 'cfp_schedules.updated_by', 'left')
        ->find($id);
    }

    public function findAllByCfpIDJUNK($cfpID = '') { //sementara dikosongin


        return $this->cfpSchedule
        ->select('cfp_schedules.*','uc.name as created_by_name', 'uu.name as updated_by_name', 'st.type_name')
        ->with(['client', 'cfp'])
        ->join('cfp_schedule_types as st', 'st.id', '=', 'cfp_schedules.schedule_type_id', 'left')
        ->join('users as uc', 'uc.id', '=', 'cfp_schedules.created_by', 'left')
        ->join('users as uu', 'uu.id', '=', 'cfp_schedules.updated_by', 'left')
        ->where('cfp_id', $cfpID)
        ->orderBy('schedule_start_date')
        ->get();
    }
    
    public function findAllBy($attributes) { //sementara dikosongin

        $cfpID = $attributes['cfp_id']; 
        $dmY_api = Carbon::createFromFormat('Y-m-d' , $attributes['yyyymm_api'].'-01');
        $dmY_current = Carbon::createFromFormat('Y-m-d' , Carbon::now()->format('Y-m').'-01');
        //dd($dmY_current);
        $dmY_api_raw = strtotime($dmY_api);
        $dmY_current_raw = strtotime($dmY_current);

        $start_end_date = '';
        if($dmY_api_raw == $dmY_current_raw){ 
            $start_date = Carbon::now()->format('Y-m-d H:i:s');//nantinya harus pakai H:i:s , saat ini masi pake 00:00:00 karena sehari full .tanggal server sekarang
            //dd($start_date);
            $end_date = $dmY_current->endOfMonth()->format('Y-m-d').' 23:59:59';//sampai akhir bulan dari tanggal server sekarang
        }elseif($dmY_api_raw < $dmY_current_raw){//expired
            $start_end_date = 'date_expired';//kosong karena expired
        }elseif($dmY_api_raw > $dmY_current_raw){
            $start_date = $dmY_api->format('Y-m-d').' 00:00:00';//awal bulan dari api
            $end_date = $dmY_api->endOfMonth()->format('Y-m-d').' 23:59:59';//akhir bulan dari api
        }
        //dd(strtotime('2017-11-23 13:10:00'));
        //dd('start : '.$start_date.' | end : '.$end_date);//tes dengan ini
        //\DB::enableQueryLog();

        //$this->cfpSchedule->whereBetween('schedule_start_date', [$start_date, $end_date])->get()->toArray();
        //dd(\DB::getQueryLog());
        //$start_date = '2017-11-23 15:30:00';//tes
        //$end_date = '2017-11-23 14:59:59';//tes
        if($start_end_date != 'date_expired'){
            return $this->cfpSchedule
            ->select('cfp_schedules.*','uc.name as created_by_name', 'uu.name as updated_by_name', 'st.type_name')
            ->with(['client.usermetas', 'cfp.usermetas'])
            ->join('cfp_schedule_types as st', 'st.id', '=', 'cfp_schedules.schedule_type_id', 'left')
            ->join('users as uc', 'uc.id', '=', 'cfp_schedules.created_by', 'left')
            ->join('users as uu', 'uu.id', '=', 'cfp_schedules.updated_by', 'left')
            ->where('cfp_id', $cfpID)
            //XXX->whereBetween('schedule_start_date', [$start_date, $end_date])
            ->where('schedule_end_date', '>=', $start_date)
            ->where('schedule_start_date', '<=', $end_date)
            ->where('cfp_schedules.record_flag', '!=', 'D')
            //++->where('schedule_start_date', '<=', $start_date)
            //++->where('schedule_end_date', '>=', $start_date)
            //++->where('schedule_start_date', '<=', $end_date)
            //XX->where('schedule_end_date', '<=', $end_date)
            ->orderBy('schedule_start_date')
            ->get();
        }else{
            return [];
        }
        
    }

    public function findClientScheduleAllBy($attributes) { //sementara dikosongin

        $clientID = $attributes['client_id']; 
        $dmY_api = Carbon::createFromFormat('Y-m-d' , $attributes['yyyymm_api'].'-01');
        $dmY_current = Carbon::createFromFormat('Y-m-d' , Carbon::now()->format('Y-m').'-01');
        //dd($dmY_current);
        $dmY_api_raw = strtotime($dmY_api);
        $dmY_current_raw = strtotime($dmY_current);

        $start_end_date = '';
        if($dmY_api_raw == $dmY_current_raw){ 
            $start_date = Carbon::now()->format('Y-m-d H:i:s');//nantinya harus pakai H:i:s , saat ini masi pake 00:00:00 karena sehari full .tanggal server sekarang
            //dd($start_date);
            $end_date = $dmY_current->endOfMonth()->format('Y-m-d').' 23:59:59';//sampai akhir bulan dari tanggal server sekarang
        }elseif($dmY_api_raw < $dmY_current_raw){//expired
            $start_end_date = 'date_expired';//kosong karena expired
        }elseif($dmY_api_raw > $dmY_current_raw){
            $start_date = $dmY_api->format('Y-m-d').' 00:00:00';//awal bulan dari api
            $end_date = $dmY_api->endOfMonth()->format('Y-m-d').' 23:59:59';//akhir bulan dari api
        }
        //dd(strtotime('2017-11-23 13:10:00'));
        //dd('start : '.$start_date.' | end : '.$end_date);//tes dengan ini
        //\DB::enableQueryLog();

        //$this->cfpSchedule->whereBetween('schedule_start_date', [$start_date, $end_date])->get()->toArray();
        //dd(\DB::getQueryLog());
        //$start_date = '2017-11-23 15:30:00';//tes
        //$end_date = '2017-11-23 14:59:59';//tes
        if($start_end_date != 'date_expired'){
            return $this->cfpSchedule
            ->select('cfp_schedules.*','uc.name as created_by_name', 'uu.name as updated_by_name', 'st.type_name')
            ->with(['client.usermetas', 'cfp.usermetas', 'reminders.reminder'])
            ->join('cfp_schedule_types as st', 'st.id', '=', 'cfp_schedules.schedule_type_id', 'left')
            ->join('users as uc', 'uc.id', '=', 'cfp_schedules.created_by', 'left')
            ->join('users as uu', 'uu.id', '=', 'cfp_schedules.updated_by', 'left')
            ->where('client_id', $clientID)
            //XXX->whereBetween('schedule_start_date', [$start_date, $end_date])
            ->where('schedule_end_date', '>=', $start_date)
            ->where('schedule_start_date', '<=', $end_date)
            ->where('cfp_schedules.record_flag', '!=', 'D')
            //++->where('schedule_start_date', '<=', $start_date)
            //++->where('schedule_end_date', '>=', $start_date)
            //++->where('schedule_start_date', '<=', $end_date)
            //XX->where('schedule_end_date', '<=', $end_date)
            ->orderBy('schedule_start_date')
            ->get();
        }else{
            return [];
        }
        
    }

    public function findCurrentScheduleBy($attributes) {

        $cfpID = $attributes['cfp_id']; 
        $current_datetime = Carbon::now()->format('Y-m-d H:i:s');
        //$current_datetime = '2017-11-23 17:00:01';//tes
        //dd($current_datetime);
/*
        $dmY_api = Carbon::createFromFormat('Y-m-d' , $attributes['yyyymm_api'].'-01');
        $dmY_current = Carbon::createFromFormat('Y-m-d' , Carbon::now()->format('Y-m').'-01');
        //dd($dmY_current);
        $dmY_api_raw = strtotime($dmY_api);
        $dmY_current_raw = strtotime($dmY_current);

        $start_end_date = '';
        if($dmY_api_raw == $dmY_current_raw){ 
            $start_date = Carbon::now()->format('Y-m-d H:i:s');//nantinya harus pakai H:i:s , saat ini masi pake 00:00:00 karena sehari full .tanggal server sekarang
            $end_date = $dmY_current->endOfMonth()->format('Y-m-d').' 23:59:59';//sampai akhir bulan dari tanggal server sekarang
        }elseif($dmY_api_raw < $dmY_current_raw){//expired
            $start_end_date = 'date_expired';//kosong karena expired
        }elseif($dmY_api_raw > $dmY_current_raw){
            $start_date = $dmY_api->format('Y-m-d').' 00:00:00';//awal bulan dari api
            $end_date = $dmY_api->endOfMonth()->format('Y-m-d').' 23:59:59';//akhir bulan dari api
        }*/

        //dd('start : '.$start_date.' | end : '.$end_date);//tes dengan ini
        //\DB::enableQueryLog();

        //$this->cfpSchedule->whereBetween('schedule_start_date', [$start_date, $end_date])->get()->toArray();
        //dd(\DB::getQueryLog());
        return $this->cfpSchedule
        ->select('cfp_schedules.*','uc.name as created_by_name', 'uu.name as updated_by_name', 'st.type_name')
        ->with(['client.usermetas', 'cfp.usermetas'])
        ->join('cfp_schedule_types as st', 'st.id', '=', 'cfp_schedules.schedule_type_id', 'left')
        ->join('users as uc', 'uc.id', '=', 'cfp_schedules.created_by', 'left')
        ->join('users as uu', 'uu.id', '=', 'cfp_schedules.updated_by', 'left')
        ->where('cfp_id', $cfpID)
        ->where('schedule_start_date', '<=', $current_datetime)
        ->where('schedule_end_date', '>=', $current_datetime)
        ->where('cfp_schedules.record_flag', '!=', 'D')
        ->orderBy('schedule_start_date')
        ->get();
        
    }

    public function findClientCurrentScheduleBy($attributes) {
        $clientID = $attributes['client_id']; 
        //$current_datetime = Carbon::now()->format('Y-m-d H:i:s');
        $cfp_working_hour_end = config_db_cached('settings::cfp_working_hour_end');
        //$end_of_working_hour_datetime  = Carbon::now()->format('Y-m-d '.$cfp_working_hour_end);

        $start_date = Carbon::now()->format('Y-m-d H:i:s');
        $end_date = Carbon::now()->format('Y-m-d '.$cfp_working_hour_end);

        return $this->cfpSchedule
        ->select('cfp_schedules.*','uc.name as created_by_name', 'uu.name as updated_by_name', 'st.type_name')
        ->with(['client.usermetas', 'cfp.usermetas'])
        ->join('cfp_schedule_types as st', 'st.id', '=', 'cfp_schedules.schedule_type_id', 'left')
        ->join('users as uc', 'uc.id', '=', 'cfp_schedules.created_by', 'left')
        ->join('users as uu', 'uu.id', '=', 'cfp_schedules.updated_by', 'left')
        ->where('client_id', $clientID)
        //->where('schedule_start_date', '<=', $current_datetime)
        //->where('schedule_end_date', '>=', $current_datetime)
        //->where('schedule_start_date', '>=', $current_datetime)
        //->where('schedule_end_date', '<=', $end_of_working_hour_datetime)

        ->where('schedule_end_date', '>=', $start_date)
        ->where('schedule_start_date', '<=', $end_date)

        ->where('cfp_schedules.record_flag', '!=', 'D')
        ->orderBy('schedule_start_date')
        ->get();
        
    }

    public function create($attributes) {
        if($this->isValid($attributes)) {
            
            $reminder_data = [];
            if(!isset($attributes['is_reminder_activated']))
                $attributes['is_reminder_activated'] = 0; 
            $cfp_working_hour_start = config_db_cached('settings::cfp_working_hour_start');//'09:00:00';
            $cfp_working_hour_end = config_db_cached('settings::cfp_working_hour_end');//'17:00:00';
            $user_id = Auth::check()?Auth::user()->id:(isset($attributes['client_id'])?$attributes['client_id']:'');//dahulukan auth user karena kemungkinan si admin yang ubah, dan kenapa client id jika via client app, karena client yang membuat schedule
            //dd($user_id);
            DB::beginTransaction(); 
            switch ($attributes['schedule_type']) {
                case 'meet_up':
                    $spare_time = 120;//in minutes, 2 hours
                    $duration = 60;
                    break;
                default: //video_call, call
                    $spare_time = 30;//in minutes, 30 minutes
                    $duration = 30;
                    break;
            }

            $meta_map = [
                'next_reminder_datetime' => [ 'meta_key' => 'next_reminder_datetime', 'type' => 'text' ]
            ];

            if(isset($attributes['is_reminder_activated']) && $attributes['is_reminder_activated'] == 0 && !isset($attributes['reminder'])){
                $attributes['reminder'] =  [
                    'module_name' => 'free',
                    'screen_name' => 'view_schedule'
                ];//reminder kita set default, jika tidak didefine
            }

            //if(isset($attributes['reminder'])){
            switch ($attributes['reminder']['module_name']) {
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
            //}

            $schedule_type = $this->cfpScheduleType->findByTypeName($attributes['schedule_type']); //dipindahkan ke dalam repository supaya terdeteksi saat validation
            $attributes['schedule_type_id'] = $schedule_type->id;

            //is start of working hour ?
            $schedule_time_slot_Hi = Carbon::parse($attributes['schedule_available_time_slot'])->format('H:i');
            $schedule_start_date_plus_spare = Carbon::parse($attributes['schedule_available_time_slot'])->subMinutes($spare_time)->format('Y-m-d H:i:s');
            if(strtotime($schedule_time_slot_Hi) == strtotime($cfp_working_hour_start)){
                $schedule_start_date_plus_spare = Carbon::parse($attributes['schedule_available_time_slot'])->format('Y-m-d H:i:s');
            }

            $attributes['schedule_start_date_plus_spare'] = $schedule_start_date_plus_spare;//Carbon::parse($attributes['schedule_available_time_slot'])->subMinutes($spare_time)->format('Y-m-d H:i:s');
            $attributes['schedule_start_date'] = Carbon::parse($attributes['schedule_available_time_slot'])->format('Y-m-d H:i:s');
            $attributes['schedule_end_date'] = Carbon::parse($attributes['schedule_available_time_slot'])->addMinutes($duration)->format('Y-m-d H:i:s');
            $client_raw = explode('__', $attributes['client_id']);
            $cfp_raw = explode('__', $attributes['cfp_id']);
            $attributes['client_id'] = $client_raw[0];
            $attributes['cfp_id'] = $cfp_raw[0];

            $cycle_saved = $this->cycle->is_full_cycle($user_id, true);
            $cycle_id = $cycle_saved['cycle_id'];
            //$schedule_type = $this->scheduleType->findByTypeName($attributes['schedule_type']);
            //$schedule_type_id = $schedule_type->id;//$attributes['schedule_type']
            //$client_raw = explode('__', $attributes['client_id']);
            //$cfp_raw = explode('__', $attributes['cfp_id']);
            //$schedule_start_date = Carbon::parse($attributes['schedule_available_time_slot'])->format('yyyy-mm-dd H:i:s');
            $schedule = $this->cfpSchedule->create([
                'schedule_type_id' => $attributes['schedule_type_id'],
                'title' => $attributes['title'],
                'client_id' => $attributes['client_id'],
                'cfp_id' => $attributes['cfp_id'],
                'schedule_start_date_plus_spare' => $attributes['schedule_start_date_plus_spare'],
                'schedule_start_date' => $attributes['schedule_start_date'],
                'schedule_end_date' => $attributes['schedule_end_date'],
                //'location' => $attributes['location'],
                'notes' => $attributes['notes'],
                'created_by' => $user_id, 
                'created_at' => Carbon::now(), 
                'updated_by' => $user_id, 
                'updated_at' => Carbon::now(),
                'record_flag' => 'N',
                'cycle_id' => $cycle_id
            ]);
            $data_res = [];
            $data_res[ 'schedule_id'] = $schedule->internalid;

            /*$reminder_meta_attributes = [
                'about' => (isset($attributes['reminder']) && isset($attributes['reminder']['about']) && $attributes['reminder']['about'] != '' )?$attributes['reminder']['about']:'',
                'note' => (isset($attributes['reminder']) && isset($attributes['reminder']['note']) && $attributes['reminder']['note'] != '' )?$attributes['reminder']['note']:'',
                'cfp_schedule_id' => $schedule->internalid,//$attributes['cfp_id'],
                'next_reminder_datetime' => ''
            ];   */     
        
            if(isset($attributes['is_reminder_activated']) && $attributes['is_reminder_activated'] == 1){
                $reminder_attributes = [
                    'user_id' => $attributes['client_id'],
                    'module_name' => $attributes['reminder']['module_name'],
                    'screen_name' => $attributes['reminder']['screen_name'],
                    'reminder_datetime' => Carbon::parse($attributes['reminder']['reminder_datetime'])->format('Y-m-d H:i:s'),
                    'is_repeated' => (isset($attributes['reminder']['is_repeated']) && $attributes['reminder']['is_repeated'] != '' )?$attributes['reminder']['is_repeated']:'does_not_repeat',
                    'created_by' => $user_id,
                    'created_at' => Carbon::now(),
                    'updated_by' => $user_id,
                    'updated_at' => Carbon::now(),
                    'record_flag' => 'N'
                ];

                //cfp reminder
                /*$cfp_reminder_attributes = $reminder_attributes;
                $cfp_time_remind_before = 30;//in munutes
                $cfp_reminder_attributes['user_id'] = $attributes['cfp_id'];
                $cfp_reminder_attributes['reminder_datetime'] = Carbon::parse($attributes['schedule_start_date_plus_spare'])->subMinutes($cfp_time_remind_before)->format('Y-m-d H:i:s');
                $cfp_reminder_attributes['is_repeated'] = 'does_not_repeat';*/

                $reminder_meta_attributes = [
                    'about' => (isset($attributes['reminder']) && isset($attributes['reminder']['about']) && $attributes['reminder']['about'] != '' )?$attributes['reminder']['about']:'',
                    'note' => (isset($attributes['reminder']) && isset($attributes['reminder']['note']) && $attributes['reminder']['note'] != '' )?$attributes['reminder']['note']:'',
                    'cfp_schedule_id' => $schedule->internalid,//$attributes['cfp_id'],
                    'next_reminder_datetime' => ''
                ];
                
                $reminder = Reminder::create($reminder_attributes);//jika repeat nya diisi, jadi kita persiapkan ini kosong dulu
                //$cfp_reminder = Reminder::create($cfp_reminder_attributes);
                if($reminder && isset($attributes['reminder'])){
                    //$meta_map = [];
                    /*$meta_map = [
                        'next_reminder_datetime' => [ 'meta_key' => 'next_reminder_datetime', 'type' => 'text' ]
                    ];
                    switch ($attributes['reminder']['module_name']) {
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
                    }*/
                    
                    foreach($meta_map as $ff_name => $meta_attr){
                        switch ($meta_attr['type']) {
                            case 'image' :
                                    $reminder_meta_attributes[$ff_name] = getImagePath($reminder_meta_attributes[$ff_name]);
                                break;
                            default:
                                $reminder_meta_attributes[$ff_name] = $reminder_meta_attributes[$ff_name];
                                break;
                        }

                        ReminderMeta::insert([
                            'reminder_id' => $reminder->id,
                            'meta_key' => $meta_attr['meta_key'],
                            'meta_value' => $reminder_meta_attributes[$ff_name] 
                        ]);
                        /*
                        ReminderMeta::insert([
                            'reminder_id' => $cfp_reminder->id,
                            'meta_key' => $meta_attr['meta_key'],
                            'meta_value' => $reminder_meta_attributes[$ff_name] 
                        ]);*/
                    }

                    $data_res['reminder_id'] = $reminder->id;

                    $reminder_data[] = [
                        'user_id' => $attributes['client_id'],
                        'cfp_schedule_id' => $schedule->internalid,
                        'reminder_id' => $reminder->id
                    ];
                    //dd($reminder_dat);
                    //$data_res['cfp_reminder_id'] = $cfp_reminder->id;
                    //$data_res['cfp_reminder_datetime'] = $cfp_reminder_attributes['reminder_datetime'];
                }
            }

            //cfp reminder even client not set the reminder
            //cfp reminder
            $cfp_time_remind_before = config_db_cached('settings::cfp_remind_x_minutes_before_schedule');//in munutes
            $cfp_reminder_attributes = [
                'user_id' => $attributes['cfp_id'],
                'module_name' => $attributes['reminder']['module_name'],
                'screen_name' => $attributes['reminder']['screen_name'],
                'reminder_datetime' => Carbon::parse($attributes['schedule_start_date_plus_spare'])->subMinutes($cfp_time_remind_before)->format('Y-m-d H:i:s'),
                'is_repeated' => 'does_not_repeat',
                'created_by' => $user_id,
                'created_at' => Carbon::now(),
                'updated_by' => $user_id,
                'updated_at' => Carbon::now(),
                'record_flag' => 'N'
            ];
            
            //$cfp_reminder_attributes['user_id'] = $attributes['cfp_id'];
            //$cfp_reminder_attributes['reminder_datetime'] = Carbon::parse($attributes['schedule_start_date_plus_spare'])->subMinutes($cfp_time_remind_before)->format('Y-m-d H:i:s');
            //$cfp_reminder_attributes['is_repeated'] = 'does_not_repeat';
            $cfp_reminder = Reminder::create($cfp_reminder_attributes);
            if($cfp_reminder){
                $reminder_meta_attributes = [
                    'about' => 'Client schedule',
                    'note' => '',
                    'cfp_schedule_id' => $schedule->internalid,
                    'next_reminder_datetime' => ''
                ]; 

                $meta_map = [
                    'cfp_schedule_id' => [ 'meta_key' => 'cfp_schedule_id', 'type' => 'text' ]
                ];
                foreach($meta_map as $ff_name => $meta_attr){
                    switch ($meta_attr['type']) {
                        case 'image' :
                                $reminder_meta_attributes[$ff_name] = getImagePath($reminder_meta_attributes[$ff_name]);
                            break;
                        default:
                            $reminder_meta_attributes[$ff_name] = $reminder_meta_attributes[$ff_name];
                            break;
                    }

                    ReminderMeta::insert([
                        'reminder_id' => $cfp_reminder->id,
                        'meta_key' => $meta_attr['meta_key'],
                        'meta_value' => $reminder_meta_attributes[$ff_name] 
                    ]);
                }
                $data_res['cfp_reminder_id'] = $cfp_reminder->id;
                $data_res['cfp_reminder_datetime'] = $cfp_reminder_attributes['reminder_datetime'];

                $reminder_data[] = [
                    'user_id' => $attributes['cfp_id'],
                    'cfp_schedule_id' => $schedule->internalid,
                    'reminder_id' => $cfp_reminder->id
                ];
            }

            //save relationship between cfp_schedule and reminder
            if(count($reminder_data)){ //dd($reminder_data);
                CfpScheduleReminder::insert($reminder_data);
            }
              
            
            $detail_schedule = $this->find($schedule->internalid); //dd($detail_schedule);
            $client_concern = $detail_schedule->title;
            $cfp_metas = userMeta($detail_schedule->cfp->userMetas); //dd($cfp_metas);
            $client_email = $detail_schedule->client->email;
            $cfp_email = $detail_schedule->cfp->email;
            $cfp_code = $cfp_metas->user_code; 
            $client_metas = userMeta($detail_schedule->client->userMetas);
            $client_code = $client_metas->user_code;
            $client_name = $detail_schedule->client->name;
            $cfp_name = $detail_schedule->cfp->name; 
            $schedule_start = Carbon::parse($detail_schedule->schedule_start_date)->format('d F Y H:i'); 


            $to_emails = [$client_email, $cfp_email];

            sendEmailWithTemplate([
                'email_template_module_id' => 2,//schedule
                'to' => $to_emails, // $client_email,
                'replace_vars' => [
                    '{client_concern}' => $client_concern,
                    '{client_name}' => $client_name,
                    '{client_code}' => $client_code,
                    '{cfp_name}' => $cfp_name,
                    '{cfp_code}' => $cfp_code,
                    '{schedule_start}' => $schedule_start
                ]
            ]);
/*
            sendEmailWithTemplate([
                'email_template_module_id' => 2,//schedule
                'to' => $cfp_email,
                'replace_vars' => [
                    '{client_concern}' => $client_concern,
                    '{client_name}' => $client_name,
                    '{client_code}' => $client_code,
                    '{cfp_name}' => $cfp_name,
                    '{cfp_code}' => $cfp_code,
                    '{schedule_start}' => $schedule_start
                ]
            ]);
*/

            DB::commit();
            return $data_res;
        }
        throw new ValidationException('Goal attribute validation failed', $this->getErrors());
    }

    public function update($id, $attributes) {
        if($this->isValid($attributes)) {
            DB::beginTransaction();
            $data_res[ 'schedule_id'] = $id;
            if(!isset($attributes['is_reminder_activated']))
                $attributes['is_reminder_activated'] = 0; 

            $cfp_working_hour_start = config_db_cached('settings::cfp_working_hour_start');//'09:00:00';
            $cfp_working_hour_end = config_db_cached('settings::cfp_working_hour_end');//'17:00:00';

            $user_id = Auth::check()?Auth::user()->id:(isset($attributes['client_id'])?$attributes['client_id']:'');//dahulukan auth user karena kemungkinan si admin yang ubah, dan kenapa client id jika via client app, karena client yang membuat schedule
            $t_attributes = array();
            $this->cfpSchedule = $this->find($id); //dd($this->cfpSchedule->toArray());
            switch ($attributes['schedule_type']) {
                case 'meet_up':
                    $spare_time = 120;//in minutes, 2 hours
                    $duration = 60;
                    break;
                default: //video_call, call
                    $spare_time = 30;//in minutes, 30 minutes
                    $duration = 30;
                    break;
            }

            $meta_map = [
                'next_reminder_datetime' => [ 'meta_key' => 'next_reminder_datetime', 'type' => 'text' ]
            ];

            if(isset($attributes['is_reminder_activated']) && $attributes['is_reminder_activated'] == 0 && !isset($attributes['reminder'])){
                $attributes['reminder'] =  [
                    'module_name' => 'free',
                    'screen_name' => 'view_schedule'
                ];//reminder kita set default, jika tidak didefine
            }

            //if(isset($attributes['reminder'])){
            switch ($attributes['reminder']['module_name']) {
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
            //}

            $schedule_type = $this->cfpScheduleType->findByTypeName($attributes['schedule_type']); //dipindahkan ke dalam repository supaya terdeteksi saat validation
            $attributes['schedule_type_id'] = $schedule_type->id;

            //is start of working hour ?
            $schedule_time_slot_Hi = Carbon::parse($attributes['schedule_available_time_slot'])->format('H:i');
            $schedule_start_date_plus_spare = Carbon::parse($attributes['schedule_available_time_slot'])->subMinutes($spare_time)->format('Y-m-d H:i:s');
            if(strtotime($schedule_time_slot_Hi) == strtotime($cfp_working_hour_start)){
                $schedule_start_date_plus_spare = Carbon::parse($attributes['schedule_available_time_slot'])->format('Y-m-d H:i:s');
            }

            $attributes['schedule_start_date_plus_spare'] = $schedule_start_date_plus_spare;//Carbon::parse($attributes['schedule_available_time_slot'])->subMinutes($spare_time)->format('Y-m-d H:i:s');
            $attributes['schedule_start_date'] = Carbon::parse($attributes['schedule_available_time_slot'])->format('Y-m-d H:i:s');
            $attributes['schedule_end_date'] = Carbon::parse($attributes['schedule_available_time_slot'])->addMinutes($duration)->format('Y-m-d H:i:s');
            $client_raw = explode('__', $attributes['client_id']);
            $cfp_raw = explode('__', $attributes['cfp_id']);
            $attributes['client_id'] = $client_raw[0];
            $attributes['cfp_id'] = $cfp_raw[0];

            //$user_id = Auth::user()->id;
            $t_attributes = [
                'schedule_type_id' => $attributes['schedule_type_id'],
                'title' => $attributes['title'],
                'client_id' => $attributes['client_id'],
                'cfp_id' => $attributes['cfp_id'],
                'schedule_start_date_plus_spare' => $attributes['schedule_start_date_plus_spare'],
                'schedule_start_date' => $attributes['schedule_start_date'],
                'schedule_end_date' => $attributes['schedule_end_date'],
                'notes' => $attributes['notes'],
                'updated_by' => $user_id, 
                'updated_at' => Carbon::now(),
                'record_flag' => 'U'
            ];
            $this->cfpSchedule->fill($t_attributes)->save();
            $cfp_schedule_id = $this->cfpSchedule->internalid;
            $data_res = [];
            $data_res[ 'schedule_id'] = $id;

            //update reminder

            //get reminder id first
            
            $cfp_time_remind_before = config_db_cached('settings::cfp_remind_x_minutes_before_schedule');//in munutes
            if(isset($attributes['is_reminder_activated']) && $attributes['is_reminder_activated'] == 1){
                $reminder_attributes = [
                    'module_name' => $attributes['reminder']['module_name'],
                    'screen_name' => $attributes['reminder']['screen_name'],
                    'reminder_datetime' => Carbon::parse($attributes['reminder']['reminder_datetime'])->format('Y-m-d H:i:s'),
                    'is_repeated' => (isset($attributes['reminder']['is_repeated']) && $attributes['reminder']['is_repeated'] != '' )?$attributes['reminder']['is_repeated']:'does_not_repeat',
                    'updated_by' => $user_id,
                    'updated_at' => Carbon::now(),
                    'record_flag' => 'U'
                ];

                $cfpScheduleReminder_client = CfpScheduleReminder::where('user_id', $attributes['client_id'])->where('cfp_schedule_id', $id)->first();
                
                if(!is_null($cfpScheduleReminder_client)){
                    $reminder_client_id = $cfpScheduleReminder_client->reminder_id;
                    Reminder::where('id', $reminder_client_id)->update($reminder_attributes);//client reminder
                }else{
                    $reminder_attributes += [
                        'user_id' => $attributes['client_id'],
                        'created_by' => $user_id,
                        'created_at' => Carbon::now(),
                        'record_flag' => 'N'
                    ];

                    $reminder_meta_attributes = [
                        'about' => (isset($attributes['reminder']) && isset($attributes['reminder']['about']) && $attributes['reminder']['about'] != '' )?$attributes['reminder']['about']:'',
                        'note' => (isset($attributes['reminder']) && isset($attributes['reminder']['note']) && $attributes['reminder']['note'] != '' )?$attributes['reminder']['note']:'',
                        'cfp_schedule_id' => $id,
                        'next_reminder_datetime' => ''
                    ];
                    
                    $reminder = Reminder::create($reminder_attributes);//jika repeat nya diisi, jadi kita persiapkan ini kosong dulu
                    if($reminder && isset($attributes['reminder'])){
                        $reminder_client_id = $reminder->id;
                        foreach($meta_map as $ff_name => $meta_attr){
                            switch ($meta_attr['type']) {
                                case 'image' :
                                        $reminder_meta_attributes[$ff_name] = getImagePath($reminder_meta_attributes[$ff_name]);
                                    break;
                                default:
                                    $reminder_meta_attributes[$ff_name] = $reminder_meta_attributes[$ff_name];
                                    break;
                            }
    
                            ReminderMeta::insert([
                                'reminder_id' => $reminder_client_id,
                                'meta_key' => $meta_attr['meta_key'],
                                'meta_value' => $reminder_meta_attributes[$ff_name] 
                            ]);
                        }
    
                        $data_res['reminder_id'] = $reminder_client_id;
    
                        $reminder_data[] = [
                            'user_id' => $attributes['client_id'],
                            'cfp_schedule_id' => $id,
                            'reminder_id' => $reminder_client_id
                        ];

                        //save relationship between cfp_schedule and reminder
                        if(count($reminder_data)){ //dd($reminder_data);
                            CfpScheduleReminder::insert($reminder_data);
                        }
                    }
                    
                }

                
                $data_res['reminder_id'] = $reminder_client_id;
            }

            if(isset($attributes['is_reminder_activated']) && $attributes['is_reminder_activated'] == 0){
                $cfpScheduleReminder_client = CfpScheduleReminder::where('user_id', $attributes['client_id'])->where('cfp_schedule_id', $id)->first();
                
                if(!is_null($cfpScheduleReminder_client)){
                    $reminder_client_id = $cfpScheduleReminder_client->reminder_id;
                    Reminder::where('id', $reminder_client_id)->delete();//client reminder
                    ReminderMeta::where('reminder_id', $reminder_client_id)->delete();
                }
            }

            $cfpScheduleReminder_cfp = CfpScheduleReminder::where('user_id', $attributes['cfp_id'])->where('cfp_schedule_id', $id)->first();
            if(!is_null($cfpScheduleReminder_cfp)){
                $reminder_datetime_cfp = Carbon::parse($attributes['schedule_start_date_plus_spare'])->subMinutes($cfp_time_remind_before)->format('Y-m-d H:i:s');
                Reminder::where('id', $cfpScheduleReminder_cfp->reminder_id)->update([
                    'reminder_datetime' => $reminder_datetime_cfp,
                    'updated_by' => $user_id,
                    'updated_at' => Carbon::now(),
                    'record_flag' => 'U'
                ]);
                $data_res['cfp_reminder_id'] = $cfpScheduleReminder_cfp->reminder_id;
                $data_res['cfp_reminder_datetime'] = $reminder_datetime_cfp;
            }

            
            // Reminder::where('user_id', $attributes['cfp_id'])->whereHas('reminderMetas', function($q) use ($cfp_schedule_id){
            //     return $q->where('meta_key', 'cfp_schedule_id')->where('meta_value', $cfp_schedule_id);
            // })->update([
            //     'reminder_datetime' => Carbon::parse($attributes['schedule_start_date_plus_spare'])->subMinutes($cfp_time_remind_before)->format('Y-m-d H:i:s')
            // ]);

            DB::commit();
            return $data_res;
        }
        throw new ValidationException('Cfp client attribute validation failed', $this->getErrors());
    }

    public function updateJUNKKRIEG($id, $attributes) {
        if($this->isValid($attributes)) {
            DB::beginTransaction();
            $t_attributes = array();
            $this->cfpClient = $this->find($id);
            switch ($attributes['schedule_type']) {
                case 'meet_up':
                    $spare_time = 120;//in minutes, 2 hours
                    $duration = 60;
                    break;
                default: //video_call, call
                    $spare_time = 30;//in minutes, 30 minutes
                    $duration = 30;
                    break;
            }
            $schedule_type = $this->cfpScheduleType->findByTypeName($attributes['schedule_type']); //dipindahkan ke dalam repository supaya terdeteksi saat validation
            $attributes['schedule_type_id'] = $schedule_type->id;
            $attributes['schedule_start_date_plus_spare'] = Carbon::parse($attributes['schedule_available_time_slot'])->subMinutes($spare_time)->format('Y-m-d H:i:s');
            $attributes['schedule_start_date'] = Carbon::parse($attributes['schedule_available_time_slot'])->format('Y-m-d H:i:s');
            $attributes['schedule_end_date'] = Carbon::parse($attributes['schedule_available_time_slot'])->addMinutes($duration)->format('Y-m-d H:i:s');;
            $client_raw = explode('__', $attributes['client_id']);
            $cfp_raw = explode('__', $attributes['cfp_id']);
            $attributes['client_id'] = $client_raw[0];
            $attributes['cfp_id'] = $cfp_raw[0];

            $user_id = Auth::user()->id;
            $t_attributes = [
                'schedule_type_id' => $attributes['schedule_type_id'],
                'title' => $attributes['title'],
                'client_id' => $attributes['client_id'],
                'cfp_id' => $attributes['cfp_id'],
                'schedule_start_date_plus_spare' => $attributes['schedule_start_date_plus_spare'],
                'schedule_start_date' => $attributes['schedule_start_date'],
                'schedule_end_date' => $attributes['schedule_end_date'],
                'notes' => $attributes['notes'],
                'updated_by' => $user_id, 
                'updated_at' => Carbon::now(),
                'record_flag' => 'U'
            ];
            $this->cfpClient->fill($t_attributes)->save();
            DB::commit();
            return true;
        }
        throw new ValidationException('Cfp client attribute validation failed', $this->getErrors());
    }


    public function delete($id) {

        /*$rules['id'] = 'required|exists:cfp_schedule,internalid';

        $attributes['id'] = $id;

        $validator = Valid::make($attributes, $rules);

        if ($validator->fails())
        { 
            throw new ValidationException('CFP Schedule attribute validation failed', $validator->errors());
        }*/
        
        $user_id = Auth::check()?Auth::user()->id:(isset($attributes['client_id'])?$attributes['client_id']:'');
        $cfpSchedule = $this->find($id);
        if(!is_null($cfpSchedule)){
            DB::beginTransaction();
            $cfpSchedule->update([
                'deleted_by' => $user_id,
                'deleted_at' => Carbon::now(),
                'record_flag' => 'D'
            ]);
            DB::commit();
            return true;
        }
        return false;

        //$cfpSchedule = $this->cfpSchedule->findOrFail($id);
       // $cfpSchedule->delete();
    }

    public function api_delete($id) {
        $cfpSchedule = $this->cfpSchedule->findOrFail($id);
        if($cfpSchedule){
            $cfpSchedule->delete();
            return true;
        }
        return false;
    }

    protected function totalCfpSchedulesJUNK($all = false) {
        return $this->cfpSchedule->count();
    }

    protected function totalCfpSchedules($filter = array()) {
        $query = $this->cfpSchedule
        ->select('cfp_schedules.id');

        if(is_array($filter)){
            foreach($filter as $ff => $term){
                if(trim($term) != ''){
                    switch ($ff) {
                        case 'schedule_type':
                            $query->where('schedule_type_id', $term);
                        break;
                        case 'client_name':
                            $query->whereHas('client', function($q) use ($term) {
                                $q
                                ->whereRaw('LOWER(name) like ?', [ '%'.strtolower($term).'%' ]);
                            });
                        break;
                        case 'cfp_name':
                            $query->whereHas('cfp', function($q) use ($term) {
                                $q
                                ->whereRaw('LOWER(name) like ?', [ '%'.strtolower($term).'%' ]);
                            });
                        break;
                    }
                }
            }
        }
        

        return $query->count();
    }

    public function availableTimeSlot($filter = array()){//$attributes){
        $query = $this->cfpSchedule->orderBy('created_at', 'DESC');
        if(is_array($filter)){
            foreach($filter as $ff => $term){
                if(trim($term) != ''){
                    switch ($ff) {
                        case 'record_flag_is_not':
                            $query->where('record_flag', '!=', $term);
                        break;
                        case 'schedule_id':
                            $query->where('internalid', '!=', $term);
                        break;
                        case 'cfp_id':
                            $query->where('cfp_id', $term);
                        break;
                        case 'schedule_start_date':
                            $query->whereRaw('TO_CHAR("schedule_start_date", \'dd FMMonth yyyy\') = ?', [ $term ]);
                        break;
                    }
                }
            }
        }    
        return $query->get();
        /*return $this->cfpSchedule
        ->where('cfp_id', $attributes['cfp_id'])
        ->whereRaw('TO_CHAR("schedule_start_date", \'dd FMMonth yyyy\') = ?', [ $attributes['schedule_start_date']])
        ->get();*/
    }

    public function findBy($filter = array()){//$attributes){
        $query = $this->cfpSchedule->orderBy('created_at', 'DESC');
        if(is_array($filter)){
            foreach($filter as $ff => $term){
                if(trim($term) != ''){
                    switch ($ff) {
                        case 'record_flag_is_not':
                            $query->where('record_flag', '!=', $term);
                        break;
                        case 'schedule_id':
                            $query->where('internalid', '=', $term);
                        break;
                        case 'cfp_id':
                            $query->where('cfp_id', $term);
                        break;
                        case 'schedule_start_date':
                            $query->whereRaw('TO_CHAR("schedule_start_date", \'dd FMMonth yyyy\') = ?', [ $term ]);
                        break;
                    }
                }
            }
        }    
        return $query->first();
    }
}
