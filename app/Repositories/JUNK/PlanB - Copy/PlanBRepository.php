<?php namespace App\Repositories\PlanB;

use App\Models\Income;
use App\Models\Expense;
use App\Models\DebtRepayment;
use App\Models\AssetRepayment;
use App\Models\Insurance;
use App\Models\ActiveVersion;
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
use App\Repositories\PlanB\PlanBInterface;
use Input;
use DB;

//use App\Taxonomy;
//use App\Repositories\Taxonomy\TaxonomyRepository;


class PlanBRepository extends RepositoryAbstract implements PlanBInterface, CrudableInterface {

    protected $perPage;
    protected $income;
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
    public function __construct(Income $income) {

        //$config = Config::get('holiday');
        //$this->perPage = $config['per_page'];
        $this->income = $income;
        //$this->taxonomy = new TaxonomyRepository(new Taxonomy);

        $rules_n_attributeNames = $this->rules();
        self::$rules = $rules_n_attributeNames['rules'];
        self::$attributeNames = $rules_n_attributeNames['attributeNames'];
    }

    public function rules(){
        $_rules = array();
        $setAttributeNames = array();

        $_rules['user_id'] = 'required';

        if(Input::has('incomes') && count(Input::get('incomes')) > 0){
            foreach(Input::get('incomes') as $idx => $income){
                $_rules['incomes.'.$idx.'.pendapatan_bulanan'] = 'required';
                $_rules['incomes.'.$idx.'.pendapatan_lain'] = 'required';

                $setAttributeNames['incomes.'.$idx.'.pendapatan_bulanan'] = 'Pendapatan bulanan';
                $setAttributeNames['incomes.'.$idx.'.pendapatan_lain'] = 'Pendapatan lain';
            }
        }

        if(Input::has('expenses') && count(Input::get('expenses')) > 0){
            foreach(Input::get('expenses') as $idx => $expense){
                $_rules['expenses.'.$idx.'.taxo_wallet_id'] = 'required';
                $_rules['expenses.'.$idx.'.anggaran_perbulan'] = 'required';

                $setAttributeNames['expenses.'.$idx.'.taxo_wallet_id'] = 'Kategori wallet #'.($idx+1);
                $setAttributeNames['expenses.'.$idx.'.anggaran_perbulan'] = 'Anggaran perbulan #'.($idx+1);
            }
        }

        if(Input::has('debt_repayments') && count(Input::get('debt_repayments')) > 0){
            foreach(Input::get('debt_repayments') as $idx => $debt_repayment){
                $_rules['debt_repayments.'.$idx.'.taxo_wallet_id'] = 'required';
                $_rules['debt_repayments.'.$idx.'.nama'] = 'required';
                $_rules['debt_repayments.'.$idx.'.cicilan_perbulan'] = 'required';
                $_rules['debt_repayments.'.$idx.'.sisa_durasi'] = 'required';

                $setAttributeNames['debt_repayments.'.$idx.'.taxo_wallet_id'] = 'Tipe #'.($idx+1);
                $setAttributeNames['debt_repayments.'.$idx.'.nama'] = 'Nama #'.($idx+1);
                $setAttributeNames['debt_repayments.'.$idx.'.cicilan_perbulan'] = 'Cicilan perbulan #'.($idx+1);
                $setAttributeNames['debt_repayments.'.$idx.'.sisa_durasi'] = 'Sisa durasi #'.($idx+1);
            }
        }

        if(Input::has('asset_repayments') && count(Input::get('asset_repayments')) > 0){
            foreach(Input::get('asset_repayments') as $idx => $debt_repayment){
                $_rules['asset_repayments.'.$idx.'.taxo_wallet_id'] = 'required';
                $_rules['asset_repayments.'.$idx.'.nama'] = 'required';
                $_rules['asset_repayments.'.$idx.'.cicilan_perbulan'] = 'required';
                $_rules['asset_repayments.'.$idx.'.sisa_durasi'] = 'required';

                $setAttributeNames['asset_repayments.'.$idx.'.taxo_wallet_id'] = 'Tipe #'.($idx+1);
                $setAttributeNames['asset_repayments.'.$idx.'.nama'] = 'Nama #'.($idx+1);
                $setAttributeNames['asset_repayments.'.$idx.'.cicilan_perbulan'] = 'Cicilan perbulan #'.($idx+1);
                $setAttributeNames['asset_repayments.'.$idx.'.sisa_durasi'] = 'Sisa durasi #'.($idx+1);
            }
        }

        if(Input::has('insurances') && count(Input::get('insurances')) > 0){
            foreach(Input::get('insurances') as $idx => $debt_repayment){
                $_rules['insurances.'.$idx.'.taxo_wallet_id'] = 'required';
                $_rules['insurances.'.$idx.'.no_polis'] = 'required';
                $_rules['insurances.'.$idx.'.premi_perbulan'] = 'required';
                $_rules['insurances.'.$idx.'.taxo_insurance_type_id'] = 'required';
                $_rules['insurances.'.$idx.'.nilai_pertanggungan'] = 'required';

                $setAttributeNames['insurances.'.$idx.'.taxo_wallet_id'] = 'Nama #'.($idx+1);
                $setAttributeNames['insurances.'.$idx.'.no_polis'] = 'No polis #'.($idx+1);
                $setAttributeNames['insurances.'.$idx.'.premi_perbulan'] = 'Premi perbulan #'.($idx+1);
                $setAttributeNames['insurances.'.$idx.'.taxo_insurance_type_id'] = 'Jenis #'.($idx+1);
                $setAttributeNames['insurances.'.$idx.'.nilai_pertanggungan'] = 'Nilai pertanggungan #'.($idx+1);
            }
        }
        
        /*$_rules['transaction_type'] = 'required';
        $_rules['amount'] = 'required';
        $_rules['wallet_category'] = 'required';
        $_rules['transaction_date'] = 'required';*/

        //$setAttributeNames['amount'] = trans('app.amount');
        //$setAttributeNames['wallet_category'] = trans('app.wallet_category');
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
        $version = $attributes['version'];
        $modules_shown = !isset($attributes['modules'])?[]:$attributes['modules'];

        //dd($modules_shown);

        switch ($version) {
            default://last
                $activeVersion = ActiveVersion::where('user_id', $user_id)->where('key', 'financialCheckup_cashflowAnalysis')->first();

                $module_version = is_null($activeVersion)?'':$activeVersion->version;
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

        $all_with = [
            'incomes' => function($query) use ($module_version) {
                $query->where('version', $module_version);
            },
            'expenses' => function($query) use ($module_version) {
                $query->select('taxonomies.title as taxo_wallet_name', 'expenses.*')
                ->where('version', $module_version)
                ->join('taxonomies', 'taxonomies.id', '=', 'expenses.taxo_wallet_id', 'left');
            },
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
            }
        ];
       // dd(array_diff(array_keys($all_with), $modules_shown));
        //dd($modules_shown);
        if(!empty($modules_shown)){ //dd(array_intersect(array_keys($all_with), $modules_shown));
            foreach (array_diff(array_keys($all_with), $modules_shown) as $module_shown) {
                unset($all_with[$module_shown]);
            }
        }
        
        

        return User::select('id','id as user_id')->with($all_with)->where('id', $user_id)->first();

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

    public function create($attributes) {

        if($this->isValid($attributes)) { //dd($attributes);
            /*
            'transaction_type_id', //simple atau detail, ini depth ke 0
            'amount', 
            'category_id', // taxonomy id, seharusnya depth ke 2
            'category_type', // income atau expense , ini depth ke 1
            'notes',
            'transaction_date',
            'created_by',
            'created_at', 
            'updated_by', 
            'updated_at', 
            'record_flag'*/
            //$category_info = $this->taxonomy->findWithParentDetail($attributes['wallet_category']);
            //dd($category_info);
            //$category_type = is_null($category_info)?'':$category_info->parentDetail->title;
            /*$user_id = isset($attributes['user_id'])?$attributes['user_id']:Auth::user()->id;//$attributes['user_id'] untuk API
            $t_attributes = [
                'transaction_type_id' => $attributes['transaction_type'],
                'amount' => $attributes['amount'],
                'category_id' => $attributes['wallet_category'],
                'category_type_id' => $attributes['category_type'],
                'notes' => $attributes['notes'],
                'transaction_date' => $attributes['transaction_date'],
                'created_by' => $user_id, 
                'created_at' => Carbon::now(), 
                'updated_by' => $user_id, 
                'updated_at' => Carbon::now(),
                'record_flag' => 'N'
            ];*/

            $user_id = isset($attributes['user_id'])?$attributes['user_id']:Auth::user()->id;//$attributes['user_id'] untuk API
            DB::beginTransaction();
            $activeVersion = ActiveVersion::where('user_id', $user_id)->where('key', 'financialCheckup_cashflowAnalysis')->first();
            //dd($activeVersion);
            /*$module_versions_raw = [];
            if(!is_null($versions)){
                foreach ($versions as $version) {
                    $module_versions_raw[$version->key] = $version->version;
                }
            }*/


            //dd($module_versions_raw);
            //dd($versions->toArray());
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

            //$module_version = !isset($module_versions_raw['financialCheckup_cashflowAnalysis'])?0:$module_versions_raw['financialCheckup_cashflowAnalysis']+1;
            
            $module_version = is_null($activeVersion)?0:$activeVersion->version+1; //dd($module_version);
            $attributes_res = [];
            foreach ($modules as $module => $attributes_arr) { 
                //$module_versions[$module] = !isset($module_versions_raw[$module])?0:$module_versions_raw[$module]+1;
                //dd($module_versions_raw);
                //$module_version = !isset($module_versions_raw[$attributes_arr['post_name']])?0:$module_versions_raw[$attributes_arr['post_name']]+1;
                //dd($module_version);
                 //dd($attributes_safe);
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
                    //if($attributes_arr['post_name'] == 'insurances')
                    //    dd($attributes_safe);
                    $model_name = '\\App\\Models\\'.$attributes_arr['model_name']; 
                    $model = new $model_name;
                    //dd($model::where('user_id', $user_id)->get()->toArray());

                    $model::insert($attributes_safe);
                    /*if($model::insert($attributes_safe)){
                        $this->updateActiveVersion($user_id, $attributes_arr['post_name'], $module_version);//update version
                    }*/

                    $attributes_res[$attributes_arr['post_name']] = $attributes_safe;
                }
            }
            $this->updateActiveVersion($user_id, 'financialCheckup_cashflowAnalysis', $module_version);//update version
            DB::commit();
            //dd($attributes_res);
            //dd($module_versions);
            //dd($version);
            //income
            //$next_version = $module_versions['income']+1;
            /*$income_data = [
                'pendapatan_bulanan' => $attributes['incomes']['pendapatan_bulanan'],
                'pendapatan_lain' => $attributes['incomes']['pendapatan_lain'],
                'version' => $module_versions['income'],
                'created_by' => $user_id, 
                'created_at' => Carbon::now(), 
                'updated_by' => $user_id, 
                'updated_at' => Carbon::now(),
            ];*/
            /*$attributes['incomes'] += [
                'version' => $module_versions['income'],
                'created_by' => $user_id, 
                'created_at' => Carbon::now(), 
                'updated_by' => $user_id, 
                'updated_at' => Carbon::now(),
            ];

            $attributes['expenses'] += [
                'version' => $module_versions['expense'],
                'created_by' => $user_id, 
                'created_at' => Carbon::now(), 
                'updated_by' => $user_id, 
                'updated_at' => Carbon::now(),
            ];

            $attributes['debt_repayments'] += [
                'version' => $module_versions['debt_repayment'],
                'created_by' => $user_id, 
                'created_at' => Carbon::now(), 
                'updated_by' => $user_id, 
                'updated_at' => Carbon::now(),
            ];*/
/*
            if(Income::insert($attributes['incomes'])){//$income_data)){
                //update activeVersion
                $this->updateActiveVersion($user_id, 'incomes', $module_versions['income']);
                /*if($module_versions['income'] === 0){
                    activeVersion::insert([
                        'user_id' => $user_id,
                        'key' => 'income',
                        'version' => $module_versions['income'],
                        'created_by' => $user_id, 
                        'created_at' => Carbon::now(), 
                        'updated_by' => $user_id, 
                        'updated_at' => Carbon::now(),
                    ]);
                }else{
                    activeVersion::where('user_id', $user_id)->where('key', 'income')->update([
                        'version' => $module_versions['income'],
                        'updated_by' => $user_id, 
                        'updated_at' => Carbon::now(),
                    ]);
                }*/
                
            /*}*/

            //++dd(ActiveVersion::where('user_id', $user_id)->get()->toArray());
            //expense

            //debt repayment

            //asset repayment

            //insurance

            



            //dd($t_attributes);

            /*$t_attributes['transaction_type_id'] = $attributes['transaction_type'];
            $t_attributes['amount'] = $attributes['amount'];
            $t_attributes['wallet_category'] = $attributes['wallet_category'];
            $t_attributes['notes'] = $attributes['notes'];
            $t_attributes['transaction_date'] = $attributes['transaction_date'];*/
            //$taxonomy = $this->wallet->create($t_attributes);
            return $attributes_res;
        }
        throw new ValidationException('Financial checkup cashflow analysis validation failed', $this->getErrors());
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
}
