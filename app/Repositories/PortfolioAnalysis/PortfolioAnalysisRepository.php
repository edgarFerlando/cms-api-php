<?php namespace App\Repositories\PortfolioAnalysis;

use App\Models\Income;
use App\Models\Expense;
use App\Models\DebtRepayment;
use App\Models\AssetRepayment;
use App\Models\Insurance;
use App\Models\ActiveVersion;
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
use App\Repositories\PortfolioAnalysis\PortfolioAnalysisInterface;
use Input;
use DB;

//use App\Taxonomy;
//use App\Repositories\Taxonomy\TaxonomyRepository;


class PortfolioAnalysisRepository extends RepositoryAbstract implements PortfolioAnalysisInterface, CrudableInterface {

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
    public function __construct(){//Income $income) {

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
        if(Input::has('asset_repayments_paid') && count(Input::get('asset_repayments_paid')) > 0){
            foreach(Input::get('asset_repayments_paid') as $idx => $asset_repayment_paid){
                $_rules['asset_repayments_paid.'.$idx.'.asset_repayment_id'] = 'required';
                $_rules['asset_repayments_paid.'.$idx.'.cicilan_terbayar'] = 'required';

                $setAttributeNames['asset_repayments_paid.'.$idx.'.asset_repayment_id'] = 'Nama #'.($idx+1);
                $setAttributeNames['asset_repayments_paid.'.$idx.'.cicilan_terbayar'] = 'Cicilan terbayar #'.($idx+1);
            }
        }

        if(Input::has('asset_repayments_paidoff') && count(Input::get('asset_repayments_paidoff')) > 0){
            foreach(Input::get('asset_repayments_paidoff') as $idx => $asset_repayment_paid){
                $_rules['asset_repayments_paidoff.'.$idx.'.taxo_wallet_id'] = 'required';
                $_rules['asset_repayments_paidoff.'.$idx.'.nama'] = 'required';
                $_rules['asset_repayments_paidoff.'.$idx.'.nilai_aset'] = 'required';

                $setAttributeNames['asset_repayments_paidoff.'.$idx.'.taxo_wallet_id'] = 'Nama #'.($idx+1);
                $setAttributeNames['asset_repayments_paidoff.'.$idx.'.nama'] = 'Cicilan terbayar #'.($idx+1);
                $setAttributeNames['asset_repayments_paidoff.'.$idx.'.nilai_aset'] = 'Nilai aset #'.($idx+1);
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
        $version = !isset($attributes['version'])?'last':$attributes['version'];//harus string
        $modules_shown = !isset($attributes['modules'])?[]:$attributes['modules'];
        $transaction_date_until = !isset($attributes['transaction_date_until'])?Carbon::today()->format('Y-m-d'):Carbon::parse($attributes['transaction_date_until'])->format('Y-m-d'); 
        $status = !isset($attributes['status'])?'approved':$attributes['status'];

        //dd($modules_shown);

        switch ($version) {
            case 'last' :
                //$activeVersion = ActiveVersion::where('user_id', $user_id)->where('key', 'financialCheckup_portfolioAnalysis')->first();
                //$module_version = is_null($activeVersion)?'':$activeVersion->version;

                $transaction_date_until_Ym = Carbon::parse($transaction_date_until)->format('Y-m');
                $dmY_api = Carbon::createFromFormat('Y-m-d' , $transaction_date_until_Ym.'-01');
                $start_date = $dmY_api->format('Y-m-d').' 00:00:00';//awal bulan dari api
                $end_date_check_version = Carbon::today()->format('Y-m-d') == $transaction_date_until?Carbon::now()->format('Y-m-d H:i:s'):$transaction_date_until.' 23:59:59';
                $maxApprovedActiveVersion = ActiveVersionDetail::where('active_version_key', 'financialCheckup_portfolioAnalysis')
                ->where('user_id', $user_id)
                ->where('status', $status)->max('version');

                //where('created_at', '>=', $start_date)->where('created_at', '<=', $end_date_check_version)
                //dicomment dulu dari Achmad Andriansyah

                $module_version = is_null($maxApprovedActiveVersion)?'':$maxApprovedActiveVersion;
            break;
            default:
                $module_version = $version;
            break;
        }

        if($module_version === '')
            return null;

        $all_with = [
            'asset_repayments_paid' => function($query) use ($module_version) {
                $query->with(['asset_repayment' => function($query2){
                    //return $query2;
                    $query2->select('taxonomies.title as taxo_wallet_name', 'taxonomies.slug as taxo_wallet_slug', 'taxonomies.image as taxo_wallet_ico', 'asset_repayments.id', 'asset_repayments.user_id', 'asset_repayments.taxo_wallet_id', 'asset_repayments.nama', 'asset_repayments.cicilan_perbulan', 'asset_repayments.sisa_durasi', 'asset_repayments.catatan')
                    ->join('taxonomies', 'taxonomies.id', '=', 'asset_repayments.taxo_wallet_id', 'left');
                }])
                ->select('asset_repayments.nama as asset_repayment_nama', 'asset_repayments_paid.*')
                ->where('asset_repayments_paid.version', $module_version)
                ->join('asset_repayments', 'asset_repayments.id', '=', 'asset_repayments_paid.asset_repayment_id', 'left');
            },
            'asset_repayments_paidoff' => function($query) use ($module_version) {
                $query->select('taxonomies.title as taxo_wallet_name', 'taxonomies.slug as taxo_wallet_slug', 'taxonomies.image as taxo_wallet_ico', 'asset_repayments_paidoff.*')
                ->where('version', $module_version)
                ->join('taxonomies', 'taxonomies.id', '=', 'asset_repayments_paidoff.taxo_wallet_id', 'left');
            }
        ];

        if(!empty($modules_shown)){
            foreach (array_diff(array_keys($all_with), $modules_shown) as $module_shown) {
                unset($all_with[$module_shown]);
            }
        }
        
        return User::select('id','id as user_id')->with($all_with)->where('id', $user_id)->first();
    }

    public function create($attributes) {
        if($this->isValid($attributes)) { 
            $user_id = isset($attributes['user_id'])?$attributes['user_id']:Auth::user()->id;//$attributes['user_id'] untuk API
            DB::beginTransaction();
            $activeVersion = ActiveVersion::where('user_id', $user_id)->where('key', 'financialCheckup_portfolioAnalysis')->first();
            $modules = [
                'asset_repayment_paid' => [
                    'post_name' => 'asset_repayments_paid',
                    'model_name' => 'AssetRepaymentPaid'
                ],
                'asset_repayment_paidoff' => [
                    'post_name' => 'asset_repayments_paidoff',
                    'model_name' => 'AssetRepaymentPaidoff'
                ]
            ];
            $module_version = is_null($activeVersion)?0:$activeVersion->version+1;
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
            }
            $this->updateActiveVersion($user_id, 'financialCheckup_portfolioAnalysis', $module_version);//update version

            //save active version details
            activeVersionDetail::insert([
                'user_id' => $user_id, 
                'version' => $module_version,
                'status' => 'draft',
                'active_version_key' => 'financialCheckup_portfolioAnalysis',
                'created_by' => $user_id, 
                'created_at' => Carbon::now(), 
                'updated_by' => $user_id, 
                'updated_at' => Carbon::now()
            ]);

            DB::commit();
            return $attributes_res;
        }
        throw new ValidationException('Financial checkup portfolio analysis validation failed', $this->getErrors());
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
        $activeVersion = ActiveVersion::where('user_id', $user_id)->where('key', 'financialCheckup_portfolioAnalysis')->first();
        $module_version = is_null($activeVersion)?'':$activeVersion->version;
        if($module_version === '')
            return 1;
        //dd($module_version);
        $need_approval = ActiveVersionDetail::where('user_id', $user_id)
        ->where('version', $module_version)
        ->where('active_version_key', 'financialCheckup_portfolioAnalysis')
        ->where('status', 'draft')->count();
        return $need_approval>0?0:1;
    }
}
