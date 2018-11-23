<?php namespace App\Repositories\WalletTransaction;

use App\Models\WalletTransaction;

use Auth;
use Config;
use Response;
use Illuminate\Support\Str;
use App\Repositories\RepositoryAbstract;
use App\Repositories\CrudableInterface as CrudableInterface;
use App\Repositories\RepositoryInterface as RepositoryInterface;
use App\Repositories\WalletTransaction\WalletTransactionInterface;
use App\Exceptions\Validation\ValidationException;
use App\Repositories\AbstractValidator as Validator;
//use LaravelLocalization;
use Notification;
use App\User;

use Validator as Valid;
use Carbon\Carbon;
use DB;
use App\Models\ActiveVersion;
use App\Models\Expense;
use App\Models\ActiveVersionDetail;

//use App\Taxonomy;
//use App\Repositories\Taxonomy\TaxonomyRepository;


class WalletTransactionRepository extends RepositoryAbstract implements WalletTransactionInterface, CrudableInterface {

    protected $perPage;
    protected $walletTransaction;
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
    public function __construct(WalletTransaction $WalletTransaction) {

        $config = Config::get('holiday');
        $this->perPage = $config['per_page'];
        $this->walletTransaction = $WalletTransaction;
        //$this->taxonomy = new TaxonomyRepository(new Taxonomy);

        $rules_n_attributeNames = $this->rules();
        self::$rules = $rules_n_attributeNames['rules'];
        self::$attributeNames = $rules_n_attributeNames['attributeNames'];
    }

    public function rules(){
        $_rules = array();
        $setAttributeNames = array();
        
        $_rules['user_id'] = 'required';
        $_rules['taxo_wallet_module_title'] = 'required';//|exists:users,id';
        $_rules['detail_id'] = 'required';//|exists:users,id';
        $_rules['amount'] = 'required';
        $_rules['transaction_date'] = 'required';

        //$setAttributeNames['amount'] = trans('app.amount');
        //$setAttributeNames['walletTransaction_category'] = trans('app.walletTransaction_category');
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
        return $this->walletTransaction->orderBy('created_by', 'DESC')->get();
    }

    public function paginate($page = 1, $limit = 10, $all = false) {

        $result = new \StdClass;
        $result->page = $page;
        $result->limit = $limit;
        $result->totalItems = 0;
        $result->items = array();

        $query = $this->walletTransaction->select('walletTransaction.*', 'uc.name as created_by_name', 'uu.name as updated_by_name');
        $query->orderBy('created_by', 'DESC');
        $query->with(['category', 'transaction_type', 'category_type']);
        //dd($query);
        if(!$all) {
            $query->where('is_published', 1);
        }

        //$query->join('users as c', 'c.id', '=', 'cfp_clients.client_id', 'left');
        //$query->join('users as cfp', 'cfp.id', '=', 'cfp_clients.cfp_id', 'left');
        $query->join('users as uc', 'uc.id', '=', 'walletTransaction.created_by', 'left');
        $query->join('users as uu', 'uu.id', '=', 'walletTransaction.updated_by', 'left');

        //\DB::enableQueryLog();

        $transactions = $query->skip($limit * ($page - 1))->take($limit)->get();

        //dd(\DB::getQueryLog());
        //dd($categoryCodes);

        $result->totalItems = $this->totalTransactions($all);
        $result->items = $transactions->all();
        /*dd($result);
        foreach ($result->items as $key => $item) {
            $result->items[$key]->WalletTransactions = $item;

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

    public function allBy($filter = array()){
        $query = $this->walletTransaction
        ->select('wallet_transactions.*', 'uc.name as created_by_name', 'uu.name as updated_by_name')
        ->with(['detail' => function($q){
            $q->select('taxonomies.title as taxo_wallet_name', 'taxonomies.image as taxo_wallet_ico', 'expenses.*')
            ->join('taxonomies', 'taxonomies.id', '=', 'expenses.taxo_wallet_id', 'left');
        }])
        ->orderBy('created_at', 'DESC');

        if (!isset($filter['user_id']))
        { 
            return [];
        }
        if(is_array($filter)){
            foreach($filter as $ff => $term){
                if(trim($term) != ''){
                    switch ($ff) {
                        case 'record_flag_is_not':
                            $query->where('wallet_transactions.record_flag', '!=', $term);
                        break;
                        case 'transaction_date':
                            $query->where('transaction_date', $term);
                        break;
                        case 'detail_id':
                            $query->where('detail_id', $term);
                        break;
                        case 'taxo_wallet_module_title':
                            $query->where('taxo_wallet_module_title', $term);
                        break;
                        case 'user_id':
                            $query->where('user_id', $term);
                        break;
                        case 'cashflow_analysis_version':
                            $query->where('cashflow_analysis_version', $term);
                        break;
                    }
                }
            }
        }

        if (!isset($filter['transaction_date']))
        { //seharusnya diset dengan tanggal cut off masing" client
			//@3apr2018 penambahan <= dari sebelumnya = 
            $query->where('transaction_date', '<=', Carbon::now()->endOfMonth()->format('Y-m-d'));
        }

        $query->join('users as uc', 'uc.id', '=', 'wallet_transactions.created_by', 'left');
        $query->join('users as uu', 'uu.id', '=', 'wallet_transactions.updated_by', 'left');
        //$query->join('expenses as e', 'e.id', '=', 'wallet_transactions.detail_id', 'left');
        //$query->join('taxonomy as t', 't.id', '=', 'expenses.taxo_wallet_id', 'left');
        return $query->get();
    }

    public function find($id) {
        return $this->walletTransaction->find($id);
    }

    public function findCfpByClientEmail($client_email) {
        return $this->walletTransaction->whereHas('cfp', function($q) use ($client_email){
            $q->whereRaw('LOWER(email) = ?', [ strtolower($client_email)]);
        })->get();
    }

    public function balance($attributes){
        $user_id = $attributes['user_id'];

        //$transaction_date_until = Carbon::parse($attributes['transaction_date_until'])->format('Y-m-d'); 
        $transaction_date_until = !isset($attributes['transaction_date_until'])?Carbon::today()->format('Y-m-d'):Carbon::parse($attributes['transaction_date_until'])->format('Y-m-d'); 
        $status = !isset($attributes['status'])?'approved':$attributes['status'];

        $transaction_date_until_Ym = Carbon::parse($transaction_date_until)->format('Y-m');
        $dmY_api = Carbon::createFromFormat('Y-m-d' , $transaction_date_until_Ym.'-01');
        $start_date = $dmY_api->startOfMonth()->format('Y-m-d').' 00:00:00';//awal bulan dari api
        
        //$end_date = $dmY_api->endOfMonth()->format('Y-m-d').' 23:59:59';//akhir bulan dari api
        //$start_date = $transaction_date_until.' 00:00:00';//awal bulan dari api
        //$end_date = $transaction_date_until.' 23:59:59';//akhir hari dari api
       
        $end_date = Carbon::today()->format('Y-m-d') == $transaction_date_until?Carbon::parse($transaction_date_until)->endOfMonth()->format('Y-m-d H:i:s'):$transaction_date_until.' 23:59:59';
        //dd($end_date);
        //dd($end_date_check_version);
        //++AND transaction_date >= \''.$start_date.'\' AND transaction_date <= \''.$end_date.'\' 

        //get active version sesuai dengan bulannya
        //berarti get yg pada bulan itu dan versinya mentok pada bulan tersebut
        //$maxApprovedActiveVersion = ActiveVersionDetail::where(\DB::raw('to_char(created_at, \'YYYY-MM\')'), $transaction_date_until_Ym)->where('status', 'approved')->get();
        //\DB::enableQueryLog();
        /*$maxApprovedActiveVersion = ActiveVersionDetail::where('created_at', '>=', $start_date)
        ->where('created_at', '<=', $end_date_check_version)
        ->where('active_version_key', 'financialCheckup_cashflowAnalysis')
        ->where('user_id', $user_id)
        ->where('status', 'approved')->max('version');*/
        //dd(\DB::getQueryLog());
        //dd($maxApprovedActiveVersion);

        //$activeVersion = ActiveVersion::where('user_id', $user_id)->where('key', 'financialCheckup_cashflowAnalysis')->first();
        //$module_version = is_null($activeVersion)?'':$activeVersion->version;
        //++$module_version = is_null($maxApprovedActiveVersion)?'':$maxApprovedActiveVersion;
        //dd($module_version);

        $latest_module_version = $module_version = getModuleVersion([
            'user_id' => $user_id,
            'status' => $status,
            'active_version_key' => 'financialCheckup_cashflowAnalysis'
        ]);

        $module_version = getModuleVersion([
            'user_id' => $user_id,
            'transaction_date_until' => $transaction_date_until, //sample '2018-02-05',//
            'status' => $status,
            'active_version_key' => 'financialCheckup_cashflowAnalysis'
        ]);
         //dd($module_version);
        if($module_version === '')
            return [];
        


        // echo $start_date;
        // echo '<br />';
        // echo $end_date;exit;
        //echo $module_version; exit;
        return DB::select(DB::raw('
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
        '));

        /*
        return DB::select(DB::raw('select expenses.*, 
            (select sum(amount) from wallet_transactions where detail_id = expenses.id group by detail_id) as pemakaian_anggaran_bulan_ini, 
            (expenses.anggaran_perbulan - pemakaian_anggaran_bulan_ini) as sisa_anggaran_bulan_ini
            from expenses where user_id = '.$user_id.' and 
            version='.$module_version)
        );*/

/*
        dd(()$expenses);
        if(!is_null($expenses)){
            foreach ($expenses as $expense) {
                # code...
            }
        }
        dd($expenses);*/
        //$query->select('taxonomies.title as taxo_wallet_name', 'taxonomies.image as taxo_wallet_ico', 'expenses.*')
        //        ->where('version', $module_version)
        //        ->join('taxonomies', 'taxonomies.id', '=', 'expenses.taxo_wallet_id', 'left');

        //return User::select('id','id as user_id')->with($all_with)->where('id', $user_id)->first();
    }

    public function create($attributes) {

        if($this->isValid($attributes)) {
            DB::beginTransaction();
            $user_id = isset($attributes['user_id'])?$attributes['user_id']:Auth::user()->id;//$attributes['user_id'] untuk API
            
            $activeVersion = ActiveVersion::where('user_id', $user_id)->where('key', 'financialCheckup_cashflowAnalysis')->first();
            $module_version = is_null($activeVersion)?'':$activeVersion->version;

            
            $t_attributes = [
                'user_id' => $user_id,
                'taxo_wallet_module_title' => $attributes['taxo_wallet_module_title'],
                'detail_id' => $attributes['detail_id'],
                'amount' => $attributes['amount'],
                'note' => $attributes['note'],
                'transaction_date' => $attributes['transaction_date'],
                'cashflow_analysis_version' => $module_version,
                'created_by' => $user_id, 
                'created_at' => Carbon::now(), 
                'updated_by' => $user_id, 
                'updated_at' => Carbon::now(),
                'record_flag' => 'N'
            ];// dd($t_attributes);
            $walletTransaction = $this->walletTransaction->create($t_attributes);
            DB::commit();
            return $walletTransaction;
        }
        throw new ValidationException('Wallet Transaction validation failed', $this->getErrors());
    }

    public function update($id, $attributes) {

        if($this->isValid($attributes)) {
            DB::beginTransaction();
            $user_id = isset($attributes['user_id'])?$attributes['user_id']:Auth::user()->id;//$attributes['user_id'] untuk API
            $this->walletTransaction = $this->find($id);
            $t_attributes = [
                'taxo_wallet_module_title' => $attributes['taxo_wallet_module_title'],
                'detail_id' => $attributes['detail_id'],
                'amount' => $attributes['amount'],
                'note' => $attributes['note'],
                'transaction_date' => $attributes['transaction_date'],
                'updated_by' => $user_id, 
                'updated_at' => Carbon::now(),
                'record_flag' => 'u'
            ];// dd($t_attributes);
            $walletTransaction = $this->walletTransaction->fill($t_attributes)->save();
            DB::commit();
            return $this->find($id)->toArray();
        }
        throw new ValidationException('Wallet Transaction validation failed', $this->getErrors());
    }

    public function update_junk($id, $attributes) {

        if($this->isValid($attributes)) {
            $user_id = isset($attributes['user_id'])?$attributes['user_id']:Auth::user()->id;//$attributes['user_id'] untuk API
            $this->transaction = $this->find($id);
            $t_attributes = [
                'transaction_type_id' => $attributes['transaction_type'],
                'amount' => $attributes['amount'],
                'category_id' => $attributes['walletTransaction_category'],
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
        throw new ValidationException('WalletTransaction validation failed', $this->getErrors());
    }

    public function delete($id) {
        $WalletTransaction = $this->walletTransaction->findOrFail($id);
        $WalletTransaction->delete();
    }

    protected function totalTransactions($all = false) {
        return $this->walletTransaction->count();
    }
}
