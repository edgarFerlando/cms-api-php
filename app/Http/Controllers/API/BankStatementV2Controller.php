<?php namespace App\Http\Controllers\API;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Repositories\CfpSchedule\CfpScheduleInterface;

use Redirect;
use View;
use Input;
use Validator;
use Response;
use App\Repositories\CfpSchedule\CfpScheduleRepository as CfpSchedule;
use App\Exceptions\Validation\ValidationException;
use Config;
use Entrust;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

use App\User;
use App\Repositories\User\UserInterface;
use App\Repositories\BankStatement\BankStatementRepository as BankStatement;
use Carbon\Carbon;
use Auth;
use App\Models\ActiveVersionDetail;
use App\Repositories\BankStatement\BankStatementInterface;
use App\Repositories\CashflowAnalysis\CashflowAnalysisRepository;
use App\Models\Income;
use App\Models\BankStatement as bankStatementModel;
use App\BankAccount;
use App\Models\Bank;
use App\Repositories\BankAccount\BankAccountRepository as bankAccountRepository;
use App\Repositories\Taxonomy\TaxonomyInterface;
use App\Repositories\BankStatementSplitExpenseCategory\BankStatementSplitExpenseCategoryRepository;
use App\Models\BankStatementSplitExpenseCategory;

class BankStatementV2Controller extends Controller {

	protected $cfpSchedule;
	protected $user;
	protected $bankStatement;
	protected $cashflowAnalysis;
	protected $bank;
	protected $bankAccount;
	protected $taxonomy;

    public function __construct(UserInterface $user, BankStatementInterface $bankStatement,CashflowAnalysisRepository $cashflowAnalysis, Bank $bank, bankAccountRepository $bankAccount, TaxonomyInterface $taxonomy, BankStatementSplitExpenseCategoryRepository $bankStatementSplitExpenseCategory) {
		$this->user = $user;
		$this->bankStatement = $bankStatement;
		$this->cashflowAnalysis = $cashflowAnalysis;
		$this->bank = $bank;
		$this->bankAccount = $bankAccount;
		$this->taxonomy = $taxonomy;
		$this->bankStatementSplitExpenseCategory = $bankStatementSplitExpenseCategory;
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

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		//
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
		//
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		//
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		//
	}

	/**
	 | -------------------------------------
	 | - Top 3
	 |		- Highest
	 |		- Active
	 |
	 | - Persentasi pengeluaran perbulan
	 */

	public function topThreeHighestV2(Request $request)
	{

	 	$user_id = $request->input('user_id');
		$today = Carbon::now()->format('Y-m-d');
		$period = Carbon::now()->format('Y-m');

		//query repository
		$cutoff = $this->user->find($user_id);

		$no_payload_cutoffdate = !empty($cutoff['cutoff_date']) && !is_null($cutoff['cutoff_date']) && $cutoff['cutoff_date'] != 0 ? $cutoff['cutoff_date'] : date("t", strtotime($today));

		//cutoff date from query
		$cutoff_date_last = $no_payload_cutoffdate < 10 ? '0'.$no_payload_cutoffdate : $no_payload_cutoffdate;
		$cutoff_date_next = $period. '-'. $no_payload_cutoffdate;

		//format cutoff for last,now,next
		$month_next = strtotime($cutoff_date_next);
		$cutoff_last = date("Y-m-$cutoff_date_last",strtotime('-1 month', $month_next));
		$cutoff_now = date("Y-m-$cutoff_date_last", strtotime($cutoff_date_next));
		$cutoff_next = date("Y-m-$cutoff_date_last", strtotime("+1 month"));

		//checkout condition if cutoff date morethan today will change next month
		$cutoff_check_last = $today >= "$period-$cutoff_date_last" && $today <= date("Y-m-t", strtotime($cutoff_now)) ? $cutoff_now : $cutoff_last;
		$cutoff_check_next = $today >= "$period-$cutoff_date_last" && $today <= date("Y-m-t", strtotime($cutoff_now)) ? $cutoff_next : $cutoff_now;

		$query_last_transaction_amount = date('N', strtotime($cutoff_check_last)) == 6 ? date('Y-m-d', strtotime('-1 day', strtotime($cutoff_check_last))) : (date('N', strtotime($cutoff_check_last)) == 7 ? date('Y-m-d', strtotime('-2 day', strtotime($cutoff_check_last))) : $cutoff_check_last) ;
		$query_next_transaction_amount = date('N', strtotime($cutoff_check_next)) == 6 ? date('Y-m-d', strtotime('-1 day', strtotime($cutoff_check_next))) : (date('N', strtotime($cutoff_check_next)) == 7 ? date('Y-m-d', strtotime('-2 day', strtotime($cutoff_check_next))) : $cutoff_check_next) ;
		//query cashflow analysis menggunakan max approved pada periode tersebut
		$cashflowAnalysis = $this->cashflowAnalysis->showByVersion([
			'transaction_date_until' => $period,
			'user_id' => $user_id,
			'modules' => [ 'incomes', 'expenses', 'plan_balances', 'debt_repayments', 'asset_repayments', 'insurances' ]
		]);

		if(!$cashflowAnalysis['expenses']){
			return response()->json([
				'result' => 'error',
				'data' => null
			])->setStatusCode(200);
		}

		
		$user_expenses = null;
		if(isset($cashflowAnalysis['expenses'])){
			$income = !isset($cashflowAnalysis['incomes'][0]) ? 0 : floatval($cashflowAnalysis['incomes'][0]['pendapatan_bulanan']);
			$anggaran=0;
			$total_pengeluaran_other=0;
			$total_pengeluaran_other = $this->bankStatement->getTransactionAmount([
				'client_id' => $user_id,
				'period-monitor' => $period,
				'transaction_type' => 'debit',
				'user_expense_id' => 65
			]);
			foreach($cashflowAnalysis['expenses'] as $expense){
				//$user_expenses[$expense->id] = [ codingan dari mas Yudi
				$anggaran+=floatval($expense->anggaran_perbulan);
			}
			$debt_repayment = 0;
			foreach($cashflowAnalysis['debt_repayments'] as $debt_repayments){
				$debt_repayment += floatval($debt_repayments->cicilan_perbulan);
			}

			$total_asset_repayment = 0;
			$total_mobil=0;$total_properti=0;$total_mutual=0;$total_pendidikan=0;
			foreach($cashflowAnalysis['asset_repayments'] as $asset_repayments){
				$total_asset[$asset_repayments->taxo_wallet_id]['total'][] = $asset_repayments->cicilan_perbulan;
			}

			$insurance = 0;
			foreach($cashflowAnalysis['insurances'] as $insurances){
				$insurance += floatval($insurances->premi_perbulan);
			}
			
			foreach($cashflowAnalysis['expenses'] as $expense){
				//$user_expenses[$expense->id] = [ codingan dari mas Yudi
				$user_expenses[$expense->taxo_wallet_id] = [ // coding dari Andriansyah
					'percent' => 0,
					'pengeluaran' => 0,
					'title' => $expense->taxo_wallet_name,
					'image' => $expense->taxo_wallet_ico,
					'color' => $expense->taxo_wallet_color,
					'desc' => $expense->taxo_wallet_description,
					'anggaran' => $expense->taxo_wallet_name == 'Other' && (int) $expense->anggaran_perbulan == 0 ? $income-($anggaran+$debt_repayment+$total_asset_repayment+$insurance) : (int) $expense->anggaran_perbulan,
					'sisa_anggaran' => $expense->taxo_wallet_name == 'Other' && (int) $expense->anggaran_perbulan == 0 ? $income-($anggaran+$debt_repayment+$total_asset_repayment+$insurance) : (int) $expense->anggaran_perbulan,
					'jml_transaksi' => 0
				];
			}

			//get detail Monthly bank transaction
			$bankStatements = $this->bankStatement->findAllByJoinSplit([
				//'user_expense_ids' => array_keys($user_expenses),
				'client_id' => $user_id,
				// 'period' => $period,
				'transaction_type' => 'debit',
				'period-last' => $query_last_transaction_amount,
				'period-next' => $query_next_transaction_amount
			]);
			
			if(!is_null($bankStatements)){
				foreach($bankStatements as $bankStatement){
					if($bankStatement->user_expense_id !== null || $bankStatement->split_expense_id !== null && $bankStatement->flag_split != 'D'){
						$data = $bankStatement->is_categorized===0||$bankStatement->is_categorized===1?$bankStatement->split_expense_id:$bankStatement->user_expense_id;
						$user_expenses[$data]['data'][] = [
							'id' => $bankStatement->is_categorized===0||$bankStatement->is_categorized===1?$bankStatement->split_id:$bankStatement->id,
							'name' => $bankStatement->transaction_description,
							'bank_name' => $bankStatement->title,
							'bank_code' => $bankStatement->slug,
							'ibank_uid' => $bankStatement->ibank_uid,
							'value' => $bankStatement->is_categorized===0||$bankStatement->is_categorized===1?(int) $bankStatement->amount : (int)$bankStatement->transaction_amount,
							'is_categorized' => $bankStatement->is_categorized,
							'expense_code' => $bankStatement->user_expense_id,
							'created_at' => $bankStatement->transaction_date
						];
						if(count($user_expenses[$data]) > 1){
							$budget_allocated = $user_expenses[$data]['anggaran'];
							$budget_remaining = $user_expenses[$data]['sisa_anggaran'];
							$budget_spent = $user_expenses[$data]['pengeluaran'];
							$user_expenses[$data]['jml_transaksi'] = count($user_expenses[$data]['data']);
							$trans_amount = $bankStatement->is_categorized===0||$bankStatement->is_categorized===1?(int) $bankStatement->amount : (int)$bankStatement->transaction_amount;
							$jml_transaksi=count($user_expenses[$data]['data']);
							$calc_remaining_budget = $budget_remaining - $trans_amount;
							$user_expenses[$data]['sisa_anggaran'] = (int) $calc_remaining_budget;
							$calc_spent_budget = $budget_spent + $trans_amount;
							$user_expenses[$data]['pengeluaran'] = (int) $calc_spent_budget;
							$percent = $budget_allocated < 0 && $jml_transaksi >= 1 ? 100 : ($budget_allocated == 0 ? 100 : ($calc_spent_budget / $budget_allocated) * 100);
							$user_expenses[$data]['percent'] = round($percent,2)  >= 100 ? 100 : round($percent,2);
						}
					}
				}
			}
		}

		arsort($user_expenses);
		
		$arr = array();
		$i = 0;
		foreach($user_expenses as $key => $value){			
			if($value['percent'] != 0){
				$arr[$i] = $value;
				$value['taxo_wallet_id'] = $key;
				if(!array_key_exists('data', $value)){
					$value['data'] = [];
				}
				$arr[$i] = $value;
				if ($i++ == 2) break;
			}
		}

		return response()->json([
			'result' => 'success',
			'cutoff_last' => $query_last_transaction_amount,
			'cutoff_next' => $query_next_transaction_amount,
			'data' => $arr //$data
		]);
	}





	public function topThreeActiveV2(Request $request)
	{
	 	
	 	$user_id = $request->input('user_id');
		$today = Carbon::now()->format('Y-m-d');
		$period = Carbon::now()->format('Y-m');

		//query repository
		$cutoff = $this->user->find($user_id);

		$no_payload_cutoffdate = !empty($cutoff['cutoff_date']) && !is_null($cutoff['cutoff_date']) && $cutoff['cutoff_date'] != 0 ? $cutoff['cutoff_date'] : date("t", strtotime($today));

		//cutoff date from query
		$cutoff_date_last = $no_payload_cutoffdate < 10 ? '0'.$no_payload_cutoffdate : $no_payload_cutoffdate;
		$cutoff_date_next = $period. '-'. $no_payload_cutoffdate;

		//format cutoff for last,now,next
		$month_next = strtotime($cutoff_date_next);
		$cutoff_last = date("Y-m-$cutoff_date_last",strtotime('-1 month', $month_next));
		$cutoff_now = date("Y-m-$cutoff_date_last", strtotime($cutoff_date_next));
		$cutoff_next = date("Y-m-$cutoff_date_last", strtotime("+1 month"));

		//checkout condition if cutoff date morethan today will change next month
		$cutoff_check_last = $today >= "$period-$cutoff_date_last" && $today <= date("Y-m-t", strtotime($cutoff_now)) ? $cutoff_now : $cutoff_last;
		$cutoff_check_next = $today >= "$period-$cutoff_date_last" && $today <= date("Y-m-t", strtotime($cutoff_now)) ? $cutoff_next : $cutoff_now;

		$query_last_transaction_amount = date('N', strtotime($cutoff_check_last)) == 6 ? date('Y-m-d', strtotime('-1 day', strtotime($cutoff_check_last))) : (date('N', strtotime($cutoff_check_last)) == 7 ? date('Y-m-d', strtotime('-2 day', strtotime($cutoff_check_last))) : $cutoff_check_last) ;
		$query_next_transaction_amount = date('N', strtotime($cutoff_check_next)) == 6 ? date('Y-m-d', strtotime('-1 day', strtotime($cutoff_check_next))) : (date('N', strtotime($cutoff_check_next)) == 7 ? date('Y-m-d', strtotime('-2 day', strtotime($cutoff_check_next))) : $cutoff_check_next) ;
		//query cashflow analysis menggunakan max approved pada periode tersebut
		$cashflowAnalysis = $this->cashflowAnalysis->showByVersion([
			'transaction_date_until' => $period,
			'user_id' => $user_id,
			'modules' => [ 'incomes', 'expenses', 'plan_balances', 'debt_repayments', 'asset_repayments', 'insurances' ]
		]);

		if(!$cashflowAnalysis['expenses']){
			return response()->json([
				'result' => 'error',
				'data' => null
			])->setStatusCode(200);
		}

		
		$user_expenses = null;
		if(isset($cashflowAnalysis['expenses'])){
			$income = !isset($cashflowAnalysis['incomes'][0]) ? 0 : floatval($cashflowAnalysis['incomes'][0]['pendapatan_bulanan']);
			$anggaran=0;
			$total_pengeluaran_other=0;
			$total_pengeluaran_other = $this->bankStatement->getTransactionAmount([
				'client_id' => $user_id,
				'period-monitor' => $period,
				'transaction_type' => 'debit',
				'user_expense_id' => 65
			]);
			foreach($cashflowAnalysis['expenses'] as $expense){
				//$user_expenses[$expense->id] = [ codingan dari mas Yudi
				$anggaran+=floatval($expense->anggaran_perbulan);
			}
			$debt_repayment = 0;
			foreach($cashflowAnalysis['debt_repayments'] as $debt_repayments){
				$debt_repayment += floatval($debt_repayments->cicilan_perbulan);
			}

			$total_asset_repayment = 0;
			$total_mobil=0;$total_properti=0;$total_mutual=0;$total_pendidikan=0;
			foreach($cashflowAnalysis['asset_repayments'] as $asset_repayments){
				$total_asset[$asset_repayments->taxo_wallet_id]['total'][] = $asset_repayments->cicilan_perbulan;
			}

			$insurance = 0;
			foreach($cashflowAnalysis['insurances'] as $insurances){
				$insurance += floatval($insurances->premi_perbulan);
			}
			
			foreach($cashflowAnalysis['expenses'] as $expense){
				//$user_expenses[$expense->id] = [ codingan dari mas Yudi
				$user_expenses[$expense->taxo_wallet_id] = [ // coding dari Andriansyah
					'jml_transaksi' => 0,
					'title' => $expense->taxo_wallet_name,
					'image' => $expense->taxo_wallet_ico,
					'color' => $expense->taxo_wallet_color,
					'desc' => $expense->taxo_wallet_description,
					'anggaran' => $expense->taxo_wallet_name == 'Other' && (int) $expense->anggaran_perbulan == 0 ? $income-($anggaran+$debt_repayment+$total_asset_repayment+$insurance) : (int) $expense->anggaran_perbulan,
					'sisa_anggaran' => $expense->taxo_wallet_name == 'Other' && (int) $expense->anggaran_perbulan == 0 ? $income-($anggaran+$debt_repayment+$total_asset_repayment+$insurance) : (int) $expense->anggaran_perbulan,
					'percent' => 0,
					'pengeluaran' => 0
				];
			}

			//get detail Monthly bank transaction
			$bankStatements = $this->bankStatement->findAllByJoinSplit([
				//'user_expense_ids' => array_keys($user_expenses),
				'client_id' => $user_id,
				// 'period' => $period,
				'transaction_type' => 'debit',
				'period-last' => $query_last_transaction_amount,
				'period-next' => $query_next_transaction_amount
			]);
			
			if(!is_null($bankStatements)){
				foreach($bankStatements as $bankStatement){
					if($bankStatement->user_expense_id !== null || $bankStatement->split_expense_id !== null && $bankStatement->flag_split != 'D'){
						$data = $bankStatement->is_categorized===0||$bankStatement->is_categorized===1?$bankStatement->split_expense_id:$bankStatement->user_expense_id;
						$user_expenses[$data]['data'][] = [
							'id' => $bankStatement->is_categorized===0||$bankStatement->is_categorized===1?$bankStatement->split_id:$bankStatement->id,
							'name' => $bankStatement->transaction_description,
							'bank_name' => $bankStatement->title,
							'bank_code' => $bankStatement->slug,
							'ibank_uid' => $bankStatement->ibank_uid,
							'value' => $bankStatement->is_categorized===0||$bankStatement->is_categorized===1?(int) $bankStatement->amount : (int)$bankStatement->transaction_amount,
							'is_categorized' => $bankStatement->is_categorized,
							'expense_code' => $bankStatement->user_expense_id,
							'created_at' => $bankStatement->transaction_date
						];
						if(count($user_expenses[$data]) > 1){
							$budget_allocated = $user_expenses[$data]['anggaran'];
							$budget_remaining = $user_expenses[$data]['sisa_anggaran'];
							$budget_spent = $user_expenses[$data]['pengeluaran'];
							$user_expenses[$data]['jml_transaksi'] = count($user_expenses[$data]['data']);
							$jml_transaksi=count($user_expenses[$data]['data']);
							$trans_amount = $bankStatement->is_categorized===0||$bankStatement->is_categorized===1?(int) $bankStatement->amount : (int)$bankStatement->transaction_amount;
							$calc_remaining_budget = $budget_remaining - $trans_amount;
							$user_expenses[$data]['sisa_anggaran'] = (int) $calc_remaining_budget;
							$calc_spent_budget = $budget_spent + $trans_amount;
							$user_expenses[$data]['pengeluaran'] = (int) $calc_spent_budget;
							$percent = $budget_allocated < 0 && $jml_transaksi >= 1 ? 100 : ($budget_allocated == 0 ? 100 : ($calc_spent_budget / $budget_allocated) * 100);
							$user_expenses[$data]['percent'] = round($percent,2)  >= 100 ? 100 : round($percent,2);
						}
					}
				}
			}
		}

		arsort($user_expenses);
		
		$arr = array();
		$i = 0;
		foreach($user_expenses as $key => $value){			
			if($value['jml_transaksi'] != 0){
				$arr[$i] = $value;
				$value['taxo_wallet_id'] = $key;
				if(!array_key_exists('data', $value)){
					$value['data'] = [];
				}
				$arr[$i] = $value;
				if ($i++ == 2) break;
			}
		}

		return response()->json([
			'result' => 'success',
			'cutoff_last' => $query_last_transaction_amount,
			'cutoff_next' => $query_next_transaction_amount,
			'data' => $arr //$data
		]);
	}

	public function detailPengeluaran(Request $request)
	{
		$client_id = $request->input('client_id');
		$period = $request->input('period');
		$expense_id = $request->input('expense_id');
		$category = $request->input('category');
		$today = Carbon::now()->format('Y-m-d');

		//query repository
		$cutoff = $this->user->find($client_id);

		$no_payload_cutoffdate = !empty($cutoff['cutoff_date']) && !is_null($cutoff['cutoff_date']) && $cutoff['cutoff_date'] != 0 ? $cutoff['cutoff_date'] : date("t", strtotime($today));

		//cutoff date from query
		$cutoff_date_last = $no_payload_cutoffdate < 10 ? '0'.$no_payload_cutoffdate : $no_payload_cutoffdate;
		$cutoff_date_next = $period. '-'. $no_payload_cutoffdate;

		//format cutoff for last,now,next
		$month_next = strtotime($cutoff_date_next);
		$cutoff_last = date("Y-m-$cutoff_date_last",strtotime('-1 month', $month_next));
		$cutoff_now = date("Y-m-$cutoff_date_last", strtotime($cutoff_date_next));
		$cutoff_next = date("Y-m-$cutoff_date_last", strtotime("+1 month"));

		//checkout condition if cutoff date morethan today will change next month
		$cutoff_check_last = $cutoff_last; //$today >= "$period-$cutoff_date_last" && $today <= date("Y-m-t", strtotime($cutoff_now)) ? $cutoff_now : $cutoff_last;
		$cutoff_check_next = $cutoff_now; //$today >= "$period-$cutoff_date_last" && $today <= date("Y-m-t", strtotime($cutoff_now)) ? $cutoff_next : $cutoff_now;

		$query_last_transaction_amount = date('N', strtotime($cutoff_check_last)) == 6 ? date('Y-m-d', strtotime('-1 day', strtotime($cutoff_check_last))) : (date('N', strtotime($cutoff_check_last)) == 7 ? date('Y-m-d', strtotime('-2 day', strtotime($cutoff_check_last))) : $cutoff_check_last) ;
		$query_next_transaction_amount = date('N', strtotime($cutoff_check_next)) == 6 ? date('Y-m-d', strtotime('-1 day', $month_next)) : (date('N', strtotime($cutoff_check_next)) == 7 ? date('Y-m-d', strtotime('-2 day', $month_next)) : $cutoff_check_next) ;
		//query cashflow analysis menggunakan max approved pada periode tersebut
		$cashflowAnalysis = $this->cashflowAnalysis->showByVersionV2([
			'transaction_date_until' => $period,
			'user_id' => $client_id,
			'expense_id' => $expense_id,
			'modules' => [ 'incomes', 'expenses', 'plan_balances', 'debt_repayments', 'asset_repayments', 'insurances' ]
		]);

		if(!$cashflowAnalysis['expenses']){
			return response()->json([
				'result' => 'error',
				'data' => null
			])->setStatusCode(200);
		}

		if($category === 'expenses')
		{
			$user_expenses[$cashflowAnalysis['expenses'][0]['taxo_wallet_id']] = [ // coding dari Andriansyah
				'taxo_wallet_name' => $cashflowAnalysis['expenses'][0]['taxo_wallet_name'],
				'taxo_wallet_ico' => $cashflowAnalysis['expenses'][0]['taxo_wallet_ico'],
				'taxo_wallet_color' => $cashflowAnalysis['expenses'][0]['taxo_wallet_color'],
				'taxo_wallet_description' => $cashflowAnalysis['expenses'][0]['taxo_wallet_description'],
				'budget_allocated' => (int) $cashflowAnalysis['expenses'][0]['anggaran_perbulan'],
				'budget_remaining' => (int) $cashflowAnalysis['expenses'][0]['anggaran_perbulan'],
				'budget_spent' => 0,
				'category' => 'expenses'
			];
		}elseif($category === 'assets')
		{
			$user_expenses[$cashflowAnalysis['asset_repayments'][0]['taxo_wallet_id']] = [ // coding dari Andriansyah
				'taxo_wallet_name' => $cashflowAnalysis['asset_repayments'][0]['taxo_wallet_name'],
				'taxo_wallet_ico' => $cashflowAnalysis['asset_repayments'][0]['taxo_wallet_ico'],
				'taxo_wallet_color' => $cashflowAnalysis['asset_repayments'][0]['taxo_wallet_color'],
				'taxo_wallet_description' => $cashflowAnalysis['asset_repayments'][0]['taxo_wallet_description'],
				'budget_allocated' => (int) $cashflowAnalysis['asset_repayments'][0]['cicilan_perbulan'],
				'budget_remaining' => (int) $cashflowAnalysis['asset_repayments'][0]['cicilan_perbulan'],
				'budget_spent' => 0,
				'category' => 'assets'
			];
		}elseif($category === 'debt')
		{
			$user_expenses[$cashflowAnalysis['debt_repayments'][0]['taxo_wallet_id']] = [ // coding dari Andriansyah
				'taxo_wallet_name' => $cashflowAnalysis['debt_repayments'][0]['taxo_wallet_name'],
				'taxo_wallet_ico' => $cashflowAnalysis['debt_repayments'][0]['taxo_wallet_ico'],
				'taxo_wallet_color' => $cashflowAnalysis['debt_repayments'][0]['taxo_wallet_color'],
				'taxo_wallet_description' => $cashflowAnalysis['debt_repayments'][0]['taxo_wallet_description'],
				'budget_allocated' => (int) $cashflowAnalysis['debt_repayments'][0]['cicilan_perbulan'],
				'budget_remaining' => (int) $cashflowAnalysis['debt_repayments'][0]['cicilan_perbulan'],
				'budget_spent' => 0,
				'category' => 'debt'
			];
		}elseif($category === 'insurances')
		{
			$user_expenses[$cashflowAnalysis['insurances'][0]['id']] = [ // coding dari Andriansyah
				'taxo_wallet_name' => $cashflowAnalysis['insurances'][0]['taxo_wallet_name'],
				'taxo_wallet_ico' => $cashflowAnalysis['insurances'][0]['taxo_wallet_ico'],
				'taxo_wallet_color' => $cashflowAnalysis['insurances'][0]['taxo_wallet_color'],
				'taxo_wallet_description' => $cashflowAnalysis['insurances'][0]['taxo_wallet_description'],
				'budget_allocated' => (int) $cashflowAnalysis['insurances'][0]['premi_perbulan'],
				'budget_remaining' => (int) $cashflowAnalysis['insurances'][0]['premi_perbulan'],
				'budget_spent' => 0,
				'category' => 'insurances'
			];
		}

		// exit();

		//get detail Monthly bank transaction
		$bankStatements = $this->bankStatement->findAllByJoinSplit([
			// 'user_expense_ids' => array_keys($user_expenses),
			'client_id' => $client_id,
			// 'period' => $period,
			'transaction_type' => 'debit',
			'period-last' => $query_last_transaction_amount,
			'period-next' => $query_next_transaction_amount
		]);

		if(!is_null($bankStatements)){
			foreach($bankStatements as $bankStatement){
				if($bankStatement->user_expense_id !== null || $bankStatement->split_expense_id !== null && $bankStatement->flag_split != 'D'){
					$data = $bankStatement->is_categorized===0||$bankStatement->is_categorized===1?$bankStatement->split_expense_id:$bankStatement->user_expense_id;
					if($data === $expense_id){
						$user_expenses[$data]['data'][] = [
							'id' => $bankStatement->is_categorized===0||$bankStatement->is_categorized===1?$bankStatement->split_id:$bankStatement->id,
							'name' => $bankStatement->transaction_description,
							'bank_name' => $bankStatement->title,
							'bank_code' => $bankStatement->slug,
							'ibank_uid' => $bankStatement->ibank_uid,
							'value' => $bankStatement->is_categorized===0||$bankStatement->is_categorized===1?(int) $bankStatement->amount : (int)$bankStatement->transaction_amount,
							'transaction_type' => $bankStatement->transaction_type,
							'is_categorized' => $bankStatement->is_categorized,
							'expense_code' => $bankStatement->user_expense_id,
							'taxo_wallet_id' => $category === 'expenses' ? $cashflowAnalysis['expenses'][0]['taxo_wallet_id'] : ($category === 'assets' ? $cashflowAnalysis['asset_repayments'][0]['taxo_wallet_id'] : ($category === 'debt' ? $cashflowAnalysis['debt_repayments'][0]['taxo_wallet_id'] : ($category === 'insurances' ? $cashflowAnalysis['insurances'][0]['id'] : '-'))),
							'taxo_wallet_ico' => $category === 'expenses' ? $cashflowAnalysis['expenses'][0]['taxo_wallet_ico'] : ($category === 'assets' ? $cashflowAnalysis['asset_repayments'][0]['taxo_wallet_ico'] : ($category === 'debt' ? $cashflowAnalysis['debt_repayments'][0]['taxo_wallet_ico'] : ($category === 'insurances' ? $cashflowAnalysis['insurances'][0]['taxo_wallet_ico'] : '-'))),
							'taxo_wallet_category' => $category === 'expenses' ? $cashflowAnalysis['expenses'][0]['taxo_wallet_name'] : ($category === 'assets' ? $cashflowAnalysis['asset_repayments'][0]['taxo_wallet_name'] : ($category === 'debt' ? $cashflowAnalysis['debt_repayments'][0]['taxo_wallet_name'] : ($category === 'insurances' ? $cashflowAnalysis['insurances'][0]['taxo_wallet_name'] : '-'))),
							'color' => $category === 'expenses' ? $cashflowAnalysis['expenses'][0]['taxo_wallet_color'] : ($category === 'assets' ? $cashflowAnalysis['asset_repayments'][0]['taxo_wallet_color'] : ($category === 'debt' ? $cashflowAnalysis['debt_repayments'][0]['taxo_wallet_color'] : ($category === 'insurances' ? $cashflowAnalysis['insurances'][0]['taxo_wallet_color'] : '-'))),
							'created_at' => $bankStatement->transaction_date,
							'flag_split' => is_null($bankStatement->user_expense_id) ? true : false
						];
						$budget_remaining = $user_expenses[$data]['budget_remaining'];
						$budget_spent = $user_expenses[$data]['budget_spent'];
						$trans_amount = $bankStatement->is_categorized===0||$bankStatement->is_categorized===1?(int) $bankStatement->amount : (int)$bankStatement->transaction_amount;
						$calc_remaining_budget = $budget_remaining - $trans_amount;
						$user_expenses[$data]['budget_remaining'] = (int) $calc_remaining_budget;
						$calc_spent_budget = $budget_spent + $trans_amount;
						$user_expenses[$data]['budget_spent'] = (int) $calc_spent_budget;
					}
				}
			}
		}

		$arr = array();
		$i = 0;
		foreach($user_expenses as $key => $value){			
			$arr[$i] = $value;
			$value['taxo_wallet_id'] = $key;
			if(!array_key_exists('data', $value)){
				$value['data'] = [];
			}
			$arr[$i] = $value;
			$i++;
		}

		return response()->json([
			'result' => 'success',
			// 'last_month' => $query_last_transaction_amount,
			// 'next_month' => $query_next_transaction_amount,
			'data' => $arr
		])->setStatusCode(200);
	}

	public function anggaranBulananV2(Request $request)
	{
		$user_id 	= $request->input('user_id');
	 	$bulan 		= $request->input('bulan');

	 	/** Jika bulannya diisi null, maka ambil bulan sekarang ... */
	 	if($bulan == null) {
	 		$now = Carbon::now();
			$bulan = $now->year.'-'.$now->month;
	 	}

	 	/** Ambil tanggal gajihan berdasarkan user */
		$user_cutoff = User::where('id', '=', $user_id)->first()->cutoff_date;

	 	if($user_cutoff == null) {
	 		$date_new = new Carbon('last day of last month');
			$user_cutoff = $date_new->day;
		}

	 	$start = '';
	 	$end = '';

	 	/** 
	 	 | Saat tanggal gajihannya di atas atau sama dengan tanggal 15
	 	 | maka gajihan tersebut di hitung gajihan belan depan.
	 	 | jika tanggal gajihannya di bawah tanggal 15 maka di hitung gajihan bulan sekarang.  
	 	 */

	 	if($user_cutoff >= 15) {
	 		$start = Carbon::parse($bulan.'-'.$user_cutoff.' 00:00:00')->subMonth(); 				// - 1 month
	 		$end = Carbon::parse($bulan.'-'.$user_cutoff.' 23:56:00')->subDay();
	 	} else {
	 		$start = Carbon::parse($bulan.'-'.$user_cutoff.' 00:00:00');
	 		$end = Carbon::parse($bulan.'-'.$user_cutoff.' 23:56:00')->addMonth()->subDay();		// + 1 month
		}
		
		//query repository
		$today = Carbon::now()->format('Y-m-d');
		$cutoff = $this->user->find($user_id);

		$no_payload_cutoffdate = !empty($cutoff['cutoff_date']) && !is_null($cutoff['cutoff_date']) && $cutoff['cutoff_date'] != 0 ? $cutoff['cutoff_date'] : date("t", strtotime($today));

		//cutoff date from query
		$cutoff_date_last = $no_payload_cutoffdate < 10 ? '0'.$no_payload_cutoffdate : $no_payload_cutoffdate;
		$cutoff_date_next = $bulan. '-'. $no_payload_cutoffdate;

		//format cutoff for last,now,next
		$month_next = strtotime($cutoff_date_next);
		$cutoff_last = date("Y-m-$cutoff_date_last",strtotime('-1 month', $month_next));
		$cutoff_now = date("Y-m-$cutoff_date_last", strtotime($cutoff_date_next));
		$cutoff_next = date("Y-m-$cutoff_date_last", strtotime("+1 month"));

		//checkout condition if cutoff date morethan today will change next month
		$cutoff_check_last = $cutoff_last;
		$cutoff_check_next = $cutoff_now;

		$query_last_transaction_amount = date('N', strtotime($cutoff_check_last)) == 6 ? date('Y-m-d', strtotime('-1 day', strtotime($cutoff_check_last))) : (date('N', strtotime($cutoff_check_last)) == 7 ? date('Y-m-d', strtotime('-2 day', strtotime($cutoff_check_last))) : $cutoff_check_last) ;
		$query_next_transaction_amount = date('N', strtotime($cutoff_check_next)) == 6 ? date('Y-m-d', strtotime('-1 day', $month_next)) : (date('N', strtotime($cutoff_check_next)) == 7 ? date('Y-m-d', strtotime('-2 day', $month_next)) : $cutoff_check_next) ;

		//query cashflow analysis menggunakan max approved pada periode tersebut
		$cashflowAnalysis = $this->cashflowAnalysis->showByVersion([
			'transaction_date_until' => $bulan,
			'user_id' => $user_id,
			'modules' => [ 'expenses', 'plan_balances', 'incomes', 'debt_repayments', 'asset_repayments', 'insurances' ]
		]);

		$dana_tersedia = 0;
		$total_anggaran = 0;
		$total_sisa_anggaran = 0;
		$total_pengeluaran = 0;
		$total_dana_tersedia = 0;
		$persen_dana_tersedia = 0;
		$persen_total_anggaran = 0;
		$persen_total_pengeluaran = 0;
		$income = 0;
		$total_pengeluaran_other = 0;
		$total_remaining=0; $total_spent=0;
		$sisa_anggaran=0;
		$percent=0;

		if(isset($cashflowAnalysis['expenses']))
		{
			$anggaran=0;
			foreach($cashflowAnalysis['expenses'] as $expense){
				$user_expenses[$expense->taxo_wallet_id] = [
					'name' => $expense->taxo_wallet_name,
					'budget_allocated' => (int) $expense->anggaran_perbulan,
					'budget_remaining' => (int) $expense->anggaran_perbulan,
					'budget_spent' => 0
				];
			}

			// $debt_repayment = 0;
			foreach($cashflowAnalysis['debt_repayments'] as $debt_repayments){
				$debt[$debt_repayments->taxo_wallet_id]['total'][] = floatval($debt_repayments->cicilan_perbulan);
				$user_expenses[$debt_repayments->taxo_wallet_id] = [
					'name' => $debt_repayments->taxo_wallet_name,
					'budget_allocated' => array_sum($debt[$debt_repayments->taxo_wallet_id]['total']),
					'budget_remaining' => array_sum($debt[$debt_repayments->taxo_wallet_id]['total']),
					'budget_spent' => 0
				];
			}

			$asset_repayment = 0;
			foreach($cashflowAnalysis['asset_repayments'] as $asset_repayments){
				$asset[$asset_repayments->taxo_wallet_id]['total'][] = floatval($asset_repayments->cicilan_perbulan);
				$user_expenses[$asset_repayments->taxo_wallet_id] = [
					'name' => $asset_repayments->taxo_wallet_name,
					'budget_allocated' => array_sum($asset[$asset_repayments->taxo_wallet_id]['total']),
					'budget_remaining' => array_sum($asset[$asset_repayments->taxo_wallet_id]['total']),
					'budget_spent' => 0
				];
			}

			$insurance = 0;
			foreach($cashflowAnalysis['insurances'] as $insurances){
				$user_expenses[$insurances->id] = [
					'name' => $insurances->taxo_wallet_name,
					'budget_allocated' => (int) $insurances->premi_perbulan,
					'budget_remaining' => (int) $insurances->premi_perbulan,
					'budget_spent' => 0
				];
			}

			//get detail Monthly bank transaction
			$bankStatements = $this->bankStatement->findAllByJoinSplit([
				// 'user_expense_ids' => array_keys($user_expenses),
				'client_id' => $user_id,
				// 'period' => $period,
				'transaction_type' => 'debit',
				'period-last' => $query_last_transaction_amount,
				'period-next' => $query_next_transaction_amount
			]);
			
			if(!is_null($bankStatements)){
				foreach($bankStatements as $bankStatement){
					if($bankStatement->user_expense_id !== null || $bankStatement->split_expense_id !== null && $bankStatement->flag_split != 'D'){
						$data = $bankStatement->is_categorized===0||$bankStatement->is_categorized===1?$bankStatement->split_expense_id:$bankStatement->user_expense_id;
						$user_expenses[$data]['data'][] = [
							'id' => $bankStatement->is_categorized===0||$bankStatement->is_categorized===1?$bankStatement->split_id:$bankStatement->id,
							'name' => $bankStatement->transaction_description,
							'bank_name' => $bankStatement->title,
							'bank_code' => $bankStatement->slug,
							'ibank_uid' => $bankStatement->ibank_uid,
							'value' => $bankStatement->is_categorized===0||$bankStatement->is_categorized===1?(int) $bankStatement->amount : (int)$bankStatement->transaction_amount,
							'transaction_type' => $bankStatement->transaction_type,
							'is_categorized' => $bankStatement->is_categorized,
							'expense_code' => $bankStatement->user_expense_id,
							'flag_split' => is_null($bankStatement->user_expense_id) ? true : false,
							'created_at' => $bankStatement->transaction_date
						];
						if(count($user_expenses[$data]) > 1){
							$budget_remaining = $user_expenses[$data]['budget_remaining'];
							$budget_spent = $user_expenses[$data]['budget_spent'];
							$trans_amount = $bankStatement->is_categorized===0||$bankStatement->is_categorized===1?(int) $bankStatement->amount : (int)$bankStatement->transaction_amount;
							$calc_remaining_budget = $budget_remaining - $trans_amount;
							$user_expenses[$data]['budget_remaining'] = (int) $calc_remaining_budget;
							$calc_spent_budget = $budget_spent + $trans_amount;
							$user_expenses[$data]['budget_spent'] = (int) $calc_spent_budget;
						}
					}
				}
			}

			$arr = array();
			$i = 0;
			
			foreach($user_expenses as $key => $value){
				if($key != 65 && $key != 303 && $key != 330 && count($value) > 1)
				{
					$arr[$i] = $value;
					$value['taxo_wallet_id'] = $key;
					if(!array_key_exists('data', $value)){
						$value['data'] = [];
					}
					$arr[$i] = $value;
					$total_anggaran += $value['budget_allocated'];
					$total_remaining += $value['budget_remaining'];
					$total_spent += $value['budget_spent'];
					$i++;
				}
			}

			//total nilai
			// $total_anggaran = $insurance+$asset_repayment+$debt_repayment+$anggaran;
			$sisa_anggaran = $total_anggaran-$total_spent;
			$percent = $total_spent <= 0 ? 0 : ($total_anggaran-$total_spent) / $total_anggaran * 100;
		}

		return response()->json([
			'result' => 'success',
			'bulan' => $bulan,
			'start' => $query_last_transaction_amount,
			'end' => $query_next_transaction_amount,
			'data' => [
				[
					'pengeluaran' => $total_spent,
					'anggaran' => $total_anggaran,
					'sisa' => $sisa_anggaran < 0 ? 0 : $sisa_anggaran,
					'percent' => (int) $percent
				]
			],
			'data_anggaran' => isset($arr) ? $arr : ''
		]);
	}

	public function getTime(Request $request)
	{
		return response()->json([
			'time' => Carbon::now()
		]);
	}

}
