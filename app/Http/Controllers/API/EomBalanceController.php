<?php namespace App\Http\Controllers\API;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use Redirect;
use View;
use Input;
use Validator;
use Response;
use App\Exceptions\Validation\ValidationException;
use Config;
use Entrust;

use App\User;
use Carbon\Carbon;
use App\Repositories\EomBalance\EomBalanceRepository;

class EomBalanceController extends Controller {

	protected $eomBalance;

    public function __construct(EomBalanceRepository $eomBalance) {
		$this->eomBalance = $eomBalance;
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

	public function store(Request $request){
		try {
			$data = $this->eomBalance->create($request->all());
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
        $category_id = $request->input('category_id');
        
        $notes = $request->input('notes');

        $transaction_date = trim($request->input('transaction_date')) == ''?'':Carbon::parse($request->input('transaction_date'))->format('Y-m-d');
        $category_type_id = $request->input('category_type_id');
        $amount = trim($request->input('amount')) == '' || $request->input['amount'] === 0?'':unformat_money($request->input('amount'));
      
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
		$wallet_categories_raw = $this->taxonomy->getTermsByPostType_n_parent('wallet', 'expense')->toHierarchy();
		$wallet_categories = [];
		foreach ($wallet_categories_raw as $wallet_category_raw) {
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

	public function showCashflowAnalysis(Request $request){
		$data = $this->cashflowAnalysis->showByVersion($request);
        return response()->json([
			'result' => 'success',
			'data' => $data
		]);
	}

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
        return response()->json([
			'result' => 'success',
			'data' => $data
		]);
	}

	public function approveFinance(Request $request){
		$data = $this->cashflowAnalysis->approveFinance($request);//sementara pakai repository milik cashflow
        return response()->json([
			'result' => 'success'
		]);
	}

	public function approvePlan(Request $request){
		$data = $this->cashflowAnalysis->approvePlan($request);//sementara pakai repository milik cashflow
        return response()->json([
			'result' => 'success'
		]);
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
		
        return response()->json([
			'result' => 'success',
			'data' => $data
		]);
	}

	public function balanceCircles(Request $request){
		$user_id = $request->input('user_id');
		$ca = $this->cashflowAnalysis->showByVersion([
			'user_id' => $user_id	
		]); // cashflow analysis
			$pendapatan = 0;
			$potongan_pendapatan=0;
			$free_cashflow=0;
			$total_expense=0;
		
		if(count($ca)){
			$incomes = $ca->incomes[0];
			$pendapatan = floatVal($incomes->pendapatan_bulanan);
			$debt_repayments = $ca->debt_repayments;
			$asset_repayments = $ca->asset_repayments;
			$insurances = $ca->insurances;
			
			$total_debt_repayment = 0;
			$total_asset_repayment = 0;
			$total_insurance = 0;

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
			
			$plan_balances = $ca->plan_balances;
			foreach($plan_balances as $plan_balance){//free cashflow yang didapat saat cashflow analysis
				if($plan_balance->name == 'free_cashflow')
					$free_cashflow = floatVal($plan_balance->balance);
			}

			$wallet_transaction_balance = $this->walletTransaction->balance([
				'user_id' => $user_id,
				'transaction_date_until' => Carbon::now()->format('Y-m-d')
			]);
			$total_expense = 0;
			foreach($wallet_transaction_balance as $wallet_transaction_percategory){
				$total_expense += $wallet_transaction_percategory->pemakaian_anggaran_bulan_ini;
			}
		}
		$circles = [
			'income' => $pendapatan,
			'potongan_income' => $potongan_pendapatan,
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

}