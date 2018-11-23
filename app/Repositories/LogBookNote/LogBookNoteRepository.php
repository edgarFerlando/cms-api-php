<?php namespace App\Repositories\LogBookNote;

use App\Models\LogBookNote;
use App\Models\IncomeSimulation;
use App\Models\ActiveVersion;
use App\Models\ActiveVersionPlan;
use App\Models\PlanProtection;
use App\Models\InsuranceCoverage;
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
use App\Repositories\LogBookNote\LogBookNoteAInterface;
use Input;
use DB;

//use App\Http\Controllers\API\PlanController;

use App\Taxonomy;
//use App\Repositories\Taxonomy\TaxonomyRepository;


class LogBookNoteRepository extends RepositoryAbstract implements LogBookNoteInterface, CrudableInterface {

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
        $_rules['note'] = 'required';

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
        $version = !isset($attributes['version'])?'last':$attributes['version'];
        //$version_cashflow_analysis = !isset($attributes['version_cashflow_analysis'])?'last':$attributes['version_cashflow_analysis'];
        $modules_shown = !isset($attributes['modules'])?[]:$attributes['modules'];
        //dd($modules_shown);
        switch ($version) {
            case 'last' :
                    $activeVersion = ActiveVersion::where('user_id', $user_id)->where('key', 'logBook_note')->first();
                    $module_version = is_null($activeVersion)?'':$activeVersion->version;

                    //version_cashflow_analysis
                    //$activeVersion_ca = ActiveVersion::where('user_id', $user_id)->where('key', 'financialCheckup_cashflowAnalysis')->first();
                    //$module_version_ca = is_null($activeVersion_ca)?'':$activeVersion_ca->version;
                
                break;
            default:
                    $module_version = $version;
                    //$module_version_ca = $version_cashflow_analysis;
                break;
        }

        if($module_version === '')
            //return [];jangan return ini , karena plananalysis dalam keadaan kosong
            $module_version = 0;//jangan ubah line 223

        //if($module_version_ca === '')
        //    $module_version_ca = 0;

        $all_with = [
            'note' => function($query) use ($module_version) {
                $query->where('version', $module_version);
            },
            'notes' => function($query) {
                $query->orderBy('created_at', 'desc');
                //$query->where('version', $module_version);
            }
        ];
        //dd(array_diff(array_keys($all_with), $modules_shown));
        if(!empty($modules_shown)){
            foreach (array_diff(array_keys($all_with), $modules_shown) as $module_shown) {
                unset($all_with[$module_shown]);
            }
        }
       
        $res = [];

        /**
         * Tambah Soft Delete untuk loogbook
         */
        // $data_raw = User::select('users.id as user_id')->with($all_with)->where('id', $user_id)->first();
        $data_raw = User::select('users.id as user_id')->with($all_with)->where('id', $user_id)->where('record_flag', '!=', 'D')->first();
        
        if(!is_null($data_raw)){
            if(in_array('note', array_keys($all_with)))
                $res['note'] = $data_raw['note'];

            if(in_array('notes', array_keys($all_with)))
                $res['notes'] = $data_raw['notes'];

        } else {
            return [];
        }

        return $res;
    }

    public function create($attributes) {

        if($this->isValid($attributes)) { 
            DB::beginTransaction();
            $activeVersionKey = 'logBook_note';
            $user_id = isset($attributes['user_id'])?$attributes['user_id']:Auth::user()->id;
            
            $activeVersion = ActiveVersion::where('user_id', $user_id)->where('key', $activeVersionKey)->first();
            $module_version = is_null($activeVersion)?0:$activeVersion->version+1; 
            //$note_calc = '';
            $log_book_note = LogBookNote::create([
                'user_id' => $user_id,
                'version' => $module_version,
                'note' => $attributes['note'],
                'created_by' => $user_id,
                'created_at' => Carbon::now(),
                'updated_by' => $user_id,
                'updated_at' => Carbon::now()
            ]);
            $this->updateActiveVersion($user_id, $activeVersionKey, $module_version);//update version
            DB::commit();
            return $this->showByVersion([
                'user_id' => $user_id
            ]);
        }
        throw new ValidationException('Log book Note validation failed', $this->getErrors());
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
