<?php namespace App\Repositories\Triangle;

use App\Models\Triangle;

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
use Illuminate\Support\MessageBag;
use Input;
use DB;
use Request;


class TriangleRepository extends RepositoryAbstract implements TriangleInterface, CrudableInterface {

    protected $perPage;
    protected $Triangle;
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
    public function __construct(Triangle $Triangle) {

        $config = Config::get('holiday');
        $this->perPage = $config['per_page'];
        $this->Triangle = $Triangle;

        $rules_n_attributeNames = $this->rules();
        self::$rules = $rules_n_attributeNames['rules'];
        self::$attributeNames = $rules_n_attributeNames['attributeNames'];
    }

    public function rules(){
        $_rules = array();
        $setAttributeNames = array();
        
        //$_rules['title'] = 'required';
        //$_rules['description'] = 'required';
        $_rules['triangle_layer_id'] = 'required';
        $_rules['step_1'] = 'required';

        if(Input::has('step_1') && Input::get('step_1') != '' ){
            $_rules['step_2'] = 'required';
            if(Input::get('step_1') == 'taxo_wallet_asset'){
                $_rules['step_3'] = 'required';
            }
        }

        if(Input::has('step_2') && Input::get('step_2') != '' && Input::get('step_1') == 'taxo_wallet_asset' ){
            $_rules['step_3'] = 'required';
        }

        //$setAttributeNames['title'] = trans('app.title');
        //$setAttributeNames['description'] = trans('app.description');
        $setAttributeNames['triangle_layer_id'] = trans('app.triangle_layer');
        $setAttributeNames['step_1'] = trans('app.step_1');
        $setAttributeNames['step_2'] = trans('app.step_2');
        $setAttributeNames['step_3'] = trans('app.step_3');

        return [
            'rules' => $_rules,
            'attributeNames' => $setAttributeNames
        ]; 

    }

    public function all() {
        return $this->Triangle->orderBy('created_by', 'DESC')->get();
    }

    public function paginate($page = 1, $limit = 10, $filter = array()) {

        $result = new \StdClass;
        $result->page = $page;
        $result->limit = $limit;
        $result->totalItems = 0;
        $result->items = array();

        /*$query = DB::select(DB::raw('
        select t.title, t.description, t.step_1, t.step_2, t.step_3, tx.title as taxo_wallet_asset from(
            select tl.title, tl.description, t.step_1, t.step_2, t.step_3,
             CASE
             WHEN t.step_2~E\'^\\d+$\' THEN
             CAST (t.step_2 AS INTEGER)
             ELSE
             0
             END as t_step_2
            from triangle t 
            left join triangle_layers tl on tl.id=t.triangle_layer_id
        ) t
        left join taxonomies tx on tx.id=t.t_step_2'));*/

        /* seharusnya pakai ini
        $query = DB::select(DB::raw('
        select t.title, t.description, t.step_1, t.step_2, t.step_3, tx.title as taxo_wallet_asset from(
            select tl.title, tl.description, t.step_1, t.step_2, t.step_3,
             CASE
             WHEN t.step_2~E\'^\\d+$\' THEN
             CAST (t.step_2 AS INTEGER)
             ELSE
             0
             END as t_step_2
            from triangle t 
            left join triangle_layers tl on tl.id=t.triangle_layer_id
        ) t
        left join taxonomies tx on tx.id=t.t_step_2'));*/

/*

        SELECT *, (anggaran_perbulan - coalesce(pemakaian_anggaran_bulan_ini,0)) AS sisa_anggaran_bulan_ini 
        FROM (
                SELECT 
                    expenses.*, 
                    (
                        SELECT sum(amount) FROM wallet_transactions 
                        WHERE detail_id = expenses.id 
                        AND transaction_date >= \''.$start_date.'\' AND transaction_date <= \''.$end_date.'\'  
                        AND record_flag != \'D\'
                        GROUP BY detail_id
                    ) AS pemakaian_anggaran_bulan_ini, 
                    t.title AS taxo_wallet_title,
                    t.slug AS taxo_wallet_slug,
                    exp2.id AS id,
                   \''.url('/').'\'||t.image::text as taxo_wallet_ico
                FROM expenses
                LEFT JOIN taxonomies AS t ON t.id = expenses.taxo_wallet_id 
                LEFT JOIN expenses exp2 ON exp2.taxo_wallet_id = expenses.taxo_wallet_id AND exp2.version = \''.$latest_module_version.'\' AND exp2.user_id = \''.$user_id.'\'
                WHERE expenses.user_id = '.$user_id.' AND expenses.version = '.$module_version.'  
            ) as whatever
    '));*/
        
        $query = $this->Triangle->select('triangle.*', 'uc.name as created_by_name', 'uu.name as updated_by_name')
        ->with('layer');
        
        //->join('taxonomies as t', 't.id', '=', 'triangle.step_2', 'left');
        //->with('layer');
        $query->orderBy('triangle.created_at', 'DESC');
        $query->join('users as uc', 'uc.id', '=', 'triangle.created_by', 'left');
        $query->join('users as uu', 'uu.id', '=', 'triangle.updated_by', 'left');

        //\DB::enableQueryLog();
        if(is_array($filter)){
            foreach($filter as $ff => $term){
                if(trim($term) != ''){
                    switch ($ff) {
                        case 'record_flag_is_not':
                            $query->where('triangle.record_flag', '!=', $term);
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

        $Triangles = $query->skip($limit * ($page - 1))->take($limit)->get();

        //dd(\DB::getQueryLog());
        //dd($categoryCodes);

        $result->totalItems = $this->totalTriangles($filter);
        $result->items = $Triangles->all();
/*
        foreach ($result->items as $key => $item) {
            $result->items[$key]->Triangles = $item;

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

    public function find($id) {
        return $this->Triangle
        ->select('triangle.*','uc.name as created_by_name', 'uu.name as updated_by_name')
        ->join('users as uc', 'uc.id', '=', 'triangle.created_by', 'left')
        ->join('users as uu', 'uu.id', '=', 'triangle.updated_by', 'left')
        ->find($id);
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

        //$this->Triangle->whereBetween('schedule_start_date', [$start_date, $end_date])->get()->toArray();
        //dd(\DB::getQueryLog());
        //$start_date = '2017-11-23 15:30:00';//tes
        //$end_date = '2017-11-23 14:59:59';//tes
        if($start_end_date != 'date_expired'){
            return $this->Triangle
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

        //$this->Triangle->whereBetween('schedule_start_date', [$start_date, $end_date])->get()->toArray();
        //dd(\DB::getQueryLog());
        //$start_date = '2017-11-23 15:30:00';//tes
        //$end_date = '2017-11-23 14:59:59';//tes
        if($start_end_date != 'date_expired'){
            return $this->Triangle
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

        //$this->Triangle->whereBetween('schedule_start_date', [$start_date, $end_date])->get()->toArray();
        //dd(\DB::getQueryLog());
        return $this->Triangle
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

        return $this->Triangle
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
            DB::beginTransaction(); 
            $user_id = Auth::user()->id;
            $step_1 = isset($attributes['step_1'])?$attributes['step_1']:'';
            $step_2 = isset($attributes['step_2'])?$attributes['step_2']:'';
            $step_3 = isset($attributes['step_3'])?$attributes['step_3']:'';
            
            $this->Triangle->create([
                'triangle_layer_id' => $attributes['triangle_layer_id'],
                'step_1' => $step_1,
                'step_2' => $step_2,
                'step_3' => $step_3,
                'created_by' => $user_id, 
                'created_at' => Carbon::now(), 
                'updated_by' => $user_id, 
                'updated_at' => Carbon::now()
            ]);

            DB::commit();
            return true;
        }
        throw new ValidationException('Triangle layer validation failed', $this->getErrors());
    }

    public function update($id, $attributes) {
        if($this->isValid($attributes)) {
            DB::beginTransaction();
            $user_id = Auth::user()->id;
            $step_1 = isset($attributes['step_1'])?$attributes['step_1']:'';
            $step_2 = isset($attributes['step_2'])?$attributes['step_2']:'';
            $step_3 = isset($attributes['step_3'])?$attributes['step_3']:'';
            
            $this->Triangle->where('id', $id)->update([
                'triangle_layer_id' => $attributes['triangle_layer_id'],
                'step_1' => $step_1,
                'step_2' => $step_2,
                'step_3' => $step_3,
                'updated_by' => $user_id, 
                'updated_at' => Carbon::now()
            ]);

            DB::commit();
            return true;
        }
        throw new ValidationException('Triangle layer validation failed', $this->getErrors());
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
            $schedule_type = $this->TriangleType->findByTypeName($attributes['schedule_type']); //dipindahkan ke dalam repository supaya terdeteksi saat validation
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

/*
    public function updateJUNK($id, $attributes) {

        $attributes['id'] = $id;
        //$attributes['schedule_start_date'] = '';
        //$attributes['schedule_end_date'] = '';

        //validasi convert strtotime
        if(strtotime($attributes['schedule_start_date']) && strtotime($attributes['schedule_end_date'])){
            $start_date = strtotime($attributes['schedule_start_date']);
            $end_date = strtotime($attributes['schedule_end_date']);

            $attributes['schedule_start_date'] = date("Y-m-d H:i:s", $start_date);
            $attributes['schedule_end_date'] = date("Y-m-d H:i:s", $end_date);
        }
 
        //$rules['id'] = 'required|exists:cfp_schedule,internalid';
        $rules['schedule_start_date'] = 'date';//|unique:mylife_trc_schedule_cfp,schedule_start_date';
        $rules['schedule_end_date'] = 'date';

        $attributeNames['schedule_start_date'] = trans('app.schedule_start_date');
        $attributeNames['schedule_end_date'] = trans('app.schedule_end_date');

        $validator = Valid::make($attributes, $rules);
        $validator->setAttributeNames($attributeNames);

        if ($validator->fails())
        { 
            throw new ValidationException('Schedule Cfp attribute validation failed', $validator->errors());
        }

        if($this->isValid($attributes)) {
            $t_attributes = array();

            if(!Auth::check())
            {
                $user_id = $attributes['client_id'];
            }else{
                $user_id = Auth::user()->id;
            }



            $this->Triangle = $this->find($id);


            $t_attributes = [
                'title' => $attributes['title'],
                'client_id' => $attributes['client_id'],
                'cfp_id' => $attributes['cfp_id'],
                'schedule_start_date' => $attributes['schedule_start_date'],
                'schedule_end_date' => $attributes['schedule_end_date'],
                'location' => $attributes['location'],
                'notes' => $attributes['notes'],
                'updated_by' => $user_id, 
                'updated_at' => Carbon::now(),
                'record_flag' => 'U'
            ];

            //dd($attributes);
            /*$t_attributes['title'] = $attributes['title'];
            $t_attributes['client_id'] = $attributes['client_id'];
            $t_attributes['cfp_id'] = $attributes['cfp_id'];
            $t_attributes['schedule_start_date'] = $attributes['schedule_start_date'];
            $t_attributes['schedule_end_date'] = $attributes['schedule_end_date'];
            $t_attributes['location'] = $attributes['location'];
            $t_attributes['notes'] = $attributes['notes'];
            $t_attributes['updated_by'] = $user;
            $t_attributes['updated_on'] = date("Y-m-d H:i:s");
            $t_attributes['record_flag'] = 'U';*/
            /*
            $t_attributes['testimonial'] = Str::slug($attributes['name'][getLang()], '_');
            foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
                $t_attributes[$locale] = [
                    'name' => $attributes['name'][$locale]
                ];
            }
            *//*
            $this->Triangle->fill($t_attributes)->save();
            return true;
        }
        throw new ValidationException('CFP Schedule attribute validation failed', $this->getErrors());
    }*/

    public function delete($id) {

        /*$rules['id'] = 'required|exists:cfp_schedule,internalid';

        $attributes['id'] = $id;

        $validator = Valid::make($attributes, $rules);

        if ($validator->fails())
        { 
            throw new ValidationException('CFP Schedule attribute validation failed', $validator->errors());
        }*/

        $Triangle = $this->Triangle->find($id);
        $Triangle->delete();
    }

    public function api_delete($id) {
        $Triangle = $this->Triangle->findOrFail($id);
        if($Triangle){
            $Triangle->delete();
            return true;
        }
        return false;
    }

    protected function totalTrianglesJUNK($all = false) {
        return $this->Triangle->count();
    }

    protected function totalTriangles($filter = array()) {
        $query = $this->Triangle
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
        $query = $this->Triangle->orderBy('created_at', 'DESC');
        if(is_array($filter)){
            foreach($filter as $ff => $term){
                if(trim($term) != ''){
                    switch ($ff) {
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
        /*return $this->Triangle
        ->where('cfp_id', $attributes['cfp_id'])
        ->whereRaw('TO_CHAR("schedule_start_date", \'dd FMMonth yyyy\') = ?', [ $attributes['schedule_start_date']])
        ->get();*/
    }
}
