<?php namespace App\Repositories\PlanB;

use App\Models\PlanB;
use App\Models\ActiveVersion;
use App\Models\ActiveVersionPlan;
use App\Models\ActiveVersionPlanDetail;
use App\Models\PlanNumberSeq;

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
use App\Repositories\PlanB\PlanBInterface;
use Input;
use DB;

//use App\Http\Controllers\API\PlanController;

//use App\Taxonomy;
//use App\Repositories\Taxonomy\TaxonomyRepository;


class PlanBRepository extends RepositoryAbstract implements PlanBInterface, CrudableInterface {

    protected $perPage;
    //protected $income;
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
    public function __construct() {

        //$config = Config::get('holiday');
        //$this->perPage = $config['per_page'];
        //$this->income = $income;
        //$this->taxonomy = new TaxonomyRepository(new Taxonomy);

        $rules_n_attributeNames = $this->rules();
        self::$rules = $rules_n_attributeNames['rules'];
        self::$attributeNames = $rules_n_attributeNames['attributeNames'];
    }

    public function rules(){
        $_rules = array();
        $setAttributeNames = array();
        $_rules['user_id'] = 'required';
        $_rules['plan_name'] = 'required';
        $_rules['kebutuhan_dana'] = 'required';
        $_rules['durasi_cicilan'] = 'required';
        $_rules['satuan_durasi_cicilan'] = 'required';
        $_rules['bunga_tahunan_flat'] = 'required';

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

    public function showByVersion($attributes){
        $user_id = $attributes['user_id'];
        $plan_type = 'plan_b';
        $plan_id = $attributes['plan_id'];
        $plan_info = $this->getOrigPlan($user_id, $plan_id); //dd($plan_info);
        $plan_id = $plan_info['plan_id'];//update to plan id original
        $ori_plan_id = $plan_id;
        $version = !isset($attributes['version'])?'last':$attributes['version'];
        $modules_shown = !isset($attributes['modules'])?[]:$attributes['modules'];
        $status = !isset($attributes['status'])?'approved':$attributes['status'];

        //dd($modules_shown);

        switch ($version) {
            case 'last' :
                    //$activeVersion = ActiveVersion::where('user_id', $user_id)->where('key', 'planB')->first();
                    //$activeVersion = ActiveVersionPlan::where('user_id', $user_id)->where('plan_type', 'plan_b')->where('plan_id', $plan_id)->first();
                    //$module_version = is_null($activeVersion)?'':$activeVersion->version;

                    if(isset($attributes['status']) && $attributes['status'] == 'all'){//draft, approved, rejected
                        $maxApprovedActivePlanVersion = ActiveVersionPlanDetail::where('plan_type', 'plan_b')
                        ->where('plan_id', $plan_id)
                        ->where('user_id', $user_id)
                        ->max('version');
                    }elseif(isset($attributes['status']) && $attributes['status'] == 'not_rejected'){//draft, approved
                        $maxApprovedActivePlanVersion = ActiveVersionPlanDetail::where('plan_type', 'plan_b')
                        ->where('plan_id', $plan_id)
                        ->where('user_id', $user_id)
                        ->where('status', '<>', 'rejected')
                        ->max('version');
                    }else{
                        $maxApprovedActivePlanVersion = ActiveVersionPlanDetail::where('plan_type', 'plan_b')
                        ->where('plan_id', $plan_id)
                        ->where('user_id', $user_id)
                        ->where('status', $status)
                        ->max('version');
                    }

                    $module_version = is_null($maxApprovedActivePlanVersion)?'':$maxApprovedActivePlanVersion;
                
                break;
            default:
                    $module_version = $version;
                break;
        }

        if($module_version === '')
            return [];
        //dd($module_version);
       // \DB::enableQueryLog();
        //User::with(['incomes'])->where('id', $user_id)->get();//, 'debt_repayments', 'asset_repayments', 'insurances'])->get();
//dd(\DB::getQueryLog());

        /*$q = User::select('id','id as user_id');
        $q->with([
            'incomes' => function($query) use ($module_version) {
                $query->where('version', $module_version);
            }]);
        $q->with([
            'expenses' => function($query) use ($module_version) {
                $query->where('version', $module_version);
            }]);
        $q->with([
            'debt_repayments' => function($query) use ($module_version) {
                $query->where('version', $module_version);
            }]);
        $q->with([
            'asset_repayments' => function($query) use ($module_version) {
                $query->where('version', $module_version);
            }]);
        $q->with([
            'insurances' => function($query) use ($module_version) {
                $query->where('version', $module_version);
            }]);
        $q->where('id', $user_id)->first();

        return $q;*/

        $plan_b_plan_parent_id = 0;
        //$plan_a_plan_id = $plan_id;
        if($module_version > 0){ 
            $plan_b_plan_parent_id = $plan_id;
            $find_plan_a = PlanB::where('version', $module_version)->where('parent_id', $plan_id)->first();
            $plan_id = $find_plan_a->id; //dd($plan_a_plan_id);
        }

        $planAnalysis_activeVersion_data = ActiveVersion::where('user_id', $user_id)->where('key', 'planAnalysis')->first();
        $planAnalysis_activeVersion = !is_null($planAnalysis_activeVersion_data)?$planAnalysis_activeVersion_data->version:0;

        $all_with = [
            'plan_b' => function($query) use ($module_version, $plan_id , $plan_b_plan_parent_id, $ori_plan_id, $planAnalysis_activeVersion) {
                $query->with(['asset_repayment' => function($query2){
                    //return $query2;
                    $query2->select('taxonomies.title as taxo_wallet_name', 'asset_repayments.id', 'asset_repayments.user_id', 'asset_repayments.taxo_wallet_id', 'asset_repayments.nama', 'asset_repayments.cicilan_perbulan', 'asset_repayments.sisa_durasi', 'asset_repayments.catatan')
                    ->join('taxonomies', 'taxonomies.id', '=', 'asset_repayments.taxo_wallet_id', 'left');
                }, 'detail_activation_latest' => function($q) use ($ori_plan_id, $planAnalysis_activeVersion){
                    $q->where('plan_analysis_activated.plan_id', $ori_plan_id)
                    ->where('plan_analysis_activated.version', $planAnalysis_activeVersion)//karena plan_analysis_activated masih menggunakan plan_id yg lama ( parent_id )
                    ->join('active_version_details', 'active_version_details.user_id', '=', 'plan_analysis_activated.user_id', 'left')
                    ->where('active_version_details.active_version_key', 'planAnalysis')
                    ->where('active_version_details.version', $planAnalysis_activeVersion)
                    ->where('active_version_details.status', '<>','rejected');
                }])
                ->select('active_version_plan_details.status as active_version_plan_detail_status', 'asset_repayments.nama as asset_repayment_nama', 'plan_b.*')
                ->join('asset_repayments', 'asset_repayments.id', '=', 'plan_b.asset_repayment_id', 'left')
                ->where('plan_b.version', $module_version)
                ->where('plan_b.id', $plan_id)
                ->where('plan_b.parent_id', $plan_b_plan_parent_id)
                ->join('active_version_plan_details', 'active_version_plan_details.user_id', '=', 'plan_b.user_id', 'left')
                ->where('active_version_plan_details.version', $module_version)
                ->where('active_version_plan_details.plan_id', $ori_plan_id); //menggunakan plan_id parent. jika nol maka gunakan plan_id orig
                //->where('active_version_plan_details.status', '!=', 'deleted');
            }/*,
            'plan_b' => function($query) use ($module_version) {
                $query->where('version', $module_version);
            },
            'income_simulations' => function($query) use ($module_version) {
                $query->where('version', $module_version);//->select('income_simulations.user_id', 'income_simulations.id','income_simulations.version', 'income_simulations.bunga_investasi_pertahun', 'income_simulations.cicilan_perbulan', 'income_simulations.produk', 'income_simulations.total_investasi');
            },
            'plan_protections' => function($query) use ($module_version) {
                $query->where('version', $module_version);//->select('income_simulations.user_id', 'income_simulations.id','income_simulations.version', 'income_simulations.bunga_investasi_pertahun', 'income_simulations.cicilan_perbulan', 'income_simulations.produk', 'income_simulations.total_investasi');
            },
            'insurance_coverages' => function($query) use ($module_version) {
                $query->where('version', $module_version);//->select('income_simulations.user_id', 'income_simulations.id','income_simulations.version', 'income_simulations.bunga_investasi_pertahun', 'income_simulations.cicilan_perbulan', 'income_simulations.produk', 'income_simulations.total_investasi');
            },
            

            ,
            'debt_repayments' => function($query) use ($module_version) {
                $query->select('taxonomies.title as taxo_wallet_name', 'debt_repayments.*')
                ->where('version', $module_version)
                ->join('taxonomies', 'taxonomies.id', '=', 'debt_repayments.taxo_wallet_id', 'left');
            },
            'asset_repayments' => function($query) use ($module_version) {
                $query->select('taxonomies.title as taxo_wallet_name', 'asset_repayments.*')
                ->where('version', $module_version)
                ->join('taxonomies', 'taxonomies.id', '=', 'asset_repayments.taxo_wallet_id', 'left');
            },
            'insurances' => function($query) use ($module_version) {
                $query->select('t.title as taxo_wallet_name', 't2.title as taxo_insurance_type_name', 'insurances.*')
                ->where('version', $module_version)
                ->join('taxonomies as t', 't.id', '=', 'insurances.taxo_wallet_id', 'left')
                ->join('taxonomies as t2', 't2.id', '=', 'insurances.taxo_insurance_type_id', 'left');
            }*/
        ];
       // dd(array_diff(array_keys($all_with), $modules_shown));
        //dd($modules_shown);
        if(!empty($modules_shown)){ //dd(array_intersect(array_keys($all_with), $modules_shown));
            foreach (array_diff(array_keys($all_with), $modules_shown) as $module_shown) {
                unset($all_with[$module_shown]);
            }
        }
        
        

        return User::select('users.id as user_id')->with($all_with)->where('id', $user_id)->first();

        /*return User::select('id','id as user_id')->with([
            'incomes' => function($query) use ($module_version) {
                $query->where('version', $module_version);
            },
            'expenses' => function($query) use ($module_version) {
                $query->where('version', $module_version);
            },
            'debt_repayments' => function($query) use ($module_version) {
                $query->where('version', $module_version);
            },
            'asset_repayments' => function($query) use ($module_version) {
                $query->where('version', $module_version);
            },
            'insurances' => function($query) use ($module_version) {
                $query->where('version', $module_version);
            }])->where('id', $user_id)->first();*/
    }

    public function getOrigPlanID_JUNK($plan_id){
        if($plan_id === ''){
            return $plan_id;
        }else{
            $planB = PlanB::select('parent_id')->where('id', $plan_id)->first(); //dd($planB);
            if(is_null($planB)){
                return 'not_found';
            }else{
                return $planB->parent_id == 0?$plan_id:$planB->parent_id;
            }
            
        }
        
    }

    public function getOrigPlan($user_id, $plan_id){
        if($plan_id === ''){
            return [
                    'plan_id' => $plan_id,
                    'plan_number' => 'next_plan_number_seq'
                ];
        }else{
            $planB = PlanB::select('parent_id', 'plan_number')
            ->where('user_id', $user_id)
            ->where('id', $plan_id)->first();
            if(is_null($planB)){
                return [
                    'plan_id' => 'not_found',
                    'plan_number' => 'not_found'
                ];
            }else{ 
                $orig_plan_id = $planB->parent_id == 0?$plan_id:$planB->parent_id;
                if($planB->parent_id == 0){
                    if(is_null($planB->plan_number)){
                        $plan_number = 'plan_number_not_set_on_plan_id__'.$orig_plan_id;
                    }else{
                        $plan_number = $planB->plan_number;
                    }
                }else{
                    if(is_null($planB->plan_number)){
                        $plan_number = 'next_plan_number_seq';
                    }else{
                        $plan_number = $planB->plan_number;
                    }
                    
                    
                }
                return [
                    'plan_id' => $orig_plan_id,
                    'plan_number' => $plan_number//$planB->parent_id == 0?(is_null($planB->plan_number)?'plan_number_not_set_on_plan_id__'.$orig_plan_id:$planB->plan_number):'next_plan_number_seq'
                ];
            }
        }
    }

    public function create($attributes) {
        if($this->isValid($attributes)) {
            $plan_id = isset($attributes['plan_id'])?$attributes['plan_id']:'';
            $plan_type = 'plan_b';
            $is_protected = isset($attributes['is_protected'])?$attributes['is_protected']:0;
            $triangle_layer_detail_id = isset($attributes['triangle_layer_detail_id'])?$attributes['triangle_layer_detail_id']:NULL;
            $user_id = isset($attributes['user_id'])?$attributes['user_id']:'';//$attributes['user_id'] untuk API
            if($user_id == '')
                throw new ValidationException('Plan validation failed', 'user_not_set');
            DB::beginTransaction();

            $plan_info = $this->getOrigPlan($user_id, $plan_id); //dd($plan_info);
            $plan_id = $plan_info['plan_id'];//$this->getOrigPlanID($plan_id);//update plan id
            $plan_number = isset($plan_info['plan_number'])?$plan_info['plan_number']:'';

            if($plan_id == 'not_found')
                throw new ValidationException('Plan validation failed', 'plan_not_found');

            $activeVersion = $plan_id === ''?NULL:ActiveVersionPlan::where('user_id', $user_id)->where('plan_type', $plan_type)->where('plan_id', $plan_id)->first();
            
            $module_version = is_null($activeVersion)?0:$activeVersion->version+1; 

            if($plan_number == 'next_plan_number_seq' || strpos($plan_number, 'plan_number_not_set') !== false ){
                $planNumberSeq = PlanNumberSeq::where('user_id', $user_id)->first();
                $planNumber = is_null($planNumberSeq)?1:$planNumberSeq->current_value+1;

                if(strpos($plan_number, 'plan_number_not_set') !== false ){
                    //plan  number kosong karena data aplikasi sebelumnya belum support plan_number
                    //jadi kita update dulu paren plus anak nya
                    //update plan_number to parent plan id dan child nya yg belum up to date plan nya

                    //get plan id
                    $plan_number_data = explode('__', $plan_number);
                    $orig_plan_id = $plan_number_data[1]; //dd($orig_plan_id);
                    PlanB::where('user_id', $user_id)->where('id', $orig_plan_id)->update([ 'plan_number' => $planNumber ]);
                    PlanB::where('user_id', $user_id)->where('parent_id', $orig_plan_id)->update([ 'plan_number' => $planNumber ]);
                }
            }else{ 
                $planNumber = $plan_number;
            }

            $plan_parent_id = $plan_id === '' ? 0 : $plan_id;//jika nol berarti parent

            $plan_perbulan = $attributes['kebutuhan_dana'] / $attributes['durasi_cicilan'];
            $bunga_perbulan = (($attributes['bunga_tahunan_flat']/100)/12)*$plan_perbulan;
            $plan_perbulan_plus_bunga = $plan_perbulan + $bunga_perbulan;
            //dd($plan_perbulan);
            $plan = PlanB::create([
                'plan_number' => $planNumber,
                'user_id' => $user_id,
                'version' => $module_version,
                'asset_repayment_id' => $attributes['asset_repayment_id'],
                'plan_name' => $attributes['plan_name'],
                'kebutuhan_dana' => $attributes['kebutuhan_dana'],
                'durasi_cicilan' => $attributes['durasi_cicilan'],
                'satuan_durasi_cicilan' => $attributes['satuan_durasi_cicilan'],
                'bunga_tahunan_flat' => $attributes['bunga_tahunan_flat'],
                'plan_perbulan' => $plan_perbulan_plus_bunga,
                'parent_id' => $plan_parent_id,
                'is_protected' => $is_protected,
                'triangle_layer_detail_id' => $triangle_layer_detail_id,
                'created_by' => $user_id,
                'created_at' => Carbon::now(),
                'updated_by' => $user_id,
                'updated_at' => Carbon::now()
            ]);

            $new_plan_id = $plan->id;
            $plan_id = $plan_id === '' ? $new_plan_id : $plan_id;

            $this->updateActiveVersionPlan($user_id, $plan_type, $plan_id, $module_version);//update version
            $this->updatePlanNumberSeq($user_id, $planNumber);//update plan number seq

            //save active version details
            activeVersionPlanDetail::insert([
                'user_id' => $user_id, 
                'version' => $module_version,
                'status' => 'draft',
                'plan_type' => 'plan_b',
                'plan_id' => $plan_id,
                'created_by' => $user_id, 
                'created_at' => Carbon::now(), 
                'updated_by' => $user_id, 
                'updated_at' => Carbon::now()
            ]);

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

    public function delete($attributes) {
        $client_id = $attributes['client_id'];
        $plan_id = $attributes['plan_id'];
        $cfp_id = $attributes['cfp_id'];
        //cek plan is exist dan memiliki versi teratas adalah nol dan berstatus draft
        $activeVersion = ActiveVersionPlanDetail::where('user_id', $client_id)
        ->where('plan_type', 'plan_b')
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
            $planB = PlanB::where('user_id', $client_id)
            ->where('id', $plan_id)->first();
            
            if($planB->record_flag != 'D'){
                PlanB::where('user_id', $client_id)
                    ->where('id', $plan_id)
                    ->update([
                        'deleted_by' => $cfp_id,
                        'deleted_at' => Carbon::now(),
                        'record_flag' => 'D'
                    ]);
                
                //flag to active version plan detail
                ActiveVersionPlanDetail::where('user_id', $client_id)
                    ->where('plan_type', 'plan_b')
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
        $ori_plan_info = $this->getOrigPlan($user_id, $plan_id); //dd($plan_info);
        $ori_plan_id = $ori_plan_info['plan_id']; //dd($ori_plan_id);
        //$plan = PlanB::find($max_plan_info['plan_id']);
        $plan = PlanB::where('id', $ori_plan_id)->orWhere('parent_id', $ori_plan_id);
        if($plan){
            //$plan->status=1;
            //$plan->update();
            $plan->update([
                'status' => 1
            ]);
        }
        
    }

    public function deactivated($user_id, $plan_id) {
        //$max_plan_info = $this->getMaxVersionPlan($user_id, $plan_id);
        //$plan = PlanB::find($max_plan_info['plan_id']);
        $ori_plan_info = $this->getOrigPlan($user_id, $plan_id); //dd($plan_info);
        $ori_plan_id = $ori_plan_info['plan_id']; //dd($ori_plan_id);
        $plan = PlanB::where('id', $ori_plan_id)->orWhere('parent_id', $ori_plan_id);
        if($plan){
            //$plan->status=0;
            //$plan->update();
            $plan->update([
                'status' => 0
            ]);
        }
        
    }

    public function getMaxVersionPlanJUNK($user_id, $plan_id){
        $planB = PlanB::select('parent_id', 'plan_number')
            ->where('user_id', $user_id)
            ->where('id', $plan_id)->first(); //dd($planA->toArray());
        if(is_null($planB)){
            return [
                'plan_id' => 'not_found',
                'plan_number' => 'not_found'
            ];
        }else{ 
            if($planB->parent_id != 0){
                //yang diaktifkan adalah versi maksimalnya, karena ini yang di tampilkan 
                //dd($plan_id);

                //untuk dapat versi plan id harus menggunakan parent_id
                $ori_plan_info = $this->getOrigPlan($user_id, $plan_id); //dd($plan_info);
                $ori_plan_id = $ori_plan_info['plan_id']; //dd($ori_plan_id);
                $activeVersion = ActiveVersionPlan::where('user_id', $user_id)->where('plan_type', 'plan_b')->where('plan_id', $ori_plan_id)->first();
                $module_version = is_null($activeVersion)?'':$activeVersion->version;
                //dd($module_version);
                $planB_max = PlanB::select('id', 'parent_id', 'plan_number')
                ->where('user_id', $user_id)
                ->where('parent_id', $ori_plan_id)
                ->where('version', $module_version)
                ->first(); //dd($planA_max);
                if(!is_null($planB_max)){
                    return [
                        'plan_id' => $planB_max->id,
                        'plan_number' => $planB_max->plan_number
                    ];
                }else{
                    return [
                        'plan_id' => $plan_id,  
                        'plan_number' => $planB->plan_number
                    ];
                }
                
            }else{
                return [
                    'plan_id' => $plan_id,  
                    'plan_number' => $planB->plan_number
                ];
            }
        }
    }

    public function checkApproval($attributes) {
        $res = [];
        $user_id = $attributes['user_id'];
        $plan_id = $attributes['plan_id'];
        $ori_plan_info = $this->getOrigPlan($user_id, $plan_id); 
        $plan_id = $ori_plan_info['plan_id'];
        if($plan_id == 'not_found')
            return 1;//permitted
        $activeVersion = ActiveVersionPlan::where('user_id', $user_id)->where('plan_type', 'plan_b')->where('plan_id', $plan_id)->first();
        $module_version = is_null($activeVersion)?'':$activeVersion->version;
        //dd($module_version);
        $need_approval = ActiveVersionPlanDetail::where('user_id', $user_id)
        ->where('version', $module_version)
        ->where('plan_type', 'plan_b')
        ->where('plan_id', $plan_id)
        ->where('status', 'draft')->count();
        return $need_approval>0?0:1;
    }
}
