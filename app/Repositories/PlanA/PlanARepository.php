<?php namespace App\Repositories\PlanA;

use App\Models\PlanA;
use App\Models\IncomeSimulation;
use App\Models\ActiveVersion;
use App\Models\ActiveVersionPlan;
use App\Models\PlanProtection;
use App\Models\InsuranceCoverage;
use App\Models\ActiveVersionPlanDetail;
use App\Models\PlanNumberSeq;
//use App\Taxonomy;

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
use App\Repositories\PlanA\PlanAInterface;
use Input;
use DB;

use App\Repositories\InterestRate\InterestRateRepository;
use App\Models\InterestRate;

use App\Models\PlanAnalysisActivated;

//use App\Repositories\CashflowAnalysis\CashflowAnalysisRepository;
//use App\Models\CashflowAnalysis;

//use App\Http\Controllers\API\PlanController;

use App\Taxonomy;
//use App\Repositories\Taxonomy\TaxonomyRepository;


class PlanARepository extends RepositoryAbstract implements PlanAInterface, CrudableInterface {

    protected $perPage;
    //protected $income;
    /**
     * Rules
     *
     * @var array
     */
    protected static $rules;
    protected static $attributeNames;
    protected $interestRate;

    /**
     * @param ProductAttribute $productAttribute
     */
    public function __construct() {

        //$config = Config::get('holiday');
        //$this->perPage = $config['per_page'];
        //$this->income = $income;
        //$this->taxonomy = new TaxonomyRepository(new Taxonomy);
        $this->interestRate = new interestRateRepository(new interestRate);
        //$this->cashflowAnalysis = new cashflowAnalysisRepository(new cashflowAnalysis);
        $rules_n_attributeNames = $this->rules();
        self::$rules = $rules_n_attributeNames['rules'];
        self::$attributeNames = $rules_n_attributeNames['attributeNames'];
    }

    public function rules(){
        $_rules = array();
        $setAttributeNames = array();
        $_rules['user_id'] = 'required';
        $_rules['kebutuhan_dana'] = 'required';
        //$_rules['pendapatan_pensiun'] = 'required';
        $_rules['status_perkawinan'] = 'required';
        $_rules['umur'] = 'required';
        $_rules['umur_pensiun'] = 'required';
        $_rules['ambil_asuransi'] = 'required';
        return [
            'rules' => $_rules,
            'attributeNames' => $setAttributeNames
        ]; 

    }

    public function all() {
        return PlanA::all();
    }

    public function getAllBy($attributes) {
        return PlanA::where('user_id', $attributes['user_id'])->where('parent_id', 0)->get();
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

        $result->totalItems = $this->totalTransactions($all);
        $result->items = $transactions->all();

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

    public function showByVersion($attributes){
        $user_id = $attributes['user_id'];
        $plan_type = 'plan_a';
        $plan_id = $attributes['plan_id'];
        //$req_plan_id = $plan_id; // requested plan_id
        $plan_info = $this->getOrigPlan($user_id, $plan_id); //dd($plan_info);

        if( $plan_info['plan_id'] == 'not_found'){
            return null;
        }

        $plan_id = $plan_info['plan_id'];//update to plan id original
        $ori_plan_id = $plan_id;
        $version = !isset($attributes['version'])?'last':$attributes['version'];
        $modules_shown = !isset($attributes['modules'])?[]:$attributes['modules'];
        $status = !isset($attributes['status'])?'approved':$attributes['status'];

        //dd($plan_id);
        //dd($modules_shown);

        switch ($version) {
            case 'last' :
                    if(isset($attributes['status']) && $attributes['status'] == 'all'){//draft, approved, rejected
                        $maxApprovedActivePlanVersion = ActiveVersionPlanDetail::where('plan_type', 'plan_a')
                        ->where('plan_id', $plan_id)
                        ->where('user_id', $user_id)
                        ->max('version');
                    }elseif(isset($attributes['status']) && $attributes['status'] == 'not_rejected'){//draft, approved
                        $maxApprovedActivePlanVersion = ActiveVersionPlanDetail::where('plan_type', 'plan_a')
                        ->where('plan_id', $plan_id)
                        ->where('user_id', $user_id)
                        ->where('status', '<>', 'rejected')
                        ->max('version');
                    }else{
                        $maxApprovedActivePlanVersion = ActiveVersionPlanDetail::where('plan_type', 'plan_a')
                        ->where('plan_id', $plan_id)
                        ->where('user_id', $user_id)
                        ->where('status', $status)
                        ->max('version');
                    }
                    
                    //dd($maxApprovedActivePlanVersion);
                    $module_version = is_null($maxApprovedActivePlanVersion)?'':$maxApprovedActivePlanVersion;
                break;
            default:
                    $module_version = $version;
                break;
        }
        
        if($module_version === '')
            return null;
        $plan_a_plan_parent_id = 0;
        //$plan_a_plan_id = $plan_id;

        //dd($ori_plan_id);
        //if($req_plan_id == $plan_id)
         //   $real_plan_id = $plan_id;


        if($module_version > 0 ){
            $plan_a_plan_parent_id = $plan_id;
            $find_plan_a = PlanA::where('version', $module_version)
            ->where('user_id', $user_id)
            ->where('parent_id', $plan_id)
            ->first();
            $plan_id = $find_plan_a->id; //dd($plan_a_plan_id);
        }

        
        
        //dd($real_plan_id);
        //dd($plan_type);
        //dd($plan_id);
        //dd($ori_plan_id);

        //menggunakan versino plananalysis terakhir
        $planAnalysis_activeVersion_data = ActiveVersion::where('user_id', $user_id)->where('key', 'planAnalysis')->first();
        $planAnalysis_activeVersion = !is_null($planAnalysis_activeVersion_data)?$planAnalysis_activeVersion_data->version:0;//defautkan 0 saja, toh engga ketemu jika memang tidak ada plan analysisnya
        
            
        $all_with = [
            'plan_a' => function($query) use ($module_version, $plan_id , $plan_a_plan_parent_id, $ori_plan_id, $planAnalysis_activeVersion) {
                $query->where('plan_a.version', $module_version)
                ->where('plan_a.id', $plan_id)
                ->where('plan_a.parent_id', $plan_a_plan_parent_id)
                ->with([ 'income_simulation.actual_interest_rates', 'detail_activation_latest' => function($q) use ($ori_plan_id, $planAnalysis_activeVersion){
                    $q->where('plan_analysis_activated.plan_id', $ori_plan_id)
                    ->where('plan_analysis_activated.version', $planAnalysis_activeVersion)//karena plan_analysis_activated masih menggunakan plan_id yg lama ( parent_id )
                    ->join('active_version_details', 'active_version_details.user_id', '=', 'plan_analysis_activated.user_id', 'left')
                    ->where('active_version_details.active_version_key', 'planAnalysis')
                    ->where('active_version_details.version', $planAnalysis_activeVersion)
                    ->where('active_version_details.status', '<>', 'rejected');
                }])
                ->select('active_version_plan_details.status as active_version_plan_detail_status', 'plan_a.*')
                ->join('active_version_plan_details', 'active_version_plan_details.user_id', '=', 'plan_a.user_id', 'left')
                ->where('active_version_plan_details.version', $module_version)
                ->where('active_version_plan_details.plan_id', $ori_plan_id); //menggunakan plan_id parent. jika nol maka gunakan plan_id orig
                //->where('active_version_plan_details.status', '<>', 'deleted');
                /*, 'active_version_plan_details' => function($q2) use ($module_version, $ori_plan_id){
                    $q2->where('version', $module_version);
                }]);//, 'detail_activation_latest.asset_repayment']);
                */
            },
            'income_simulations' => function($query) use ($module_version, $plan_id, $plan_type) {
                $query->where('version', $module_version)
                ->where('plan_type', $plan_type)
                ->where('plan_id', $plan_id);//->select('income_simulations.user_id', 'income_simulations.id','income_simulations.version', 'income_simulations.bunga_investasi_pertahun', 'income_simulations.cicilan_perbulan', 'income_simulations.produk', 'income_simulations.total_investasi');
            },
            'plan_protections' => function($query) use ($module_version, $plan_id, $plan_type) {
                $query->where('version', $module_version)
                ->where('plan_type', $plan_type)
                ->where('plan_id', $plan_id);//->select('income_simulations.user_id', 'income_simulations.id','income_simulations.version', 'income_simulations.bunga_investasi_pertahun', 'income_simulations.cicilan_perbulan', 'income_simulations.produk', 'income_simulations.total_investasi');
            },
            'insurance_coverages' => function($query) use ($module_version, $plan_id, $plan_type) {
                $query->select('t2.title as taxo_insurance_type_name', 'insurance_coverages.*')
                ->where('version', $module_version)
                ->where('plan_type', $plan_type)
                ->where('plan_id', $plan_id)
                ->join('taxonomies as t2', 't2.id', '=', 'insurance_coverages.taxo_insurance_type_id', 'left');//->select('income_simulations.user_id', 'income_simulations.id','income_simulations.version', 'income_simulations.bunga_investasi_pertahun', 'income_simulations.cicilan_perbulan', 'income_simulations.produk', 'income_simulations.total_investasi');
            },
        ];
       // dd(array_diff(array_keys($all_with), $modules_shown));
        //dd($modules_shown);
        if(!empty($modules_shown)){ //dd(array_intersect(array_keys($all_with), $modules_shown));
            foreach (array_diff(array_keys($all_with), $modules_shown) as $module_shown) {
                unset($all_with[$module_shown]);
            }
        }

        // if($plan_id == 305){ //debug
        //     \DB::enableQueryLog();
        //     User::select('users.id as user_id')->with($all_with)->where('id', $user_id)->first();
        //     dd(\DB::getQueryLog());
        // }
        return User::select('users.id as user_id')->with($all_with)->where('id', $user_id)->first();
    }

    public function getOrigPlan($user_id, $plan_id){
        if($plan_id === ''){
            return [
                    'plan_id' => $plan_id,
                    'plan_number' => 'next_plan_number_seq'
                ];
        }else{ //dd($plan_id);
            $planA = PlanA::select('parent_id', 'plan_number')
            ->where('user_id', $user_id)
            ->where('id', $plan_id)->first(); //dd($planA->toArray());
            if(is_null($planA)){
                return [
                    'plan_id' => 'not_found',
                    'plan_number' => 'not_found'
                ];
            }else{ 
                $orig_plan_id = $planA->parent_id == 0?$plan_id:$planA->parent_id; 
                //dd($orig_plan_id);
                return [
                    'plan_id' => $orig_plan_id,
                    'plan_number' => $planA->parent_id == 0?(is_null($planA->plan_number)?'plan_number_not_set_on_plan_id__'.$orig_plan_id:$planA->plan_number):'next_plan_number_seq'
                ];
            }   
        }
    }

    public function create($attributes) {
        if($this->isValid($attributes)) { //dd($this->getMaxVersionPlan(75, 265));
            $plan_id = isset($attributes['plan_id'])?$attributes['plan_id']:'';
            $increment_plan_number = $plan_id === ''?true:false; //dd($increment_plan_number);
            $plan_type = 'plan_a';
            $user_id = isset($attributes['user_id'])?$attributes['user_id']:'';//$attributes['user_id'] untuk API
            $triangle_layer_detail_id = isset($attributes['triangle_layer_detail_id'])?$attributes['triangle_layer_detail_id']:NULL;
            if($user_id == '')
                throw new ValidationException('Plan validation failed', 'user_not_set');
            //DB::rollBack();//hanya untuk testing
            DB::beginTransaction();

            $plan_info = $this->getOrigPlan($user_id, $plan_id); //dd($plan_info);
            $plan_id = $plan_info['plan_id'];//$this->getOrigPlanID($plan_id);//update plan id
            $plan_number = isset($plan_info['plan_number'])?$plan_info['plan_number']:'';
            if($plan_id == 'not_found')
                throw new ValidationException('Plan validation failed', 'plan_not_found');
            
            $activeVersion = $plan_id === ''?NULL:ActiveVersionPlan::where('user_id', $user_id)->where('plan_type', $plan_type)->where('plan_id', $plan_id)->first();
            $module_version = is_null($activeVersion)?0:$activeVersion->version+1; //dd($module_version);

            //dd($plan_id);    
            
            if($plan_number == 'next_plan_number_seq' || strpos($plan_number, 'plan_number_not_set') !== false ){
                $planNumberSeq = PlanNumberSeq::where('user_id', $user_id)->first(); //dd($planNumberSeq->toArray());
                $planNumber = is_null($planNumberSeq)?1:($increment_plan_number === false?$planNumberSeq->current_value:$planNumberSeq->current_value+1);
                //dd($planNumber);
                if(strpos($plan_number, 'plan_number_not_set') !== false ){
                    //plan  number kosong karena data aplikasi sebelumnya belum support plan_number
                    //jadi kita update dulu paren plus anak nya
                    //update plan_number to parent plan id dan child nya yg belum up to date plan nya

                    //get plan id
                    $plan_number_data = explode('__', $plan_number);
                    $orig_plan_id = $plan_number_data[1]; //dd($orig_plan_id);
                    PlanA::where('user_id', $user_id)->where('id', $orig_plan_id)->update([ 'plan_number' => $planNumber ]);
                    PlanA::where('user_id', $user_id)->where('parent_id', $orig_plan_id)->update([ 'plan_number' => $planNumber ]);
                }
            }else{ 
                //$planNumberSeq = $plan_number === ''?NULL:PlanNumberSeq::where('user_id', $user_id)->first();
                //$planNumber = is_null($planNumberSeq)?1:$planNumberSeq->current_value+1;
                $planNumber = $plan_number;
            }

            $modules = [ 
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

            //$module_version = is_null($activeVersion)?0:$activeVersion->version+1; //dd($module_version);
            $inflasi_pertahun = config_db_cached('settings::rate_inflation');
            $selisih_umur = intval($attributes['umur_pensiun']) - intval($attributes['umur']);
            $selisih_umur_dalam_bulan = $selisih_umur*12;

            $deposito_rate = config_db_cached('settings::rate_deposit');
            $pendapatan_pensiun = floatval($attributes['kebutuhan_dana'])*(($deposito_rate/100)/12);

            $fv_kebutuhan_dana_raw = calc_inf_fv('needinv', $attributes['kebutuhan_dana'], $inflasi_pertahun, $selisih_umur, false);
            //dd($fv_kebutuhan_dana_raw);
            $fv_kebutuhan_dana = $fv_kebutuhan_dana_raw['needinv']['fv_inf_needinv'];

            $plan_parent_id = $plan_id === '' ? 0 : $plan_id;//jika nol berarti parent

            //dd($plan_parent_id);

            //dd(intval($plan_parent_id));
            //$activeVersionPlan_plan_id = $plan_id;//akan selalu pakai parent id
            //save plan A
            $plan = PlanA::create([
                'plan_number' => $planNumber,
                'user_id' => $user_id,
                'version' => $module_version,
                'kebutuhan_dana' => $attributes['kebutuhan_dana'],
                'pendapatan_pensiun' => $pendapatan_pensiun,
                'status_perkawinan' => $attributes['status_perkawinan'],
                'umur' => $attributes['umur'],
                'umur_pensiun' => $attributes['umur_pensiun'],
                'inflasi_pertahun' => $inflasi_pertahun,
                'durasi_tahun_inflasi' => $selisih_umur,
                'durasi_tahun_investasi' => $selisih_umur,
                'fv_kebutuhan_dana' => $fv_kebutuhan_dana,
                //'income_simulation_id' => $income_simulation_id,
                'ambil_asuransi' => $attributes['ambil_asuransi'],
                'parent_id' => $plan_parent_id,
                'triangle_layer_detail_id' => $triangle_layer_detail_id,
                'created_by' => $user_id,
                'created_at' => Carbon::now(),
                'updated_by' => $user_id,
                'updated_at' => Carbon::now()
            ]);

            $new_plan_id = $plan->id;
            $plan_id = $plan_id === '' ? $new_plan_id : $plan_id;
            //dd($plan_id);

            //saving income simulation
            //sebenernya tidak erlu hitung lagi, karena widi sudah ada sama persis. tapi tidak dikirim balik kemari. 
            //jadi api proses sendiri lalu masukin juga ke table 
            $inv_rate_options = [];
            $inv_rate_options = $this->interestRate->all();
            $inv_rate_options = income_simulation_rate_options($inv_rate_options, [
                'lama_bulan_investasi' => $selisih_umur_dalam_bulan,
                'needinv' => $fv_kebutuhan_dana_raw['needinv']
            ]);
            //dd($inv_rate_options);
            //dd($plan_type);
            $attributes_safe = [];
            foreach ($inv_rate_options as $idx => $inv_rate_option) {
                $inv_slug = slugify($inv_rate_option['product'], '_').'_'.$inv_rate_option['rate'];
                $attributes_safe[$idx] = [
                    'user_id' => $user_id,
                    'interest_rate_id' => $inv_rate_option['interest_rate_id'],
                    'bunga_investasi_pertahun' => $inv_rate_option['rate'], 
                    'cicilan_perbulan' => $inv_rate_option['details']['pv_inv_'.$inv_slug],
                    'produk' => $inv_rate_option['product'],
                    'total_investasi' => $inv_rate_option['details']['fv_inv_'.$inv_slug],
                    'plan_id' => $new_plan_id,
                    'plan_type' => $plan_type,
                    'bgcolor' => $inv_rate_option['bgcolor'],
                    'bgcolor2' => $inv_rate_option['bgcolor2'],
                    'version' => $module_version,
                    'taxo_wallet_asset_id' => $inv_rate_option['taxo_wallet_asset_id'],
                    'created_by' => $user_id,
                    'created_at' => Carbon::now(),
                    'updated_by' => $user_id,
                    'updated_at' => Carbon::now()
                ];
            }
            IncomeSimulation::insert($attributes_safe);

            //dd(IncomeSimulation::all()->toArray());

            //dd($module_version);
            //get income simulations ids
            $rate_options_raw = IncomeSimulation::where('user_id', $user_id)
            ->where('version', $module_version)
            ->where('plan_id', $new_plan_id)
            ->get();
            //dd($rate_options_raw->toArray());
            $rate_options = [];
            $income_simulation_id = '';
            $plan_perbulan = 0;
            $debug_plan_perbulan = [];
            foreach ($rate_options_raw as $rate_option_raw) {
               $rate_options[$rate_option_raw->bunga_investasi_pertahun] = $rate_option_raw->id;
               if( $rate_option_raw->bunga_investasi_pertahun == $attributes['income_simulation_id'] ){
                    $income_simulation_id = $rate_option_raw->id;
                    $plan_perbulan += $rate_option_raw->cicilan_perbulan;
                    $debug_plan_perbulan[] = 'income_simulation_id : '.$attributes['income_simulation_id'];
               }
                 //   
            }
            //dd($plan_perbulan);
            //dd($rate_options);

            //saving Plan A
            //$income_simulation_id = $rate_options[$attributes['income_simulation_id']];//income_simulation_id, seharusnya jangan pakai nama ini, pakai aja choosed_rate atau lainnya
            
            $plan->income_simulation_id = $income_simulation_id;
            $plan->save();//update income simulation id

            //save plan protection
            $kebutuhan_nilai_pertanggunan = calc_ins('planprotection', [
                'needinv' => $fv_kebutuhan_dana_raw['needinv'],
                'ori' => $fv_kebutuhan_dana_raw['ori']
                ]);
            //dd($kebutuhan_nilai_pertanggunan);
            //dd($kebutuhan_nilai_pertanggunan['planprotection']['fv_inf_planprotection']);
            PlanProtection::create([
                'user_id' => $user_id,
                'kebutuhan_dana' => $attributes['kebutuhan_dana'],
                'pendapatan_pensiun' => $pendapatan_pensiun,
                'inflasi_pertahun' => $inflasi_pertahun,
                'durasi_proteksi' => $kebutuhan_nilai_pertanggunan['planprotection']['tenor_planprotection'],
                'bunga_deposito' => $deposito_rate,
                'kebutuhan_nilai_pertanggungan' => $kebutuhan_nilai_pertanggunan['planprotection']['fv_inf_planprotection'],
                'plan_id' => $new_plan_id,
                'plan_type' => $plan_type,
                'version' => $module_version,
                'created_by' => $user_id,
                'created_at' => Carbon::now(),
                'updated_by' => $user_id,
                'updated_at' => Carbon::now()
            ]);
            //dd($attributes['nilai_pertanggungan_by_insurance_types']);
            //dd(PlanProtection::all()->toArray());

            $taxo_insurance_types = Taxonomy::where('post_type', 'insurance_type')->get();
            $taxo_insurance_types_slug = [];
            if(count($taxo_insurance_types)){
                foreach ($taxo_insurance_types as $taxo_insurance_type) {
                    $taxo_insurance_types_slug[$taxo_insurance_type->id] = $taxo_insurance_type->slug;
                }
                
            }
            //dd($taxo_insurance_types_slug);
            //dd(count($attributes['nilai_pertanggungan_by_insurance_types']));
            //save insurance coverages
            if(isset($attributes['nilai_pertanggungan_by_insurance_types']) && count($attributes['nilai_pertanggungan_by_insurance_types']) > 0){
                $attributes_safe = [];
                foreach ($attributes['nilai_pertanggungan_by_insurance_types'] as $idx_nilai_pertanggungan => $nilai_pertanggungan_by_insurance_type) {
                    //dd($taxo_insurance_types_slug[$nilai_pertanggungan_by_insurance_type['taxo_insurance_type_id']]);
                    //$taxo_insurance_type = Taxonomy::where('id', $nilai_pertanggungan_by_insurance_type['taxo_insurance_type_id'])->first()->toArray());
                        
                    $premi_perbulan = $this->calc_ins_coverage($taxo_insurance_types_slug[$nilai_pertanggungan_by_insurance_type['taxo_insurance_type_id']], $nilai_pertanggungan_by_insurance_type['nilai_pertanggungan']);
                    //dd($premi_perbulan);
                    if($attributes['ambil_asuransi'] === 1){
                        $plan_perbulan += $premi_perbulan;
                        $debug_plan_perbulan[] = 'asuransi : '.$nilai_pertanggungan_by_insurance_type['nilai_pertanggungan'];
                    }
                    $attributes_safe[] = [
                        'user_id' => $user_id,
                        'taxo_insurance_type_id' => $nilai_pertanggungan_by_insurance_type['taxo_insurance_type_id'],
                        'nilai_pertanggungan' => $nilai_pertanggungan_by_insurance_type['nilai_pertanggungan'],
                        'premi_perbulan' => $premi_perbulan,
                        'plan_id' => $new_plan_id,
                        'plan_type' => $plan_type,
                        'version' => $module_version,
                        'created_by' => $user_id,
                        'created_at' => Carbon::now(),
                        'updated_by' => $user_id,
                        'updated_at' => Carbon::now()
                    ];
                }
                InsuranceCoverage::insert($attributes_safe);
                //dd(InsuranceCoverage::all()->toArray());
            }
            //dd($plan_id);
            $this->updateActiveVersionPlan($user_id, $plan_type, $plan_id, $module_version);//update version
            $this->updatePlanNumberSeq($user_id, $planNumber);//update plan number seq

            $plan->income_simulation_id = $income_simulation_id;
            $plan->plan_perbulan = $plan_perbulan;
            $plan->save();
            //dd($debug_plan_perbulan);
            //dd($plan_perbulan);

            //save active version details
            activeVersionPlanDetail::insert([
                'user_id' => $user_id, 
                'version' => $module_version,
                'status' => 'draft',
                'plan_type' => 'plan_a',
                'plan_id' => $plan_id,
                'created_by' => $user_id, 
                'created_at' => Carbon::now(), 
                'updated_by' => $user_id, 
                'updated_at' => Carbon::now()
            ]);

            //update plan_id pada model PlanAnalysisActivated by user_id dan asset_repayment_id
            //$this->updatePlanAUsage($user_id, $plan_id, $new_plan_id);

            //dd($user_id.' '.$plan_id);
            DB::commit();
           //dd($plan_id);
            return $this->showByVersion([
                'user_id' => $user_id,
                'plan_id' => $plan_id,
                'status' => 'draft'
            ]);
        }
        throw new ValidationException('Plan A validation failed', $this->getErrors());
    }

    private function calc_ins_coverage($insurance_type, $nilai_pertanggungan){
        $price_life_insurance = config_db_cached('settings::price_life_insurance');
        $price_critical_insurance = config_db_cached('settings::price_critical_insurance');
        switch ($insurance_type) {
            case 'jiwa':
                return (($nilai_pertanggungan/1000000000)*$price_life_insurance)/12;
                break;
            case 'kritis':
                return ($nilai_pertanggungan/1000000000)*$price_critical_insurance;
                break;
            case 'kesehatan':
                return 0;
                break;
        }
    }
    

    private function updatePlanNumberSeq($user_id, $planNumber){
        $check = PlanNumberSeq::where('user_id', $user_id)->count();
        //if($planNumber == 1){
        if($check === 0){
            PlanNumberSeq::insert([
                'user_id' => $user_id,
                'current_value' => $planNumber
            ]);
        }else{
            PlanNumberSeq::where('user_id', $user_id)->update([
                'current_value' => $planNumber
            ]);
        }
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

    private function updateActiveVersionPlan($user_id, $plan_type, $plan_id, $module_version){
        if($module_version == 0){
            activeVersionPlan::insert([
                'user_id' => $user_id,
                'plan_type' => $plan_type,
                'plan_id' => $plan_id,
                'version' => $module_version,
                'created_by' => $user_id, 
                'created_at' => Carbon::now(), 
                'updated_by' => $user_id, 
                'updated_at' => Carbon::now(),
            ]);
        }else{
            //dd(activeVersionPlan::where('user_id', $user_id)->where('plan_type', $plan_type)->where('plan_id', $plan_id)->get()->toArray());
            activeVersionPlan::where('user_id', $user_id)->where('plan_type', $plan_type)->where('plan_id', $plan_id)->update([
                'version' => $module_version,
                'updated_by' => $user_id, 
                'updated_at' => Carbon::now(),
            ]);
        }
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

    public function delete($attributes) {
        $client_id = $attributes['client_id'];
        $plan_id = $attributes['plan_id'];
        $cfp_id = $attributes['cfp_id'];
        //cek plan is exist dan memiliki versi teratas adalah nol dan berstatus draft
        $activeVersion = ActiveVersionPlanDetail::where('user_id', $client_id)
        ->where('plan_type', 'plan_a')
        ->where('plan_id', $plan_id)
        ->where('status', 'draft')->max('version');
        //dd($activeVersion);
        //$module_version = is_null($activeVersion)?'':$activeVersion->version;
        if( is_null($activeVersion) || $activeVersion > 0){
            throw new ValidationException('Plan validation failed', [
                'plan' => 'Could not be deleted'
            ]);
        }else{
            DB::beginTransaction();
            $planA = PlanA::where('user_id', $client_id)
            ->where('id', $plan_id)->first();
            
            if($planA->record_flag != 'D'){
                PlanA::where('user_id', $client_id)
                    ->where('id', $plan_id)
                    ->update([
                        'deleted_by' => $cfp_id,
                        'deleted_at' => Carbon::now(),
                        'record_flag' => 'D'
                    ]);
                
                //flag to active version plan detail
                ActiveVersionPlanDetail::where('user_id', $client_id)
                    ->where('plan_type', 'plan_a')
                    ->where('plan_id', $plan_id)
                    ->where('version', $activeVersion)->update([
                        'status' => 'deleted'
                    ]);
            }else{
                throw new ValidationException('Plan validation failed', [
                    'plan' => 'Plan has been deleted'
                ]);
            }
            
            DB::commit();
            return true;
        }
    }

    protected function totalTransactions($all = false) {
        return $this->wallet->count();
    }

    public function activated($user_id, $plan_id) {
        //$max_plan_info = $this->getMaxVersionPlan($user_id, $plan_id); //dd($max_plan_info);
        //$plan = PlanA::find($max_plan_info['plan_id']);
        $ori_plan_info = $this->getOrigPlan($user_id, $plan_id); 
        $ori_plan_id = $ori_plan_info['plan_id'];
        $plan = PlanA::where('id', $ori_plan_id)->orWhere('parent_id', $ori_plan_id);
        if($plan){
            $plan->update([
                'status' => 1
            ]);
            //$plan->status=1;
           // $plan->save();
        }   
    }

    public function deactivated($user_id, $plan_id) {
        $ori_plan_info = $this->getOrigPlan($user_id, $plan_id); 
        $ori_plan_id = $ori_plan_info['plan_id'];
        $plan = PlanA::where('id', $ori_plan_id)->orWhere('parent_id', $ori_plan_id);
        //$max_plan_info = $this->getMaxVersionPlan($user_id, $plan_id);
        //$plan = PlanA::find($max_plan_info['plan_id']);
        if($plan){
            $plan->update([
                'status' => 0
            ]);
        }   
    }

    public function checkApproval($attributes) {
        $res = [];
        $user_id = $attributes['user_id'];
        $plan_id = $attributes['plan_id'];
        $ori_plan_info = $this->getOrigPlan($user_id, $plan_id); 
        $plan_id = $ori_plan_info['plan_id'];
        if($plan_id == 'not_found')
            return 1;
        $activeVersion = ActiveVersionPlan::where('user_id', $user_id)->where('plan_type', 'plan_a')->where('plan_id', $plan_id)->first();
        $module_version = is_null($activeVersion)?'':$activeVersion->version;
        //dd($module_version);
        $need_approval = ActiveVersionPlanDetail::where('user_id', $user_id)
        ->where('version', $module_version)
        ->where('plan_type', 'plan_a')
        ->where('plan_id', $plan_id)
        ->where('status', 'draft')->count();
        return $need_approval>0?0:1;
    }
}
