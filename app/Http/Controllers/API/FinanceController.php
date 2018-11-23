<?php namespace App\Http\Controllers\API;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Repositories\CfpSchedule\CfpScheduleInterface;
use App\Repositories\User\UserInterface;
use Redirect;
use View;
use Input;
use Validator;
use Response;
use App\Repositories\CfpSchedule\CfpScheduleRepository as CfpSchedule;
use App\Exceptions\Validation\ValidationException;
use Config;
use Entrust;

use App\User;
use App\Repositories\Wallet\WalletRepository as Wallet;
use Carbon\Carbon;
use App\Repositories\Taxonomy\TaxonomyInterface;
use App\Repositories\CashflowAnalysis\CashflowAnalysisRepository;
use App\Repositories\PortfolioAnalysis\PortfolioAnalysisRepository;
use App\Repositories\LogBookNote\LogBookNoteRepository;
use App\Repositories\PlanAnalysis\PlanAnalysisRepository;
use App\Repositories\PlanA\PlanARepository;
use App\Repositories\PlanB\PlanBRepository;
use App\Repositories\WalletTransaction\WalletTransactionRepository;

use App\Models\AssetRepayment;
use App\Models\AssetRepaymentPaid;
use App\Repositories\EomBalance\EomBalanceRepository;
use App\Repositories\InvestmentInformation\InvestmentInformationRepository;
use App\Repositories\InvestmentInformationClient\InvestmentInformationClientRepository;
use App\Repositories\FinancialHealth\FinancialHealthRepository;
use App\Models\ActiveVersionDetail;
use App\Models\FinancialHealth;
use App\Repositories\Cycle\CycleRepository;
use App\Repositories\ConvertCash\ConvertCashRepository;
use Auth;

use App\Taxonomy;

class FinanceController extends Controller {

	protected $cashflowAnalysis;
	protected $portfolioAnalysis;
	protected $user;
	protected $planAnalysis;
	protected $planA;
	protected $planB;
	protected $eomBalance;
	protected $investmentInformation;
	protected $financialHealth;
	protected $cycle;
	protected $convertCash;

    public function __construct(TaxonomyInterface $taxonomy, UserInterface $user, CashflowAnalysisRepository $cashflowAnalysis, PortfolioAnalysisRepository $portfolioAnalysis, LogBookNoteRepository $logBookNote, PlanAnalysisRepository $planAnalysis, PlanARepository $planA, PlanBRepository $planB, WalletTransactionRepository $walletTransaction, EomBalanceRepository $eomBalance, InvestmentInformationRepository $investmentInformation, InvestmentInformationClientRepository $investmentInformationClient, FinancialHealthRepository $financialHealth, CycleRepository $cycle, ConvertCashRepository $convertCash) {
        $this->user = $user;
        $this->cashflowAnalysis = $cashflowAnalysis;
        $this->portfolioAnalysis = $portfolioAnalysis;
        $this->planAnalysis = $planAnalysis;
        $this->taxonomy = $taxonomy;
        $this->logBookNote = $logBookNote;
        $this->planA = $planA;
		$this->planB = $planB;
		$this->walletTransaction = $walletTransaction;
		$this->eomBalance = $eomBalance;
		$this->investmentInformation = $investmentInformation;
		$this->investmentInformationClient = $investmentInformationClient;
		$this->FinancialHealth = $financialHealth;
		$this->cycle = $cycle;
		$this->convertCash = $convertCash;
    }
	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		//
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		//
	}

	public function store(Request $request)
	{ 
		$transaction_type_id = $request->input('transaction_type_id');
		
        $category_id = $request->input('category_id');
        
        $notes = $request->input('notes');

        $transaction_date = trim($request->input('transaction_date')) == ''?'':Carbon::parse($request->input('transaction_date'))->format('Y-m-d');
        $category_type_id = $request->input('category_type_id'); //dd($request->input('amount'));
        $amount = trim($request->input('amount')) == '' || $request->input['amount'] === 0?'':unformat_money($request->input('amount'));
       //dd($amount);
		$user_id = $request->input('user_id');
		try {
			$data = $this->wallet->create([
				'user_id' => $user_id,
				'transaction_type' => $transaction_type_id,
	            'amount' => $amount,
	            'wallet_category' => $category_id,
	            'category_type' => $category_type_id,
	            'notes' => $notes,
	            'transaction_date' => $transaction_date,
	            'created_by' => $user_id, 
	            'created_at' => Carbon::now(), 
	            'updated_by' => $user_id, 
	            'updated_at' => Carbon::now(),
	            'record_flag' => 'N'
			]);
            return response()->json([
				'result' => 'success',
				'data' => $data
			]);
        } catch (ValidationException $e) {
            return response()->json([
					'result' => 'error',
					'alerts' => $e->getErrors()
			]);
        }
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		//
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		//$input = Input::all();
		//dd($input);
		$alerts = [
				'id' => null
			];

		$cfpSchedule = $this->cfpSchedule->find($id);
        //dd($cfpSchedule);
        if($cfpSchedule['id']){

        	$alerts['id'] = $cfpSchedule['id'];
        	return response()->json([
				'result' => 'error',
				'alerts' => $alerts, 
				'data' => null
			]);
        }

        return response()->json([
			'result' => 'success',
			'alerts' => $alerts, 
			'data' => $cfpSchedule->toArray()
		]);
	}

	public function getJson()
	{
		
	}

	public function update(Request $request)
	{ 
		$transaction_type_id = $request->input('transaction_type_id');
		//$amount = unformat_money($request->input('amount'));
        $category_id = $request->input('category_id');
        
        $notes = $request->input('notes');
        //$transaction_date = Carbon::parse($request->input('transaction_date'))->format('Y-m-d');

        $transaction_date = trim($request->input('transaction_date')) == ''?'':Carbon::parse($request->input('transaction_date'))->format('Y-m-d');
        $category_type_id = $request->input('category_type_id'); //dd($request->input('amount'));
        $amount = trim($request->input('amount')) == '' || $request->input['amount'] === 0?'':unformat_money($request->input('amount'));
       //dd($amount);
		$user_id = $request->input('user_id');
		try {
			$data = $this->wallet->update($request->input('id'), [
				'user_id' => $user_id,
				'transaction_type' => $transaction_type_id,
	            'amount' => $amount,
	            'wallet_category' => $category_id,
	            'category_type' => $category_type_id,
	            'notes' => $notes,
	            'transaction_date' => $transaction_date,
	            'updated_by' => $user_id, 
	            'updated_at' => Carbon::now(),
	            'record_flag' => 'U'
			]);
            return response()->json([
				'result' => 'success',
				'data' => $data
			]);
        } catch (ValidationException $e) {
            return response()->json([
					'result' => 'error',
					'alerts' => $e->getErrors()
			]);
        }
    }

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy() //delete belom selesai , terakhir ngerjain tanggal 10 oct 2017
	{
		try {
			$wallet = $this->wallet->find(Input::get('id'));
	        if($wallet['id']){
	        	$wallet->delete();
	            return response()->json([
					'result' => 'success'
				]);
	        }

	        return response()->json([
					'result' => 'error',
					'alerts' => [
						'id' => 'not exist'
					]
			]);
			
        } catch (ValidationException $e) {
            return response()->json([
					'result' => 'error',
					'alerts' => $e->getErrors()
			]);
        }
		
	}

	public function getWalletCategories()
	{							 
		$wallet_categories_raw = $this->taxonomy->getTermsByPostType_n_parent('wallet', 'expense');

		$auth = Auth::user()->id;
		$user_role = $this->user->find($auth);

		if(is_null($wallet_categories_raw)){
			return response()->json([
				'result' => 'error',
				'data' => 'taxo_parent_not_found'
			]);
		}else{
			$wallet_categories_raw = $wallet_categories_raw->toHierarchy();
		}
		
		
		$wallet_categories = [];
		foreach ($wallet_categories_raw as $wallet_category_raw) {
			if($wallet_category_raw->slug != 'titipan-transfer' || $user_role['roles'][0]['name'] != "certified_financial_planner")
			$wallet_categories[] = $wallet_category_raw;
		}
		return response()->json([
				'result' => 'success',
				'data' => $wallet_categories
		]);
	}

	public function getDebtRepaymentCategories()
	{
		$debt_repayment_categories_raw = $this->taxonomy->getTermsByPostType_n_parent('wallet', 'debt')->toHierarchy();
		$debt_repayment_categories = [];
		foreach ($debt_repayment_categories_raw as $debt_repayment_category_raw) {
			$debt_repayment_categories[] = $debt_repayment_category_raw;
		}
		return response()->json([
				'result' => 'success',
				'data' => $debt_repayment_categories
		]);
	}

	public function getAssetRepaymentCategories()
	{
		$asset_repayment_categories_raw = $this->taxonomy->getTermsByPostType_n_parent('wallet', 'asset')->toHierarchy();
		$asset_repayment_categories = [];
		foreach ($asset_repayment_categories_raw as $asset_repayment_category_raw) {
			$asset_repayment_categories[] = $asset_repayment_category_raw;
		}
		return response()->json([
				'result' => 'success',
				'data' => $asset_repayment_categories
		]);
	}

	public function getInsuranceNames()
	{
		$insurance_names_raw = $this->taxonomy->getTermsByPostType_n_parent('wallet', 'insurance')->toHierarchy();
		$insurance_names = [];
		foreach ($insurance_names_raw as $insurance_name_raw) {
			$insurance_names[] = $insurance_name_raw;
		}
		return response()->json([
				'result' => 'success',
				'data' => $insurance_names
		]);
	}

	public function getInsuranceTypes()
	{
		$insurance_types_raw = $this->taxonomy->getTermsByPostType('insurance_type')->toHierarchy(); 
		$insurance_types = [];
		foreach ($insurance_types_raw as $insurance_type_raw) {
			$insurance_types[] = $insurance_type_raw;
		}
		return response()->json([
				'result' => 'success',
				'data' => $insurance_types
		]);
	}

	public function getIncomes()
	{
		$incomes_raw = $this->taxonomy->getTermsByPostType_n_parent('wallet', 'income')->toHierarchy(); 
		$incomes = [];
		foreach ($incomes_raw as $income_raw) {
			$incomes[] = $income_raw;
		}
		return response()->json([
			'result' => 'success',
			'data' => $incomes
		]);
	}

	public function storeCashflowAnalysis(Request $request){
		try {
			$data = $this->cashflowAnalysis->create($request->all());
            return response()->json([
				'result' => 'success',
				'data' => $data
			]);
        } catch (ValidationException $e) {
            return response()->json([
					'result' => 'error',
					'alerts' => $e->getErrors()
			]);
        }
	}






	/**
     | Category untuk Self financial checkup ...
     | Financila Checkup Client ...
     | ----------------------------------------------
     | 30 Agustus 2018
     | Gugun DP
     |
     | Function di bawah ini merupakan hasil copy dari function yang sudah ada
     | function untuk mengambil category dari pendapatan & pengeluaran
     |
     | Ada 4 function yang di copy
     | - getWalletCategories			-> getExpenseCategories
	 | - getDebtRepaymentCategories		-> getDebtCategories
	 | - getAssetRepaymentCategories	-> getAssetCategories
	 | - getInsuranceNames				-> getInsuranceCategories
	 |
     */


	public function getExpenseCategories()
	{
		$wallet_categories_raw = $this->taxonomy->getCategoryPengeluaranPendapatan('wallet', 'expense');

		$auth = Auth::user()->id;
		$user_role = $this->user->find($auth);

		if(is_null($wallet_categories_raw)){
			return response()->json([
				'result' => 'error',
				'data' => 'taxo_parent_not_found'
			]);
		}else{
			$wallet_categories_raw = $wallet_categories_raw->toHierarchy();
		}
		
		
		$wallet_categories = [];
		foreach ($wallet_categories_raw as $wallet_category_raw) {
			if(
				$wallet_category_raw->slug != 'titipan-transfer' AND
				$user_role['roles'][0]['name'] != "certified_financial_planner" AND
				$wallet_category_raw->is_hidden == null
			) {
				$wallet_categories[] = $wallet_category_raw;	
			}	
		}

		return response()->json([
			'result' => 'success',
			'data' => $wallet_categories
		]);
	}

	/** Daftar categori Expense yang Default (7 Category) */
	public function getExpenseDefaultCategories()
	{
		$datas = \DB::select("
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

		return response()->json([
				'result' => 'success',
				'data' => $datas
		]);
	}

	public function getDebtCategories()
	{
		$debt_repayment_categories_raw = $this->taxonomy->getCategoryPengeluaranPendapatan('wallet', 'debt')->toHierarchy();

		$debt_repayment_categories = [];
		foreach ($debt_repayment_categories_raw as $debt_repayment_category_raw) {
			if ($debt_repayment_category_raw->is_hidden == null)
			$debt_repayment_categories[] = $debt_repayment_category_raw;
		}
		return response()->json([
				'result' => 'success',
				'data' => $debt_repayment_categories
		]);
	}

	public function getAssetCategories()
	{
		$asset_repayment_categories_raw = $this->taxonomy->getCategoryPengeluaranPendapatan('wallet', 'asset')->toHierarchy();
		$asset_repayment_categories = [];
		foreach ($asset_repayment_categories_raw as $asset_repayment_category_raw) {
			if ($asset_repayment_category_raw->is_hidden == null)
			$asset_repayment_categories[] = $asset_repayment_category_raw;
		}
		return response()->json([
				'result' => 'success',
				'data' => $asset_repayment_categories
		]);
	}

	public function getInsuranceCategories()
	{
		$insurance_names_raw = $this->taxonomy->getCategoryPengeluaranPendapatan('wallet', 'insurance')->toHierarchy();
		$insurance_names = [];
		foreach ($insurance_names_raw as $insurance_name_raw) {
			if ($insurance_name_raw->is_hidden == null)
			$insurance_names[] = $insurance_name_raw;
		}
		return response()->json([
				'result' => 'success',
				'data' => $insurance_names
		]);
	}

	public function showCashflowAnalysis(Request $request){
		$input = $request->only('user_id','version','modules');
		$add_modules_for_icon = array(4=>'incomes',5=>'plan_balances');
		$input['modules'] = $input['modules'] == null ? null : $input['modules']+$add_modules_for_icon;
		$data = $this->cashflowAnalysis->showByVersion($input);

		if(!is_null($data)){
			$showCashflow = array();

			$showCashflow['id'] = $data->id;
			$showCashflow['user_id'] = $data->user_id;
			$showCashflow['incomes'] = $data->incomes;
			foreach($data->expenses as $expenses){
				if($expenses->taxo_wallet_slug != 'other' && $expenses->taxo_wallet_slug != 'titipan-transfer')
				$showCashflow['expenses'][] = $expenses;
			}
			$showCashflow['debt_repayments'] = $data->debt_repayments;
			$showCashflow['asset_repayments'] = $data->asset_repayments;
			$showCashflow['insurances'] = $data->insurances;
			$showCashflow['plan_balances'] = $data->plan_balances;
		}

        return response()->json([
			'result' => 'success',
			'data' => !is_null($data) ? $showCashflow : $data
		]);
	}


	/**
     | Self financial checkup ...
     | Financila Checkup Client ...
     | ----------------------------------------------
     | 29 Agustus 2018
     | Gugun DP
     |
     | Function ini (storeSelfCashflowAnalysis) copy dari function 'storeCashflowAnalysis'
     | dimana funcsi 'storeCashflowAnalysis' digunakan untuk financial checkup oleh CFP
     | sedangkan funcsi ini 'storeSelfCashflowAnalysis' digunakan supaya Client bisa melakukan financial checkup sendiri

   	 | Function ini (showSelfCashflowAnalysis) copy dari function 'showCashflowAnalysis'
   	 | Alasannya sama kaya storeSelfCashflowAnalysis
   	 |
     */

	public function storeSelfCashflowAnalysis(Request $request){
		try {
			$input = $request->all();
			$data = $this->cashflowAnalysis->createSelf($request->all());
            return response()->json([
				'result' => 'success',
				'data' => [ 
					'incomes' => $data['incomes'], 
					'expenses' => $data['expenses'], 
					'status_default_categories' => !isset($input['expenses'][0]) ? true : false,
					'debt_repayments' => $data['debt_repayments'],
					'asset_repayments' => $data['asset_repayments'],
					'insurances' => $data['insurances'],
					'plan_balances' => $data['plan_balances'],
				]
			]);
        } catch (ValidationException $e) {
            return response()->json([
					'result' => 'error',
					'alerts' => $e->getErrors()
			]);
        }
	}

		public function storeSelfIncome(Request $request){
			try {
				$data = $this->cashflowAnalysis->createSelfIncome($request->all());
	            return response()->json([
					'result' => 'success',
					'data' => $data
				]);
	        } catch (ValidationException $e) {
	            return response()->json([
						'result' => 'error',
						'alerts' => $e->getErrors()
				]);
	        }
		}

		public function storeSelfExpense(Request $request){

			/**
			 * Lakukan cek apakah beberapa Category yang is_required semuanya diisi ...
			 */

			// ambil daftar Expense yang is_required ...
            $expense_required = Taxonomy::where('is_required', '=', '1')->get();

            foreach ($expense_required as $index => $required) {
                
                $available = false;

                foreach($request['data'] as $idx => $attribute_arr) {
                    // jika ada
                    if($expense_required[$index]->id == $request['data'][$idx]['taxo_wallet_id']) {
                        $available = true;
                    }
                }

                if($available == false) {
                	$data = [
                		'message'	=> 'Belum mengisi',
                		'category'	=> $expense_required[$index]->title
                	];
                    return response()->json([
						'result' => 'error',
						'alerts' => $data
					]);  
                }
            }

			try {
				$data = $this->cashflowAnalysis->createSelfExpense($request->all());
	            return response()->json([
					'result' => 'success',
					'data' => $data
				]);
	        } catch (ValidationException $e) {
	            return response()->json([
						'result' => 'error',
						'alerts' => $e->getErrors()
				]);
	        }
		}

			public function storeSelfExpenseAuto(Request $request){
				try {
					$data = $this->cashflowAnalysis->createSelfExpenseAuto($request->all());
		            return response()->json([
						'result' => 'success',
						'data' => $data
					]);
		        } catch (ValidationException $e) {
		            return response()->json([
							'result' => 'error',
							'alerts' => $e->getErrors()
					]);
		        }
			}

		public function storeSelfDebt(Request $request){
			try {
				$data = $this->cashflowAnalysis->createSelfDebt($request->all());
	            return response()->json([
					'result' => 'success',
					'data' => $data
				]);
	        } catch (ValidationException $e) {
	            return response()->json([
						'result' => 'error',
						'alerts' => $e->getErrors()
				]);
	        }
		}

		public function storeSelfAsset(Request $request){
			try {
				$data = $this->cashflowAnalysis->createSelfAsset($request->all());
	            return response()->json([
					'result' => 'success',
					'data' => $data
				]);
	        } catch (ValidationException $e) {
	            return response()->json([
						'result' => 'error',
						'alerts' => $e->getErrors()
				]);
	        }
		}

		public function storeSelfInsurances(Request $request){
			try {
				$data = $this->cashflowAnalysis->createSelfInsurances($request->all());
	            return response()->json([
					'result' => 'success',
					'data' => $data
				]);
	        } catch (ValidationException $e) {
	            return response()->json([
						'result' => 'error',
						'alerts' => $e->getErrors()
				]);
	        }
		}

		public function selfRekap(Request $request){
			try {
				$data = $this->cashflowAnalysis->createSelfRekap($request->all());
	            return response()->json([
					'result' => 'success',
					'data' => $data
				]);
	        } catch (ValidationException $e) {
	            return response()->json([
						'result' => 'error',
						'alerts' => $e->getErrors()
				]);
	        }
		}

	/**
	 * Tampilkan yang sudah di save sebagai active_version
	 */
	public function showSelfCashflowAnalysis(Request $request){
		$input = $request->only('user_id','version','modules');
		$add_modules_for_icon = array(4 => 'incomes',5 => 'plan_balances');
		$input['modules'] = $input['modules'] == null ? null : $input['modules'] + $add_modules_for_icon;
		$data = $this->cashflowAnalysis->showByVersion($input);

		if(!is_null($data)){
			$showCashflow = array();

			$showCashflow['id'] = $data->id;
			$showCashflow['user_id'] = $data->user_id;
			//incomes
			$income_bulan=0;$income_lain=0;
			foreach($data->incomes as $incomes){
				$nested['id'] = $incomes['id'];
				$nested['user_id'] = $incomes['user_id'];
				$nested['pendapatan_bulanan'] = (int) $incomes['pendapatan_bulanan'];
				$income_bulan += (int) $incomes['pendapatan_bulanan'];
				$nested['pendapatan_lain'] = (int) $incomes['pendapatan_lain'];
				$income_lain += (int) $incomes['pendapatan_lain'];
				$nested['pendapatan_tidak_tetap_bulan'] = (int) $incomes['pendapatan_tidak_tetap_bulan'];
				$nested['version'] = $incomes['version'];
				$nested['taxo_wallet_ico'] = $incomes['taxo_wallet_ico'];
				$showCashflow['incomes'][] = $nested;
			}
			// $showCashflow['incomes'] = $data->incomes;
			
			//expenses
			$sum_expenses=0;
			foreach($data->expenses as $expenses){
				if($expenses->taxo_wallet_slug != 'other' && $expenses->taxo_wallet_slug != 'titipan-transfer')
				{
					$nestedExpenses['id'] = $expenses['id'];
					$nestedExpenses['user_id'] = $expenses['user_id'];
					$nestedExpenses['taxo_wallet_id'] = $expenses['taxo_wallet_id'];
					$nestedExpenses['taxo_wallet_name'] = $expenses['taxo_wallet_name'];
					$nestedExpenses['taxo_wallet_slug'] = $expenses['taxo_wallet_slug'];
					$nestedExpenses['taxo_wallet_ico'] = $expenses['taxo_wallet_ico'];
					$nestedExpenses['taxo_wallet_color'] = $expenses['taxo_wallet_color'];
					$nestedExpenses['taxo_wallet_description'] = $expenses['taxo_wallet_description'];
					$nestedExpenses['anggaran_perbulan'] = (int) $expenses['anggaran_perbulan'];
					$sum_expenses+= (int) $expenses['anggaran_perbulan'];
					$nestedExpenses['catatan'] = $expenses['catatan'];
					$nestedExpenses['version'] = $expenses['version'];
					$showCashflow['expenses'][] = $nestedExpenses;
				}
			}

			$sum_debt=0;
			foreach($data->debt_repayments as $debt_repayments){
				$nestedDebt['id'] = $debt_repayments['id'];
				$nestedDebt['user_id'] = $debt_repayments['user_id'];
				$nestedDebt['nama'] = $debt_repayments['nama'];
				$nestedDebt['taxo_wallet_id'] = $debt_repayments['taxo_wallet_id'];
				$nestedDebt['taxo_wallet_name'] = $debt_repayments['taxo_wallet_name'];
				$nestedDebt['taxo_wallet_slug'] = $debt_repayments['taxo_wallet_slug'];
				$nestedDebt['taxo_wallet_ico'] = $debt_repayments['taxo_wallet_ico'];
				$nestedDebt['taxo_wallet_color'] = $debt_repayments['taxo_wallet_color'];
				$nestedDebt['taxo_wallet_description'] = $debt_repayments['taxo_wallet_description'];
				$nestedDebt['cicilan_perbulan'] = (int) $debt_repayments['cicilan_perbulan'];
				$sum_debt+=(int) $debt_repayments['cicilan_perbulan'];
				$nestedDebt['sisa_durasi'] = $debt_repayments['sisa_durasi'];
				$nestedDebt['catatan'] = $debt_repayments['catatan'];
				$nestedDebt['version'] = $debt_repayments['version'];
				$data_debt[] = $nestedDebt;
			}

			$showCashflow['debt_repayments'] = isset($data_debt) ? $data_debt : $data->debt_repayments;
			
			$sum_asset=0;
			foreach($data->asset_repayments as $asset_repayments){
				$nestedAsset_repayments['id'] = $asset_repayments['id'];
				$nestedAsset_repayments['user_id'] = $asset_repayments['user_id'];
				$nestedAsset_repayments['nama'] = $asset_repayments['nama'];
				$nestedAsset_repayments['taxo_wallet_id'] = $asset_repayments['taxo_wallet_id'];
				$nestedAsset_repayments['taxo_wallet_name'] = $asset_repayments['taxo_wallet_name'];
				$nestedAsset_repayments['taxo_wallet_slug'] = $asset_repayments['taxo_wallet_slug'];
				$nestedAsset_repayments['taxo_wallet_ico'] = $asset_repayments['taxo_wallet_ico'];
				$nestedAsset_repayments['taxo_wallet_color'] = $asset_repayments['taxo_wallet_color'];
				$nestedAsset_repayments['taxo_wallet_description'] = $asset_repayments['taxo_wallet_description'];
				$nestedAsset_repayments['cicilan_perbulan'] = (int) $asset_repayments['cicilan_perbulan'];
				$sum_asset+=(int) $asset_repayments['cicilan_perbulan'];
				$nestedAsset_repayments['sisa_durasi'] = $asset_repayments['sisa_durasi'];
				$nestedAsset_repayments['catatan'] = $asset_repayments['catatan'];
				$nestedAsset_repayments['plan_analysis_activated'] = $asset_repayments['plan_analysis_activated'];
				$nestedAsset_repayments['version'] = $asset_repayments['version'];
				$data_asset[] = $nestedAsset_repayments;
			}
			$showCashflow['asset_repayments'] = isset($data_asset) ? $data_asset : $data->asset_repayments;
			
			$sum_insurance=0;
			foreach($data->insurances as $insurances){
				$nestedInsurances['id'] = $insurances['id'];
				$nestedInsurances['user_id'] = $insurances['user_id'];
				$nestedInsurances['nama'] = $insurances['nama'];
				$nestedInsurances['taxo_wallet_id'] = $insurances['taxo_wallet_id'];
				$nestedInsurances['taxo_wallet_name'] = $insurances['taxo_wallet_name'];
				$nestedInsurances['taxo_wallet_slug'] = $insurances['taxo_wallet_slug'];
				$nestedInsurances['taxo_wallet_ico'] = $insurances['taxo_wallet_ico'];
				$nestedInsurances['taxo_wallet_color'] = $insurances['taxo_wallet_color'];
				$nestedInsurances['taxo_wallet_description'] = $insurances['taxo_wallet_description'];
				$nestedInsurances['taxo_insurance_type_id'] = is_null($insurances['taxo_insurance_type_id']) ? 30 :$insurances['taxo_insurance_type_id'];
				$nestedInsurances['taxo_insurance_type_name'] = $insurances['taxo_insurance_type_name'];
				$nestedInsurances['no_polis'] = $insurances['no_polis'];
				$nestedInsurances['premi_perbulan'] = (int) $insurances['premi_perbulan'];
				$sum_insurance+=(int) $insurances['premi_perbulan'];
				$nestedInsurances['nilai_pertanggungan'] = (int) $insurances['nilai_pertanggungan'];
				$nestedInsurances['catatan'] = $insurances['catatan'];
				$nestedInsurances['version'] = $insurances['version'];
				$data_insurances[] = $nestedInsurances;
			}
			
			$showCashflow['insurances'] = isset($data_insurances) ? $data_insurances : $data->insurances;
			
			$showCashflow['plan_balances'] = $data->plan_balances;
			$showCashflow['anggaran'] = ($sum_expenses+$sum_asset+$sum_debt+$sum_insurance);
		}

        return response()->json([
			'result' => 'success',
			'data' => !is_null($data) ? $showCashflow : $data
		]);
	}


	/**
	 * Tampilkan yang belum di save sebagai active_version (OnGoing)
	 */
	public function showSelfFincheckOnGoing(Request $request){
		$input = $request->only('user_id','version','modules');
		$add_modules_for_icon = array(4 => 'incomes',5 => 'plan_balances');
		$input['modules'] = $input['modules'] == null ? null : $input['modules'] + $add_modules_for_icon;
		$data = $this->cashflowAnalysis->showByVersionFincheckOnGoing($input);

		$anggaran = $this->cashflowAnalysis->getAnggaranOnGoing($request->user_id);

		if(!is_null($data)){
			$showCashflow = array();

			$showCashflow['id'] = $data->id;
			$showCashflow['user_id'] = $data->user_id;
			$showCashflow['incomes'] = $data->incomes;
			foreach($data->expenses as $expenses){
				if($expenses->taxo_wallet_slug != 'other' && $expenses->taxo_wallet_slug != 'titipan-transfer')
				$showCashflow['expenses'][] = $expenses;
			}
			$showCashflow['debt_repayments'] = $data->debt_repayments;
			$showCashflow['asset_repayments'] = $data->asset_repayments;
			$showCashflow['insurances'] = $data->insurances;
			$showCashflow['plan_balances'] = $data->plan_balances;
			$showCashflow['anggaran'] = $anggaran;
		}

        return response()->json([
			'result' => 'success',
			'data' => !is_null($data) ? $showCashflow : $data
		]);
	}

	/**
	 | ----------------------------------------------
	 */









	public function storePortfolioAnalysis(Request $request){
		try {
			$data = $this->portfolioAnalysis->create($request->all());
            return response()->json([
				'result' => 'success',
				'data' => $data
			]);
        } catch (ValidationException $e) {
            return response()->json([
					'result' => 'error',
					'alerts' => $e->getErrors()
			]);
        }
	}

	public function showPortfolioAnalysis(Request $request){
		$data = $this->portfolioAnalysis->showByVersion($request);
        return response()->json([
			'result' => 'success',
			'data' => $data
		]);
	}

	public function storeLogBookNote(Request $request){
		try {
			$data = $this->logBookNote->create($request->all());
            return response()->json([
				'result' => 'success',
				'data' => $data
			]);
        } catch (ValidationException $e) {
            return response()->json([
					'result' => 'error',
					'alerts' => $e->getErrors()
			]);
        }
	}

	public function showLogBookNote(Request $request){
		$data = $this->logBookNote->showByVersion($request);
        return response()->json([
			'result' => 'success',
			'data' => $data
		]);
	}

	public function needApproval(Request $request){
		$data = $this->cashflowAnalysis->needApproval($request);//sementara pakai repository milik cashflow

		if(isset($data['cashflowAnalysis'])){
			$cash = json_decode($data['cashflowAnalysis']);			
			
			$approval = array();
			$nested['id'] = $cash->id;
			$nested['user_id'] = $cash->user_id;
			$nested['active_version_detail_id'] = $cash->active_version_detail_id;
			$nested['incomes'] = $cash->incomes;

			foreach($cash->expenses as $expenses){
				if($expenses->taxo_wallet_slug != 'other' && $expenses->taxo_wallet_slug != 'titipan-transfer')
				$nested['expenses'][] = $expenses;
			}

			$nested['debt_repayments'] = $cash->debt_repayments;
			$nested['asset_repayments'] = $cash->asset_repayments;
			$nested['insurances'] = $cash->insurances;
			$nested['plan_balances'] = $cash->plan_balances;

			$approval['cashflowAnalysis'] = $nested;
			
			
			// foreach($cash as $datas){
			// 	$nested['id'] = $datas->id;
			// 	$nested['user_id'] = $datas->user_id;
			// 	$nested['active_version_detail_id'] = $datas->active_version_detail_id;
			// 	$nested['incomes'] = $datas->incomes;

			// 	foreach($datas->expenses as $expenses){
			// 		if($expenses->taxo_wallet_slug != 'other' && $expenses->taxo_wallet_slug != 'titipan-transfer')
			// 		$nested['expenses'][] = $expenses;
			// 	}

			// 	$nested['debt_repayments'] = $datas->debt_repayments;
			// 	$nested['asset_repayments'] = $datas->asset_repayments;
			// 	$nested['insurances'] = $datas->insurances;
			// 	$nested['plan_balances'] = $datas->plan_balances;

			// 	$approval['cashflowAnalysis'] = $nested;
			// }
		}

		return response()->json([
			'result' => 'success',
			'data' => isset($data['cashflowAnalysis']) ? $approval : $data
		]);
	}

	public function approveFinance(Request $request){
		try {
			$this->cashflowAnalysis->approveFinance($request->all());//sementara pakai repository milik cashflow
            return response()->json([
				'result' => 'success'
			]);
        } catch (ValidationException $e) {
            return response()->json([
				'result' => 'error',
				'alerts' => $e->getErrors()
			]);
        }
	}

	public function approvePlan(Request $request){
        try {
			$data = $this->cashflowAnalysis->approvePlan($request->all());//sementara pakai repository milik cashflow
            return response()->json([
				'result' => 'success'
			]);
        } catch (ValidationException $e) {
            return response()->json([
				'result' => 'error',
				'alerts' => $e->getErrors()
			]);
        }
	}

	public function approveActionPlan(Request $request){
        try {
			$data = $this->cashflowAnalysis->approveActionPlan($request->all());//sementara pakai repository milik cashflow
			return response()->json([
				'result' => 'success',
				'data' => $data
			]);
        } catch (ValidationException $e) {
            return response()->json([
				'result' => 'error',
				'alerts' => $e->getErrors()
			]);
        }
	}

	public function showAssetRepayment(Request $request){
		$assetRepayment = AssetRepayment::find($request->input('id')); //dd($data);
		$data = null;
		if(!is_null($assetRepayment)){
			$assetRepaymentPaid = AssetRepaymentPaid::with('asset_repayment')->whereHas('asset_repayment', function($q) use ($assetRepayment){
	            $q->where('taxo_wallet_id', $assetRepayment->taxo_wallet_id)
	            ->whereRaw('LOWER(nama) = ?', [ strtolower($assetRepayment->nama)]);
	        })->first();
	        if(!is_null($assetRepaymentPaid)){
        		$data = $assetRepaymentPaid->asset_repayment;
        	}
		}
        
        return response()->json([
				'result' => 'success',
				'data' => $data
			]);
	}

	public function checkApproval(Request $request){
		//$user_id = $request->input('user_id');
		$module = $request->input('module');
		
		switch ($module) {
			case 'cashflow_analysis':
				$data = $this->cashflowAnalysis->checkApproval($request);
			break;
			case 'portfolio_analysis':
				$data = $this->portfolioAnalysis->checkApproval($request);
			break;
			case 'plan_analysis':
				$data = $this->planAnalysis->checkApproval($request);
			break;
			case 'plan_a':
				$data = $this->planA->checkApproval($request);
			break;
			case 'plan_b':
				$data = $this->planB->checkApproval($request);
			break;
		}
		
		//$data = $this->cashflowAnalysis->needApproval($request);//sementara pakai repository milik cashflow
        return response()->json([
			'result' => 'success',
			'data' => $data
		]);
	}

	public function checkFreeConsultation(Request $request){
		try {
			$user_id = $request->input('user_id');
			//jjika tidak diset maka akan men-lookup ke 3 active_version_key ( financialCheckup_cashflowAnalysis, financialCheckup_portfolioAnalysis, planAnalysis )
			$active_version_key = $request->has('active_version_key')?$request->get('active_version_key'):'financialCheckup_cashflowAnalysis|financialCheckup_portfolioAnalysis|planAnalysis';
			$res = $this->planAnalysis->checkFreeConsultation_withData(['user_id' => $user_id, 'active_version_key' => $active_version_key]);
			return response()->json($res);
		} catch (ValidationException $e) {//hanya untuk menampilkan error jika active version key tidak valid
			return response()->json([
					'result' => 'error',
					'alerts' => $e->getErrors()
			]);
		}
	}

	public function balanceCircles(Request $request){
		$user_id = $request->input('user_id');
		$transaction_date_until = $request->has('transaction_date_until')?Carbon::parse($request->input('transaction_date_until'))->format('Y-m-d'):Carbon::now()->format('Y-m-d');
		//balance ( besar ), free cashflow ( kecil kiri), expenses ( kecil kanan )
		
		$ca = $this->cashflowAnalysis->showByVersion([
			'user_id' => $user_id,
			'transaction_date_until' => $transaction_date_until
		]); // cashflow analysis
			$pendapatan = 0;
			$potongan_pendapatan=0;
			$free_cashflow=0;
			$total_expense=0;
			$kas_ditangan = 0;
		
		if(count($ca)){
			$incomes = $ca->incomes[0];
			$pendapatan = floatVal($incomes->pendapatan_bulanan);
			$debt_repayments = $ca->debt_repayments;
			$asset_repayments = $ca->asset_repayments;
			$insurances = $ca->insurances;
			$expenses = $ca->expenses;
			
			$total_debt_repayment = 0;
			$total_asset_repayment = 0;
			$total_insurance = 0;
			$expense_other_budget = 0;

			foreach($debt_repayments as $debt_repayment){
				$total_debt_repayment += $debt_repayment->cicilan_perbulan;
			}

			foreach($asset_repayments as $asset_repayment){
				$total_asset_repayment += $asset_repayment->cicilan_perbulan;
			}

			foreach($insurances as $insurance){
				$total_insurance += $insurance->premi_perbulan;
			}

			$potongan_pendapatan = $total_debt_repayment + $total_asset_repayment + $total_insurance;//tanpa expenses dari wallet
			
			foreach($expenses as $expense){//free cashflow yang didapat saat cashflow analysis
				if($expense->taxo_wallet_slug == 'other'){
					$expense_other_budget = floatval($expense->anggaran_perbulan);
				}
			}
			

			$wallet_transaction_balance = $this->walletTransaction->balance([
				'user_id' => $user_id,
				//'transaction_date_until' => $transaction_date_until //dimatikan karena tidak ada opsi tanggal lagi di tampilan fundtastic yang latest
			]);
			$total_expense = 0;
			foreach($wallet_transaction_balance as $wallet_transaction_percategory){
				if($wallet_transaction_percategory->taxo_wallet_slug == 'other'){
					$free_cashflow = $expense_other_budget - $wallet_transaction_percategory->pemakaian_anggaran_bulan_ini;
				}else{
					$total_expense += $wallet_transaction_percategory->pemakaian_anggaran_bulan_ini;
				}
			}

			$kas_ditangan = $pendapatan - $total_expense;
		}
		$circles = [
			'income' => $pendapatan,
			//'potongan_income' => $potongan_pendapatan,
			'kas_ditangan' => $kas_ditangan,
			'free_cashflow' => $free_cashflow,
			'expense' => $total_expense
		];

		return response()->json([
			'result' => 'success',
			'data' => $circles
		]);

	}

	public function eomCreate(Request $request){
		$data = $this->eomBalance->create($request);
        return response()->json([
			'result' => 'success'
		]);
	}

	public function investmentInformation(){
		$data = $this->investmentInformation->all();
        return response()->json([
			'result' => 'success',
			'data' => $data
		]);
	}

	public function investmentInformationConfirm(Request $request){
		try {
			$data = $this->investmentInformationClient->create($request->all());
			return response()->json([
				'result' => 'success',
				'data' => $data
			]);
		} catch (ValidationException $e) {
			return response()->json([
					'result' => 'error',
					'alerts' => $e->getErrors()
			]);
		}
	}

	public function financialHealthStructure(){
		$items_raw = $this->taxonomy->getTermsByPostType('financial_health_structure')->toHierarchy(); 

		$items = $this->taxonomy->buildWithMetas($items_raw);

		return response()->json([
				'result' => 'success',
				'data' => $items
		]);
	}

	public function FinancialHealthStore(Request $request){
		try {
			$data = $this->FinancialHealth->create($request->all());
			return response()->json([
				'result' => 'success',
				'data' => $data
			]);
		} catch (ValidationException $e) {
			return response()->json([
					'result' => 'error',
					'alerts' => $e->getErrors()
			]);
		}
	}

	public function hasApprovedCashflowAnalysis(Request $request){
		$input = $request->all();
		$user_id = $input['user_id'];
		$is_clean = $this->cashflowAnalysis->checkApproval([
			'user_id' => $user_id
		]);
		if($is_clean === 1){//is clear
			$last_status = $this->cashflowAnalysis->hasApprovedData(['user_id' => $user_id]);
			if($last_status == 'rejected'){
				//jika rejected. Maka harus membuat mulai dari cashflow analysis lagi
				return response()->json([
					'result' => 'error',
					'data' => 'Please update your previous rejected cashflow analysis data.'
				]);
			}else{//jika tidak ada yang butuh approval maka hasilnya akan sama dengan API need approval
				return response()->json([
					'result' => 'success',
					'data' => (string) $is_clean
				]);
			}
		}else{//jika ada yang butuh approval maka hasilnya akan sama dengan API need approval
			return response()->json([
				'result' => 'success',
				'data' => (string) $is_clean
			]);
		}
	}

	public function financialHealthCheckupView($email_web_path){
		$item = FinancialHealth::where('email_web_path', $email_web_path)->with('metas')->first();
		if(is_null($item)){
			abort(404);
		}else{
			//structure
            $structure_raw = $this->taxonomy->getTermsByPostType('financial_health_structure')->toHierarchy(); 
            $structure = $this->taxonomy->buildWithMetas($structure_raw);
			$input_names_titles = renderTaxoTitle($structure,'slug', 'id');
			
			$item_metas = usermeta($item->metas); 
			if($item_metas){
				$html_pemasukan = '';
				$html_pengeluaran = '';
				$data_pendapatan_count = 0;
				$data_pengeluaran_count = 0;
				foreach ($item_metas as $item_meta_key => $item_meta) {
					if(isset($input_names_titles[$item_meta_key])){
						$input_name_raw = explode('_', $item_meta_key);
						$input_type_form = $input_name_raw[0];
						$input_name_title = $input_names_titles[$item_meta_key];
						switch ($input_type_form) {
							case 'pemasukan':
								$html_pemasukan .= '<div class="grid-x grid-padding-x">
									<div class="small-6 cell"><label for="right-label">'.($data_pendapatan_count+1).'. '.$input_name_title.'</label></div>
									<div class="small-6 cell"><div class="label-rounded label-input">Rp. '.money($item_meta, 2).'</div></div>
								</div>'; 
								$data_pendapatan_count++;
								break;
							case 'pengeluaran':
								$html_pengeluaran .= '<div class="grid-x grid-padding-x">
									<div class="small-6 cell"><label for="right-label">'.($data_pengeluaran_count+1).'. '.$input_name_title.'</label></div>
									<div class="small-6 cell"><div class="label-rounded label-input">Rp. '.money($item_meta, 2).'</div></div>
								</div>'; 
								$data_pengeluaran_count++;
								break;
						}
					}
				}
			}

			return view('frontend.emailWeb.financialHealthCheckup', compact('item', 'html_pemasukan', 'html_pengeluaran'));
		}
	}

	public function isFullCycle(Request $request){
		try {
			$client_id = $request->input('client_id');
			$res = $this->cycle->is_full_cycle($client_id);
			return response()->json([
				'result' => 'success',
				'data' => $res
			]);
		} catch (ValidationException $e) {
			return response()->json([
					'result' => 'error',
					'alerts' => $e->getErrors()
			]);
		}
	}

	public function convertCash(Request $request){
		try {
			$attrs = $request->only([
				'created_by_id',
				'client_id',
				'asset_repayment_id',
				'active_version_plan_detail_id',
				'catatan'
			]);
			$res = $this->convertCash->create($attrs);
			return response()->json([
				'result' => 'success'
			]);
		} catch (ValidationException $e) {
			return response()->json([
					'result' => 'error',
					'alerts' => $e->getErrors()
			]);
		}
	}
}
