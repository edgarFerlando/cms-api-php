<?php namespace App\Repositories\CfpRating;

use App\Models\CfpRating;

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


class CfpRatingRepository extends RepositoryAbstract implements CfpRatingInterface, CrudableInterface {

    protected $perPage;
    protected $triangleLayer;
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
    public function __construct(CfpRating $cfpRating) {

        // $config = Config::get('holiday');
        // $this->perPage = $config['per_page'];
        $this->cfpRating = $cfpRating;

        $rules_n_attributeNames = $this->rules();
        self::$rules = $rules_n_attributeNames['rules'];
        self::$attributeNames = $rules_n_attributeNames['attributeNames'];
    }

    public function rules(){
        $_rules = array();
        $setAttributeNames = array();
        
        $_rules["cfp_id"] = "required";
        $_rules["rating_stars"] = "required";
        $_rules["client_id"] = "required";
        $_rules["plan_analysis_version"] = "required";

        $setAttributeNames['cfp_id'] = "cfp id";
        //$setAttributeNames['cfp_id'] = trans('app.title');
        //$setAttributeNames['stack_number'] = trans('app.stack_number');
        
        return [
            'rules' => $_rules,
            'attributeNames' => $setAttributeNames
        ]; 

    }

    public function all() {
        return $this->triangleLayer->orderBy('created_by', 'DESC')->get();
    }

    public function paginate($page = 1, $limit = 10, $filter = array()) {

        $result = new \StdClass;
        $result->page = $page;
        $result->limit = $limit;
        $result->totalItems = 0;
        $result->items = array();

        
        $query = $this->triangleLayer->select('triangle_layers.*', 'uc.name as created_by_name', 'uu.name as updated_by_name');
        
        //->join('taxonomies as t', 't.id', '=', 'CfpRating.step_2', 'left');
        //->with('layer');
        $query->orderBy('triangle_layers.created_at', 'DESC');
        $query->join('users as uc', 'uc.id', '=', 'triangle_layers.created_by', 'left');
        $query->join('users as uu', 'uu.id', '=', 'triangle_layers.updated_by', 'left');

        //\DB::enableQueryLog();
        if(is_array($filter)){
            foreach($filter as $ff => $term){
                if(trim($term) != ''){
                    switch ($ff) {
                        case 'record_flag_is_not':
                            $query->where('CfpRating.record_flag', '!=', $term);
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

        $CfpRatings = $query->skip($limit * ($page - 1))->take($limit)->get();

        //dd(\DB::getQueryLog());
        //dd($categoryCodes);

        $result->totalItems = $this->totalCfpRatings($filter);
        $result->items = $CfpRatings->all();


        return $result;
    }

    public function find($id) {
        return $this->triangleLayer
        ->select('triangle_layers.*','uc.name as created_by_name', 'uu.name as updated_by_name')
        ->join('users as uc', 'uc.id', '=', 'triangle_layers.created_by', 'left')
        ->join('users as uu', 'uu.id', '=', 'triangle_layers.updated_by', 'left')
        ->find($id);
    }

    public function findAverage($id){
        //select avg(rating_stars) from cfp_ratings where cfp_id=313
        $avg = $this->cfpRating
        ->where('cfp_id', $id)
        ->avg('rating_stars');
        return intval($avg);
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

        //$this->triangleLayer->whereBetween('schedule_start_date', [$start_date, $end_date])->get()->toArray();
        //dd(\DB::getQueryLog());
        //$start_date = '2017-11-23 15:30:00';//tes
        //$end_date = '2017-11-23 14:59:59';//tes
        if($start_end_date != 'date_expired'){
            return $this->triangleLayer
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

        //$this->triangleLayer->whereBetween('schedule_start_date', [$start_date, $end_date])->get()->toArray();
        //dd(\DB::getQueryLog());
        //$start_date = '2017-11-23 15:30:00';//tes
        //$end_date = '2017-11-23 14:59:59';//tes
        if($start_end_date != 'date_expired'){
            return $this->triangleLayer
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

        return $this->triangleLayer
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

        return $this->triangleLayer
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
            $client_id = $attributes['client_id'];
            $this->cfpRating->create([
                'cfp_id' => $attributes['cfp_id'],
                'rating_stars' => $attributes['rating_stars'],
                'comments' => $attributes['comments'],
                'client_id' => $client_id,
                'plan_analysis_version' => $attributes['plan_analysis_version'],
                'created_by' => $client_id, 
                'created_at' => Carbon::now(), 
                'updated_by' => $client_id, 
                'updated_at' => Carbon::now(),
                'record_flag' => 'N'
            ]);

            DB::commit();
            return true;
        }
        throw new ValidationException('CfpRating layer validation failed', $this->getErrors());
    }

    public function update($id, $attributes) {
        if($this->isValid($attributes)) {
            DB::beginTransaction();
            $user_id = Auth::user()->id;
            
            $this->triangleLayer->where('id', $id)->update([
                'title' => $attributes['title'],
                'description' => $attributes['description'],
                'stack_number' => $attributes['stack_number'],
                'updated_by' => $user_id, 
                'updated_at' => Carbon::now()
            ]);

            DB::commit();
            return true;
        }
        throw new ValidationException('CfpRating layer validation failed', $this->getErrors());
    }


    public function delete($id) {

        /*$rules['id'] = 'required|exists:cfp_schedule,internalid';

        $attributes['id'] = $id;

        $validator = Valid::make($attributes, $rules);

        if ($validator->fails())
        { 
            throw new ValidationException('CFP Schedule attribute validation failed', $validator->errors());
        }*/

        $CfpRating = $this->triangleLayer->find($id);
        $CfpRating->delete();
    }

    public function api_delete($id) {
        $CfpRating = $this->triangleLayer->findOrFail($id);
        if($CfpRating){
            $CfpRating->delete();
            return true;
        }
        return false;
    }

    protected function totalCfpRatingsJUNK($all = false) {
        return $this->triangleLayer->count();
    }

    protected function totalCfpRatings($filter = array()) {
        $query = $this->triangleLayer
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
        $query = $this->triangleLayer->orderBy('created_at', 'DESC');
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
        /*return $this->triangleLayer
        ->where('cfp_id', $attributes['cfp_id'])
        ->whereRaw('TO_CHAR("schedule_start_date", \'dd FMMonth yyyy\') = ?', [ $attributes['schedule_start_date']])
        ->get();*/
    }
}
