<?php namespace App\Repositories\PlanAnalysis;

use App\Models\PlanAnalysis;
use App\Models\ActiveVersion;
use App\Models\PlanAnalysisPlan;
use App\Models\PlanAnalysisActivated;
use App\Models\ActiveVersionDetail;

use Auth;
use Config;
use Response;
use Illuminate\Support\Str;
use App\Repositories\RepositoryAbstract;
use App\Repositories\CrudableInterface as CrudableInterface;
use App\Repositories\RepositoryInterface as RepositoryInterface;
use App\Exceptions\Validation\ValidationException;
use App\Repositories\AbstractValidator as Validator;
//use LaravelLocalization;
use Notification;
use App\User;

use Validator as Valid;
use Carbon\Carbon;
use App\Repositories\PlanAnalysis\PlanAnalysisInterface;
use Input;
use DB;

use App\Models\PlanA;
use App\Repositories\PlanA\PlanARepository;
use App\Models\PlanB;
use App\Repositories\PlanB\PlanBRepository;
use App\Models\ClientActionPlan;
use App\Models\ClientActionPlanDetail;
use Log;
use App\Repositories\User\UserRepository;

class PlanAnalysisRepository extends RepositoryAbstract implements PlanAnalysisInterface, CrudableInterface {

    protected $perPage;
    //protected $income;
    /**
     * Rules
     *
     * @var array
     */
    protected static $rules;
    protected static $attributeNames;
    protected $planA;
    protected $planB;

    /**
     * @param ProductAttribute $productAttribute
     */
    public function __construct() {

        //$config = Config::get('holiday');
        //$this->perPage = $config['per_page'];
        //$this->income = $income;
        //$this->taxonomy = new TaxonomyRepository(new Taxonomy);

        $rules_n_attributeNames = $this->rules();
        self::$rules = $rules_n_attributeNames['rules'];
        self::$attributeNames = $rules_n_attributeNames['attributeNames'];
        $this->planA = new PlanARepository(new PlanA);
        $this->planB = new PlanBRepository(new PlanB);
        $this->user = new UserRepository(new User);
    }

    public function rules(){
        $_rules = array();
        $setAttributeNames = array();
        $_rules['user_id'] = 'required';

        if((Input::has('plans') && count(Input::get('plans')) == 0) || !Input::has('plans')){
            $_rules['plans'] = 'required';
        }

        return [
            'rules' => $_rules,
            'attributeNames' => $setAttributeNames
        ]; 

    }

    public function all() {
        return $this->wallet->orderBy('created_by', 'DESC')->get();
    }

    public function paginate($page = 1, $limit = 10, $all = false) {

        $result = new \StdClass;
        $result->page = $page;
        $result->limit = $limit;
        $result->totalItems = 0;
        $result->items = array();

        $query = $this->wallet->select('wallet.*', 'uc.name as created_by_name', 'uu.name as updated_by_name');
        $query->orderBy('created_by', 'DESC');
        $query->with(['category', 'transaction_type', 'category_type']);
        //dd($query);
        if(!$all) {
            $query->where('is_published', 1);
        }

        //$query->join('users as c', 'c.id', '=', 'cfp_clients.client_id', 'left');
        //$query->join('users as cfp', 'cfp.id', '=', 'cfp_clients.cfp_id', 'left');
        $query->join('users as uc', 'uc.id', '=', 'wallet.created_by', 'left');
        $query->join('users as uu', 'uu.id', '=', 'wallet.updated_by', 'left');

        //\DB::enableQueryLog();

        $transactions = $query->skip($limit * ($page - 1))->take($limit)->get();

        //dd(\DB::getQueryLog());
        //dd($categoryCodes);

        $result->totalItems = $this->totalTransactions($all);
        $result->items = $transactions->all();
        /*dd($result);
        foreach ($result->items as $key => $item) {
            $result->items[$key]->Wallets = $item;

            /*$customer_user = User::find($item->client_id);
            $cfp_user = User::find($item->cfp_id);
            $userCreate = User::find($item->created_by);

            $result->items[$key]->clientName = $customer_user->name;
            $result->items[$key]->cfpName = $cfp_user->name;
            $result->items[$key]->userCreate = $userCreate->name;

            if(!is_null($item->updated_by))
            {
                $userUpdate = User::find($item->updated_by);
                $result->items[$key]->userUpdate = $userUpdate->name;
            }*/
      /*  };*/
        
        //dd($result);

        return $result;
    }

    public function find($id) {
        return $this->wallet->find($id);
    }

    public function findCfpByClientEmail($client_email) {
        return $this->wallet->whereHas('cfp', function($q) use ($client_email){
            $q->whereRaw('LOWER(email) = ?', [ strtolower($client_email)]);
        })->get();
    }

    public function showAllDraftJUNK($attributes){
        $user_id = $attributes['user_id'];
        $version = !isset($attributes['version'])?'last':$attributes['version'];
        $modules_shown = !isset($attributes['modules'])?[]:$attributes['modules'];
        //dd($modules_shown);
        switch ($version) {
            case 'last' :
                    $activeVersion = ActiveVersion::where('user_id', $user_id)->where('key', 'planAnalysis')->first();
                    $module_version = is_null($activeVersion)?'':$activeVersion->version;
                
                break;
            default:
                    $module_version = $version;
                break;
        }

        if($module_version === '')
            return [];

        $all_with = [
            'a_plan_all' => function($query) use ($module_version) {
                $query->where('parent_id', 0);
            },
            'b_plan_all' => function($query) use ($module_version) {
                $query->where('parent_id', 0);
            }
        ];

        if(!empty($modules_shown)){
            foreach (array_diff(array_keys($all_with), $modules_shown) as $module_shown) {
                unset($all_with[$module_shown]);
            }
        }
        
        $res = [];
        $data_raw = User::select('users.id as user_id')->with($all_with)->where('id', $user_id)->first();
        //dd($data_raw->toArray());
        //dd($data_raw['a_plan_all']);
        if(!is_null($data_raw)){
            //$res['plan_analysis'] = $data_raw['plan_analysis'];
            if(count($data_raw['a_plan_all']) > 0)
                foreach ($data_raw['a_plan_all'] as $a_plan) {
                    $a_plans = $this->planA->showByVersion([
                        'user_id' => $user_id,
                        'plan_id' => $a_plan->id,
                        //'modules' => ['plan_a']
                    ]);
                    if(!is_null($a_plans))
                        $res['a_plans'][] = $a_plans;
                }
            if(count($data_raw['b_plan_all']) > 0)
                foreach ($data_raw['b_plan_all'] as $b_plan) {
                    $b_plans = $this->planB->showByVersion([
                        'user_id' => $user_id,
                        'plan_id' => $b_plan->id,
                        //'modules' => ['plan_b']
                    ]);
                    if(!is_null($b_plans))
                        $res['b_plans'][] = $b_plans;
                }

        }else{
            return [];
        }

        return $res;
    }

    public function showByVersion($attributes){
        $user_id = $attributes['user_id'];
        $version = !isset($attributes['version'])?'last':$attributes['version'];
        $version_cashflow_analysis = !isset($attributes['version_cashflow_analysis'])?'last':$attributes['version_cashflow_analysis'];
        
        /*if(!isset($attributes['version_cashflow_analysis'])){
            //get last version of cashflow analysis pada sesuai tanggal created plan analysisnya
            $version_cashflow_analysis = ActiveVersionDetail::where('active_version_key', 'planAnalysis')
            ->where('user_id', $user_id)
            ->where('status', 'approved')->max('version');
        }else{
            $version_cashflow_analysis = $attributes['version_cashflow_analysis'];
        }*/
        
        $modules_shown = !isset($attributes['modules'])?[]:$attributes['modules'];
        $transaction_date_until = !isset($attributes['transaction_date_until'])?Carbon::today()->format('Y-m-d'):Carbon::parse($attributes['transaction_date_until'])->format('Y-m-d'); 
        $status = !isset($attributes['status'])?'approved':$attributes['status']; 
        //dd($modules_shown);
        switch ($version) {
            case 'last' :
                    //$activeVersion = ActiveVersion::where('user_id', $user_id)->where('key', 'planAnalysis')->first();
                    //$module_version = is_null($activeVersion)?'':$activeVersion->version;
                    $transaction_date_until_Ym = Carbon::parse($transaction_date_until)->format('Y-m');
                    $dmY_api = Carbon::createFromFormat('Y-m-d' , $transaction_date_until_Ym.'-01');
                    $start_date = $dmY_api->format('Y-m-d').' 00:00:00';//awal bulan dari api
                    $end_date_check_version = Carbon::today()->format('Y-m-d') == $transaction_date_until?Carbon::now()->format('Y-m-d H:i:s'):$transaction_date_until.' 23:59:59';
                    //DB::enableQueryLog();

                    if(isset($attributes['status']) && $attributes['status'] == 'all'){//draft, approved, rejected
                        $maxApprovedActiveVersion = ActiveVersionDetail::where('created_at', '>=', $start_date)
                        ->where('created_at', '<=', $end_date_check_version)
                        ->where('active_version_key', 'planAnalysis')
                        ->where('user_id', $user_id)->max('version');
                    }elseif(isset($attributes['status']) && $attributes['status'] == 'not_rejected'){//draft, approved
                        $maxApprovedActiveVersion = ActiveVersionDetail::where('created_at', '>=', $start_date)
                        ->where('created_at', '<=', $end_date_check_version)
                        ->where('active_version_key', 'planAnalysis')
                        ->where('status', '<>', 'rejected')
                        ->where('user_id', $user_id)->max('version');
                    }else{
                        $maxApprovedActiveVersion = ActiveVersionDetail::where('created_at', '>=', $start_date)
                        ->where('created_at', '<=', $end_date_check_version)
                        ->where('active_version_key', 'planAnalysis')
                        ->where('status', $status)
                        ->where('user_id', $user_id)->max('version');
                    }
                    
                    //dd(DB::getQueryLog());
                    $module_version = is_null($maxApprovedActiveVersion)?'':$maxApprovedActiveVersion;
                    
                    //version_cashflow_analysis
                    //$activeVersion_ca = ActiveVersion::where('user_id', $user_id)->where('key', 'financialCheckup_cashflowAnalysis')->first();
                    //$module_version_ca = is_null($activeVersion_ca)?'':$activeVersion_ca->version;
                    $maxApprovedActiveVersion_ca = ActiveVersionDetail::where('created_at', '>=', $start_date)
                    ->where('created_at', '<=', $end_date_check_version)
                    ->where('active_version_key', 'financialCheckup_cashflowAnalysis')
                    ->where('user_id', $user_id)
                    ->where('status', 'approved')->max('version');

                    $module_version_ca = is_null($maxApprovedActiveVersion_ca)?'':$maxApprovedActiveVersion_ca;
                
                break;
            default:
                    $module_version = $version;

                    //get kapan plan analysis tersebut dibuat
                    $module_version_created_at = ActiveVersionDetail::where('active_version_key', 'planAnalysis')
                    ->where('user_id', $user_id)
                    ->where('version', $module_version)->pluck('created_at');//karena hanya untuk nge get created_at nya maka sepertinya ga perlu status
                    //->where('status', $status)
                    
                    
                    //gunakan periode sesuai dengan tanggal plan analysis created untuk mencari max version cashflow analysis pada periode tersebut
                    if(!is_null($module_version_created_at)){
                        $start_date = Carbon::parse($module_version_created_at)->startOfMonth()->format('Y-m-d 00:00:00');
                        $end_date_check_version = $module_version_created_at;
                        //dd('created_at >= '.$start_date.' , created_at <= '.$end_date_check_version);
                        $maxApprovedActiveVersion_ca = ActiveVersionDetail::where('created_at', '>=', $start_date)
                        ->where('created_at', '<=', $end_date_check_version)
                        ->where('active_version_key', 'financialCheckup_cashflowAnalysis')
                        ->where('user_id', $user_id)
                        ->where('status', 'approved')->max('version');
                        //dd($maxApprovedActiveVersion_ca);
                        $module_version_ca = is_null($maxApprovedActiveVersion_ca)?'':$maxApprovedActiveVersion_ca;
                    }else{
                        $module_version_ca = '';//sebenarnya ga mungkin ga ada isinya
                    }
                    //$module_version_ca = $version_cashflow_analysis;
                break;
        }

        if($module_version === '')
            //return [];jangan return ini , karena plananalysis dalam keadaan kosong
            $module_version = 0;//jangan ubah line 223

        if($module_version_ca === '')
            $module_version_ca = 0;
       
        $all_with = [
            'plan_analysis' => function($query) use ($module_version) {
                //$query->where('version', $module_version)->with('client_action_plan.details'); //client action plan hanya akan muncul jika plan analysis pada version tersebut tersedia
                
                $query->where('version', $module_version)->with([ 'client_action_plan' => function($q){
                    $q->with(['details' => function($q2){
                        $q2->select('client_action_plan_details.*', 't.title as taxo_action_plan_title', 't2.title as taxo_action_plan_parent_title', 't2.id as taxo_action_plan_parent_id')
                        ->join('taxonomies as t', 't.id', '=', 'taxo_action_plan_id', 'left')
                        ->join('taxonomies as t2', 't2.id', '=', 't.parent_id', 'left');
                    }]);
                }])->whereHas('active_version_details', function($qq1) use ($module_version){
                    $qq1->where('status', '<>', 'rejected')
                    ->where('active_version_key', 'planAnalysis')
                    ->where('version', $module_version);
                });
                
                //->with(['client_action_plan' => function($q) use ($module_version){
                //    $q->where('version', $module_version);
                //}]);
            },
            'a_plans' => function($query) {
                //get yang version bukan nol sekaligus bukan rejected
                $query->where('parent_id', 0)
                ->whereHas('active_version_plan_details', function($q2){
                    $q2->where(function($q3){
                        $q3->where(function($q4){
                            $q4->where('version', '<>', 0)
                                ->where('status', 'rejected');
                        })->orWhere(function($q5){
                            $q5->where(function($q6){
                                $q6->where('version', '<>', 0)
                                    ->orWhereNotNull('version');
                            })->where('status', '<>', 'rejected');
                        });
                    });
                })
                ->where(function($qq1){//tidak menampilkan yang deleted
                    $qq1->where('record_flag', '!=', 'D')
                    ->orWhere('record_flag', NULL);
                });
            },
            'b_plans' => function($query) {
                $query->where('parent_id', 0)
                ->whereHas('active_version_plan_details', function($q2){
                    $q2->where(function($q3){
                        $q3->where(function($q4){
                            $q4->where('version', '<>', 0)
                                ->where('status', 'rejected');
                        })->orWhere(function($q5){
                            $q5->where(function($q6){
                                $q6->where('version', '<>', 0)
                                    ->orWhereNotNull('version');
                            })->where('status', '<>', 'rejected');
                        });
                    });
                })
                ->where(function($qq1){//tidak menampilkan yang deleted
                    $qq1->where('record_flag', '!=', 'D')
                    ->orWhere('record_flag', NULL);
                });
            },
            'plan_balances' => function($query) use ($module_version_ca) {
                $query->select('plan_balances.user_id','plan_balances.version', 'plan_balances.name', 'plan_balances.balance', 'plan_balances.balance_datetime' )
                ->where('version', $module_version_ca);
            }
        ];
        //dd(array_diff(array_keys($all_with), $modules_shown));
        if(!empty($modules_shown)){
            foreach (array_diff(array_keys($all_with), $modules_shown) as $module_shown) {
                unset($all_with[$module_shown]);
            }
        }
       
        $res = [];
        //DB::enableQueryLog();
        $data_raw = User::select('users.id as user_id')->with($all_with)->where('id', $user_id)->first();
        //dd(DB::getQueryLog());
        //dd($data_raw['plan_balances']->toArray());
        //dd($data_raw['a_plans']->toArray());
        if(!is_null($data_raw)){ 
            if(in_array('plan_analysis', array_keys($all_with)))
                $res['plan_analysis'] = $data_raw['plan_analysis'];
            
            if(in_array('plan_balances', array_keys($all_with)))
                $res['plan_balances'] = $data_raw['plan_balances'];
            if(count($data_raw['a_plans']) > 0)
                foreach ($data_raw['a_plans'] as $a_plan) {
                    $a_plans = $this->planA->showByVersion([
                        'user_id' => $user_id,
                        'plan_id' => $a_plan->id,
                        'status' => 'not_rejected'
                        //'status' =>$a_plan->status
                        //'modules' => ['plan_a']//kalau tes buka aja biar ga banyak
                    ]);
                    if(!empty($a_plans))
                        $res['a_plans'][] = $a_plans;
                }
            if(count($data_raw['b_plans']) > 0)
                foreach ($data_raw['b_plans'] as $b_plan) {
                    $b_plans = $this->planB->showByVersion([
                        'user_id' => $user_id,
                        'plan_id' => $b_plan->id,
                        'status' => 'not_rejected'
                        //'status' =>$a_plan->status
                        //'modules' => ['plan_b']//kalau tes buka aja biar ga banyak
                    ]);
                    if(!empty($b_plans))
                        $res['b_plans'][] = $b_plans;
                }

        }else{
            return null;
        }

        return $res;
    }

    public function create($attributes) {

        if($this->isValid($attributes)) { 
            $activeVersionKey = 'planAnalysis';
            $user_id = isset($attributes['user_id'])?$attributes['user_id']:Auth::user()->id;
            $user_dtl = $this->user->findWithMetas($user_id);
            
            if(is_null($user_dtl)){
                throw new ValidationException('Plan Analysis validation failed', [
                       'user' => 'not found',
                ]);
            }
            $user_code = isset($user_dtl['user_code'])?$user_dtl['user_code']:'';

            DB::beginTransaction();
            $activeVersion = ActiveVersion::where('user_id', $user_id)->where('key', $activeVersionKey)->first();
            $module_version = is_null($activeVersion)?0:$activeVersion->version+1; 
            $note_calc = '';
            $plan_analysis = PlanAnalysis::create([
                'user_id' => $user_id,
                'version' => $module_version,
                'note' => $attributes['note'],
                'internal_note_calc' => $note_calc,
                'record_flag' => 'N',
                'created_by' => $user_id,
                'created_at' => Carbon::now(),
                'updated_by' => $user_id,
                'updated_at' => Carbon::now()
            ]);
            $plan_analysis_id = $plan_analysis->id;
            
            if(isset($attributes['plans']) && count($attributes['plans']) > 0){
                $attributes_safe = [];
                $planAnalysisActivated_safe = []; //dd($attributes['plans']);
                foreach ($attributes['plans'] as $idx_plan => $plan) {
                    switch ($plan['plan_type']) {
                        case 'a':
                            $plan_info = $this->planA->getOrigPlan($user_id, $plan['plan_id']);
                            $plan_id = $plan_info['plan_id'];
                            break;
                        case 'b':
                            $plan_info = $this->planB->getOrigPlan($user_id, $plan['plan_id']); //dd($plan_info);
                            $plan_id = $plan_info['plan_id'];
                            break;
                    }
                    //dd($plan_id);
                    //$plan_id = $this->getOrigPlanID($plan['plan_id']);//update plan id
                            //if($plan_id == 'not_found')
                            //    throw new ValidationException('Plan validation failed', 'plan_not_found');

                    /*$attributes_safe = [
                        'user_id' => $user_id,
                        'version' => $module_version,
                        'plan_analysis_id' => $plan_analysis_id,
                        'plan_id' => $plan['plan_id'],
                        'plan_type' => $plan['plan_type'],
                        'status' => 0,//not active
                        'record_flag' => 'N',
                        'total' => 0,//set nanti
                        'created_by' => $user_id,
                        'created_at' => Carbon::now(),
                        'updated_by' => $user_id,
                        'updated_at' => Carbon::now()
                    ];
                    $planAnalysisPlan = PlanAnalysisPlan::create($attributes_safe);*/
                    if(isset($plan['status'])){
                        if($plan['status'] === 1 && ( isset($plan['asset_repayment_id']) && $plan['asset_repayment_id'] !== 0 && $plan['asset_repayment_id'] !== '' )){//simpann ke table activated jika hanya status 1
                            //seharusnya cek juga asset_repayment_id nya valid atau tidak
                            

                            $planAnalysisActivated_safe = [
                                'user_id' => $user_id,
                                'version' => $module_version,
                                'plan_type' => $plan['plan_type'],
                                'plan_id' => $plan_id,//$plan['plan_id'],//jika bermasalah kemungkinan karena ini bukan parent_id, maka harus pakai gerOriPlanID
                                'asset_repayment_id' => $plan['asset_repayment_id'],
                                'created_by' => $user_id,
                                'created_at' => Carbon::now(),
                                'updated_by' => $user_id,
                                'updated_at' => Carbon::now()
                            ];
                            PlanAnalysisActivated::create($planAnalysisActivated_safe);
                            //update status plan parent id
                            //$planAnalysisPlan->status = 1;//activated
                            //$planAnalysisPlan->save();
                            switch ($plan['plan_type']) {
                                case 'a':
                                    $this->planA->activated($user_id, $plan['plan_id']);
                                    //dd(PlanA::where('id', 125)->get()->toArray());
                                    break;
                                
                                case 'b':
                                    $this->planB->activated($user_id, $plan['plan_id']);
                                    break;
                            }
                        }else{//plan akan di nonaktifkan, tidak masuk lagi ke table activated
                            //tanggal deactivated nya sama dengan saaat versi terakhir nya masuk
                            switch ($plan['plan_type']) {
                                case 'a':
                                    $this->planA->deactivated($user_id, $plan['plan_id']);//mungkin perlu update status keseluruhan yg berhubungan dengan plan_id dan parent_id nya
                                    break;
                                
                                case 'b':
                                    $this->planB->deactivated($user_id, $plan['plan_id']);
                                    break;
                            }
                        }

                        //masi kurang kasi flag ke asset_repayment_id yang dilinking
                    }
                }
            }

            if(isset($attributes['action_plans']) && count($attributes['action_plans']) > 0){
                $action_plans_safe = [
                    'plan_analysis_id' => $plan_analysis_id,
                    'version' => $module_version,
                    'user_id' => $user_id,
                    'created_by' => $user_id,
                    'created_at' => Carbon::now(),
                    'updated_by' => $user_id,
                    'updated_at' => Carbon::now()
                ]; 
                $action_plan = ClientActionPlan::create($action_plans_safe);
                $action_plan_id = $action_plan->id;
                $action_plans_dtls_safe = [];
                foreach ($attributes['action_plans'] as $idx_action_plan_dtl => $action_plan_dtl) {
                    $action_plans_dtls_safe[] = [
                        'client_action_plan_id' => $action_plan_id,
                        'taxo_action_plan_id' => $action_plan_dtl['taxo_action_plan_id'],
                        'timeline' => $action_plan_dtl['timeline'],
                        'note' => $action_plan_dtl['note'],
                        'status' => 'draft',
                        'created_by' => $user_id,
                        'created_at' => Carbon::now(),
                        'updated_by' => $user_id,
                        'updated_at' => Carbon::now()
                    ];
                } //dd($action_plans_dtls_safe);
                ClientActionPlanDetail::insert($action_plans_dtls_safe);
            }

            $this->updateActiveVersion($user_id, $activeVersionKey, $module_version);//update version

            //save active version details
            $email_web_path = md5($user_dtl['email'].'|planAnalysis|'.$plan_analysis_id);
            $activeVersionDetail = activeVersionDetail::create([
                'user_id' => $user_id, 
                'version' => $module_version,
                'status' => 'draft',
                'active_version_key' => 'planAnalysis',
                'email_web_path' => $email_web_path,
                'created_by' => $user_id, 
                'created_at' => Carbon::now(), 
                'updated_by' => $user_id, 
                'updated_at' => Carbon::now()
            ]);
                
            $activeVersionDetailId = $activeVersionDetail->id;
          
            //build html
            $update_data = [];
            if(sendEmailWithTemplate([
                'email_template_module_id' => 5,//plan analysis
                'to' => $user_dtl['email'],
                'replace_vars' => [
                    '{client_code}' => $user_code,
                    '{client_name}' => $user_dtl['name'],
                    '{client_email}' => $user_dtl['email'],
                    '{client_phone}' => $user_dtl['phone'],
                    '{email_web_path}' => '<a href="'.url('email/plan-analysis/'.$email_web_path).'">'.trans('app.link_label_email_plan_analysis').'</a>'
                ]
            ])){
                $update_data += [
                    'is_email_sent' => 1
                ];
                
            }
            if(count($update_data))
                activeVersionDetail::where([ 'id' => $activeVersionDetailId ])->update($update_data);
            DB::commit();
            return true;
        }
        throw new ValidationException('Plan Analysis validation failed', $this->getErrors());
    }

    public function createJUNKJUNK($attributes) {

        if($this->isValid($attributes)) { 
            $activeVersionKey = 'planAnalysis';
            $user_id = isset($attributes['user_id'])?$attributes['user_id']:Auth::user()->id;//$attributes['user_id'] untuk API
            DB::beginTransaction();
            $activeVersion = ActiveVersion::where('user_id', $user_id)->where('key', $activeVersionKey)->first();
            /*$modules = [ 
                'income' => [
                    'post_name' => 'incomes',
                    'model_name' => 'Income'
                ], 
                'expense' => [ 
                    'post_name' => 'expenses',
                    'model_name' => 'Expense'
                ], 
                'debt_repayment' => [
                    'post_name' => 'debt_repayments',
                    'model_name' => 'DebtRepayment'
                ], 
                'asset_repayment' => [
                    'post_name' => 'asset_repayments',
                    'model_name' => 'AssetRepayment'
                ],
                'insurance' => [
                    'post_name' => 'insurances',
                    'model_name' => 'Insurance'
                ]
            ];
*/
            $module_version = is_null($activeVersion)?0:$activeVersion->version+1; //dd($module_version);
  /*          $inflasi_pertahun = config_db_cached('settings::rate_inflation');
            $selisih_umur = intval($attributes['umur_pensiun']) - intval($attributes['umur']);
            $selisih_umur_dalam_bulan = $selisih_umur*12;

            $deposito_rate = config_db_cached('settings::rate_deposit');
            $pendapatan_pensiun = floatval($attributes['kebutuhan_dana'])*(($deposito_rate/100)/12);

            $fv_kebutuhan_dana_raw = calc_inf_fv('needinv', $attributes['kebutuhan_dana'], $inflasi_pertahun, $selisih_umur, false);
            //dd($fv_kebutuhan_dana_raw);
            $fv_kebutuhan_dana = $fv_kebutuhan_dana_raw['needinv']['fv_inf_needinv'];
            //saving income simulation
            $inv_rate_options = [
                0 => [
                    'bgcolor' => '#EED1E9',
                    'product' => 'Deposito',
                    'rates' => [
                        5, 6,7,8
                    ]
                ],
                1 => [
                    'bgcolor' => '#D1E1EE',
                    'product' => 'Government Bond',
                    'rates' => [
                        9
                    ]
                ],
                2 => [
                    'bgcolor' => '#D9EFD2',
                    'product' => 'Corporate Bond',
                    'rates' => [
                        10,11,12
                    ]
                ],
                3 => [
                    'bgcolor' => '#F4F2D6',
                    'product' => 'Money Market',
                    'rates' => [
                        13
                    ]
                ],
                4 => [
                    'bgcolor' => '#F2D4D8',
                    'product' => 'Money Market',
                    'rates' => [
                        14
                    ]
                ],
                5 => [
                    'bgcolor' => '#DFD1EE',
                    'product' => 'Money Market',
                    'rates' => [
                        15,16,17,18
                    ]
                ]
            ];
            $inv_rate_options = income_simulation_rate_options($inv_rate_options, [
                'lama_bulan_investasi' => $selisih_umur_dalam_bulan,
                'needinv' => $fv_kebutuhan_dana_raw['needinv']
            ]);

            $attributes_safe = [];
            foreach ($inv_rate_options as $idx => $inv_rate_option) {
                
                //foreach ($attributes[$attributes_arr['post_name']] as $idx => $attribute_arr) {
                    //$attributes_safe[$idx] = $attribute_arr;
                    $inv_slug = slugify($inv_rate_option['product'], '_').'_'.$inv_rate_option['rate'];
                    $attributes_safe[$idx] = [
                        'user_id' => $user_id,
                        'bunga_investasi_pertahun' => $inv_rate_option['rate'], 
                        'cicilan_perbulan' => $inv_rate_option['details']['pv_inv_'.$inv_slug],
                        'produk' => $inv_rate_option['product'],
                        'total_investasi' => $inv_rate_option['details']['fv_inv_'.$inv_slug],
                        'version' => $module_version,
                        'created_by' => $user_id,
                        'created_at' => Carbon::now(),
                        'updated_by' => $user_id,
                        'updated_at' => Carbon::now()
                    ];
                //}
                //if($attributes_arr['post_name'] == 'insurances')
                   // dd($attributes_safe);
                //$model_name = '\\App\\Models\\'.$attributes_arr['model_name']; 
                //$model = new $model_name;
                //dd($model::where('user_id', $user_id)->get()->toArray());

                
            }
            IncomeSimulation::insert($attributes_safe);
            //dd($module_version);
            //get income simulations ids
            $rate_options_raw = IncomeSimulation::where('user_id', $user_id)->where('version', $module_version)->get();
            $rate_options = [];
            foreach ($rate_options_raw as $rate_option_raw) {
               $rate_options[$rate_option_raw->bunga_investasi_pertahun] = $rate_option_raw->id;
            }*/
            //dd($rate_options);
            //dd($rate_options);
            /*$planController = new PlanController();
            $planSimulation = $planController->planSimulation([
                'usia' => $attributes['umur'],
                'usia_pensiun' => $attributes['umur_pensiun'],
                'pendapatan_pensiun_perbulan' => $pendapatan_pensiun
            ]);*/
            //dd($planSimulation);
            //dd($attributes['income_simulation_id']);
            //saving Plan A
            //$income_simulation_id = $rate_options[$attributes['income_simulation_id']];
            $note_calc = '';
            $plan_analysis = PlanAnalysis::create([
                'user_id' => $user_id,
                'version' => $module_version,
                'note' => $attributes['note'],
                'internal_note_calc' => $note_calc,
                'record_flag' => 'N',
                'created_by' => $user_id,
                'created_at' => Carbon::now(),
                'updated_by' => $user_id,
                'updated_at' => Carbon::now()
            ]);
            $plan_analysis_id = $plan_analysis->id;

            //dd(PlanAnalysis::all()->toArray());
/*  
            //save plan protection
            $kebutuhan_nilai_pertanggunan = calc_ins('planprotection', [
                'needinv' => $fv_kebutuhan_dana_raw['needinv'],
                'ori' => $fv_kebutuhan_dana_raw['ori']
            ]);
            //dd($kebutuhan_nilai_pertanggunan);
            //dd($kebutuhan_nilai_pertanggunan['planprotection']['fv_inf_planprotection']);
            PlanProtection::create([
                'user_id' => $user_id,
                'version' => $module_version,
                'kebutuhan_dana' => $attributes['kebutuhan_dana'],
                'pendapatan_pensiun' => $pendapatan_pensiun,
                'inflasi_pertahun' => $inflasi_pertahun,
                'durasi_proteksi' => $kebutuhan_nilai_pertanggunan['planprotection']['tenor_planprotection'],
                'bunga_deposito' => $deposito_rate,
                'kebutuhan_nilai_pertanggungan' => $kebutuhan_nilai_pertanggunan['planprotection']['fv_inf_planprotection'],
                'created_by' => $user_id,
                'created_at' => Carbon::now(),
                'updated_by' => $user_id,
                'updated_at' => Carbon::now()
            ]);*/

            //dd(PlanProtection::all()->toArray());

            //dd(count($attributes['nilai_pertanggungan_by_insurance_types']));
            //save insurance coverages
            if(isset($attributes['plans']) && count($attributes['plans']) > 0){
                $attributes_safe = [];
                $planAnalysisActivated_safe = [];
                foreach ($attributes['plans'] as $idx_plan => $plan) {
                    //$attributes_safe[] = [
                    $attributes_safe = [
                        'user_id' => $user_id,
                        'version' => $module_version,
                        'plan_analysis_id' => $plan_analysis_id,
                        'plan_id' => $plan['plan_id'],
                        'plan_type' => $plan['plan_type'],
                        'status' => 0,//not active
                        'record_flag' => 'N',
                        'total' => 0,//set nanti
                        'created_by' => $user_id,
                        'created_at' => Carbon::now(),
                        'updated_by' => $user_id,
                        'updated_at' => Carbon::now()
                    ];
                    $planAnalysisPlan = PlanAnalysisPlan::create($attributes_safe);
                    if(isset($plan['asset_repayment_id']) && $plan['asset_repayment_id']!== 0 && $plan['asset_repayment_id']!== '' ){
                        $planAnalysisActivated_safe = [
                            'plan_analysis_plan_id' => $planAnalysisPlan->id,
                            'asset_repayment_id' => $plan['asset_repayment_id'],
                            'created_by' => $user_id,
                            'created_at' => Carbon::now(),
                            'updated_by' => $user_id,
                            'updated_at' => Carbon::now()
                        ];
                        PlanAnalysisActivated::create($planAnalysisActivated_safe);
                        //update status
                        $planAnalysisPlan->status = 1;//activated
                        $planAnalysisPlan->save();
                    }
                }
                //PlanAnalysisPlan::insert($attributes_safe);
                //PlanAnalysisActivated::insert($planAnalysisActivated_safe);
                //dd(PlanAnalysisActivated::all()->toArray());
                //dd(PlanAnalysisPlan::where('user_id', $user_id)->where('version', $module_version)->where('plan_analysis_id', $plan_analysis_id));

                //dd(InsuranceCoverage::all()->toArray());
            }
            //dd(PlanAnalysisPlan::all()->toArray());
            //dd('eeeee');
             
/*
            $attributes_res = [];
            foreach ($modules as $module => $attributes_arr) { 
                if(isset($attributes[$attributes_arr['post_name']])){
                    switch ($module) {
                        default:
                            $attributes_safe = [];
                            foreach ($attributes[$attributes_arr['post_name']] as $idx => $attribute_arr) {
                                $attributes_safe[$idx] = $attribute_arr;
                                $attributes_safe[$idx] += [
                                    'user_id' => $user_id,
                                    'version' => $module_version,
                                    'created_by' => $user_id,
                                    'created_at' => Carbon::now(),
                                    'updated_by' => $user_id,
                                    'updated_at' => Carbon::now()
                                ];
                            }
                            break;
                    }
                
                    $model_name = '\\App\\Models\\'.$attributes_arr['model_name']; 
                    $model = new $model_name;

                    $model::insert($attributes_safe);

                    $attributes_res[$attributes_arr['post_name']] = $attributes_safe;
                }
            }*/
            $this->updateActiveVersion($user_id, $activeVersionKey, $module_version);//update version
            DB::commit();
            return true;
        }
        throw new ValidationException('Plan Analysis validation failed', $this->getErrors());
    }

    private function updateActiveVersion($user_id, $module_name, $module_version){
        if($module_version == 0){
            activeVersion::insert([
                'user_id' => $user_id,
                'key' => $module_name,
                'version' => $module_version,
                'created_by' => $user_id, 
                'created_at' => Carbon::now(), 
                'updated_by' => $user_id, 
                'updated_at' => Carbon::now(),
            ]);
        }else{
            activeVersion::where('user_id', $user_id)->where('key', $module_name)->update([
                'version' => $module_version,
                'updated_by' => $user_id, 
                'updated_at' => Carbon::now(),
            ]);
        }
    }

    public function createJUNK($attributes) { //dd($attributes);
        $rules['client_id'] = 'unique:cfp_clients,client_id';
        $rules['client_id'] = '';
        $attributeNames['client_id'] = trans('app.customer_name');

        $validator = Valid::make($attributes, $rules);
        $validator->setAttributeNames($attributeNames);

        if ($validator->fails())
        { 
            //dd($validator->errors());
            throw new ValidationException('Cfp client attribute validation failed', $validator->errors());
        }

        if($this->isValid($attributes)) {
            /*$t_attributes = array();
            $t_attributes['client_id'] = $attributes['client_id'];
            $t_attributes['cfp_id'] = $attributes['cfp_id'];
            $t_attributes['notes'] = $attributes['notes'];
            $t_attributes['created_by'] = Auth::user()->id;
            $t_attributes['created_on'] = date("Y-m-d H:i:s");
            $t_attributes['record_flag'] = 'N';*/
            /*
            $t_attributes['testimonial'] = Str::slug($attributes['name'][getLang()], '_');
            foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
                $t_attributes[$locale] = [
                    'name' => $attributes['name'][$locale]
                ];
            }*/
            $user_id = Auth::user()->id;
            $client_raw = explode('__', $attributes['client_id']);
            $cfp_raw = explode('__', $attributes['cfp_id']);
            $t_attributes = [
                'client_id' => $client_raw[0],
                'cfp_id' => $cfp_raw[0],
                'notes' => $attributes['notes'],
                'created_by' => $user_id, 
                'created_at' => Carbon::now(), 
                'updated_by' => $user_id, 
                'updated_at' => Carbon::now(),
                'record_flag' => 'N'
            ];
            
            
            //dd($t_attributes);
            $this->wallet->fill($t_attributes)->save();

            return true;
        }
        throw new ValidationException('Cfp client attribute validation failed', $this->getErrors());
    }

    public function update($id, $attributes) {

        if($this->isValid($attributes)) {
            $user_id = isset($attributes['user_id'])?$attributes['user_id']:Auth::user()->id;//$attributes['user_id'] untuk API
            $this->transaction = $this->find($id);
            $t_attributes = [
                'transaction_type_id' => $attributes['transaction_type'],
                'amount' => $attributes['amount'],
                'category_id' => $attributes['wallet_category'],
                'category_type_id' => $attributes['category_type'],
                'notes' => $attributes['notes'],
                'transaction_date' => $attributes['transaction_date'],
                'updated_by' => $user_id, 
                'updated_at' => Carbon::now(),
                'record_flag' => 'U'
            ];
            $transaction = $this->transaction->fill($t_attributes)->save();
            return $this->find($id)->toArray();
        }
        throw new ValidationException('Wallet validation failed', $this->getErrors());
    }

    public function updateJUNK($id, $attributes) {
        if($this->isValid($attributes)) {
            $t_attributes = array();
            $this->wallet = $this->find($id);


            $user_id = Auth::user()->id;
            $client_raw = explode('__', $attributes['client_id']);
            $cfp_raw = explode('__', $attributes['cfp_id']);
            $t_attributes = [
                'client_id' => $client_raw[0],
                'cfp_id' => $cfp_raw[0],
                'notes' => $attributes['notes'],
                'updated_by' => $user_id, 
                'updated_at' => Carbon::now(),
                'record_flag' => 'U'
            ];


            //dd($attributes);
            /*$t_attributes['customer_id'] = $attributes['customer_id'];
            $t_attributes['cfp_id'] = $attributes['cfp_id'];
            $t_attributes['notes'] = $attributes['notes'];
            $t_attributes['updated_by'] = Auth::user()->id;
            $t_attributes['updated_on'] = date("Y-m-d H:i:s");
            $t_attributes['record_flag'] = 'U';*/
            /*
            $t_attributes['testimonial'] = Str::slug($attributes['name'][getLang()], '_');
            foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
                $t_attributes[$locale] = [
                    'name' => $attributes['name'][$locale]
                ];
            }
            */
            $this->wallet->fill($t_attributes)->save();
            return true;
        }
        throw new ValidationException('Cfp client attribute validation failed', $this->getErrors());
    }

    public function delete($id) {
        $Wallet = $this->wallet->findOrFail($id);
        $Wallet->delete();
    }

    protected function totalTransactions($all = false) {
        return $this->wallet->count();
    }

    public function checkApproval($attributes) {
        $res = [];
        $user_id = $attributes['user_id'];
        //$module = $attributes['module'];
        $activeVersion = ActiveVersion::where('user_id', $user_id)->where('key', 'planAnalysis')->first();
        $module_version = is_null($activeVersion)?'':$activeVersion->version;

        if($module_version === '')
            return 1;
        //dd($module_version);
        $need_approval = ActiveVersionDetail::where('user_id', $user_id)
        ->where('version', $module_version)
        ->where('active_version_key', 'planAnalysis')
        ->where('status', 'draft')->count();

        //digabung dengan action plan
        $draft_action_plans = ClientActionPlanDetail::whereHas('clientActionPlan', function($q) use ($user_id, $module_version){
            $q->where('user_id', $user_id)
            ->where('version', $module_version)
            ->whereHas('activeVersionDetail', function($q2) use ($module_version){
                $q2->where('active_version_key','planAnalysis')
                ->where('version',$module_version)
                ->where('status','approved');
            });
        })->where('status', 'draft')
        ->count();
        //dd($draft_action_plans);

        $need_approval += $draft_action_plans;

        return $need_approval>0?0:1;
    }

    /*
    hanya untuk active_version_key cashflow, portfolio, plan analysis. kalau free consultation saat ini perhitungannya masih dengan menggunakan cashflow analysis saja
    */
    public function checkFreeConsultation_JUNK($attributes) { 
        $user_id = $attributes['user_id'];
        $active_version_key = $attributes['active_version_key'];
        $free_consultation_limit = config_db_cached('settings::free_consultation_limit');
        $need_check_modules = explode('|',$active_version_key);//supaya dapat array

        $activeVersionKey_overLimit = 0;
        foreach ($need_check_modules as $active_version_key_check) {
            $active_version_key_check = trim($active_version_key_check);
            $freeConsultationCount_by_activeVersionKey = ActiveVersionDetail::where('active_version_key', $active_version_key_check)
            ->where('status', 'approved')
            ->where('user_id', $user_id)
            ->count();

            if($freeConsultationCount_by_activeVersionKey >= $free_consultation_limit){
                $activeVersionKey_overLimit++;
            }
        }
        
        //sebenarnya bisa digabung dalam satu if tapi supaya tidak membingungkan jika ada revisi maka tetap pakai if else
        $is_overLimit = false;
        if(count($need_check_modules) > 1){//check all ada tiga
            if($activeVersionKey_overLimit >= count($need_check_modules)){ //$need_check_modules = 3 adalah financialCheckup_cashflowAnalysis, financialCheckup_portfolioAnalysis, planAnalysis
                $is_overLimit = true;
            }
        }else if(count($need_check_modules) == 1){//per satuan
            if($activeVersionKey_overLimit > 0){//check satu-satunya active_version_key yang diparsing di parameter
                $is_overLimit = true;
            }
        }

        if($is_overLimit === true){
            throw new ValidationException('Free consultation is over limit', [
                'consultation' => 'Free consultation is over limit',
            ]);
        }else{
            return true;
        }
    }

    public function checkFreeConsultation_withData($attributes) { 
        $user_id = $attributes['user_id'];
        $active_version_key = $attributes['active_version_key'];
        $free_consultation_limit = intval(config_db_cached('settings::free_consultation_limit'));
        $need_check_modules = explode('|',$active_version_key);//supaya dapat array

        $activeVersionKey_overLimit = 0;
        $data = [];
        $permitted_active_version_keys = [ 'financialCheckup_cashflowAnalysis', 'financialCheckup_portfolioAnalysis', 'planAnalysis'];
        foreach ($need_check_modules as $active_version_key_check) {
            if(in_array($active_version_key_check, $permitted_active_version_keys)){
                $active_version_key_check = trim($active_version_key_check);
                $freeConsultationCount_by_activeVersionKey = ActiveVersionDetail::where('active_version_key', $active_version_key_check)
                ->where('status', 'approved')
                ->where('user_id', $user_id)
                ->count();

                $data[$active_version_key_check]['current'] = $freeConsultationCount_by_activeVersionKey;
                $data[$active_version_key_check]['limit'] = $free_consultation_limit;

                if($freeConsultationCount_by_activeVersionKey >= $free_consultation_limit){
                    $activeVersionKey_overLimit++;
                }
            }else{
                throw new ValidationException('Active version key not valid', [
                    'active_version_key' => 'Active version key not valid',
                ]);
            }
        }
       
        //sebenarnya bisa digabung dalam satu if tapi supaya tidak membingungkan jika ada revisi maka tetap pakai if else
        $is_overLimit = false;
        if(count($need_check_modules) > 1){//check all ada tiga
            if($activeVersionKey_overLimit >= count($need_check_modules)){ //$need_check_modules = 3 adalah financialCheckup_cashflowAnalysis, financialCheckup_portfolioAnalysis, planAnalysis
                $is_overLimit = true;
            }
        }else if(count($need_check_modules) == 1){//per satuan
            if($activeVersionKey_overLimit > 0){//check satu-satunya active_version_key yang diparsing di parameter
                $is_overLimit = true;
                //$data = $data[$need_check_modules[0]];
            }
        }

        if($is_overLimit === true){
            return [
                'result' => 'error',
                'data' => $data,
                'alerts' => [ 'consultation' => 'Free consultation is over limit' ]
            ];
            /*throw new ValidationException('Free consultation is over limit', [
                'consultation' => 'Free consultation is over limit',
            ]);*/
        }else{ 
            return [
                'result' => 'success',
                'data' => $data
            ];
        }
    }
}
