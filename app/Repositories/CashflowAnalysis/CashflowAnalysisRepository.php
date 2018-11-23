<?php namespace App\Repositories\CashflowAnalysis;

use App\Models\Income;
use App\Models\Expense;
use App\Models\DebtRepayment;
use App\Models\AssetRepayment;
use App\Models\Insurance;
use App\Models\ActiveVersion;
use App\Models\PlanBalance;
use App\Models\ActiveVersionDetail;
use App\Models\ActiveVersionPlanDetail;
use App\Models\WalletTransaction;
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
use App\Repositories\CashflowAnalysis\CashflowAnalysisInterface;
use Input;
use DB;

use App\Models\PlanA;
use App\Repositories\PlanA\PlanARepository;
use App\Models\PlanB;
use App\Repositories\PlanB\PlanBRepository;

use App\Models\PortfolioAnalysis;
use App\Repositories\PortfolioAnalysis\PortfolioAnalysisRepository;

use App\Repositories\PlanAnalysis\PlanAnalysisRepository;
use App\Models\PlanAnalysisActivated;

use App\Models\AssetRepaymentPaid;

use App\Taxonomy;
use App\Repositories\Taxonomy\TaxonomyRepository;
use App\Models\CfpClient;
use App\Models\ClientActionPlanDetail;
use App\Models\ClientActionPlan;
use App\Repositories\Cycle\CycleRepository;
use App\Models\Cycle;


class CashflowAnalysisRepository extends RepositoryAbstract implements CashflowAnalysisInterface, CrudableInterface {

    protected $perPage;
    //protected $income;
    /**
     * Rules
     *
     * @var array
     */
    protected static $rules;
    protected static $attributeNames;
    //protected $cashflowAnalysis;
    protected $portfolioAnalysis;
    protected $planA;
    protected $planB;
    protected $taxonomy;

    /**
     * @param ProductAttribute $productAttribute
     */
    public function __construct(){

        $rules_n_attributeNames = $this->rules();
        self::$rules = $rules_n_attributeNames['rules'];
        self::$attributeNames = $rules_n_attributeNames['attributeNames'];
        $this->portfolioAnalysis = new PortfolioAnalysisRepository();//modelnya pakai sembarang aja yang penting ada parameter nya
        $this->planAnalysis = new PlanAnalysisRepository();
        $this->planA = new PlanARepository(new PlanA);
        $this->planB = new PlanBRepository(new PlanB);
        $this->taxonomy = new TaxonomyRepository(new Taxonomy);
        $this->cycle = new CycleRepository(new Cycle);
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
                // $_rules['insurances.'.$idx.'.taxo_insurance_type_id'] = 'required';
                $_rules['insurances.'.$idx.'.nilai_pertanggungan'] = 'required';

                $setAttributeNames['insurances.'.$idx.'.taxo_wallet_id'] = 'Nama #'.($idx+1);
                $setAttributeNames['insurances.'.$idx.'.no_polis'] = 'No polis #'.($idx+1);
                $setAttributeNames['insurances.'.$idx.'.premi_perbulan'] = 'Premi perbulan #'.($idx+1);
                // $setAttributeNames['insurances.'.$idx.'.taxo_insurance_type_id'] = 'Jenis #'.($idx+1);
                $setAttributeNames['insurances.'.$idx.'.nilai_pertanggungan'] = 'Nilai pertanggungan #'.($idx+1);
            }
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
        $version = !isset($attributes['version'])?'last':$attributes['version'];
        $modules_shown = !isset($attributes['modules'])?[]:$attributes['modules'];

        $transaction_date_until = !isset($attributes['transaction_date_until'])?Carbon::today()->format('Y-m-d'):returnValidDate($attributes['transaction_date_until']);

        $status = !isset($attributes['status'])?'approved':$attributes['status'];
        
        $client_cutoff_date = User::where('id', $user_id)->pluck('cutoff_date');
        $client_cutoff_date = 31;
        switch ($version) {
            case 'last' :
                $latest_module_version = ActiveVersionDetail::where('active_version_key', 'financialCheckup_cashflowAnalysis')
                    ->where('user_id', $user_id)
                    ->where('status', $status)->max('version');//langsung dapatkan yang terakhir saja

                if($latest_module_version === '')
                    return null;//tidak ditemukan version cashflow analysis terakhir
                /*$latest_module_version = getModuleVersion([
                    'user_id' => $user_id,
                    'status' => $status,
                    'active_version_key' => 'financialCheckup_cashflowAnalysis'
                ]);*/
                    // $datacheck = [
                    //     'user_id' => $user_id,
                    //     'transaction_date_until' => $transaction_date_until, //sample '2018-02-05',//
                    //     'status' => $status,
                    //     'active_version_key' => 'financialCheckup_cashflowAnalysis',
                    //     'client_cutoff_date' => $client_cutoff_date
                    // ];
                $module_version = getModuleVersion([
                    'user_id' => $user_id,
                    'transaction_date_until' => $transaction_date_until, //sample '2018-02-05',//
                    'status' => $status,
                    'active_version_key' => 'financialCheckup_cashflowAnalysis',
                    'client_cutoff_date' => $client_cutoff_date
                ]);

                //dd($datacheck);
                //dd($client_cutoff_date);
                //dd($module_version);
                
                //dd('now : '.$module_version.',  latest : '.$latest_module_version);
            break;
            default:
                $latest_module_version = $version;
                $module_version = $version;
            break;
        }

        if($module_version === '')
            return null;

        $all_with = [
            'incomes' => function($query) use ($module_version) {
                $query->select('*', DB::raw("'https://mylife.fundtastic.co.id/uploads/wallet_icon/icon_pendapatan_bulanan.png' as taxo_wallet_ico") )->where('version', $module_version);
            },
            'expenses' => function($query) use ($module_version, $latest_module_version, $user_id) {
                $query->select('taxonomies.title as taxo_wallet_name', 'taxonomies.slug as taxo_wallet_slug', 'taxonomies.image as taxo_wallet_ico', 'taxonomies.color as taxo_wallet_color', 'taxonomies.description as taxo_wallet_description','expenses.*')
                ->where('version', $latest_module_version)
                ->join('taxonomies', 'taxonomies.id', '=', 'expenses.taxo_wallet_id', 'left');

                //if $module_version is different from $latest_module_version then load expenses from the latest module version 
                //but make sure that the taxo wallet id loaded are the same as taxo wallet id on module version
                if($module_version != $latest_module_version){
                    $query->whereRaw('expenses.taxo_wallet_id in ( select taxo_wallet_id from expenses where user_id = \''.$user_id.'\' AND version=\''.$module_version.'\' )');
                }
            },
            'debt_repayments' => function($query) use ($module_version) {
                $query->select('taxonomies.title as taxo_wallet_name', 'taxonomies.slug as taxo_wallet_slug', 'taxonomies.image as taxo_wallet_ico', 'taxonomies.color as taxo_wallet_color', 'taxonomies.description as taxo_wallet_description','debt_repayments.*')
                ->where('version', $module_version)
                ->join('taxonomies', 'taxonomies.id', '=', 'debt_repayments.taxo_wallet_id', 'left');
            },
            'asset_repayments' => function($query) use ($module_version) {
                $query->select('taxonomies.title as taxo_wallet_name', 'taxonomies.slug as taxo_wallet_slug', 'taxonomies.image as taxo_wallet_ico', 'taxonomies.color as taxo_wallet_color', 'taxonomies.description as taxo_wallet_description', 'asset_repayments.*')
                ->with(['plan_analysis_activated'])
                ->where('version', $module_version)
                ->join('taxonomies', 'taxonomies.id', '=', 'asset_repayments.taxo_wallet_id', 'left');
            },
            'insurances' => function($query) use ($module_version) {
                $query->select('t.title as taxo_wallet_name', 't.image as taxo_wallet_ico', 't.color as taxo_wallet_color', 't2.title as taxo_insurance_type_name', 't.description as taxo_wallet_description','insurances.*')
                ->where('version', $module_version)
                ->join('taxonomies as t', 't.id', '=', 'insurances.taxo_wallet_id', 'left')
                ->join('taxonomies as t2', 't2.id', '=', 'insurances.taxo_insurance_type_id', 'left');
            },
            'plan_balances' => function($query) use ($module_version) {
                $query->select('plan_balances.user_id','plan_balances.version', 'plan_balances.name', 'plan_balances.balance', 'plan_balances.balance_datetime', DB::raw("'https://mylife.fundtastic.co.id/uploads/wallet_icon/icon_free_cashflow_tahunan.png' as taxo_wallet_ico_freecashflow_tahunan, 'https://mylife.fundtastic.co.id/uploads/wallet_icon/icon_free_cashflow_bulanan.png' as taxo_wallet_ico_freecashflow_bulanan") )
                ->where('version', $module_version);
            },
        ];
        if(!empty($modules_shown)){ 
            foreach (array_diff(array_keys($all_with), $modules_shown) as $module_shown) {
                unset($all_with[$module_shown]);
            }
        }
        return User::select('id','id as user_id')->with($all_with)->where('id', $user_id)->first();
    }

    public function showByVersionV2($attributes){
        $user_id = $attributes['user_id'];
        $expense_id = $attributes['expense_id'];
        $version = !isset($attributes['version'])?'last':$attributes['version'];
        $modules_shown = !isset($attributes['modules'])?[]:$attributes['modules'];

        $transaction_date_until = !isset($attributes['transaction_date_until'])?Carbon::today()->format('Y-m-d'):returnValidDate($attributes['transaction_date_until']);

        $status = !isset($attributes['status'])?'approved':$attributes['status'];
        
        $client_cutoff_date = User::where('id', $user_id)->pluck('cutoff_date');
        $client_cutoff_date = 31;
        switch ($version) {
            case 'last' :
                $latest_module_version = ActiveVersionDetail::where('active_version_key', 'financialCheckup_cashflowAnalysis')
                    ->where('user_id', $user_id)
                    ->where('status', $status)->max('version');//langsung dapatkan yang terakhir saja

                if($latest_module_version === '')
                    return null;
                    
                $module_version = getModuleVersion([
                    'user_id' => $user_id,
                    'transaction_date_until' => $transaction_date_until, //sample '2018-02-05',//
                    'status' => $status,
                    'active_version_key' => 'financialCheckup_cashflowAnalysis',
                    'client_cutoff_date' => $client_cutoff_date
                ]);
            break;
            default:
                $latest_module_version = $version;
                $module_version = $version;
            break;
        }

        if($module_version === '')
            return null;

        $all_with = [
            'incomes' => function($query) use ($module_version) {
                $query->select('*', DB::raw("'https://mylife.fundtastic.co.id/uploads/wallet_icon/icon_pendapatan_bulanan.png' as taxo_wallet_ico") )->where('version', $module_version);
            },
            'expenses' => function($query) use ($module_version, $latest_module_version, $user_id, $expense_id) {
                $query->select('taxonomies.title as taxo_wallet_name', 'taxonomies.slug as taxo_wallet_slug', 'taxonomies.image as taxo_wallet_ico', 'taxonomies.color as taxo_wallet_color', 'taxonomies.description as taxo_wallet_description','expenses.*')
                ->where('version', $latest_module_version)
                ->where('expenses.taxo_wallet_id', $expense_id)
                ->join('taxonomies', 'taxonomies.id', '=', 'expenses.taxo_wallet_id', 'left');

                //if $module_version is different from $latest_module_version then load expenses from the latest module version 
                //but make sure that the taxo wallet id loaded are the same as taxo wallet id on module version
                if($module_version != $latest_module_version){
                    $query->whereRaw('expenses.taxo_wallet_id in ( select taxo_wallet_id from expenses where user_id = \''.$user_id.'\' AND version=\''.$module_version.'\' )');
                }
            },
            'debt_repayments' => function($query) use ($module_version,$expense_id) {
                $query->select('taxonomies.title as taxo_wallet_name', 'taxonomies.image as taxo_wallet_ico', 'taxonomies.color as taxo_wallet_color', 'taxonomies.description as taxo_wallet_description','debt_repayments.*')
                ->where('version', $module_version)
                ->where('debt_repayments.taxo_wallet_id', $expense_id)
                ->join('taxonomies', 'taxonomies.id', '=', 'debt_repayments.taxo_wallet_id', 'left');
            },
            'asset_repayments' => function($query) use ($module_version,$expense_id) {
                $query->select('taxonomies.title as taxo_wallet_name', 'taxonomies.slug as taxo_wallet_slug', 'taxonomies.image as taxo_wallet_ico', 'taxonomies.color as taxo_wallet_color', 'taxonomies.description as taxo_wallet_description', 'asset_repayments.*')
                ->with(['plan_analysis_activated'])
                ->where('version', $module_version)
                ->where('asset_repayments.taxo_wallet_id', $expense_id)
                ->join('taxonomies', 'taxonomies.id', '=', 'asset_repayments.taxo_wallet_id', 'left');
            },
            'insurances' => function($query) use ($module_version,$expense_id) {
                $query->select('t.title as taxo_wallet_name', 't.image as taxo_wallet_ico', 't.color as taxo_wallet_color', 't2.title as taxo_insurance_type_name', 't.description as taxo_wallet_description','insurances.*')
                ->where('version', $module_version)
                ->where('insurances.id', $expense_id)
                ->join('taxonomies as t', 't.id', '=', 'insurances.taxo_wallet_id', 'left')
                ->join('taxonomies as t2', 't2.id', '=', 'insurances.taxo_insurance_type_id', 'left');
            },
            'plan_balances' => function($query) use ($module_version) {
                $query->select('plan_balances.user_id','plan_balances.version', 'plan_balances.name', 'plan_balances.balance', 'plan_balances.balance_datetime', DB::raw("'https://mylife.fundtastic.co.id/uploads/wallet_icon/icon_free_cashflow_tahunan.png' as taxo_wallet_ico_freecashflow_tahunan, 'https://mylife.fundtastic.co.id/uploads/wallet_icon/icon_free_cashflow_bulanan.png' as taxo_wallet_ico_freecashflow_bulanan") )
                ->where('version', $module_version);
            },
        ];
        if(!empty($modules_shown)){ 
            foreach (array_diff(array_keys($all_with), $modules_shown) as $module_shown) {
                unset($all_with[$module_shown]);
            }
        }
        return User::select('id','id as user_id')->with($all_with)->where('id', $user_id)->first();
    }















    /**
     | ---------------------------------------------------------------------------------------------------
     | Menampilkan Pendapatan dan pengeluaran yang belum tersimpan di active_version
     |
     |  MASIH DALAM TABEL 
     |  - incomes
     |  - expenses
     |  - debt_repayments
     |  - asset_repayments
     |  - insurances
     |
     |  BELUM MASUK TABEL
     |  - plan_balances
     |  - active_version
     |  - active_version_details
     |  - plan_analysis_activated
     |  - asset_repayments_paid
     |  - cycles
     |
     | 
     */

    public function showByVersionFincheckOnGoing($attributes){
        $user_id = $attributes['user_id'];
        $modules_shown = !isset($attributes['modules'])?[]:$attributes['modules'];

        $module_version = Income::where('user_id', $user_id)->max('version'); //langsung dapatkan yang terakhir saja

        if($module_version === '')
            return null;

        $all_with = [
            'incomes' => function($query) use ($module_version) {
                $query->select('*', DB::raw("'https://mylife.fundtastic.co.id/uploads/wallet_icon/icon_pendapatan_bulanan.png' as taxo_wallet_ico") )->where('version', $module_version);
            },
            'expenses' => function($query) use ($module_version, $user_id) {
                $query->select('taxonomies.title as taxo_wallet_name', 'taxonomies.slug as taxo_wallet_slug', 'taxonomies.image as taxo_wallet_ico', 'taxonomies.color as taxo_wallet_color', 'expenses.*')
                ->where('version', $module_version)
                ->join('taxonomies', 'taxonomies.id', '=', 'expenses.taxo_wallet_id', 'left');

                if($module_version != $module_version){
                    $query->whereRaw('expenses.taxo_wallet_id in ( select taxo_wallet_id from expenses where user_id = \''.$user_id.'\' AND version=\''.$module_version.'\' )');
                }
            },
            'debt_repayments' => function($query) use ($module_version) {
                $query->select('taxonomies.title as taxo_wallet_name', 'taxonomies.image as taxo_wallet_ico', 'taxonomies.color as taxo_wallet_color', 'debt_repayments.*')
                ->where('version', $module_version)
                ->join('taxonomies', 'taxonomies.id', '=', 'debt_repayments.taxo_wallet_id', 'left');
            },
            'asset_repayments' => function($query) use ($module_version) {
                $query->select('taxonomies.title as taxo_wallet_name', 'taxonomies.slug as taxo_wallet_slug', 'taxonomies.image as taxo_wallet_ico', 'taxonomies.color as taxo_wallet_color', 'asset_repayments.*')
                ->with(['plan_analysis_activated'])
                ->where('version', $module_version)
                ->join('taxonomies', 'taxonomies.id', '=', 'asset_repayments.taxo_wallet_id', 'left');
            },
            'insurances' => function($query) use ($module_version) {
                $query->select('t.title as taxo_wallet_name', 't.image as taxo_wallet_ico', 't.color as taxo_wallet_color', 't2.title as taxo_insurance_type_name', 'insurances.*')
                ->where('version', $module_version)
                ->join('taxonomies as t', 't.id', '=', 'insurances.taxo_wallet_id', 'left')
                ->join('taxonomies as t2', 't2.id', '=', 'insurances.taxo_insurance_type_id', 'left');
            },
            'plan_balances' => function($query) use ($module_version) {
                $query->select('plan_balances.user_id','plan_balances.version', 'plan_balances.name', 'plan_balances.balance', 'plan_balances.balance_datetime', DB::raw("'https://mylife.fundtastic.co.id/uploads/wallet_icon/icon_free_cashflow_tahunan.png' as taxo_wallet_ico_freecashflow_tahunan, 'https://mylife.fundtastic.co.id/uploads/wallet_icon/icon_free_cashflow_bulanan.png' as taxo_wallet_ico_freecashflow_bulanan") )
                ->where('version', $module_version);
            },
        ];
        if(!empty($modules_shown)){ 
            foreach (array_diff(array_keys($all_with), $modules_shown) as $module_shown) {
                unset($all_with[$module_shown]);
            }
        }
        return User::select('id','id as user_id')->with($all_with)->where('id', $user_id)->first();
    }

    public function getAnggaranOnGoing($user_id) {

        $module_version = Income::where('user_id', $user_id)->max('version'); //langsung dapatkan yang terakhir saja

        if($module_version === '')
            return null;

        /**
         * Get All budget / pengeluaran / Anggaran per bulan ...
         */
        
        $sum_expense = Expense::where('user_id', $user_id)->where('version', $module_version)->sum('anggaran_perbulan');
        $sum_debtRepayment = DebtRepayment::where('user_id', $user_id)->where('version', $module_version)->sum('cicilan_perbulan');
        $sum_assetRepayment = AssetRepayment::where('user_id', $user_id)->where('version', $module_version)->sum('cicilan_perbulan');
        $sum_insurance = Insurance::where('user_id', $user_id)->where('version', $module_version)->sum('premi_perbulan');

        $all_expenses = $sum_expense + $sum_debtRepayment + $sum_assetRepayment + $sum_insurance;

        return $all_expenses;

    }




    public function create($attributes) {

        if($this->isValid($attributes)) { 

            $user_id = isset($attributes['user_id'])?$attributes['user_id']:Auth::user()->id;//$attributes['user_id'] untuk API
            DB::beginTransaction();

            //get cfp_id
            $cfp_id = isset($attributes['user_id'])?$attributes['user_id']:CfpClient::select('cfp_id')->where('client_id', $user_id)->pluck('cfp_id');
            if(is_null($cfp_id)){
                throw new ValidationException('Financial checkup cashflow analysis validation failed', [
                    'cfp' => 'You don\'t have CFP assigned',
                ]);
            }

            $activeVersion = ActiveVersion::where('user_id', $user_id)->where('key', 'financialCheckup_cashflowAnalysis')->first();
            
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

            $all_expenses = 0;
            $all_expenses_map = [
                'expense' => 'anggaran_perbulan',
                'debt_repayment' => 'cicilan_perbulan',
                'asset_repayment' => 'cicilan_perbulan',
                'insurance' => 'premi_perbulan'
            ];

            $module_version = is_null($activeVersion)?0:$activeVersion->version+1; //dd($module_version);
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
                                    'created_by' => $cfp_id,
                                    'created_at' => Carbon::now(),
                                    'updated_by' => $cfp_id,
                                    'updated_at' => Carbon::now()
                                ];
                           
                                if(in_array($module, array_keys($all_expenses_map))){
                                    $all_expenses += $attribute_arr[$all_expenses_map[$module]];
                                }
                            }
                            break;
                    }
              
                    $model_name = '\\App\\Models\\'.$attributes_arr['model_name']; 
                    $model = new $model_name;

                    $model::insert($attributes_safe);

                    $attributes_res[$attributes_arr['post_name']] = $attributes_safe;
                }
            }
            
            $income = $attributes['incomes'][0]['pendapatan_bulanan'];
            $pendapatan_lain = $attributes['incomes'][0]['pendapatan_lain'];
            $free_cash_flow_tahunan = $pendapatan_lain;
            $free_cash_flow = $income - $all_expenses;

            //add wallet other
            //get wallet id where has slug 'other'
            $taxo_wallet_other = $this->taxonomy->findBySlug('other', 'wallet');
            $taxo_wallet_titipan_transfer = $this->taxonomy->findBySlug('titipan-transfer', 'wallet');
            if($taxo_wallet_other && $taxo_wallet_titipan_transfer){
                $taxo_wallet_titipan_transfer_data = [
                    'taxo_wallet_id' => $taxo_wallet_titipan_transfer->id,
                    'anggaran_perbulan' => 0,
                    'catatan' => 'Titipan Transfer',
                    'user_id' => $user_id,
                    'version' => $module_version,
                    'created_by' => $cfp_id,
                    'created_at' => Carbon::now(),
                    'updated_by' => $cfp_id,
                    'updated_at' => Carbon::now()
                ];

                $taxo_wallet_other_data = [
                    'taxo_wallet_id' => $taxo_wallet_other->id,
                    'anggaran_perbulan' => $free_cash_flow,
                    'catatan' => 'Your free cashflow',
                    'user_id' => $user_id,
                    'version' => $module_version,
                    'created_by' => $cfp_id,
                    'created_at' => Carbon::now(),
                    'updated_by' => $cfp_id,
                    'updated_at' => Carbon::now()
                ];
                //dd($taxo_wallet_other_data);
                $taxo_wallet_titipan_transfer = Expense::insert($taxo_wallet_titipan_transfer_data);
                $taxo_wallet_other = Expense::insert($taxo_wallet_other_data);//perhitungannya terpisah dari expenses, karena ambilnya dari free cashflow
                //dd($taxo_wallet_other);
                $attributes_res['expenses'][] = $taxo_wallet_titipan_transfer_data;
                $attributes_res['expenses'][] = $taxo_wallet_other_data;
            }
            // end add wallet other

            $balance_attributes_safe = [
                [
                    'name' => 'free_cashflow',
                    'balance' => $free_cash_flow,
                    'balance_datetime' => Carbon::now(),
                    'user_id' => $user_id,
                    'version' => $module_version,
                    'created_by' => $cfp_id,
                    'created_at' => Carbon::now(),
                    'updated_by' => $cfp_id,
                    'updated_at' => Carbon::now()
                ],
                [
                    'name' => 'free_cashflow_annual',
                    'balance' => $free_cash_flow_tahunan,
                    'balance_datetime' => Carbon::now(),
                    'user_id' => $user_id,
                    'version' => $module_version,
                    'created_by' => $cfp_id,
                    'created_at' => Carbon::now(),
                    'updated_by' => $cfp_id,
                    'updated_at' => Carbon::now()
                ]
            ];
            $attributes_res['plan_balances'] = $balance_attributes_safe;
            PlanBalance::insert($balance_attributes_safe);
            
            $this->updateActiveVersion($user_id, 'financialCheckup_cashflowAnalysis', $module_version);//update version

            //save active version details
            activeVersionDetail::insert([
                'user_id' => $user_id, 
                'version' => $module_version,
                'status' => 'draft',
                'active_version_key' => 'financialCheckup_cashflowAnalysis',
                'created_by' => $cfp_id, 
                'created_at' => Carbon::now(), 
                'updated_by' => $cfp_id, 
                'updated_at' => Carbon::now()
            ]);

            //update asset repayment id lama dengan yg baru saja disimpan
            $this->updateAssetRepaymentUsage($user_id, $module_version);

            //copy wallet transactions old to new
            if($module_version > 0){
                $this->updateWalletTransactions($user_id, $module_version);
            }
            
            DB::commit();
            return $attributes_res;
        }
        throw new ValidationException('Financial checkup cashflow analysis validation failed', $this->getErrors());
    }




    /**
     | *** NOTE ***
     | Function ini tidak dugunakan lagi.. 
     | di ganti dengan function di bawahnya
     |     - createSelfIncome
     |     - createSelfExpense
     |         - createSelfExpenseAuto
     |     - createSelfDebt
     |     - createSelfAsset
     |     - createSelfInsurances
     |     - createSelfRekap
     | yang di bagi manjadi 6 function
     |
     | ----------------------------------------------
     | Self financial checkup ...
     | Financila Checkup Client ...
     | ----------------------------------------------
     | 29 Agustus 2018
     | Gugun DP
     |
     | Function ini copy dari function 'create'
     | dimana funcsi 'create' digunakan untuk financial checkup oleh CFP
     | sedangkan funcsi ini 'createSelf' digunakan supaya Client bisa melakukan financial checkup sendiri
     | 
     */

    public function createSelf($attributes) {

        if($this->isValid($attributes)) {
            
            /**
             * mengambil client_id
             */

            $user_id = isset($attributes['user_id'])?$attributes['user_id']:Auth::user()->id; //$attributes['user_id'] untuk API
            DB::beginTransaction();

            /**
             * mengambil cfp_id
             */

            $cfp_id = isset($attributes['user_id'])?$attributes['user_id']:CfpClient::select('cfp_id')->where('client_id', $user_id)->pluck('cfp_id');
            if(is_null($cfp_id)){
                throw new ValidationException('Financial checkup cashflow analysis validation failed', [
                    'cfp' => 'You don\'t have CFP assigned',
                ]);
            }

            /**
             | Mengmbil version terakhir ...
             | ------------------------------------
             | Version penomoran untuk client setiap kali melakukan financial checkup
             | Query di bawah berfungsi untuk mengecek version terakhir 
             | yang nilainya sama dengan jumlah setiap kali client melakukan financial checkup
             | Jika client belum melakukan financila checkup maka nilainya 0
             |
             */

            $activeVersion = ActiveVersion::where('user_id', $user_id)->where('key', 'financialCheckup_cashflowAnalysis')->first();
            
            /**
             * Daftar category pendapatan & pengeluaran ...
             */

            $modules = [ 
                
                // Pendapatan ...

                'income' => [
                    'post_name' => 'incomes',
                    'model_name' => 'Income'
                ], 
                
                // Pengeluaran ... 
                
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

            $all_expenses = 0;
            $all_expenses_map = [
                'expense' => 'anggaran_perbulan',
                'debt_repayment' => 'cicilan_perbulan',
                'asset_repayment' => 'cicilan_perbulan',
                'insurance' => 'premi_perbulan'
            ];


            /**
             * Version terakhir + 1 sebagai nilai version baru yang akan di masukan
             */

            $module_version = is_null($activeVersion)?0:$activeVersion->version+1;

            $attributes_res = [];

            /**
             | Dilakukan perulangan untuk setiap module pendapatan & pengeluaran
             | - income
             | - expense
             | - debt_repayment
             | - asset_repayment
             | - insurance
             |
             */

            $default_categories = \DB::select("
                SELECT
                    a.id,
                    a.title,
                    a.slug,
                    '".url()."/' || a.image image,
                    a.description,
                    to_number(to_char(a.budged_percentage, 'FM99999.9999'), '9999.99') budged_percentage
                FROM taxonomies a 
                WHERE 
                    a.post_type = 'wallet' 
                    AND a.budged_percentage is not null
                ORDER BY a.id
            ");
            $pendapatan_tidak_tetap_default = isset($attributes['incomes'][0]['pendapatan_tidak_tetap_bulan']) ? $attributes['incomes'][0]['pendapatan_tidak_tetap_bulan'] : 0;

            $income_default = $attributes['incomes'][0]['pendapatan_bulanan'] + $pendapatan_tidak_tetap_default;
            if(!isset($attributes['expenses'][0])){
                foreach($default_categories as $default){
                    $nested['taxo_wallet_id'] = $default->id;
                    $nested['anggaran_perbulan'] = $default->budged_percentage / 100 * $income_default;
                    $nested['catatan'] = '';
                    $attributes['expenses'][] = $nested;
                }
            }

            // if(!isset($attributes['debt_repayments'][0]) && !is_null($activeVersion)){
            //     $data_debt_repayments = DebtRepayment::where('user_id', $user_id)->where('version',$activeVersion->version)->get();

            //     foreach($data_debt_repayments as $data_debt){
            //         $nested_debt['taxo_wallet_id'] = $data_debt->taxo_wallet_id;
            //         $nested_debt['nama'] = $data_debt->nama;
            //         $nested_debt['cicilan_perbulan'] = $data_debt->cicilan_perbulan;
            //         $nested_debt['sisa_durasi'] = $data_debt->sisa_durasi;
            //         $nested_debt['catatan'] = $data_debt->catatan;
            //         $attributes['debt_repayments'][] = $nested_debt;
            //     }
            // }

            // if(!isset($attributes['asset_repayments'][0]) && !is_null($activeVersion)){
            //     $data_asset_repayments = AssetRepayment::where('user_id', $user_id)->where('version',$activeVersion->version)->get();
            //     foreach($data_asset_repayments as $data_asset){
            //         $nested_asset['taxo_wallet_id'] = $data_asset->taxo_wallet_id;
            //         $nested_asset['nama'] = $data_asset->nama;
            //         $nested_asset['cicilan_perbulan'] = $data_asset->cicilan_perbulan;
            //         $nested_asset['sisa_durasi'] = $data_asset->sisa_durasi;
            //         $nested_asset['catatan'] = $data_asset->catatan;
            //         $attributes['asset_repayments'][] = $nested_asset;
            //     }
            // }

            // if(!isset($attributes['insurances'][0]) && !is_null($activeVersion)){
            //     $data_insurances = Insurance::where('user_id', $user_id)->where('version',$activeVersion->version)->get();
            //     foreach($data_insurances as $data_insurance){
            //         $nested_insurance['taxo_wallet_id'] = $data_insurance->taxo_wallet_id;
            //         $nested_insurance['no_polis'] = $data_insurance->no_polis;
            //         $nested_insurance['premi_perbulan'] = $data_insurance->premi_perbulan;
            //         $nested_insurance['taxo_insurance_type_id'] = $data_insurance->taxo_insurance_type_id;
            //         $nested_insurance['nilai_pertanggungan'] = $data_insurance->nilai_pertanggungan;
            //         $nested_insurance['catatan'] = $data_insurance->catatan;
            //         $attributes['insurances'][] = $nested_insurance;
            //     }
            // }

            foreach ($modules as $module => $attributes_arr) { 
                if(isset($attributes[$attributes_arr['post_name']])) { // incomes, expenses, debt_repayments, asset_repayments, insurances
                    switch ($module) {
                        default:
                            $attributes_safe = [];
                            foreach ($attributes[$attributes_arr['post_name']] as $idx => $attribute_arr) { 
                                $attributes_safe[$idx] = $attribute_arr;
                                // mengecek request payload untuk pendapatan tidak tetap bulanan apabila tidak diisi
                                if($attributes_arr['post_name'] == 'incomes'){
                                    $attributes_safe[$idx] = [
                                        'pendapatan_bulanan' => isset($attributes_safe[0]['pendapatan_bulanan']) ? $attributes_safe[0]['pendapatan_bulanan'] : 0,
                                        'pendapatan_tidak_tetap_bulan' => isset($attributes_safe[0]['pendapatan_tidak_tetap_bulan']) ? $attributes_safe[0]['pendapatan_tidak_tetap_bulan'] : 0,
                                        'pendapatan_lain' => isset($attributes_safe[0]['pendapatan_lain']) ? $attributes_safe[0]['pendapatan_lain'] : 0, 
                                    ];
                                }
                                $attributes_safe[$idx] += [
                                    'user_id' => $user_id,
                                    'version' => $module_version,
                                    'created_by' => $cfp_id,
                                    'created_at' => Carbon::now(),
                                    'updated_by' => $cfp_id,
                                    'updated_at' => Carbon::now()
                                ];
                           
                                if(in_array($module, array_keys($all_expenses_map))){
                                    $all_expenses += $attribute_arr[$all_expenses_map[$module]];
                                }
                            }
                            break;
                    }
              
                    $model_name = '\\App\\Models\\'.$attributes_arr['model_name']; 
                    $model = new $model_name;

                    /**
                     | Melakukan insert terhadap tabel Pendapatan dan pengeluaran
                     | Tabel:
                     |  - incomes
                     |  - expenses
                     |  - debt_repayments
                     |  - asset_repayments
                     |  - insurances
                     */

                    $model::insert($attributes_safe);

                    $attributes_res[$attributes_arr['post_name']] = $attributes_safe;
                }
            }


            /**
             * Perhitungan Rekapitulasi ...
             */

            $pendapatan_tidak_tetap = isset($attributes['incomes'][0]['pendapatan_tidak_tetap_bulan']) ? $attributes['incomes'][0]['pendapatan_tidak_tetap_bulan'] : 0;

            $income = $attributes['incomes'][0]['pendapatan_bulanan'] + $pendapatan_tidak_tetap;
            $pendapatan_lain = $attributes['incomes'][0]['pendapatan_lain'];

            $free_cash_flow_tahunan = $pendapatan_lain;
            
            /**
             * Free cash flow adalah pendapatan bulanan di kurangi pengeluaran
             * Sisa anggaran
             */

            $free_cash_flow = $income - $all_expenses;


            /**
             * Add wallet other ...
             */

            // Get wallet id where has slug 'other' & 'titipan-transfer'
            $taxo_wallet_other = $this->taxonomy->findBySlug('other', 'wallet');
            $taxo_wallet_titipan_transfer = $this->taxonomy->findBySlug('titipan-transfer', 'wallet');
            
            /**
             |
             | Pada proses ini
             | Memasukan "Titipan Transfer" dengan nilai 0 
             | dan memasukan "Your free cashflow" / Sisa anggaran
             | pada tabel "expense"
             |
             | 
             */

            if($taxo_wallet_other && $taxo_wallet_titipan_transfer){
                $taxo_wallet_titipan_transfer_data = [
                    'taxo_wallet_id' => $taxo_wallet_titipan_transfer->id,
                    'anggaran_perbulan' => 0,
                    'catatan' => 'Titipan Transfer',
                    'user_id' => $user_id,
                    'version' => $module_version,
                    'created_by' => $cfp_id,
                    'created_at' => Carbon::now(),
                    'updated_by' => $cfp_id,
                    'updated_at' => Carbon::now()
                ];

                $taxo_wallet_other_data = [
                    'taxo_wallet_id' => $taxo_wallet_other->id,
                    'anggaran_perbulan' => $free_cash_flow,
                    'catatan' => 'Your free cashflow',
                    'user_id' => $user_id,
                    'version' => $module_version,
                    'created_by' => $cfp_id,
                    'created_at' => Carbon::now(),
                    'updated_by' => $cfp_id,
                    'updated_at' => Carbon::now()
                ];
                
                $taxo_wallet_titipan_transfer = Expense::insert($taxo_wallet_titipan_transfer_data);
                $taxo_wallet_other = Expense::insert($taxo_wallet_other_data); 
                // perhitungannya terpisah dari expenses, karena ambilnya dari free cashflow
                
                $attributes_res['expenses'][] = $taxo_wallet_titipan_transfer_data;
                $attributes_res['expenses'][] = $taxo_wallet_other_data;
            }
            
            /**
             | --------------------------------------------------------
             | Masukan Sisa anggaran bulanan (free_cashflow)
             | dan pendapatan lain per tahunan (free_cashflow_annual)
             |--------------------------------------------------------
             */

            $balance_attributes_safe = [
                [
                    'name' => 'free_cashflow',
                    'balance' => $free_cash_flow,
                    'balance_datetime' => Carbon::now(),
                    'user_id' => $user_id,
                    'version' => $module_version,
                    'created_by' => $cfp_id,
                    'created_at' => Carbon::now(),
                    'updated_by' => $cfp_id,
                    'updated_at' => Carbon::now()
                ],
                [
                    'name' => 'free_cashflow_annual',
                    'balance' => $free_cash_flow_tahunan,
                    'balance_datetime' => Carbon::now(),
                    'user_id' => $user_id,
                    'version' => $module_version,
                    'created_by' => $cfp_id,
                    'created_at' => Carbon::now(),
                    'updated_by' => $cfp_id,
                    'updated_at' => Carbon::now()
                ]
            ];
            $attributes_res['plan_balances'] = $balance_attributes_safe;
            PlanBalance::insert($balance_attributes_safe);
            
            
            /**
             * Tambahkan (jika belum ada) / update (jika sudah ada) financialCheckup_cashflowAnalysis di tabel active_version
             * untuk menandakan version terakhir
             * (tabel active_version)
             */

            $this->updateActiveVersion($user_id, 'financialCheckup_cashflowAnalysis', $module_version);

            /**
             * Save active version details (tabel active_version_details) ...
             * di tabel ini lah yang jika statusnya masih "draf" perlu di approve menjadi "approved"
             */

            $insertActiveVersionDetail = activeVersionDetail::create([
                'user_id' => $user_id, 
                'version' => $module_version,
                'status' => 'draft',
                'active_version_key' => 'financialCheckup_cashflowAnalysis',
                'created_by' => $cfp_id,
                'created_at' => Carbon::now(),
                'updated_by' => $cfp_id,
                'updated_at' => Carbon::now()
            ]);

            $activeVersionDetail_id = $insertActiveVersionDetail->id;

            /** 
             * update asset repayment id lama dengan yg baru saja disimpan
             * tabel plan_analysis_activated
             */
            $this->updateAssetRepaymentUsage($user_id, $module_version);

            /** 
             * copy wallet transactions old to new
             * tabel asset_repayments_paid
             */
            if($module_version > 0) {
                $this->updateWalletTransactions($user_id, $module_version);
            }
            
            DB::commit();













            /**
             | --------------------------------------------------
             | Proses approve 
             | --------------------------------------------------
             | Script di bawah ini copy dari function "approveFinance"
             | Sebelumnya yang memasukan Financial Checkup adalah CFP
             | Sehingga setelah CFP mengisi FInalcial Checkup Clinet harus mengapprove
             | Di function ini yang memasukan Financial Checkup adalah client 
             | jadi functioin approve otomatis di jalankan di function input Financial Checkup ini
             | 
             | -------------------------------
             | Parametar yang di perlukan
             |
             | {
             |   "user_id" : 196,
             |   "active_version_detail_id" : 176,
             |   "status" : "approved",
             |   "reason_reject" : "gue reject nih xxx"
             | }
             |
            */

            DB::beginTransaction();
            $activeVersionDetail = ActiveVersionDetail::where('id', $activeVersionDetail_id)
            ->where('user_id', $user_id)
            ->where('status', 'draft')->first();
            if(is_null($activeVersionDetail)){
                throw new ValidationException('Approval failed', [
                    'finance' => 'Data not found or already approved or rejected',
                ]);
            }else{
                
                $data_update = [
                    'status'        => 'approved',
                    'approved_by'   => $user_id,
                    'approved_at'   => Carbon::now(),
                    'updated_by'    => $user_id,
                    'updated_at'    => Carbon::now()
                ];

                $cycle_saved = $this->cycle->is_full_cycle($user_id);
                        
                if($activeVersionDetail->active_version_key == 'financialCheckup_cashflowAnalysis') {
                    $cycle_update['cashflow_analysis_version_approved'] = $activeVersionDetail->version;
                } elseif($activeVersionDetail->active_version_key == 'financialCheckup_portfolioAnalysis') {
                    $cycle_update['portfolio_analysis_version_approved'] = $activeVersionDetail->version;
                } elseif($activeVersionDetail->active_version_key == 'planAnalysis') {
                    $cycle_update['plan_analysis_version_approved'] = $activeVersionDetail->version;
                    $cycle_update['completed_at'] = Carbon::now();
                }
                
                /**
                 * Melakukan update di tabel cycles, untuk menentukan 
                 */

                Cycle::where('id', $cycle_saved['cycle_id'])->update($cycle_update);
                        
                $activeVersionDetail->update($data_update);
            }

            DB::commit();





            /**
             | ----------------------------------------------------------------------------------------------------
             | Kembalikan data mutasi yang sudah di kategorikan ke uncategories, 
             | ketika category tidak di pilih lagi saat financial checkup di periode yang sama.
             | ----------------------------------------------------------------------------------------------------
             | 10 September 2018
             | 
             */
            $this->updateCategoryMutasiAfterFincheck($user_id, $module_version);

            //mutasi akan diunset apabila saat fincheck category tidak tersedia
            $this->updateCategoryMutasiSplitAfterFincheck($user_id, $attributes_res);






            return $attributes_res;
        }

        throw new ValidationException('Financial checkup cashflow analysis validation failed', $this->getErrors());
    }






    /**
     | Self financial checkup 6 potongan...
     | Financila Checkup Client ...
     | ----------------------------------------------
     | 30 Agustus 2018
     | Gugun DP
     |
     | Function ini copy dari function 'create' / 'createSelf'
     | dimana funcsi 'create' digunakan untuk financial checkup oleh CFP
     | sedangkan funcsi ini 'createSelf' digunakan supaya Client bisa melakukan financial checkup sendiri
     | 
     | dari function create / createSelf di pecah menjadi 6 function
     | - createSelfIncome
     | - createSelfExpense
     |      - createSelfExpenseAuto         ->  function ini di jalankan saat user hanya mengisi Income (Pendapatan) saja, sehingga system akan menggenerate Expense otomatis untuk 7 category expense yang umum.
     | - createSelfDebt
     | - createSelfAsset
     | - createSelfInsurances
     | - createSelfRekap
     |
     | - updateCategoryMutasiAfterFincheck  ->  function ini digunakan untuk mengecek data mutasi yang sebelumnya sudah di category kan ke pengeluaran. 
     |                                          saat user tidak memilih lagi category expense tersebut, 
     |                                          yang dimana ada beberapa data mutasi sudah di masukan ke category tersebut di periode yang sama makan data mutasi akan kembali di uncategories (karena category nya tidak dipilih lagi).
     |
     */


    public function createSelfIncome($attributes) {

        if($this->isValid($attributes)) {
            /**
             * mengambil client_id
             */

            $user_id = isset($attributes['user_id'])?$attributes['user_id']:Auth::user()->id; //$attributes['user_id'] untuk API
            DB::beginTransaction();

            /**
             * mengambil cfp_id
             */

            $cfp_id = isset($attributes['user_id'])?$attributes['user_id']:CfpClient::select('cfp_id')->where('client_id', $user_id)->pluck('cfp_id');
            if(is_null($cfp_id)){
                throw new ValidationException('Financial checkup cashflow analysis validation failed', [
                    'cfp' => 'You don\'t have CFP assigned',
                ]);
            }

            /**
             | Mengmbil version terakhir ...
             | ------------------------------------
             | Version penomoran untuk client setiap kali melakukan financial checkup
             | Query di bawah berfungsi untuk mengecek version terakhir 
             | yang nilainya sama dengan jumlah setiap kali client melakukan financial checkup
             | Jika client belum melakukan financila checkup maka nilainya 0
             |
             */

            $activeVersion = ActiveVersion::where('user_id', $user_id)->where('key', 'financialCheckup_cashflowAnalysis')->first();

            /**
             * Version terakhir + 1 sebagai nilai version baru yang akan di masukan
             */

            $module_version = is_null($activeVersion)?0:$activeVersion->version+1;

            /** 
             | Delete from Income where user_id and version ... 
             | Jika sebelumnya sudah mengisi Income, tapi tidak dilanjutkan (Manjadi data sampah)
             | Bersihkan dulu dengan menghapus data sebelumnya ...
            */

            $income_exists = Income::where('user_id', '=', $user_id)->where('version', '=', $module_version);
            $income_exists->delete();
           
            $save = Income::create([
                'pendapatan_bulanan'            => $attributes['pendapatan_bulanan'],
                'pendapatan_lain'               => $attributes['pendapatan_lain'],
                'pendapatan_tidak_tetap_bulan'  => $attributes['pendapatan_tidak_tetap_bulan'],
                'user_id'                       => $user_id,
                'version'                       => $module_version,
                'created_by'                    => $cfp_id,
                'created_at'                    => Carbon::now(),
                'updated_by'                    => $cfp_id,
                'updated_at'                    => Carbon::now()
            ]);
            DB::commit();

            /**
             * Jika selesai maka lakukan rekapitulsai untuk menghitung free_cashflow, active version, dll
             */
            $selesai = isset($attributes['selesai'])?$attributes['selesai']:false;
            if($selesai) {
                $attr = ['user_id' => $user_id];
                $this->createSelfExpenseAuto($attr);
                $this->createSelfRekap($attr);
            }

            return $save;
        }

        throw new ValidationException('Financial checkup cashflow analysis validation failed', $this->getErrors());
    }





    public function createSelfExpense($attributes) {

        if($this->isValid($attributes)) {
            
            /**
             * mengambil client_id
             */

            $user_id = isset($attributes['user_id'])?$attributes['user_id']:Auth::user()->id; //$attributes['user_id'] untuk API
            DB::beginTransaction();

            /**
             * mengambil cfp_id
             */

            $cfp_id = isset($attributes['user_id'])?$attributes['user_id']:CfpClient::select('cfp_id')->where('client_id', $user_id)->pluck('cfp_id');
            if(is_null($cfp_id)){
                throw new ValidationException('Financial checkup cashflow analysis validation failed', [
                    'cfp' => 'You don\'t have CFP assigned',
                ]);
            }

            /**
             | Mengmbil version terakhir ...
             | ------------------------------------
             | Version penomoran untuk client setiap kali melakukan financial checkup
             | Query di bawah berfungsi untuk mengecek version terakhir 
             | yang nilainya sama dengan jumlah setiap kali client melakukan financial checkup
             | Jika client belum melakukan financila checkup maka nilainya 0
             |
             */

            $activeVersion = ActiveVersion::where('user_id', $user_id)->where('key', 'financialCheckup_cashflowAnalysis')->first();

            /**
             * Version terakhir + 1 sebagai nilai version baru yang akan di masukan
             */

            $module_version = is_null($activeVersion)?0:$activeVersion->version+1;

            // delete from Expense where user_id and version
            $expense_exists = Expense::where('user_id', '=', $user_id)->where('version', '=', $module_version);
            $expense_exists->delete();
           
            
            foreach ($attributes['data'] as $idx => $attribute_arr) { 

                $save = Expense::create([
                    'taxo_wallet_id'        => $attributes['data'][$idx]['taxo_wallet_id'],
                    'anggaran_perbulan'     => $attributes['data'][$idx]['anggaran_perbulan'],
                    'catatan'               => $attributes['data'][$idx]['catatan'],
                    'user_id'               => $user_id,
                    'version'               => $module_version,
                    'created_by'            => $cfp_id,
                    'created_at'            => Carbon::now(),
                    'updated_by'            => $cfp_id,
                    'updated_at'            => Carbon::now()
                ]);
                DB::commit();

            }

            /**
             * Jika selesai maka lakukan rekapitulsai untuk menghitung free_cashflow, active version, dll
             */
            $selesai = isset($attributes['selesai'])?$attributes['selesai']:false;
            if($selesai) {
                $attr = ['user_id' => $user_id];
                $this->createSelfRekap($attr);
            }

            return $attributes['data'];
        }

        throw new ValidationException('Financial checkup cashflow analysis validation failed', $this->getErrors());
    }


    /**
     |
     | Function createSelfExpenseAuto ini sama dengan function createSelfExpense,
     | untuk mengisi Expense.
     | hanya saja untuk Auto ini di panggil saat user/client hanya mengisi Income
     | jadi Expense di buat secara otomatis berdasarkan persentasi Expense yang is_required dari Income yang di insert user.
     |
     | Ada beberapa Expense yang wajib diisi, dan ketika user hanya mengisi Income maka Expense yang wajib diisi tersebut di buat otomatis oleh System, berdasarkan persentasi Income.
     |
     */

    public function createSelfExpenseAuto($attributes) {

        if($this->isValid($attributes)) {
            
            /**
             * mengambil client_id
             */

            $user_id = isset($attributes['user_id'])?$attributes['user_id']:Auth::user()->id; //$attributes['user_id'] untuk API
            DB::beginTransaction();

            /**
             * mengambil cfp_id
             */

            $cfp_id = isset($attributes['user_id'])?$attributes['user_id']:CfpClient::select('cfp_id')->where('client_id', $user_id)->pluck('cfp_id');
            if(is_null($cfp_id)){
                throw new ValidationException('Financial checkup cashflow analysis validation failed', [
                    'cfp' => 'You don\'t have CFP assigned',
                ]);
            }

            /**
             | Mengmbil version terakhir ...
             | ------------------------------------
             | Version penomoran untuk client setiap kali melakukan financial checkup
             | Query di bawah berfungsi untuk mengecek version terakhir 
             | yang nilainya sama dengan jumlah setiap kali client melakukan financial checkup
             | Jika client belum melakukan financila checkup maka nilainya 0
             |
             */

            $activeVersion = ActiveVersion::where('user_id', $user_id)->where('key', 'financialCheckup_cashflowAnalysis')->first();

            /**
             * Version terakhir + 1 sebagai nilai version baru yang akan di masukan
             */

            $module_version = is_null($activeVersion)?0:$activeVersion->version+1;

            // delete from Expense where user_id and version
            $expense_exists = Expense::where('user_id', '=', $user_id)->where('version', '=', $module_version);
            $expense_exists->delete();

            /**
             | Get Total Income
             |
             | Ambil 
                - pendapatan_bulanan
                - pendapatan_tidak_tetap_bulan
                - pendapatan_lain (tahunan)

             | Dari tabel income
             */

            $data_income = Income::where('user_id', $user_id)->where('version', $module_version)->first();

            $income = $data_income->pendapatan_bulanan + $data_income->pendapatan_tidak_tetap_bulan;
            $pendapatan_lain = $data_income->pendapatan_lain;



            /**
             * Ambil Expense yang akan di jadikan default ...
             */
            $expense_required = \DB::select("
                SELECT * FROM taxonomies a 
                WHERE 
                    a.post_type = 'wallet' 
                    AND 
                        (a.parent_id = '33'
                        OR a.parent_id IN (
                            SELECT b.id FROM taxonomies b WHERE b.parent_id = '33'
                        ))
                    AND a.budged_percentage is not null
                    AND a.budged_percentage > 0
                ORDER BY a.id
            ");
            

            foreach($expense_required as $id => $expense_r) {
                $save = Expense::create([
                    'taxo_wallet_id'        => $expense_required[$id]->id,
                    'anggaran_perbulan'     => $expense_required[$id]->budged_percentage / 100 * $income,
                    'catatan'               => '',
                    'user_id'               => $user_id,
                    'version'               => $module_version,
                    'created_by'            => $cfp_id,
                    'created_at'            => Carbon::now(),
                    'updated_by'            => $cfp_id,
                    'updated_at'            => Carbon::now()
                ]);
                DB::commit();
            }

            return $expense_required;
        }

        throw new ValidationException('Financial checkup cashflow analysis validation failed', $this->getErrors());
    }



    public function createSelfDebt($attributes) {

        if($this->isValid($attributes)) {
            
            /**
             * mengambil client_id
             */

            $user_id = isset($attributes['user_id'])?$attributes['user_id']:Auth::user()->id; //$attributes['user_id'] untuk API
            DB::beginTransaction();

            /**
             * mengambil cfp_id
             */

            $cfp_id = isset($attributes['user_id'])?$attributes['user_id']:CfpClient::select('cfp_id')->where('client_id', $user_id)->pluck('cfp_id');
            if(is_null($cfp_id)){
                throw new ValidationException('Financial checkup cashflow analysis validation failed', [
                    'cfp' => 'You don\'t have CFP assigned',
                ]);
            }

            /**
             | Mengmbil version terakhir ...
             | ------------------------------------
             | Version penomoran untuk client setiap kali melakukan financial checkup
             | Query di bawah berfungsi untuk mengecek version terakhir 
             | yang nilainya sama dengan jumlah setiap kali client melakukan financial checkup
             | Jika client belum melakukan financila checkup maka nilainya 0
             |
             */

            $activeVersion = ActiveVersion::where('user_id', $user_id)->where('key', 'financialCheckup_cashflowAnalysis')->first();

            /**
             * Version terakhir + 1 sebagai nilai version baru yang akan di masukan
             */

            $module_version = is_null($activeVersion)?0:$activeVersion->version+1;

            // delete from DebtRepayment where user_id and version
            $debt_exists = DebtRepayment::where('user_id', '=', $user_id)->where('version', '=', $module_version);
            $debt_exists->delete();
           
            
            foreach ($attributes['data'] as $idx => $attribute_arr) { 

                $save = DebtRepayment::create([
                    
                    'taxo_wallet_id'        => $attributes['data'][$idx]['taxo_wallet_id'],
                    'nama'                  => $attributes['data'][$idx]['nama'],
                    'cicilan_perbulan'      => $attributes['data'][$idx]['cicilan_perbulan'],
                    'sisa_durasi'           => $attributes['data'][$idx]['sisa_durasi'],
                    'catatan'               => $attributes['data'][$idx]['catatan'],

                    'user_id'               => $user_id,
                    'version'               => $module_version,
                    'created_by'            => $cfp_id,
                    'created_at'            => Carbon::now(),
                    'updated_by'            => $cfp_id,
                    'updated_at'            => Carbon::now()
                ]);
                DB::commit();

            }

            /**
             * Jika selesai maka lakukan rekapitulsai untuk menghitung free_cashflow, active version, dll
             */
            $selesai = isset($attributes['selesai'])?$attributes['selesai']:false;
            if($selesai) {
                $attr = ['user_id' => $user_id];
                $this->createSelfRekap($attr);
            }

            return $attributes['data'];
        }

        throw new ValidationException('Financial checkup cashflow analysis validation failed', $this->getErrors());
    }

    public function createSelfAsset($attributes) {

        if($this->isValid($attributes)) {
            
            /**
             * mengambil client_id
             */

            $user_id = isset($attributes['user_id'])?$attributes['user_id']:Auth::user()->id; //$attributes['user_id'] untuk API
            DB::beginTransaction();

            /**
             * mengambil cfp_id
             */

            $cfp_id = isset($attributes['user_id'])?$attributes['user_id']:CfpClient::select('cfp_id')->where('client_id', $user_id)->pluck('cfp_id');
            if(is_null($cfp_id)){
                throw new ValidationException('Financial checkup cashflow analysis validation failed', [
                    'cfp' => 'You don\'t have CFP assigned',
                ]);
            }

            /**
             | Mengmbil version terakhir ...
             | ------------------------------------
             | Version penomoran untuk client setiap kali melakukan financial checkup
             | Query di bawah berfungsi untuk mengecek version terakhir 
             | yang nilainya sama dengan jumlah setiap kali client melakukan financial checkup
             | Jika client belum melakukan financila checkup maka nilainya 0
             |
             */

            $activeVersion = ActiveVersion::where('user_id', $user_id)->where('key', 'financialCheckup_cashflowAnalysis')->first();

            /**
             * Version terakhir + 1 sebagai nilai version baru yang akan di masukan
             */

            $module_version = is_null($activeVersion)?0:$activeVersion->version+1;
            
            // delete from AssetRepayment where user_id and version
            $asset_exists = AssetRepayment::where('user_id', '=', $user_id)->where('version', '=', $module_version);
            $asset_exists->delete();

            
            foreach ($attributes['data'] as $idx => $attribute_arr) { 

                $save = AssetRepayment::create([
                    
                    'taxo_wallet_id'        => $attributes['data'][$idx]['taxo_wallet_id'],
                    'nama'                  => $attributes['data'][$idx]['nama'],
                    'cicilan_perbulan'      => $attributes['data'][$idx]['cicilan_perbulan'],
                    'sisa_durasi'           => $attributes['data'][$idx]['sisa_durasi'],
                    'catatan'               => $attributes['data'][$idx]['catatan'],

                    'user_id'               => $user_id,
                    'version'               => $module_version,
                    'created_by'            => $cfp_id,
                    'created_at'            => Carbon::now(),
                    'updated_by'            => $cfp_id,
                    'updated_at'            => Carbon::now()
                ]);
                DB::commit();

            }

            /**
             * Jika selesai maka lakukan rekapitulsai untuk menghitung free_cashflow, active version, dll
             */
            $selesai = isset($attributes['selesai'])?$attributes['selesai']:false;
            if($selesai) {
                $attr = ['user_id' => $user_id];
                $this->createSelfRekap($attr);
            }

            return $attributes['data'];
        }

        throw new ValidationException('Financial checkup cashflow analysis validation failed', $this->getErrors());
    }


    public function createSelfInsurances($attributes) {

        if($this->isValid($attributes)) {
            
            /**
             * mengambil client_id
             */

            $user_id = isset($attributes['user_id'])?$attributes['user_id']:Auth::user()->id; //$attributes['user_id'] untuk API
            DB::beginTransaction();

            /**
             * mengambil cfp_id
             */

            $cfp_id = isset($attributes['user_id'])?$attributes['user_id']:CfpClient::select('cfp_id')->where('client_id', $user_id)->pluck('cfp_id');
            if(is_null($cfp_id)){
                throw new ValidationException('Financial checkup cashflow analysis validation failed', [
                    'cfp' => 'You don\'t have CFP assigned',
                ]);
            }

            /**
             | Mengmbil version terakhir ...
             | ------------------------------------
             | Version penomoran untuk client setiap kali melakukan financial checkup
             | Query di bawah berfungsi untuk mengecek version terakhir 
             | yang nilainya sama dengan jumlah setiap kali client melakukan financial checkup
             | Jika client belum melakukan financila checkup maka nilainya 0
             |
             */

            $activeVersion = ActiveVersion::where('user_id', $user_id)->where('key', 'financialCheckup_cashflowAnalysis')->first();

            /**
             * Version terakhir + 1 sebagai nilai version baru yang akan di masukan
             */

            $module_version = is_null($activeVersion)?0:$activeVersion->version+1;
            
            // delete from Insurance where user_id and version
            $insurance_exists = Insurance::where('user_id', '=', $user_id)->where('version', '=', $module_version);
            $insurance_exists->delete();


            foreach ($attributes['data'] as $idx => $attribute_arr) { 

                $save = Insurance::create([
                    
                    'taxo_wallet_id'            => $attributes['data'][$idx]['taxo_wallet_id'],
                    'no_polis'                  => $attributes['data'][$idx]['no_polis'],
                    'premi_perbulan'            => $attributes['data'][$idx]['premi_perbulan'],
                    'taxo_insurance_type_id'    => isset($attributes['data'][$idx]['taxo_insurance_type_id'])?$attributes['data'][$idx]['taxo_insurance_type_id']:0,
                    'nilai_pertanggungan'       => $attributes['data'][$idx]['nilai_pertanggungan'],
                    'catatan'                   => $attributes['data'][$idx]['catatan'],

                    'user_id'               => $user_id,
                    'version'               => $module_version,
                    'created_by'            => $cfp_id,
                    'created_at'            => Carbon::now(),
                    'updated_by'            => $cfp_id,
                    'updated_at'            => Carbon::now()
                ]);
                DB::commit();

            }

            /**
             * Jika selesai maka lakukan rekapitulsai untuk menghitung free_cashflow, active version, dll
             */
            $selesai = isset($attributes['selesai'])?$attributes['selesai']:false;
            if($selesai) {
                $attr = ['user_id' => $user_id];
                $this->createSelfRekap($attr);
            }

            return $attributes['data'];
        }

        throw new ValidationException('Financial checkup cashflow analysis validation failed', $this->getErrors());
    }





    public function createSelfRekap($attributes) {

        if($this->isValid($attributes)) {
            
            /**
             * mengambil client_id
             */

            $user_id = isset($attributes['user_id'])?$attributes['user_id']:Auth::user()->id; //$attributes['user_id'] untuk API
            DB::beginTransaction();

            /**
             * mengambil cfp_id
             */

            $cfp_id = isset($attributes['user_id'])?$attributes['user_id']:CfpClient::select('cfp_id')->where('client_id', $user_id)->pluck('cfp_id');
            if(is_null($cfp_id)){
                throw new ValidationException('Financial checkup cashflow analysis validation failed', [
                    'cfp' => 'You don\'t have CFP assigned',
                ]);
            }

            /**
             | Mengmbil version terakhir ...
             | ------------------------------------
             | Version penomoran untuk client setiap kali melakukan financial checkup
             | Query di bawah berfungsi untuk mengecek version terakhir 
             | yang nilainya sama dengan jumlah setiap kali client melakukan financial checkup
             | Jika client belum melakukan financila checkup maka nilainya 0
             |
             */

            $activeVersion = ActiveVersion::where('user_id', $user_id)->where('key', 'financialCheckup_cashflowAnalysis')->first();

            /**
             * Version terakhir + 1 sebagai nilai version baru yang akan di masukan
             */

            $module_version = is_null($activeVersion)?0:$activeVersion->version+1;

            /**
             | Ambil 
                - pendapatan_bulanan
                - pendapatan_tidak_tetap_bulan
                - pendapatan_lain (tahunan)

             | Dari tabel income
             */

            $data_income = Income::where('user_id', $user_id)->where('version', $module_version)->first();

            // $income = $attributes['incomes'][0]['pendapatan_bulanan'] + $attributes['incomes'][0]['pendapatan_tidak_tetap_bulan'];
            // $pendapatan_lain = $attributes['incomes'][0]['pendapatan_lain'];

            $income = $data_income->pendapatan_bulanan + $data_income->pendapatan_tidak_tetap_bulan;
            $pendapatan_lain = $data_income->pendapatan_lain;

            $free_cash_flow_tahunan = $pendapatan_lain;
            

            /**
             | Get all expense from expense, debt, asset, insurances. data yang sudah dimasukan dalam satu version
             | Tabel
             |
             |  - expenses                      anggaran_perbulan
             |  - debt_repayments               cicilan_perbulan
             |  - asset_repayments              cicilan_perbulan
             |  - insurances                    premi_perbulan
             */

            $sum_expense = Expense::where('user_id', $user_id)->where('version', $module_version)->sum('anggaran_perbulan');
            $sum_debtRepayment = DebtRepayment::where('user_id', $user_id)->where('version', $module_version)->sum('cicilan_perbulan');
            $sum_assetRepayment = AssetRepayment::where('user_id', $user_id)->where('version', $module_version)->sum('cicilan_perbulan');
            $sum_insurance = Insurance::where('user_id', $user_id)->where('version', $module_version)->sum('premi_perbulan');

            $all_expenses = $sum_expense + $sum_debtRepayment + $sum_assetRepayment + $sum_insurance;

            /**
             * Free cash flow adalah pendapatan bulanan di kurangi pengeluaran
             * Sisa anggaran
             */

            $free_cash_flow = $income - $all_expenses;


            /**
             * Add wallet other ...
             */

            // Get wallet id where has slug 'other' & 'titipan-transfer'
            $taxo_wallet_other = $this->taxonomy->findBySlug('other', 'wallet');
            $taxo_wallet_titipan_transfer = $this->taxonomy->findBySlug('titipan-transfer', 'wallet');
            
            /**
             |
             | Pada proses ini
             | Memasukan "Titipan Transfer" dengan nilai 0 
             | dan memasukan "Your free cashflow" / Sisa anggaran
             | pada tabel "expense"
             |
             | 
             */

            if($taxo_wallet_other && $taxo_wallet_titipan_transfer){
                $taxo_wallet_titipan_transfer_data = [
                    'taxo_wallet_id' => $taxo_wallet_titipan_transfer->id,
                    'anggaran_perbulan' => 0,
                    'catatan' => 'Titipan Transfer',
                    'user_id' => $user_id,
                    'version' => $module_version,
                    'created_by' => $cfp_id,
                    'created_at' => Carbon::now(),
                    'updated_by' => $cfp_id,
                    'updated_at' => Carbon::now()
                ];

                $taxo_wallet_other_data = [
                    'taxo_wallet_id' => $taxo_wallet_other->id,
                    'anggaran_perbulan' => $free_cash_flow,
                    'catatan' => 'Your free cashflow',
                    'user_id' => $user_id,
                    'version' => $module_version,
                    'created_by' => $cfp_id,
                    'created_at' => Carbon::now(),
                    'updated_by' => $cfp_id,
                    'updated_at' => Carbon::now()
                ];
                
                $taxo_wallet_titipan_transfer = Expense::insert($taxo_wallet_titipan_transfer_data);
                $taxo_wallet_other = Expense::insert($taxo_wallet_other_data); 
                // perhitungannya terpisah dari expenses, karena ambilnya dari free cashflow
                
                $attributes_res['expenses'][] = $taxo_wallet_titipan_transfer_data;
                $attributes_res['expenses'][] = $taxo_wallet_other_data;
            }
            
            /**
             | --------------------------------------------------------
             | Masukan Sisa anggaran bulanan (free_cashflow)
             | dan pendapatan lain per tahunan (free_cashflow_annual)
             | --------------------------------------------------------
             */

            $balance_attributes_safe = [
                [
                    'name' => 'free_cashflow',
                    'balance' => $free_cash_flow,
                    'balance_datetime' => Carbon::now(),
                    'user_id' => $user_id,
                    'version' => $module_version,
                    'created_by' => $cfp_id,
                    'created_at' => Carbon::now(),
                    'updated_by' => $cfp_id,
                    'updated_at' => Carbon::now()
                ],
                [
                    'name' => 'free_cashflow_annual',
                    'balance' => $free_cash_flow_tahunan,
                    'balance_datetime' => Carbon::now(),
                    'user_id' => $user_id,
                    'version' => $module_version,
                    'created_by' => $cfp_id,
                    'created_at' => Carbon::now(),
                    'updated_by' => $cfp_id,
                    'updated_at' => Carbon::now()
                ]
            ];
            $attributes_res['plan_balances'] = $balance_attributes_safe;
            PlanBalance::insert($balance_attributes_safe);
            
            
            /**
             * Tambahkan (jika belum ada) / update (jika sudah ada) financialCheckup_cashflowAnalysis di tabel active_version
             * untuk menandakan version terakhir
             * (tabel active_version)
             */

            $this->updateActiveVersion($user_id, 'financialCheckup_cashflowAnalysis', $module_version);

            /**
             * Save active version details (tabel active_version_details) ...
             * di tabel ini lah yang jika statusnya masih "draf" perlu di approve menjadi "approved"
             */

            $insertActiveVersionDetail = activeVersionDetail::create([
                'user_id' => $user_id, 
                'version' => $module_version,
                'status' => 'draft',
                'active_version_key' => 'financialCheckup_cashflowAnalysis',
                'created_by' => $cfp_id,
                'created_at' => Carbon::now(),
                'updated_by' => $cfp_id,
                'updated_at' => Carbon::now()
            ]);

            $activeVersionDetail_id = $insertActiveVersionDetail->id;

            /** 
             * update asset repayment id lama dengan yg baru saja disimpan
             * tabel plan_analysis_activated
             */
            $this->updateAssetRepaymentUsage($user_id, $module_version);

            /** 
             * copy wallet transactions old to new
             * tabel asset_repayments_paid
             */
            if($module_version > 0) {
                $this->updateWalletTransactions($user_id, $module_version);
            }
            
            DB::commit();






            /**
             | --------------------------------------------------
             | Proses approve 
             | --------------------------------------------------
             | Script di bawah ini copy dari function "approveFinance"
             | Sebelumnya yang memasukan Financial Checkup adalah CFP
             | Sehingga setelah CFP mengisi FInalcial Checkup Clinet harus mengapprove
             | Di function ini yang memasukan Financial Checkup adalah client 
             | jadi functioin approve otomatis di jalankan di function input Financial Checkup ini
             | 
             | -------------------------------
             | Parametar yang di perlukan
             |
             | {
             |   "user_id" : 196,
             |   "active_version_detail_id" : 176,
             |   "status" : "approved",
             |   "reason_reject" : "gue reject nih xxx"
             | }
             |
             */

            DB::beginTransaction();
            $activeVersionDetail = ActiveVersionDetail::where('id', $activeVersionDetail_id)
            ->where('user_id', $user_id)
            ->where('status', 'draft')->first();
            if(is_null($activeVersionDetail)){
                throw new ValidationException('Approval failed', [
                    'finance' => 'Data not found or already approved or rejected',
                ]);
            }else{
                
                $data_update = [
                    'status'        => 'approved',
                    'approved_by'   => $user_id,
                    'approved_at'   => Carbon::now(),
                    'updated_by'    => $user_id,
                    'updated_at'    => Carbon::now()
                ];

                $cycle_saved = $this->cycle->is_full_cycle($user_id);
                        
                if($activeVersionDetail->active_version_key == 'financialCheckup_cashflowAnalysis') {
                    $cycle_update['cashflow_analysis_version_approved'] = $activeVersionDetail->version;
                } elseif($activeVersionDetail->active_version_key == 'financialCheckup_portfolioAnalysis') {
                    $cycle_update['portfolio_analysis_version_approved'] = $activeVersionDetail->version;
                } elseif($activeVersionDetail->active_version_key == 'planAnalysis') {
                    $cycle_update['plan_analysis_version_approved'] = $activeVersionDetail->version;
                    $cycle_update['completed_at'] = Carbon::now();
                }
                
                /**
                 * Melakukan update di tabel cycles, untuk menentukan 
                 */

                Cycle::where('id', $cycle_saved['cycle_id'])->update($cycle_update);
                        
                $activeVersionDetail->update($data_update);
            }

            DB::commit();


            /**
             | ----------------------------------------------------------------------------------------------------
             | Kembalikan data mutasi yang sudah di kategorikan ke uncategories, 
             | ketika category tidak di pilih lagi saat financial checkup di periode yang sama.
             | ----------------------------------------------------------------------------------------------------
             | 10 September 2018
             | 
             */
            $this->updateCategoryMutasiAfterFincheck($user_id, $module_version);



            return $attributes_res;
        }

        throw new ValidationException('Financial checkup cashflow analysis validation failed', $this->getErrors());
    }


    public function updateCategoryMutasiAfterFincheck($user_id, $version) {
        /**
         | ----------------------------------------------------------------------------------------------------
         | Kembalikan data mutasi yang sudah di kategorikan ke uncategories,
         | ketika category tidak di pilih lagi saat financial checkup di periode yang sama.
         | ----------------------------------------------------------------------------------------------------
         | 10 September 2018
         | 
         */

        /**
         | ----------------------------------------------------------------------------------------------------
         | Query ini di gunkana untuk mengambil daftar bank_statements yang sudah di category kan ke pengeluaran
         | dan category nya tidak di ambil lagi oleh user tesebut saat checkup di periode yang sama dengan data mutasi.
         | sehingga kita perlu uncategories lagi data mutasinya...
         | 
         | Outputnya hanya daftar id bank_statements / data mutasi yang perlu di uncategorise...
         */

        $bank_statement_update_category_null = \DB::select("
            select 
                a.id
            from 
                bank_statements a
                INNER JOIN users c ON a.user_id = c.id
            where
                a.user_id = '".$user_id."'
                and a.user_expense_id is not null
                and 
                    case when EXTRACT(day FROM CURRENT_DATE) > c.cutoff_date then
                        a.transaction_date
                    else
                        date_trunc('day', a.transaction_date + interval '1 month') 
                    end
                >=
                to_timestamp(
                case 
                    when (c.cutoff_date = 0) then '01'
                    when (c.cutoff_date > 9) then c.cutoff_date::text
                    else coalesce(0::text, '') || coalesce( c.cutoff_date::text , '')
                end
                || '-' ||
                case when (EXTRACT(month FROM CURRENT_DATE) > 9) then
                    EXTRACT(month FROM CURRENT_DATE)::text
                else 
                    coalesce(0::text, '') || coalesce( EXTRACT(month FROM CURRENT_DATE)::text , '')
                end
                || '-' || 
                EXTRACT(year FROM CURRENT_DATE)::text
                , 'DD-MM-YYYY'
                )::timestamp without time zone
                and a.user_expense_id in (
                    -- Daftar category expense yang engga di pilih di fincheck terbaru
                    select distinct aa.id from taxonomies aa
                    where
                        aa.post_type = 'wallet'
                        and (
                            aa.parent_id = '33'
                            or aa.parent_id in (
                                select bb.id from taxonomies bb where bb.parent_id = '33'
                            )
                        )
                        and aa.id not in (
                            select distinct cc.taxo_wallet_id from expenses cc
                            where 
                                cc.user_id = '".$user_id."' 
                                and cc.version = '".$version."'
                        )
                )
        ");
            
        foreach($bank_statement_update_category_null as $id => $bsucn) {

            $id_bs = $bank_statement_update_category_null[$id]->id;

            DB::table('bank_statements')
                ->where('id', $id_bs)
                ->update(['user_expense_id' => null]);

            DB::commit();
        }

        /**
         | End ...
         | --------------------------------------------------
         */
    }


    public function updateCategoryMutasiSplitAfterFincheck($user_id, $taxo_wallet_id)
    {
        //request payload from cashflow store taxo_wallet_id
        foreach($taxo_wallet_id['expenses'] as $expense_id){
            $nested = $expense_id['taxo_wallet_id'];
            $data[] = $nested;
        }

        foreach($taxo_wallet_id['debt_repayments'] as $debt_id){
            $nested = $debt_id['taxo_wallet_id'];
            $data[] = $nested;
        }

        foreach($taxo_wallet_id['asset_repayments'] as $asset_id){
            $nested = $asset_id['taxo_wallet_id'];
            $data[] = $nested;
        }

        foreach($taxo_wallet_id['insurances'] as $insurances){
            $nested = $insurances['taxo_wallet_id'];
            $data[] = $nested;
        }

        //end cashflow store
        $distinct = array_unique($data);

        $result_splits = DB::table('bank_statements_split_expense_categories')->where('user_id', $user_id)->get();

        foreach($result_splits as $result_split){
            if(!in_array($result_split->user_expense_id, $distinct)){
                $bank_statements = DB::table('bank_statements')->where('id', $result_split->bank_statement_id)->first();

                if(!is_null($bank_statements)){
                    $amount_balance = $bank_statements->transaction_amount - $result_split->amount;

                    if($amount_balance < 1){
                        DB::table('bank_statements_split_expense_categories')
                            ->where('id', $result_split->id)
                            ->update(['bank_statement_id' => null,'user_expense_id' => null]);


                        DB::table('bank_statements')
                            ->where('id', $bank_statements->id)
                            ->update(['is_categorized' => null]);

                    }else{
                        DB::table('bank_statements_split_expense_categories')
                        ->where('id', $result_split->id)
                        ->update(['bank_statement_id' => null,'user_expense_id' => null]);


                        DB::table('bank_statements')
                            ->where('id', $bank_statements->id)
                            ->update(['is_categorized' => 0]);
                    }
                }
            }
        }
    }


    /**
     | END Self Financial Checkup ...
     | Version 2
     | 29 Agustus 2018
     | ----------------------------------------------------------------------------------------------------
     */




















    private function updateWalletTransactions($user_id, $module_version){
        //get all expense from expenses where version is prev version -> X1
        $old_wallet_transactions_raw = Expense::select('id', 'taxo_wallet_id')->where('user_id', $user_id)->where('version', ($module_version-1))->get()->toArray();
        $old_wallet_transactions = array_column($old_wallet_transactions_raw, 'id', 'taxo_wallet_id'); //dd($old_wallet_transactions);
        $old_wallet_transactions_reverse = array_column($old_wallet_transactions_raw, 'taxo_wallet_id', 'id'); //dd($old_wallet_transactions_reverse);

        $existing_wallet_transactions_raw = Expense::select('id', 'taxo_wallet_id')->where('user_id', $user_id)->where('version', ($module_version))->get()->toArray();
        $existing_wallet_transactions = array_column($existing_wallet_transactions_raw, 'id', 'taxo_wallet_id'); //dd($existing_wallet_transactions);
        
        //mapping taxo wallet ids to the new expense ids -> X2

        //dd(array_values($old_wallet_transactions));
        //loop data from X1 and update the expense id from 
        $wallet_transactions = WalletTransaction::where('user_id', $user_id)->whereIn('detail_id', array_values($old_wallet_transactions))->get();
        //dd($wallet_transactions);
        if(count($wallet_transactions)){
            $copy_data_wallet_transactions = [];
            foreach($wallet_transactions as $wallet_transaction){ //dd($old_wallet_transactions_reverse);
                if(isset($old_wallet_transactions_reverse[$wallet_transaction->detail_id])){
                    $taxo_wallet_id = $old_wallet_transactions_reverse[$wallet_transaction->detail_id]; //dd($taxo_wallet_id);
                    if(isset($existing_wallet_transactions[$taxo_wallet_id])){
                        $existing_detail_id = $existing_wallet_transactions[$taxo_wallet_id]; //dd($existing_detail_id);
                        $copy_data_wallet_transactions[] = [
                            'user_id' => $wallet_transaction->user_id,
                            'taxo_wallet_module_title' => $wallet_transaction->taxo_wallet_module_title,
                            'detail_id' => $existing_detail_id,
                            'amount' => $wallet_transaction->amount,
                            'note' => $wallet_transaction->note,
                            'transaction_date' => $wallet_transaction->transaction_date,
                            'cashflow_analysis_version' => $module_version,
                            'created_by' => $user_id, 
                            'created_at' => Carbon::now(), 
                            'updated_by' => $user_id, 
                            'updated_at' => Carbon::now(),
                            'record_flag' => $wallet_transaction->record_flag
                        ];
                    }
                }
            }
            if(count($copy_data_wallet_transactions))
                WalletTransaction::insert($copy_data_wallet_transactions);
        }
    }

    private function updateAssetRepaymentUsage($user_id, $module_version){
        //model PlanAnalysisActivated
        //$planAnalysis_activeVersion = ActiveVersion::where('user_id', $user_id)->where('key', 'planAnalysis')->first(); //dd($planAnalysis_activeVersion->toArray());
        
        $maxApprovedPlanAnalysisVersion = ActiveVersionDetail::where('active_version_key', 'planAnalysis')
            ->where('user_id', $user_id)
            ->where('status', 'approved')->max('version');

        if(!is_null($maxApprovedPlanAnalysisVersion)){//pastikan bahwa plan analysis untuk user id ini sudah pernah dibuat
            $old_data_assetRepayments = PlanAnalysisActivated::select('plan_analysis_activated.id','asset_repayment_id', 'ar.nama', 'ar.taxo_wallet_id')
            ->where('plan_analysis_activated.user_id', $user_id)
            ->where('plan_analysis_activated.version', $maxApprovedPlanAnalysisVersion)
            ->join('asset_repayments as ar', 'ar.id', '=', 'plan_analysis_activated.asset_repayment_id', 'left')
            ->get();
            if(!is_null($old_data_assetRepayments)){
                foreach ($old_data_assetRepayments as $old_data_assetRepayment) {
                    //cek asset repayment id dari versi cashflow analysis yg baru berdasarkan nama aset repayment nya ( kan dari aplikasi sudah dibatasi untuk nama hanya boleh satu)
                    $new_data_assetRepayment = AssetRepayment::where('user_id', $user_id)->where('version', $module_version)->where('nama', $old_data_assetRepayment->nama)->first();
                    if(!is_null($new_data_assetRepayment)){
                        $new_data_assetRepayment_id = $new_data_assetRepayment->id;
                        PlanAnalysisActivated::where('id', $old_data_assetRepayment->id)->update([
                            'asset_repayment_id' => $new_data_assetRepayment_id
                        ]);
                    }
                }
            }
        }

        //model AssetRepaymentPaid
        //$portfolioAnalysis_activeVersion = ActiveVersion::where('user_id', $user_id)->where('key', 'financialCheckup_portfolioAnalysis')->first(); //dd($planAnalysis_activeVersion->toArray());
        $maxApprovedPortfolioAnalysisVersion = ActiveVersionDetail::where('active_version_key', 'financialCheckup_portfolioAnalysis')
            ->where('user_id', $user_id)
            ->where('status', 'approved')->max('version');
        if(!is_null($maxApprovedPortfolioAnalysisVersion)){//pastikan bahwa plan analysis untuk user id ini sudah pernah dibuat
            $old_data_assetRepayments = AssetRepaymentPaid::select('asset_repayments_paid.id','asset_repayment_id', 'ar.nama', 'ar.taxo_wallet_id')
            ->where('asset_repayments_paid.user_id', $user_id)
            ->where('asset_repayments_paid.version', $maxApprovedPortfolioAnalysisVersion)
            ->join('asset_repayments as ar', 'ar.id', '=', 'asset_repayments_paid.asset_repayment_id', 'left')
            ->get();
            //dd($old_data_assetRepayments->toArray());
            if(!is_null($old_data_assetRepayments)){
                foreach ($old_data_assetRepayments as $old_data_assetRepayment) {
                    //cek asset repayment id dari versi cashflow analysis yg baru berdasarkan nama aset repayment nya ( kan dari aplikasi sudah dibatasi untuk nama hanya boleh satu)
                    $new_data_assetRepayment = AssetRepayment::where('user_id', $user_id)->where('version', $module_version)->where('nama', $old_data_assetRepayment->nama)->first();
                    if(!is_null($new_data_assetRepayment)){
                        $new_data_assetRepayment_id = $new_data_assetRepayment->id;
                        AssetRepaymentPaid::where('id', $old_data_assetRepayment->id)->update([
                            'asset_repayment_id' => $new_data_assetRepayment_id
                        ]);
                    }
                }
            }
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

    public function needApproval($attributes) {
        $res = [];
        $user_id = $attributes['user_id'];
        //\DB::enableQueryLog();
        $finance_details = ActiveVersionDetail::where('user_id', $user_id)->where('status', 'draft')->get();

        //last version dari plan analysis yang statusnya approved
        $maxApprovedPlanAnalysisVersion = ActiveVersionDetail::where('active_version_key', 'planAnalysis')
            ->where('user_id', $user_id)
            ->where('status', 'draft')->max('version');//menampilkan yang action plan terlebih dahulu

        //dd(\DB::getQueryLog());
        if(!is_null($finance_details)){
            foreach ($finance_details as $finance_detail) {
                switch ($finance_detail->active_version_key) {
                    case 'financialCheckup_cashflowAnalysis':
                        $res['cashflowAnalysis'] = $this->showByVersion(['user_id' => $user_id, 'status' => 'draft']);
                        $res['cashflowAnalysis']['active_version_detail_id'] = $finance_detail->id;
                    break;
                    case 'financialCheckup_portfolioAnalysis':
                        $res['portfolioAnalysis'] = $this->portfolioAnalysis->showByVersion(['user_id' => $user_id, 'status' => 'draft']);
                        $res['portfolioAnalysis']['active_version_detail_id'] = $finance_detail->id;
                    break;
                    case 'planAnalysis':
                        //cek issue #285
                        //cek total items
                        $totalActionPlan = ClientActionPlanDetail::whereHas('clientActionPlan', function($q) use ($user_id, $maxApprovedPlanAnalysisVersion){
                            $q->where('user_id', $user_id)
                            ->where('version', $maxApprovedPlanAnalysisVersion);
                        })->count('id'); 
                       
                        $totalActionPlanProcessed = ClientActionPlanDetail::whereHas('clientActionPlan', function($q) use ($user_id, $maxApprovedPlanAnalysisVersion){
                            $q->where('user_id', $user_id)
                            ->where('version', $maxApprovedPlanAnalysisVersion);
                        })->where('status', '<>', 'draft')->count('id'); 
                       
                        $totalActionPlanHasRejected = ClientActionPlanDetail::whereHas('clientActionPlan', function($q) use ($user_id, $maxApprovedPlanAnalysisVersion){
                            $q->where('user_id', $user_id)
                            ->where('version', $maxApprovedPlanAnalysisVersion);
                        })->where('status', 'rejected')->count('id');
                        
                        //semua sudah diproses tetapi ada salah satu statusnya rejected
                        if($totalActionPlan === 0 || ( $totalActionPlan == $totalActionPlanProcessed && $totalActionPlanHasRejected > 0)){
                            $res['planAnalysis'] = $this->planAnalysis->showByVersion(['user_id' => $user_id, 'status' => 'draft']);
                            $res['planAnalysis']['active_version_detail_id'] = $finance_detail->id;
                        }
                    break;
                }
            }
        }

        $plan_details = ActiveVersionPlanDetail::where('user_id', $user_id)->where('status', 'draft')->get();
        if(!is_null($plan_details)){
            $plan_a_idx = 0;
            $plan_b_idx = 0;
            foreach ($plan_details as $plan_detail) {
                switch ($plan_detail->plan_type) {
                    case 'plan_a':
                        $res['a_plans'][$plan_a_idx] = $this->planA->showByVersion([
                            'user_id' => $user_id, 
                            'plan_id' => $plan_detail->plan_id,
                            'status' => 'draft'
                        ]);
                        $res['a_plans'][$plan_a_idx]['active_version_plan_detail_id'] = $plan_detail->id;
                        $plan_a_idx++;
                        break;
                    case 'plan_b':
                        $res['b_plans'][$plan_b_idx] = $this->planB->showByVersion([
                            'user_id' => $user_id, 
                            'plan_id' => $plan_detail->plan_id,
                            'status' => 'draft'
                        ]);
                        $res['b_plans'][$plan_b_idx]['active_version_plan_detail_id'] = $plan_detail->id;
                        $plan_b_idx++;
                        break;
                }
            }
        }

        //last version dari plan analysis yang statusnya approved
        // $maxApprovedPlanAnalysisVersion = ActiveVersionDetail::where('active_version_key', 'planAnalysis')
        //     ->where('user_id', $user_id)
        //     ->where('status', 'draft')->max('version');//menampilkan yang action plan terlebih dahulu

        if(!is_null($maxApprovedPlanAnalysisVersion)){
            //$maxApprovedPlanAnalysisVersion = is_null($maxApprovedPlanAnalysisVersion)?'':$maxApprovedPlanAnalysisVersion;
            $draft_action_plans = ClientActionPlanDetail::whereHas('clientActionPlan', function($q) use ($user_id, $maxApprovedPlanAnalysisVersion){
                    $q->where('user_id', $user_id)
                    ->where('version', $maxApprovedPlanAnalysisVersion);
                })->where('status', 'draft')
                ->get();
           
            if(!is_null($draft_action_plans) && count($draft_action_plans)){
                $res['action_plans'] = ClientActionPlan::with(['details' => function($q){
                    $q->select('client_action_plan_details.*', 't.title as taxo_action_plan_title', 't2.title as taxo_action_plan_parent_title', 't2.id as taxo_action_plan_parent_id')
                    ->join('taxonomies as t', 't.id', '=', 'taxo_action_plan_id', 'left')
                    ->join('taxonomies as t2', 't2.id', '=', 't.parent_id', 'left')
                    ->where('status', 'draft');
                }])->where('user_id', $user_id)
                ->where('version', $maxApprovedPlanAnalysisVersion)
                ->get();
            }
        }
        return count($res)?$res:null;
    }

    public function approveFinance($attributes) { 
        $rules['user_id'] = 'required';
        $rules['status'] = 'required';
        $rules['active_version_detail_id'] = 'required';
        if(isset($attributes['status']) && $attributes['status'] == 'rejected'){
            $rules['reason_reject'] = 'required';
        }

        $attributeNames['user_id'] = 'user_id';
        $attributeNames['active_version_detail_id'] = 'active_version_detail_id';
        $validator = Valid::make($attributes, $rules);
        $validator->setAttributeNames($attributeNames);

        if ($validator->fails())
        { 
            throw new ValidationException('Approve finance validation failed', $validator->errors());
        }

        $user_id = $attributes['user_id'];
        $active_version_detail_id = $attributes['active_version_detail_id'];
        $status = $attributes['status'];
        $reason_reject = isset($attributes['status'])?$attributes['status']:'';

        DB::beginTransaction();
        $activeVersionDetail = ActiveVersionDetail::where('id', $active_version_detail_id)
        ->where('user_id', $user_id)
        ->where('status', 'draft')->first();
        if(is_null($activeVersionDetail)){
            throw new ValidationException('Approval failed', [
                'finance' => 'Data not found or already approved or rejected',
            ]);
        }else{
            $data_update = [
                'status' => $status
            ];
            switch ($status) {
                case 'approved':
                    $data_update += [
                        'approved_by' => $user_id, 
                        'approved_at' => Carbon::now(),
                        'updated_by' => $user_id, 
                        'updated_at' => Carbon::now()
                    ];

                    //to do :Cek nih dah bener beloom
                    //get cycle id
                    $cycle_saved = $this->cycle->is_full_cycle($user_id);
                    //if($cycle_saved['next_step'] == $activeVersionDetail->active_version_key){//sebenarnya ini untuk mencegah agar next step nya sesuai urutan mulai dari cashflow, portfolio, plan analysis
                        if($activeVersionDetail->active_version_key == 'financialCheckup_cashflowAnalysis'){
                            $cycle_update['cashflow_analysis_version_approved'] = $activeVersionDetail->version;
                        }elseif($activeVersionDetail->active_version_key == 'financialCheckup_portfolioAnalysis'){
                            $cycle_update['portfolio_analysis_version_approved'] = $activeVersionDetail->version;
                        }elseif($activeVersionDetail->active_version_key == 'planAnalysis'){
                            $cycle_update['plan_analysis_version_approved'] = $activeVersionDetail->version;
                            $cycle_update['completed_at'] = Carbon::now();
                        }
                    //}
                    Cycle::where('id', $cycle_saved['cycle_id'])->update($cycle_update);

                    break;
                case 'rejected' : 
                    $data_update += [
                        'reason_reject' => $reason_reject,
                        'rejected_by' => $user_id,
                        'rejected_at' => Carbon::now(),
                        'updated_by' => $user_id, 
                        'updated_at' => Carbon::now()
                    ];

                    if($activeVersionDetail->active_version_key == 'planAnalysis'){
                        $restore_status_plans = PlanAnalysisActivated::where('version', $activeVersionDetail->version)
                        ->where('user_id', $activeVersionDetail->user_id)->get();
                        if(!is_null($restore_status_plans)){ //mengembalikan status plan
                            $id_a_plans = [];
                            $id_b_plans = [];
                            foreach ($restore_status_plans as $restore_status_plan) {
                                if($restore_status_plan->plan_type == 'a'){
                                    $id_a_plans[] = $restore_status_plan->plan_id;
                                } elseif($restore_status_plan->plan_type == 'b'){
                                    $id_b_plans[] = $restore_status_plan->plan_id;
                                }
                            }

                            if(count($id_a_plans)){
                                $id_a_plans = array_unique($id_a_plans);
                                PlanA::whereIn('id', $id_a_plans)->update([
                                    'status' => 0 
                                ]);
                            }elseif(count($id_b_plans)){
                                $id_b_plans = array_unique($id_b_plans);
                                PlanB::whereIn('id', $id_b_plans)->update([
                                    'status' => 0 
                                ]);
                            }
                        }
                    }

                break;
            }

            $activeVersionDetail->update($data_update);
        }
        DB::commit();
        return true;
    }

    public function approvePlan($attributes) { //dd($attributes);
        $rules['user_id'] = 'required';
        $rules['status'] = 'required';
        $rules['active_version_plan_detail_id'] = 'required';
        if(isset($attributes['status']) && $attributes['status'] == 'rejected'){
            $rules['reason_reject'] = 'required';
        }

        $attributeNames['user_id'] = 'user_id';
        $attributeNames['active_version_plan_detail_id'] = 'active_version_plan_detail_id';
        $validator = Valid::make($attributes, $rules);
        $validator->setAttributeNames($attributeNames);

        if ($validator->fails())
        { 
            //dd($validator->errors());
            throw new ValidationException('Approve plan validation failed', $validator->errors());
        }

        //if($validator->isValid($attributes)) {
        $user_id = $attributes['user_id'];
        $active_version_plan_detail_id = $attributes['active_version_plan_detail_id'];
        $status = $attributes['status'];
        $reason_reject = isset($attributes['status'])?$attributes['status']:'';

        DB::beginTransaction();
        $activeVersionPlanDetail = ActiveVersionPlanDetail::where('id', $active_version_plan_detail_id)
        ->where('user_id', $user_id)
        ->where('status', 'draft')->first();
        if(is_null($activeVersionPlanDetail)){
            throw new ValidationException('Approval failed', [
                'plan' => 'Data not found or already approved or rejected',
            ]);
        }else{
            $data_update = [
                'status' => $status
            ];
            switch ($status) {
                case 'approved':
                    $data_update += [
                        'approved_by' => $user_id, 
                        'approved_at' => Carbon::now(),
                        'updated_by' => $user_id, 
                        'updated_at' => Carbon::now()
                    ];
                    break;
                case 'rejected' : 
                    $data_update += [
                        'reason_reject' => $reason_reject,
                        'rejected_by' => $user_id,
                        'rejected_at' => Carbon::now(),
                        'updated_by' => $user_id, 
                        'updated_at' => Carbon::now()
                    ];
                break;
            }

            $activeVersionPlanDetail->update($data_update);
        }
        DB::commit();
        return true;
        //}
        //throw new ValidationException('Approve plan validation failed', $validator->getErrors());
    }

    public function approveActionPlan($attributes) {
        $rules['user_id'] = 'required';
        $rules['status'] = 'required';
        $rules['action_plan_detail_id'] = 'required';
        if(isset($attributes['status']) && $attributes['status'] == 'rejected'){
            $rules['reason_reject'] = 'required';
        }

        $attributeNames['user_id'] = 'user_id';
        $attributeNames['action_plan_detail_id'] = 'action_plan_detail_id';
        $validator = Valid::make($attributes, $rules);
        $validator->setAttributeNames($attributeNames);

        if ($validator->fails())
        { 
            //dd($validator->errors());
            throw new ValidationException('Approve action plan validation failed', $validator->errors());
        }

        //if($validator->isValid($attributes)) {
        $user_id = $attributes['user_id'];
        $action_plan_detail_id = $attributes['action_plan_detail_id'];
        $status = $attributes['status'];
        $reason_reject = isset($attributes['status'])?$attributes['status']:'';

        DB::beginTransaction();
        $actionPlanDetail = ClientActionPlanDetail::where('id', $action_plan_detail_id)
        ->where('status', 'draft')->first();
        if(is_null($actionPlanDetail)){
            throw new ValidationException('Approval failed', [
                'action_plan' => 'Data not found or already approved or rejected',
            ]);
        }else{
            $data_update = [
                'status' => $status
            ];
            switch ($status) {
                case 'approved':
                    $data_update += [
                        'approved_by' => $user_id, 
                        'approved_at' => Carbon::now(),
                        'updated_by' => $user_id, 
                        'updated_at' => Carbon::now()
                    ];
                    break;
                case 'rejected' : 
                    $data_update += [
                        'reason_reject' => $reason_reject,
                        'rejected_by' => $user_id,
                        'rejected_at' => Carbon::now(),
                        'updated_by' => $user_id, 
                        'updated_at' => Carbon::now()
                    ];
                break;
            }

            $actionPlanDetail->update($data_update);

            //cek jika seluruhnya approved maka approve otomatis plan analysisnya
            $maxApprovedPlanAnalysisVersion = ActiveVersionDetail::where('active_version_key', 'planAnalysis')
                ->where('user_id', $user_id)
                ->where('status', 'draft')->max('version');
                
            if(!is_null($maxApprovedPlanAnalysisVersion)){
                $totalActionPlan = ClientActionPlanDetail::whereHas('clientActionPlan', function($q) use ($user_id, $maxApprovedPlanAnalysisVersion){
                    $q->where('user_id', $user_id)
                    ->where('version', $maxApprovedPlanAnalysisVersion);
                })->count('id'); 

                $totalActionPlanHasApproved = ClientActionPlanDetail::whereHas('clientActionPlan', function($q) use ($user_id, $maxApprovedPlanAnalysisVersion){
                    $q->where('user_id', $user_id)
                    ->where('version', $maxApprovedPlanAnalysisVersion);
                })->where('status', 'approved')->count('id');

                if($totalActionPlan == $totalActionPlanHasApproved){
                    //update status
                    $planAnalysisId = ActiveVersionDetail::where('active_version_key', 'planAnalysis')
                        ->where('user_id', $user_id)
                        ->where('status', 'draft')->pluck('id');

                    $this->approveFinance([
                        'user_id' => $user_id,
                        'active_version_detail_id' => $planAnalysisId,
                        'status' => 'approved'
                    ]);
                    
                }
            }
            //end check
        }

        $totalActionPlan = ClientActionPlanDetail::whereHas('clientActionPlan', function($q) use ($user_id, $maxApprovedPlanAnalysisVersion){
            $q->where('user_id', $user_id)
            ->where('version', $maxApprovedPlanAnalysisVersion);
        })->count('id'); 
       
        $totalActionPlanProcessed = ClientActionPlanDetail::whereHas('clientActionPlan', function($q) use ($user_id, $maxApprovedPlanAnalysisVersion){
            $q->where('user_id', $user_id)
            ->where('version', $maxApprovedPlanAnalysisVersion);
        })->where('status', '<>', 'draft')->count('id'); 
       
        $totalActionPlanHasRejected = ClientActionPlanDetail::whereHas('clientActionPlan', function($q) use ($user_id, $maxApprovedPlanAnalysisVersion){
            $q->where('user_id', $user_id)
            ->where('version', $maxApprovedPlanAnalysisVersion);
        })->where('status', 'rejected')->count('id');

        $totalActionPlanApproved = ClientActionPlanDetail::whereHas('clientActionPlan', function($q) use ($user_id, $maxApprovedPlanAnalysisVersion){
            $q->where('user_id', $user_id)
            ->where('version', $maxApprovedPlanAnalysisVersion);
        })->where('status', 'approved')->count('id');
        
        DB::commit();

        $rating_form = false;
        if($totalActionPlan == $totalActionPlanApproved){
            $rating_form = true;
        }

        $res = [
            'plan_analysis_version' => $maxApprovedPlanAnalysisVersion,
            'action_plan_total' => $totalActionPlan,
            'action_plan_processed' => $totalActionPlanProcessed,
            'action_plan_rejected' => $totalActionPlanHasRejected,
            'action_plan_approved' => $totalActionPlanHasApproved,
            'rating_form' => $rating_form
        ];
        //dd($res);
        return $res;
    }

    public function checkApproval($attributes) { 
        $res = [];
        $user_id = $attributes['user_id']; 
        //$module = $attributes['module'];
        $activeVersion = ActiveVersion::where('user_id', $user_id)->where('key', 'financialCheckup_cashflowAnalysis')->first();
        $module_version = is_null($activeVersion)?'':$activeVersion->version;
        if($module_version === '')
            return 1;
        //dd($module_version);
        $need_approval = ActiveVersionDetail::where('user_id', $user_id)
            ->where('version', $module_version)
            ->where('active_version_key', 'financialCheckup_cashflowAnalysis')
            ->where('status', 'draft')->count();

        return $need_approval>0?0:1;
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

    public function delete($id) {
        $Wallet = $this->wallet->findOrFail($id);
        $Wallet->delete();
    }

    protected function totalTransactions($all = false) {
        return $this->wallet->count();
    }

    public function hasApprovedData($attributes) { 
        //$res = [];
        $user_id = $attributes['user_id'];
       // \DB::enableQueryLog();
        $last_status = ActiveVersionDetail::select('status')
            ->where('user_id', $user_id)
            ->where('active_version_key', 'financialCheckup_cashflowAnalysis')
            ->orderBy('version', 'desc')
            //->get();
            ->limit(1)
            ->pluck('status');
            //->first();
        //dd(\DB::getQueryLog());
        return $last_status;
    }
}
