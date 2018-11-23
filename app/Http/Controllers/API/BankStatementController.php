<?php 

namespace App\Http\Controllers\API;

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
use DB;
use App\Taxonomy;

class BankStatementController extends Controller {

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
	public function index(Request $request)
	{
		$result = $this->bankStatementMutationRepo->search($request->all());
		$bankStatements = $result[0];

		if(!$bankStatements){
			return response()->json([
				'result' => 'error',
				'total' => count($bankStatements),
				'data' => 'Data Mutation Not Found'
			], 404);
		}

		return response()->json([
			'result' => 'success',
			'next_page_url' => isset($input['next_page_url']) ? (int) $input['next_page_url'] : NULL,
			'prev_page_url' => isset($input['prev_page_url']) ? (int) $input['prev_page_url'] : NULL,
			'total' => count($bankStatements),
			'data' => $bankStatements
		]);
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
		try {
			
        } catch (ValidationException $e) {
            return response()->json([
					'result' => 'error',
					'alerts' => $e->getErrors()
			]);
        }
	}

	public function update(Request $request)
	{ 

		try {
			
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
		$result = BankStatement::find($id);

		if(!$result){
			return response()->json([
				'result' => 'success',
				'next_page_url' => '',
				'prev_page_url' => '',
				'total' => '',
				'data' => 'Data Not Found'
			], 404);
		}

		return response()->json([
			'result' => 'success',
			'next_page_url' => '',
			'prev_page_url' => '',
			'total' => '',
			'data' => $result
		]);
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
	public function destroy() //delete belom selesai , terakhir ngerjain tanggal 10 oct 2017
	{
		try {			
        } catch (ValidationException $e) {
            return response()->json([
					'result' => 'error',
					'alerts' => $e->getErrors()
			]);
        }		
	}

	public function bankStatements(Request $request) { 
		$page = $request->input('page');
		$perPage = $request->input('perpage');
		$filter = $request->input('filter');

		$today = Carbon::now()->format('Y-m-d');
		$cutoff = $this->user->find($filter['user_id']);

		$no_payload_cutoffdate = !empty($cutoff['cutoff_date']) && !is_null($cutoff['cutoff_date']) && $cutoff['cutoff_date'] != 0 ? $cutoff['cutoff_date'] : date("t", strtotime($today));

		//cutoff date from query
		$cutoff_date_last = $no_payload_cutoffdate < 10 ? '0'.$no_payload_cutoffdate : $no_payload_cutoffdate;
		$cutoff_date_next = empty($filter['transaction_monthly']) ? $filter['transaction_date_period'] : $filter['transaction_monthly']. '-'. $no_payload_cutoffdate;

		//format cutoff for last,now,next
		$month_next = strtotime($cutoff_date_next);
		$cutoff_last = date("Y-m-$cutoff_date_last",strtotime('-1 month', $month_next));
		$cutoff_now = date("Y-m-$cutoff_date_last", strtotime($cutoff_date_next));
		$cutoff_next = date("Y-m-$cutoff_date_last", strtotime("+1 month"));

		//checkout condition if cutoff date morethan today will change next month
		$cutoff_check_last = $cutoff_last; //$today >= "$period-$cutoff_date_last" && $today <= date("Y-m-t", strtotime($cutoff_now)) ? $cutoff_now : $cutoff_last;
		$cutoff_check_next = $cutoff_now; //$today >= "$period-$cutoff_date_last" && $today <= date("Y-m-t", strtotime($cutoff_now)) ? $cutoff_next : $cutoff_now;

		$query_last_transaction_amount = date('N', strtotime($cutoff_check_last)) == 6 ? date('Y-m-d', strtotime('-1 day', strtotime($cutoff_check_last))) : (date('N', strtotime($cutoff_check_last)) == 7 ? date('Y-m-d', strtotime('-2 day', strtotime($cutoff_check_last))) : $cutoff_check_last) ;
		$query_next_transaction_amount = date('N', strtotime($cutoff_check_next)) == 6 ? date('Y-m-d', strtotime('-1 day', strtotime($cutoff_check_next))) : (date('N', strtotime($cutoff_check_next)) == 7 ? date('Y-m-d', strtotime('-2 day', strtotime($cutoff_check_next))) : $cutoff_check_next) ;

		if(isset($filter['transaction_date_period'])){
			$filter['transaction_date_period'] = $query_last_transaction_amount;
			$filter['transaction_date_period_next'] = $query_next_transaction_amount;
		}

		if(isset($filter['transaction_monthly'])){
			$filter['transaction_monthly'] = $query_last_transaction_amount;
			$filter['transaction_monthly_next'] = $query_next_transaction_amount;
		}

        $pagiData = $this->bankStatement->paginate($page, $perPage, $filter);
		$totalItems = $pagiData->totalItems;

		// dd($totalItems); // 21

		if($perPage === 0 )
			$perPage = $totalItems;
        	$bankStatements = new LengthAwarePaginator($pagiData->items, $totalItems, $perPage, $page, [
            'path' => Paginator::resolveCurrentPath()
		]);

        

		// perpage
		// dd($perPage); // 10
		
		$bankStatements_arr = $bankStatements->toArray();
		
		//coding Achmad Andriansyah
		$data_bank_statement=array();
		foreach($bankStatements_arr['data'] as $bank_statement){

			$bankStatementSplitExpenseCategory = BankStatementSplitExpenseCategory::where('bank_statement_id', $bank_statement['id'])->get();

			$amount_split = 0;
			foreach($bankStatementSplitExpenseCategory as $split_category)
			{
				$amount_split += $split_category['amount'];
			}
			
			if($bank_statement['record_flag'] != 'D' && !isset($filter['is_uncategorized'])){
				$nested_data['id'] = $bank_statement['id'];
				$nested_data['user_id'] = $bank_statement['user_id'];
				$nested_data['bank_code_name'] = $bank_statement['bank_code_name'];
				$nested_data['bank_account_number'] = $bank_statement['bank_account_number'];
				$nested_data['transaction_type'] = $bank_statement['transaction_type'];
				$nested_data['transaction_description'] = $bank_statement['transaction_description'];
				$nested_data['transaction_date'] = $bank_statement['transaction_date'];
				$nested_data['transaction_branch'] = $bank_statement['transaction_branch'];
				$nested_data['transaction_amount'] = $bank_statement['transaction_amount'];
				$nested_data['transaction_balance'] = $bank_statement['transaction_balance'];
				$nested_data['user_expense_id'] = $bank_statement['user_expense_id'];
				$nested_data['created_by'] = $bank_statement['created_by'];
				$nested_data['created_at'] = $bank_statement['created_at'];
				$nested_data['updated_by'] = $bank_statement['updated_by'];
				$nested_data['updated_at'] = $bank_statement['updated_at'];
				$nested_data['deleted_by'] = $bank_statement['deleted_by'];
				$nested_data['deleted_at'] = $bank_statement['deleted_at'];
				$nested_data['record_flag'] = $bank_statement['record_flag'];
				$nested_data['bank_account_no'] = $bank_statement['bank_account_no'];
				$nested_data['bank_account_id'] = $bank_statement['bank_account_id'];
				$nested_data['created_by_name'] = $bank_statement['created_by_name'];
				$nested_data['updated_by_name'] = $bank_statement['updated_by_name'];
				$nested_data['bank_name'] = $bank_statement['title'];
				$nested_data['bank_code'] = $bank_statement['slug'];
				$cencors_length = strlen($bank_statement['ibank_uid']) - 3;
    			$cencors = str_repeat('*', $cencors_length);
				$nested_data['ibank_uid'] = substr_replace($bank_statement['ibank_uid'], $cencors ,0, $cencors_length);
				$nested_data['color'] = $bank_statement['color'];
				$nested_data['featured_image'] = $bank_statement['featured_image'];
				$nested_data['is_categorized'] = $bank_statement['is_categorized'];
				$data_bank_statement[] = $nested_data;
			}

			if(isset($filter['is_uncategorized']) && $bank_statement['is_categorized'] != 1){
				$nested_data['id'] = $bank_statement['id'];
				$nested_data['user_id'] = $bank_statement['user_id'];
				$nested_data['bank_code_name'] = $bank_statement['bank_code_name'];
				$nested_data['bank_account_number'] = $bank_statement['bank_account_number'];
				$nested_data['transaction_type'] = $bank_statement['transaction_type'];
				$nested_data['transaction_description'] = $bank_statement['transaction_description'];
				$nested_data['transaction_date'] = $bank_statement['transaction_date'];
				$nested_data['transaction_branch'] = $bank_statement['transaction_branch'];
				$nested_data['transaction_amount'] = $bank_statement['transaction_amount'];
				$nested_data['transaction_balance'] = $bank_statement['transaction_balance'];
				$nested_data['user_expense_id'] = $bank_statement['user_expense_id'];
				$nested_data['created_by'] = $bank_statement['created_by'];
				$nested_data['created_at'] = $bank_statement['created_at'];
				$nested_data['updated_by'] = $bank_statement['updated_by'];
				$nested_data['updated_at'] = $bank_statement['updated_at'];
				$nested_data['deleted_by'] = $bank_statement['deleted_by'];
				$nested_data['deleted_at'] = $bank_statement['deleted_at'];
				$nested_data['record_flag'] = $bank_statement['record_flag'];
				$nested_data['bank_account_no'] = $bank_statement['bank_account_no'];
				$nested_data['bank_account_id'] = $bank_statement['bank_account_id'];
				$nested_data['created_by_name'] = $bank_statement['created_by_name'];
				$nested_data['updated_by_name'] = $bank_statement['updated_by_name'];
				$nested_data['bank_name'] = $bank_statement['title'];
				$nested_data['bank_code'] = $bank_statement['slug'];
				$cencors_length = strlen($bank_statement['ibank_uid']) - 3;
    			$cencors = str_repeat('*', $cencors_length);
				$nested_data['ibank_uid'] = substr_replace($bank_statement['ibank_uid'], $cencors ,0, $cencors_length);
				$nested_data['color'] = $bank_statement['color'];
				$nested_data['featured_image'] = $bank_statement['featured_image'];
				$nested_data['is_categorized'] = $bank_statement['is_categorized'];
				$nested_data['transaction_amount_remaining'] = is_null($bank_statement['is_categorized']) ? 0 : ($bank_statement['is_categorized'] == 0 && $amount_split != 0 ? $bank_statement['transaction_amount'] - $amount_split : 0);
				$data_bank_statement[] = $nested_data;
			}
		}
		//dd($bankStatements_arr);
		return response()->json([
			'result' => 'success',
			'cutoff_last' => $query_last_transaction_amount,
			'cutoff_next' => $query_next_transaction_amount,
			'total' => !is_null($bankStatements_arr['data']) ? count($data_bank_statement) : $bankStatements_arr['total'],
			'per_page' => $bankStatements_arr['per_page'],
			'current_page' => $bankStatements_arr['current_page'],
			'last_page' => $bankStatements_arr['last_page'],
			'next_page_url' => $bankStatements_arr['next_page_url'],
			'prev_page_url' => $bankStatements_arr['prev_page_url'],
			'data' => !is_null($bankStatements_arr['data']) ? $data_bank_statement : $bankStatements_arr['data']
		])->setStatusCode(200);
    }

	public function expensesSetCategory(Request $request){
		try {			
			$bank_statement_id = $request->input('bank_statement_id');
			$user_expense_id = $request->input('user_expense_id');
			$bankStatement = $this->bankStatement->update($bank_statement_id, [
				'user_expense_id' => $user_expense_id
			]);
			return response()->json([
				'result' => 'success',
				'data' => $bankStatement
			])->setStatusCode(200);
			//update statement id to spesific wallet id
        } catch (ValidationException $e) {
			$statusCode = 412;
			if(isset($e->getErrors()['bank_statement']) && $e->getErrors()['bank_statement'] == 'not_found'){
				$statusCode = 503;
			}	
            return response()->json([
					'result' => 'error',
					'alerts' => $e->getErrors()
			])->setStatusCode($statusCode);
        }	
	}

	public function monitorMonthly(Request $request){
		$client_id = $request->input('client_id');
		$period = $request->input('period');
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
		$cutoff_check_last = $today >= "$period-$cutoff_date_last" && $today <= date("Y-m-t", strtotime($cutoff_now)) ? $cutoff_now : $cutoff_last;
		$cutoff_check_next = $today >= "$period-$cutoff_date_last" && $today <= date("Y-m-t", strtotime($cutoff_now)) ? $cutoff_next : $cutoff_now;

		$query_last_transaction_amount = date('N', strtotime($cutoff_check_last)) == 6 ? date('Y-m-d', strtotime('-1 day', strtotime($cutoff_check_last))) : (date('N', strtotime($cutoff_check_last)) == 7 ? date('Y-m-d', strtotime('-2 day', strtotime($cutoff_check_last))) : $cutoff_check_last) ;
		$query_next_transaction_amount = date('N', strtotime($cutoff_check_next)) == 6 ? date('Y-m-d', strtotime('-1 day', $month_next)) : (date('N', strtotime($cutoff_check_next)) == 7 ? date('Y-m-d', strtotime('-2 day', $month_next)) : $cutoff_check_next) ;

		//query cashflow analysis menggunakan max approved pada periode tersebut
		$cashflowAnalysis = $this->cashflowAnalysis->showByVersion([
			'transaction_date_until' => $period,
			'user_id' => $client_id,
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

		if(isset($cashflowAnalysis['expenses']))
		{
			$income = !isset($cashflowAnalysis['incomes'][0]) ? 0 : floatval($cashflowAnalysis['incomes'][0]['pendapatan_bulanan']);
			$anggaran=0;
			$other_category = array();
			foreach($cashflowAnalysis['expenses'] as $expense){
				if($expense->taxo_wallet_name != 'Other'){
					$anggaran += floatval($expense->anggaran_perbulan);
				}
			}

			$plan_balance = 0;
			foreach($cashflowAnalysis['plan_balances'] as $plan_balances){
				$plan_balance += floatval($plan_balances->balance);
			}

			$debt_repayment = 0;
			foreach($cashflowAnalysis['debt_repayments'] as $debt_repayments){
				$debt_repayment += floatval($debt_repayments->cicilan_perbulan);
			}

			$asset_repayment = 0;
			foreach($cashflowAnalysis['asset_repayments'] as $asset_repayments){
				$asset_repayment += floatval($asset_repayments->cicilan_perbulan);
			}

			$insurance = 0;
			foreach($cashflowAnalysis['insurances'] as $insurances){
				$insurance += floatval($insurances->premi_perbulan);
			}

			$total_pengeluaran = $this->bankStatement->getTransactionAmount([
				'client_id' => $client_id,
				// 'period-monitor' => $period,
				'transaction_type' => 'debit',
				'kategori-expenses' => [303],
				'period-last' => $query_last_transaction_amount,
				'period-next' => $query_next_transaction_amount
			]);

			$total_pengeluaran_other = $this->bankStatement->getTransactionAmount([
				'client_id' => $client_id,
				// 'period-monitor' => $period,
				'transaction_type' => 'debit',
				'user_expense_id' => 65,
				'period-last' => $query_last_transaction_amount,
				'period-next' => $query_next_transaction_amount
			]);

			//total nilai
			$total_anggaran = $insurance+$asset_repayment+$debt_repayment+$anggaran;
			$total_sisa_anggaran =$total_anggaran - $total_pengeluaran;
			$total_dana_tersedia = $income-$total_anggaran;
			//$kondisi_nol_dana_tersedia = ($total_pengeluaran - $total_anggaran)/$total_dana_tersedia * 100;

			//total persen
			$persen_total_anggaran = $total_sisa_anggaran == 0 || $total_anggaran == 0 ? 0 :($total_sisa_anggaran/$total_anggaran)*100;
			$persen_total_pengeluaran = $total_pengeluaran == 0 || $total_anggaran == 0 ? 0 : ($total_pengeluaran/$total_anggaran)*100;
			$persen_dana_tersedia = $total_dana_tersedia < 0 || $total_dana_tersedia == 0 ? 0 : 100 - (($total_pengeluaran_other/$total_dana_tersedia) * 100);
		}
		
		return response()->json([
			'result' => 'success',
			'data' => [
				[
					'label' => 'Dana Tersedia',
					'percentage' => $persen_dana_tersedia < 0 ? 0 : money($persen_dana_tersedia, 1),
					'value' => $total_dana_tersedia-$total_pengeluaran_other
				],
				[
					'label' => 'Anggaran',
					'percentage' => money($persen_total_anggaran, 1),
					'value' => $total_sisa_anggaran
				],
				[
					'label' => 'Pengeluaran',
					'percentage' => money($persen_total_pengeluaran, 1),
					'value' => floatval($total_pengeluaran)
				]
			]
		])->setStatusCode(200);
	}

	function move_to_bottom(&$array, $key) {
		$value = $array[$key];
		unset($array[$key]);
		$array[$key] = $value;
	}

	public function expensesMonthly(Request $request){
		$client_id = $request->input('client_id');
		$period = $request->input('period');
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
		$cashflowAnalysis = $this->cashflowAnalysis->showByVersion([
			'transaction_date_until' => $period,
			'user_id' => $client_id,
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
				'client_id' => $client_id,
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
					'taxo_wallet_name' => $expense->taxo_wallet_name,
					'taxo_wallet_ico' => $expense->taxo_wallet_ico,
					'taxo_wallet_color' => $expense->taxo_wallet_color,
					'taxo_wallet_description' => $expense->taxo_wallet_description,
					'budget_allocated' => $expense->taxo_wallet_name == 'Other' && (int) $expense->anggaran_perbulan == 0 ? $income-($anggaran+$debt_repayment+$total_asset_repayment+$insurance) : (int) $expense->anggaran_perbulan,
					'budget_remaining' => $expense->taxo_wallet_name == 'Other' && (int) $expense->anggaran_perbulan == 0 ? $income-($anggaran+$debt_repayment+$total_asset_repayment+$insurance) : (int) $expense->anggaran_perbulan,
					'budget_spent' => 0
				];
			}

			foreach($cashflowAnalysis['debt_repayments'] as $debt_repayment){
				//$user_expenses[$expense->id] = [ codingan dari mas Yudi
				$user_expenses[$debt_repayment->taxo_wallet_id] = [ // coding dari Andriansyah
					'taxo_wallet_name' => $debt_repayment->taxo_wallet_name,
					'taxo_wallet_ico' => $debt_repayment->taxo_wallet_ico,
					'taxo_wallet_color' => $debt_repayment->taxo_wallet_color,
					'taxo_wallet_description' => $debt_repayment->taxo_wallet_description,
					'budget_allocated' => (int) $debt_repayment->cicilan_perbulan,
					'budget_remaining' => (int) $debt_repayment->cicilan_perbulan,
					'budget_spent' => 0
				];
			}

			foreach($cashflowAnalysis['asset_repayments'] as $asset_repayment){
				//$user_expenses[$expense->id] = [ codingan dari mas Yudi
				$user_expenses[$asset_repayment->taxo_wallet_id] = [ // coding dari Andriansyah
					'taxo_wallet_name' => $asset_repayment->taxo_wallet_name,
					'taxo_wallet_ico' => $asset_repayment->taxo_wallet_ico,
					'taxo_wallet_color' => $asset_repayment->taxo_wallet_color,
					'taxo_wallet_description' => $asset_repayment->taxo_wallet_description,
					'budget_allocated' => array_sum($total_asset[$asset_repayment->taxo_wallet_id]['total']),
					'budget_remaining' => array_sum($total_asset[$asset_repayment->taxo_wallet_id]['total']),
					'budget_spent' => 0
				];
			}

			foreach($cashflowAnalysis['insurances'] as $insurance){
				//$user_expenses[$expense->id] = [ codingan dari mas Yudi
				$user_expenses[$insurance->id] = [ // coding dari Andriansyah
					'taxo_wallet_name' => $insurance->taxo_wallet_name,
					'taxo_wallet_no_polis' => $insurance->no_polis,
					'taxo_wallet_ico' => $insurance->taxo_wallet_ico,
					'taxo_wallet_color' => $insurance->taxo_wallet_color,
					'taxo_wallet_description' => $insurance->taxo_wallet_description,
					'budget_allocated' => (int) $insurance->premi_perbulan,
					'budget_remaining' => (int) $insurance->premi_perbulan,
					'budget_spent' => 0
				];
			}

			//get detail Monthly bank transaction
			$bankStatements = $this->bankStatement->findAllByJoin([
				'user_expense_ids' => array_keys($user_expenses),
				'client_id' => $client_id,
				// 'period' => $period,
				'transaction_type' => 'debit',
				'period-last' => $query_last_transaction_amount,
				'period-next' => $query_next_transaction_amount
			]);
			
			if(!is_null($bankStatements)){
				foreach($bankStatements as $bankStatement){
					$user_expenses[$bankStatement->user_expense_id]['data'][] = [
						'id' => $bankStatement->id,
						'name' => $bankStatement->transaction_description,
						'bank_name' => $bankStatement->title,
						'bank_code' => $bankStatement->slug,
						'ibank_uid' => $bankStatement->ibank_uid,
						'value' => (int) $bankStatement->transaction_amount,
						'created_at' => $bankStatement->transaction_date
					];
					$budget_remaining = $user_expenses[$bankStatement->user_expense_id]['budget_remaining'];
					$budget_spent = $user_expenses[$bankStatement->user_expense_id]['budget_spent'];
					$calc_remaining_budget = $budget_remaining - $bankStatement->transaction_amount;
					$user_expenses[$bankStatement->user_expense_id]['budget_remaining'] = (int) $calc_remaining_budget;
					$calc_spent_budget = $budget_spent + $bankStatement->transaction_amount;
					$user_expenses[$bankStatement->user_expense_id]['budget_spent'] = (int) $calc_spent_budget;
				}
			}
		}

		$this->move_to_bottom($user_expenses, 65);
		
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

	public function expensesMonthlyV2(Request $request){
		$client_id = $request->input('client_id');
		$period = $request->input('period');
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
		$cashflowAnalysis = $this->cashflowAnalysis->showByVersion([
			'transaction_date_until' => $period,
			'user_id' => $client_id,
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
				'client_id' => $client_id,
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
				$debt_repayment += $debt_repayments->cicilan_perbulan;
				$debt[$debt_repayments->taxo_wallet_id]['total'][] = $debt_repayments->cicilan_perbulan;
			}

			$total_asset_repayment = 0;
			$total_mobil=0;$total_properti=0;$total_mutual=0;$total_pendidikan=0;
			foreach($cashflowAnalysis['asset_repayments'] as $asset_repayments){
				$total_asset_repayment += $asset_repayments->cicilan_perbulan;
				$total_asset[$asset_repayments->taxo_wallet_id]['total'][] = $asset_repayments->cicilan_perbulan;
			}

			$insurance = 0;
			foreach($cashflowAnalysis['insurances'] as $insurances){
				$insurance += floatval($insurances->premi_perbulan);
			}
			
			foreach($cashflowAnalysis['expenses'] as $expense){
				//$user_expenses[$expense->id] = [ codingan dari mas Yudi
				$user_expenses[$expense->taxo_wallet_id] = [ // coding dari Andriansyah
					'taxo_wallet_name' => $expense->taxo_wallet_name,
					'taxo_wallet_ico' => $expense->taxo_wallet_ico,
					'taxo_wallet_color' => $expense->taxo_wallet_color,
					'taxo_wallet_description' => $expense->taxo_wallet_description,
					'budget_allocated' => $expense->taxo_wallet_name == 'Other' && (int) $expense->anggaran_perbulan == 0 ? $income-($anggaran+$debt_repayment+$total_asset_repayment+$insurance) : (int) $expense->anggaran_perbulan,
					'budget_remaining' => $expense->taxo_wallet_name == 'Other' && (int) $expense->anggaran_perbulan == 0 ? $income-($anggaran+$debt_repayment+$total_asset_repayment+$insurance) : (int) $expense->anggaran_perbulan,
					'budget_spent' => 0,
					'category' => 'expenses'
				];
			}

			foreach($cashflowAnalysis['debt_repayments'] as $debt_repayment){
				//$user_expenses[$expense->id] = [ codingan dari mas Yudi
				$user_expenses[$debt_repayment->taxo_wallet_id] = [ // coding dari Andriansyah
					'taxo_wallet_name' => $debt_repayment->taxo_wallet_name,
					'taxo_wallet_ico' => $debt_repayment->taxo_wallet_ico,
					'taxo_wallet_color' => $debt_repayment->taxo_wallet_color,
					'taxo_wallet_description' => $debt_repayment->taxo_wallet_description,
					'budget_allocated' => array_sum($debt[$debt_repayment->taxo_wallet_id]['total']),
					'budget_remaining' => array_sum($debt[$debt_repayment->taxo_wallet_id]['total']),
					'budget_spent' => 0,
					'category' => 'debt'
				];
			}

			foreach($cashflowAnalysis['asset_repayments'] as $asset_repayment){
				//$user_expenses[$expense->id] = [ codingan dari mas Yudi
				$user_expenses[$asset_repayment->taxo_wallet_id] = [ // coding dari Andriansyah
					'taxo_wallet_name' => $asset_repayment->taxo_wallet_name,
					'taxo_wallet_ico' => $asset_repayment->taxo_wallet_ico,
					'taxo_wallet_color' => $asset_repayment->taxo_wallet_color,
					'taxo_wallet_description' => $asset_repayment->taxo_wallet_description,
					'budget_allocated' => array_sum($total_asset[$asset_repayment->taxo_wallet_id]['total']),
					'budget_remaining' => array_sum($total_asset[$asset_repayment->taxo_wallet_id]['total']),
					'budget_spent' => 0,
					'category' => 'assets'
				];
			}

			foreach($cashflowAnalysis['insurances'] as $insurance){
				//$user_expenses[$expense->id] = [ codingan dari mas Yudi
				$user_expenses[$insurance->id] = [ // coding dari Andriansyah
					'taxo_wallet_name' => $insurance->taxo_wallet_name,
					'taxo_wallet_no_polis' => $insurance->no_polis,
					'taxo_wallet_ico' => $insurance->taxo_wallet_ico,
					'taxo_wallet_color' => $insurance->taxo_wallet_color,
					'taxo_wallet_description' => $insurance->taxo_wallet_description,
					'budget_allocated' => (int) $insurance->premi_perbulan,
					'budget_remaining' => (int) $insurance->premi_perbulan,
					'budget_spent' => 0,
					'category' => 'insurances'
				];
			}

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
		}

		if(isset($user_expenses[330])){
			$this->move_to_bottom($user_expenses, 330);
		}
		if(isset($user_expenses[303])){
			$this->move_to_bottom($user_expenses, 303);
		}

		$this->move_to_bottom($user_expenses, 65);
		
		$arr = array();
		$i = 0;
		foreach($user_expenses as $key => $value){
			if(count($value) > 1)
			{
				$arr[$i] = $value;
				$value['taxo_wallet_id'] = $key;
				if(!array_key_exists('data', $value)){
					$value['data'] = [];
				}
				$arr[$i] = $value;
				$i++;
			}
		}

		return response()->json([
			'result' => 'success',
			// 'last_month' => $query_last_transaction_amount,
			// 'next_month' => $query_next_transaction_amount,
			'data' => $arr
		])->setStatusCode(200);
	}

	//API pendapatan Achmad Andriansyah
	public function pendapatanList(Request $request){
		$client_id = $request->input('client_id');
		$period = $request->input('period');
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
		$cashflowAnalysis = $this->cashflowAnalysis->showByVersion([
			'transaction_date_until' => $period,
			'user_id' => $client_id,
			'modules' => [ 'incomes' ]
		]);

		if(!$cashflowAnalysis['incomes']){
			return response()->json([
				'result' => 'error',
				'data' => null
			])->setStatusCode(200);
		}

		
		$user_expenses = null;
		if(isset($cashflowAnalysis['incomes'])){
			$income_month = (int) $cashflowAnalysis['incomes'][0]['pendapatan_bulanan'];
			$income_other = (int) $cashflowAnalysis['incomes'][0]['pendapatan_lain'];
			$pendapatan_tidak_tetap = (int) $cashflowAnalysis['incomes'][0]['pendapatan_tidak_tetap_bulan'];

			$incomes_raw = $this->taxonomy->getTermsByPostType_n_parent('wallet', 'income')->toHierarchy(); 

			foreach ($incomes_raw as $income_raw) {
				// $incomes[] = $income_raw;
				$user_expenses[$income_raw->id] = [ // coding dari Andriansyah
					'taxo_wallet_name' => $income_raw->title,
					'taxo_wallet_ico' => $income_raw->image,
					'taxo_wallet_color' => $income_raw->color,
					'taxo_wallet_description' => $income_raw->description,
					'expected_value' => $income_raw->slug === 'pendapatan-tahunan' ? $income_other : ($income_raw->slug === 'pendapatan-tetap-bulanan' ? $income_month : ($income_raw->slug === 'pendapatan-tidak-tetap-bulanan' ? $pendapatan_tidak_tetap : 0)),
					'actual_value' => 0,
					// 'value' => 0
				];
			}

			//get detail Monthly bank transaction
			$bankStatements = $this->bankStatement->findAllByJoin([
				'user_expense_ids' => array_keys($user_expenses),
				'client_id' => $client_id,
				// 'period' => $period,
				'transaction_type' => 'credit',
				'period-last' => $query_last_transaction_amount,
				'period-next' => $query_next_transaction_amount
			]);
			
			if(!is_null($bankStatements)){
				foreach($bankStatements as $bankStatement){
					$user_expenses[$bankStatement->user_expense_id]['data'][] = [
						'id' => $bankStatement->id,
						'name' => $bankStatement->transaction_description,
						'bank_name' => $bankStatement->title,
						'bank_code' => $bankStatement->slug,
						'ibank_uid' => $bankStatement->ibank_uid,
						'value' => (int)$bankStatement->transaction_amount,
						'created_at' => $bankStatement->transaction_date
					];
					$actual_value = $user_expenses[$bankStatement->user_expense_id]['actual_value'];
					$calc_actual_value = $actual_value + $bankStatement->transaction_amount;
					$user_expenses[$bankStatement->user_expense_id]['actual_value'] = (int) $calc_actual_value;
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
			'today' => $today,
			'cutoff_last' => $query_last_transaction_amount,
			'cutoff_next' => $query_next_transaction_amount,
			'data' => $arr
		])->setStatusCode(200);
	}

	// codingan Achmad Andriansyah
	public function transactionAccount(Request $request)
	{
		//request payload
		$client_id = $request->input('client_id');
		$period = $request->input('period');
		$today = Carbon::now()->format('Y-m-d');
		
		//query repository
		$query = BankAccount::query();
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
		$cutoff_check_last = $today >= "$period-$cutoff_date_last" && $today <= date("Y-m-t", strtotime($cutoff_now)) ? $cutoff_now : $cutoff_last;
		$cutoff_check_next = $today >= "$period-$cutoff_date_last" && $today <= date("Y-m-t", strtotime($cutoff_now)) ? $cutoff_next : $cutoff_now;

		$query_last_transaction_amount = date('N', strtotime($cutoff_check_last)) == 6 ? date('Y-m-d', strtotime('-1 day', strtotime($cutoff_check_last))) : (date('N', strtotime($cutoff_check_last)) == 7 ? date('Y-m-d', strtotime('-2 day', strtotime($cutoff_check_last))) : $cutoff_check_last) ;
		$query_next_transaction_amount = date('N', strtotime($cutoff_check_next)) == 6 ? date('Y-m-d', strtotime('-1 day', $month_next)) : (date('N', strtotime($cutoff_check_next)) == 7 ? date('Y-m-d', strtotime('-2 day', $month_next)) : $cutoff_check_next) ;

		//menampilkan bank_account_id dari tabel bank_accounts
		$bank_account_id = $query->where('user_id', $client_id)->get();
		$data_account = array();
		foreach($bank_account_id as $bank_accounts){
			$nested_data['id'] = $bank_accounts['id'];
			$data_account[] = $nested_data;
		}
		$account_id = array_column($data_account, 'id');

		//saldo_awal
		$saldo_awal = $this->bankStatement->getLastTransactionBalanceLast($client_id,$query_last_transaction_amount);
		$total_saldo_awal=0;
		if($saldo_awal){
			foreach($saldo_awal as $awal_saldo){
				if($awal_saldo->record_flag != 'D'){
					$total_saldo_awal += $awal_saldo->transaction_balance;
					
				}
			}
		}

		// //saldo_akhir
		$saldo_akhir = $this->bankStatement->getLastTransactionBalanceNow($client_id);
		$total_saldo_akhir=0;
		if($saldo_akhir){
			foreach($saldo_akhir as $akhir_saldo){
				if($akhir_saldo->record_flag != 'D'){
					$total_saldo_akhir += $akhir_saldo->transaction_balance;
				}
			}
		}

		// $saldo_awal = $this->bankStatement->getLastTransactionBalance([
		// 	'period-last-cutoff' => $cutoff_check_last, //Carbon::now()->format('Y-m'),
		// 	//'period-now' => $cutoff_check_last,
		// 	'client_id' => $client_id,
		// ]);

		// $saldo_akhir = $this->bankStatement->getLastTransactionBalance([
		// 	'client_id' => $client_id,
		// ]);

		$pemasukan = $this->bankStatement->getTransactionAmount([
			'period-last' => $query_last_transaction_amount,
			'period-next' => $query_next_transaction_amount,
			'client_id' => $client_id,
			'transaction_type' => 'credit',
			'bank_account_id' => $account_id
		]);

		$pengeluaran = $this->bankStatement->getTransactionAmount([
			'period-last' => $query_last_transaction_amount,
			'period-next' => $query_next_transaction_amount,
			'client_id' => $client_id,
			'transaction_type' => 'debit',
			//'kategori-expenses' => [303]
			'bank_account_id' => $account_id
		]);

		//query bank account untuk melihat status bank account
		$status = 'Failed';
		$status_account = $query->where('user_id', $client_id)->orderBy('id','DESC')->take(1)->get();
		if(!empty($status_account[0]) && $status_account[0]['record_flag'] != 'D'){
			switch($status_account[0]['status']){
				case 'on_process' : $status = 'On Process'; break;
				case 'connected' : $status = 'Connected'; break;
			}
		}

		return response()->json([
			'result' => 'success',
			'status_account' => !empty($status_account[0]) ? $status : 'Failed' ,
			'cutoff_last' => $query_last_transaction_amount,
			'cutoff_next' => $query_next_transaction_amount,
			'data' => [
				[
					'label' => 'Saldo Awal',
					'value' => $total_saldo_awal 
					// !empty($saldo_awal[0])?floatval($saldo_awal[0]['transaction_balance']):0
				],
				[
					'label' => 'Mutasi Kredit',
					'value' => floatval($pemasukan)
				],
				[
					'label' => 'Mutasi Debit',
					'value' => floatval($pengeluaran)
				],
				[
					'label' => 'Saldo Akhir',
					'value' => $total_saldo_akhir
					// !empty($saldo_akhir[0])?floatval($saldo_akhir[0]['transaction_balance']):0
				]
			]
		])->setStatusCode(200);
	}

	public function transactionAccountV2(Request $request)
	{
		//request payload
		$client_id = $request->input('client_id');
		$period = $request->input('period');
		$today = Carbon::now()->format('Y-m-d');
		
		//query repository
		$query = BankAccount::query();
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
		$cutoff_check_last = $today >= "$period-$cutoff_date_last" && $today <= date("Y-m-t", strtotime($cutoff_now)) ? $cutoff_now : $cutoff_last;
		$cutoff_check_next = $today >= "$period-$cutoff_date_last" && $today <= date("Y-m-t", strtotime($cutoff_now)) ? $cutoff_next : $cutoff_now;

		$query_last_transaction_amount = date('N', strtotime($cutoff_check_last)) == 6 ? date('Y-m-d', strtotime('-1 day', strtotime($cutoff_check_last))) : (date('N', strtotime($cutoff_check_last)) == 7 ? date('Y-m-d', strtotime('-2 day', strtotime($cutoff_check_last))) : $cutoff_check_last) ;
		$query_next_transaction_amount = date('N', strtotime($cutoff_check_next)) == 6 ? date('Y-m-d', strtotime('-1 day', $month_next)) : (date('N', strtotime($cutoff_check_next)) == 7 ? date('Y-m-d', strtotime('-2 day', $month_next)) : $cutoff_check_next) ;

		//menampilkan bank_account_id dari tabel bank_accounts
		$bank_account_id = $query->where('user_id', $client_id)->get();
		$data_account = array();
		foreach($bank_account_id as $bank_accounts){
			$nested_data['id'] = $bank_accounts['id'];
			$data_account[] = $nested_data;
		}
		$account_id = array_column($data_account, 'id');

		//saldo_awal
		$saldo_awal = $this->bankStatement->getLastTransactionBalanceLast($client_id,$query_last_transaction_amount);
		$total_saldo_awal=0;
		if($saldo_awal){
			foreach($saldo_awal as $awal_saldo){
				if($awal_saldo->record_flag != 'D'){
					$total_saldo_awal += $awal_saldo->transaction_balance;
					
				}
			}
		}

		// //saldo_akhir
		$saldo_akhir = $this->bankStatement->getLastTransactionBalanceNow($client_id);
		$total_saldo_akhir=0;
		if($saldo_akhir){
			foreach($saldo_akhir as $akhir_saldo){
				if($akhir_saldo->record_flag != 'D'){
					$total_saldo_akhir += $akhir_saldo->transaction_balance;
				}
			}
		}

		// $saldo_awal = $this->bankStatement->getLastTransactionBalance([
		// 	'period-last-cutoff' => $cutoff_check_last, //Carbon::now()->format('Y-m'),
		// 	//'period-now' => $cutoff_check_last,
		// 	'client_id' => $client_id,
		// ]);

		// $saldo_akhir = $this->bankStatement->getLastTransactionBalance([
		// 	'client_id' => $client_id,
		// ]);

		$pemasukan = $this->bankStatement->getTransactionAmount([
			'period-last' => $query_last_transaction_amount,
			'period-next' => $query_next_transaction_amount,
			'client_id' => $client_id,
			'transaction_type' => 'credit',
			'bank_account_id' => $account_id
		]);

		$pengeluaran = $this->bankStatement->getTransactionAmount([
			'period-last' => $query_last_transaction_amount,
			'period-next' => $query_next_transaction_amount,
			'client_id' => $client_id,
			'transaction_type' => 'debit',
			//'kategori-expenses' => [303]
			'bank_account_id' => $account_id
		]);

		//query bank account untuk melihat status bank account
		$status = 'Failed';
		$status_account = $query->where('user_id', $client_id)->orderBy('id','DESC')->take(1)->get();
		if(!empty($status_account[0]) && $status_account[0]['record_flag'] != 'D'){
			switch($status_account[0]['status']){
				case 'on_process' : $status = 'On Process'; break;
				case 'connected' : $status = 'Connected'; break;
			}
		}

		return response()->json([
			'result' => 'success',
			'data' => [[
				'status_account' => !empty($status_account[0]) ? $status : 'Failed' ,
				'cutoff_last' => $query_last_transaction_amount,
				'cutoff_next' => $query_next_transaction_amount,
				'saldo_awal' =>  (int) $total_saldo_awal, //!empty($saldo_awal[0])?floatval($saldo_awal[0]['transaction_balance']):0,
				'pendapatan' =>  (int) $pemasukan,
				'pengeluaran' => (int) $pengeluaran,
				'dana_tersisa' => (int) $pemasukan - (int) $pengeluaran,
				'saldo_akhir' => (int) $total_saldo_akhir //!empty($saldo_akhir[0])?floatval($saldo_akhir[0]['transaction_balance']):0
			]]
		])->setStatusCode(200);
	}

	public function bankList(){
		$banks = $this->bank->where('is_status', 1)->orderBy('title','ASC')->get();
		
		return response()->json([
			'result' => 'success',
			'data' => !empty($banks) ? $banks : null
		])->setStatusCode(200);
	}
	








	/**
	 | -------------------------------------
	 | - Top 3
	 |		- Highest
	 |		- Active
	 |
	 | - Persentasi pengeluaran perbulan
	 |
	 | -------------------------------------
	 | Gugun Dwi Permana
	 | 20 Agustus 2018
	 | -------------------------------------
	 */

	public function topThreeHighest(Request $request)
	{

	 	$user_id = $request->input('user_id');

		$data = \DB::select("
		select * from (
		select 
			'".url('/')."' || z.image image
			, z.title
			, z.color
			, z.count jml_transaksi
			, z.pengeluaran
			, z.anggaran
			-- membuat persentasi
			, case 
			 	when z.anggaran < 1 then 0 
				when ((z.pengeluaran / z.anggaran) * 100) > 100 then 100
				else (z.pengeluaran / z.anggaran) * 100
			end::numeric::integer as percent
		from 
		(
			select 
				b.image,
				b.title,
				-- ambil color, dari setiap category
				(select distinct z.color from taxonomies z where z.title = b.title and z.color is not null limit 1),
				count(*),
				sum(a.transaction_amount) as pengeluaran,
				d.anggaran_perbulan anggaran
			from 
				bank_statements a LEFT JOIN taxonomies b ON a.user_expense_id = b.id
				INNER JOIN users c ON a.user_id = c.id
				LEFT JOIN expenses d ON d.taxo_wallet_id = b.id
			where
				a.user_id = '".$user_id."'
				and a.transaction_type = 'debit'
				-- ambil data hanya dimana transaction_date di bulan periode gajihan sekarang 
				and 
					-- jika tanggal sekarang lebih besar dari tanggal cutoff_date user
					-- maka berarti start gajihannya di bulan sekarang
					case when EXTRACT(day FROM CURRENT_DATE) > c.cutoff_date then
						a.transaction_date
					else
						-- jika tanggal sekarang lebih kecil dari tanggal cutoff_date user
						-- berarti bulan sekarang belum gajihan, dan ambil dari bulan kemarin
						date_trunc('day', a.transaction_date + interval '1 month') 
					end
				>=
					to_timestamp(
						-- TANGGAL
						-- ambil tanggal cutoff_date user (tanggal gajihan)
						case 
							-- jika cutoff_date di user tidak diisi maka kita set 01
							when (c.cutoff_date = 0) then '01'
							-- jika cutoff_date terdiri dari 2 digit abaikan, dan jika cuman 1 digin tambah '0' di depan
							when (c.cutoff_date > 9) then c.cutoff_date::text
							else coalesce(0::text, '') || coalesce( c.cutoff_date::text , '')
						end
						|| '-' ||
						-- BULAN
						-- ambil bulan sekarang
						case 
							-- jika bulan sekarang 2 digit, abaikan. jika hanya 1 digit tambah '0' di depan
							when (EXTRACT(month FROM CURRENT_DATE) > 9) then
								EXTRACT(month FROM CURRENT_DATE)::text
							else 
								coalesce(0::text, '') || coalesce( EXTRACT(month FROM CURRENT_DATE)::text , '')
							end
						|| '-' ||
						-- TAHUN 
						-- ambil tahun sekarang
						EXTRACT(year FROM CURRENT_DATE)::text
					, 'DD-MM-YYYY'
					)::timestamp without time zone
				-- ambil dengan data anggaran version terbaru
				and d.version = (
					select distinct e.version from expenses e
					where
						e.user_id = a.user_id
					order by e.version DESC
					limit 1
				)
				and d.user_id = a.user_id
			group by b.image, b.title, d.anggaran_perbulan
			order by sum(a.transaction_amount) DESC
		) z
		) y
		order by y.percent DESC
		limit 3
		");
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
		$query_next_transaction_amount = date('N', strtotime($cutoff_check_next)) == 6 ? date('Y-m-d', strtotime('-1 day', $month_next)) : (date('N', strtotime($cutoff_check_next)) == 7 ? date('Y-m-d', strtotime('-2 day', $month_next)) : $cutoff_check_next) ;
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
			$bankStatements = $this->bankStatement->findAllByJoin([
				'user_expense_ids' => array_keys($user_expenses),
				'client_id' => $user_id,
				// 'period' => $period,
				'transaction_type' => 'debit',
				'period-last' => $query_last_transaction_amount,
				'period-next' => $query_next_transaction_amount
			]);
			
			if(!is_null($bankStatements)){
				foreach($bankStatements as $bankStatement){
					$user_expenses[$bankStatement->user_expense_id]['data'][] = [
						'id' => $bankStatement->id,
						'name' => $bankStatement->transaction_description,
						'bank_name' => $bankStatement->title,
						'bank_code' => $bankStatement->slug,
						'ibank_uid' => $bankStatement->ibank_uid,
						'value' => (int) $bankStatement->transaction_amount,
						'created_at' => $bankStatement->transaction_date
					];
					$budget_allocated = $user_expenses[$bankStatement->user_expense_id]['anggaran'];
					$budget_remaining = $user_expenses[$bankStatement->user_expense_id]['sisa_anggaran'];
					$budget_spent = $user_expenses[$bankStatement->user_expense_id]['pengeluaran'];
					$user_expenses[$bankStatement->user_expense_id]['jml_transaksi'] = count($user_expenses[$bankStatement->user_expense_id]['data']);
					$jml_transaksi=count($user_expenses[$bankStatement->user_expense_id]['data']);
					$calc_remaining_budget = $budget_remaining - $bankStatement->transaction_amount;
					$user_expenses[$bankStatement->user_expense_id]['sisa_anggaran'] = (int) $calc_remaining_budget;
					$calc_spent_budget = $budget_spent + $bankStatement->transaction_amount;
					$user_expenses[$bankStatement->user_expense_id]['pengeluaran'] = (int) $calc_spent_budget;
					$percent = $budget_allocated < 0 && $jml_transaksi >= 1 ? 100 : ($budget_allocated == 0 ? 100 : ($calc_spent_budget / $budget_allocated) * 100);
					$user_expenses[$bankStatement->user_expense_id]['percent'] = round($percent,2)  >= 100 ? 100 : round($percent,2);
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





	public function topThreeActive(Request $request)
	{
	 	
	 	$user_id = $request->input('user_id');

		$data = \DB::select("
		select * from (
		select 
			'".url('/')."' || z.image image
			, z.title
			, z.color
			, z.count jml_transaksi
			, z.pengeluaran
			, z.anggaran
			-- membuat persentasi
			, case 
			 	when z.anggaran < 1 then 0 
				when ((z.pengeluaran / z.anggaran) * 100) > 100 then 100
				else (z.pengeluaran / z.anggaran) * 100
			end::numeric::integer as percent
		from 
		(
			select 
				b.image,
				b.title,
				-- ambil color, dari setiap category
				(select distinct z.color from taxonomies z where z.title = b.title and z.color is not null limit 1),
				count(*),
				sum(a.transaction_amount) as pengeluaran,
				d.anggaran_perbulan anggaran
			from 
				bank_statements a LEFT JOIN taxonomies b ON a.user_expense_id = b.id
				INNER JOIN users c ON a.user_id = c.id
				LEFT JOIN expenses d ON d.taxo_wallet_id = b.id
			where
				a.user_id = '".$user_id."'
				and a.transaction_type = 'debit'
				-- ambil data hanya dimana transaction_date di bulan periode gajihan sekarang 
				and 
					-- jika tanggal sekarang lebih besar dari tanggal cutoff_date user
					-- maka berarti start gajihannya di bulan sekarang
					case when EXTRACT(day FROM CURRENT_DATE) > c.cutoff_date then
						a.transaction_date
					else
						-- jika tanggal sekarang lebih kecil dari tanggal cutoff_date user
						-- berarti bulan sekarang belum gajihan, dan ambil dari bulan kemarin
						date_trunc('day', a.transaction_date + interval '1 month') 
					end
				>=
					to_timestamp(
						-- TANGGAL
						-- ambil tanggal cutoff_date user (tanggal gajihan)
						case 
							-- jika cutoff_date di user tidak diisi maka kita set 01
							when (c.cutoff_date = 0) then '01'
							-- jika cutoff_date terdiri dari 2 digit abaikan, dan jika cuman 1 digin tambah '0' di depan
							when (c.cutoff_date > 9) then c.cutoff_date::text
							else coalesce(0::text, '') || coalesce( c.cutoff_date::text , '')
						end
						|| '-' ||
						-- BULAN
						-- ambil bulan sekarang
						case 
							-- jika bulan sekarang 2 digit, abaikan. jika hanya 1 digit tambah '0' di depan
							when (EXTRACT(month FROM CURRENT_DATE) > 9) then
								EXTRACT(month FROM CURRENT_DATE)::text
							else 
								coalesce(0::text, '') || coalesce( EXTRACT(month FROM CURRENT_DATE)::text , '')
							end
						|| '-' ||
						-- TAHUN 
						-- ambil tahun sekarang
						EXTRACT(year FROM CURRENT_DATE)::text
					, 'DD-MM-YYYY'
					)::timestamp without time zone
				-- ambil dengan data anggaran version terbaru
				and d.version = (
					select distinct e.version from expenses e
					where
						e.user_id = a.user_id
					order by e.version DESC
					limit 1
				)
				and d.user_id = a.user_id
			group by b.image, b.title, d.anggaran_perbulan
			order by sum(a.transaction_amount) DESC
		) z
		) y
		order by y.jml_transaksi DESC
		limit 3
		");

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
		$query_next_transaction_amount = date('N', strtotime($cutoff_check_next)) == 6 ? date('Y-m-d', strtotime('-1 day', $month_next)) : (date('N', strtotime($cutoff_check_next)) == 7 ? date('Y-m-d', strtotime('-2 day', $month_next)) : $cutoff_check_next) ;
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
			$bankStatements = $this->bankStatement->findAllByJoin([
				'user_expense_ids' => array_keys($user_expenses),
				'client_id' => $user_id,
				// 'period' => $period,
				'transaction_type' => 'debit',
				'period-last' => $query_last_transaction_amount,
				'period-next' => $query_next_transaction_amount
			]);
			
			if(!is_null($bankStatements)){
				foreach($bankStatements as $bankStatement){
					$user_expenses[$bankStatement->user_expense_id]['data'][] = [
						'id' => $bankStatement->id,
						'name' => $bankStatement->transaction_description,
						'bank_name' => $bankStatement->title,
						'bank_code' => $bankStatement->slug,
						'ibank_uid' => $bankStatement->ibank_uid,
						'value' => (int) $bankStatement->transaction_amount,
						'created_at' => $bankStatement->transaction_date
					];
					$budget_allocated = $user_expenses[$bankStatement->user_expense_id]['anggaran'];
					$budget_remaining = $user_expenses[$bankStatement->user_expense_id]['sisa_anggaran'];
					$budget_spent = $user_expenses[$bankStatement->user_expense_id]['pengeluaran'];
					$calc_remaining_budget = $budget_remaining - $bankStatement->transaction_amount;
					$user_expenses[$bankStatement->user_expense_id]['jml_transaksi'] = count($user_expenses[$bankStatement->user_expense_id]['data']);
					$jml_transaksi=count($user_expenses[$bankStatement->user_expense_id]['data']);
					$user_expenses[$bankStatement->user_expense_id]['sisa_anggaran'] = (int) $calc_remaining_budget;
					$calc_spent_budget = $budget_spent + $bankStatement->transaction_amount;
					$user_expenses[$bankStatement->user_expense_id]['pengeluaran'] = (int) $calc_spent_budget;
					$percent = $budget_allocated < 0 && $jml_transaksi >= 1 ? 100 : ($budget_allocated == 0 ? 100 : ($calc_spent_budget / $budget_allocated) * 100);
					$user_expenses[$bankStatement->user_expense_id]['percent'] = round($percent,2)  >= 100 ? 100 : round($percent,2);
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


	/**
	 |
	 | Gugun Dwi Permana
	 | Function ini digunakan untuk mengambil data persentasi pengeluaran terhadap anggaran berdasarkan periode.
	 |
	 */

	public function percentageRencanaBulanan(Request $request)
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

		$data = \DB::select("
		select 
			z.pengeluaran, 
			z.anggaran,
			z.anggaran - z.pengeluaran sisa,
			case when z.anggaran < 1 then 0
			when (((z.anggaran - z.pengeluaran) / z.anggaran) * 100 ) < 0 then 0
			else
				((z.anggaran - z.pengeluaran) / z.anggaran) * 100 
			end::numeric::integer as percent
		FROM
		(
			select 
				CASE WHEN sum(a.transaction_amount) IS NULL THEN 0 ELSE sum(a.transaction_amount) END pengeluaran,
				(
					(
					select CASE WHEN sum(a.anggaran_perbulan) IS NULL THEN 0 ELSE sum(a.anggaran_perbulan) END 
					from expenses a
					where
						a.user_id = '".$user_id."'
						and a.version = (
							select e.version from active_version e 
							where e.user_id = '".$user_id."' and key = 'financialCheckup_cashflowAnalysis'
						)
					) +
					(
					select CASE WHEN sum(a.cicilan_perbulan) IS NULL THEN 0 ELSE sum(a.cicilan_perbulan) END 
					from debt_repayments a
					where
						a.user_id = '".$user_id."'
						and a.version = (
							select e.version from active_version e 
							where e.user_id = '".$user_id."' and key = 'financialCheckup_cashflowAnalysis'
						)
					) +
					(
					select CASE WHEN sum(a.cicilan_perbulan) IS NULL THEN 0 ELSE sum(a.cicilan_perbulan) END 
					from asset_repayments a
					where
						a.user_id = '".$user_id."'
						and a.version = (
							select e.version from active_version e 
							where e.user_id = '".$user_id."' and key = 'financialCheckup_cashflowAnalysis'
						)
					) +
					(
					select CASE WHEN sum(a.premi_perbulan) IS NULL THEN 0 ELSE sum(a.premi_perbulan) END 
					from insurances a
					where
						a.user_id = '".$user_id."'
						and a.version = (
							select e.version from active_version e 
							where e.user_id = '".$user_id."' and key = 'financialCheckup_cashflowAnalysis'
						)
					)
				) anggaran
			from 
				bank_statements a INNER JOIN users c ON a.user_id = c.id
				LEFT JOIN taxonomies b ON a.user_expense_id = b.id
			where
				a.user_id = '".$user_id."'
				and a.transaction_type = 'debit'
				and a.user_expense_id is not null
				and a.user_expense_id not in (303, 65, 330)
				and a.transaction_date between '".$start."' and '".$end."'
		) z
		");

		return response()->json([
			'result' => 'success',
			'data' => $data
		]);
	}


	/**
	 | END
	 | - Top 3
	 | - Persentasi pengeluaran perbulan
	 | ----------------------------------------
	 */







	public function cutoffDateClient(Request $request){
		//request payload
		$client_id = $request->input('client_id');
		$today = Carbon::now()->format('Y-m-d');
		$period = Carbon::now()->format('Y-m');
		
		//query repository
		$cutoff = $this->user->find($client_id);

		// var_dump($cutoff['cutoff_date']);
		// exit();

		$no_payload_cutoffdate = !empty($cutoff['cutoff_date']) && !is_null($cutoff['cutoff_date']) && $cutoff['cutoff_date'] != 0 ? $cutoff['cutoff_date'] : date("t", strtotime($today));

		//cutoff date from query
		$cutoff_date_last = $no_payload_cutoffdate < 10 ? '0'.$no_payload_cutoffdate : $no_payload_cutoffdate;
		$cutoff_date_next = $period. '-'. $no_payload_cutoffdate;

		//format cutoff for last,now,next
		$cutoff_last = date("Y-m-$cutoff_date_last", strtotime("-1 month"));
		$cutoff_now = date("Y-m-$cutoff_date_last", strtotime($cutoff_date_next));
		$cutoff_next = date("Y-m-$cutoff_date_last", strtotime("+1 month"));

		//checkout condition if cutoff date morethan today will change next month
		$cutoff_check_last = $today >= "$period-$cutoff_date_last" && $today <= date("Y-m-t", strtotime($cutoff_now)) ? $cutoff_now : $cutoff_last;
		$cutoff_check_next = $today >= "$period-$cutoff_date_last" && $today <= date("Y-m-t", strtotime($cutoff_now)) ? $cutoff_next : $cutoff_now;

		return response()->json([
			'result' => 'success',
			'cutoff_date' => $no_payload_cutoffdate,
			'cutoff_last' => $cutoff_check_last,
			'cutoff_next' => $cutoff_check_next
		])->setStatusCode(200);
	}

	public function cutoffDateClientUpdate(Request $request){
		$input = $request->all();

		$user = User::find($input['id']);

		if(!empty($user) || !is_null($user)){
			$user->update($input);
		}

		return response()->json([
			'result' => !empty($user) || !is_null($user) ? 'success' : 'error',
			'data' => !empty($user) || !is_null($user) ? $user : null
		])->setStatusCode(200);
	}

	public function bankAccountsDetail(Request $request)
	{
		$input = $request->all();

		$saldo_akhir = $this->bankStatement->getLastTransactionBalance([
			'period' => $input['period'],
			'client_id' => $input['client_id'],
			'bank-account-id' => $input['id']
		]);

		$pendapatan = $this->bankStatement->getTransactionAmount([
			'period-monitor' => $input['period'],
			'client_id' => $input['client_id'],
			'transaction_type' => 'credit',
			'bank-account-id' => $input['id']
		]);

		$pengeluaran = $this->bankStatement->getTransactionAmount([
			'period-monitor' => $input['period'],
			'client_id' => $input['client_id'],
			'transaction_type' => 'debit',
			'bank-account-id' => $input['id']
		]);

		$bank_statement = $this->bankStatement->findAllBy([
			'bank-account-id' => $input['id'],
			'period' => $input['period'],
			'client_id' => $input['client_id']
		]);

		$bank_account_detail = $this->bankAccount->findJoin($input['id']);
		$text = $bank_account_detail['ibank_uid'];
		$cencors_length = strlen($text) - 3;
    	$cencors = str_repeat('*', $cencors_length);
		return response()->json([
			'result' => 'success',
			'period' => $input['period'],
			'bank_name' => $bank_account_detail['title'],
			'slug' => $bank_account_detail['slug'],
			'saldo' => !empty($saldo_akhir[0])? (int) $saldo_akhir[0]['transaction_balance']:0,
			'pendapatan' => (int) $pendapatan,
			'pengeluaran' => (int) $pengeluaran,
			'ibank_uid' => substr_replace($text, $cencors ,0, $cencors_length),
			'status' => $bank_account_detail['status'],
			'featured_image' => $bank_account_detail['image_color'],
			'data' => $bank_statement
		]);
	}

	public function splitExpenseCategoryStore(Request $request)
	{
		$input = $request->all();

		$bankStatementDetail = $this->bankStatement->find($input['bank_statement_id']);

		$bankStatementSplitExpenseCategory = BankStatementSplitExpenseCategory::where('bank_statement_id', $input['bank_statement_id'])->get();

		$amount_split = 0;
		foreach($bankStatementSplitExpenseCategory as $split_category)
		{
			$amount_split += $split_category['amount'];
		}

		$amount = 0;		
		foreach($input['category'] as $category){
			$wallet_id = Taxonomy::where('id', $category['user_expense_id'])->get();

			if($category['amount'] != 0){
				$nested_data['bank_statement_id'] = $bankStatementDetail['id'];
				$nested_data['user_expense_id'] = $category['user_expense_id'];
				$nested_data['description'] = $bankStatementDetail['transaction_description'];
				$nested_data['amount'] = $category['amount'];
				$nested_data['user_id'] = $bankStatementDetail['user_id'];
				$nested_data['bank_account_id'] = $bankStatementDetail['bank_account_id'];
				$amount += $category['amount'];

				$data[] = $nested_data;
			}
		}

		$amount_category = $amount_split == 0 ? $amount : $amount_split + $amount;
		$amount_remaining = $bankStatementDetail['transaction_amount'] - $amount_category;

		//kondisi untuk data bankstatementdetal, amount_remaining = 0 dan bank statement yang statusnya sudah terkategori
		if(!$bankStatementDetail || $amount_remaining < 0 || $bankStatementDetail['is_categorized'] == 1 || $bankStatementDetail['user_expense_id'] != null || !isset($data)){
			return response()->json([
				'result' => 'error',
				'data' => ''
			]);
		}

		//mengubah status kategori setelah amount_remaining = 0
		if($amount_remaining < 1){
			$this->bankStatementSplitExpenseCategory->updateBankStatement($bankStatementDetail['id'], [
				'is_categorized' => 1
			]);
		}else{
			$this->bankStatementSplitExpenseCategory->updateBankStatement($bankStatementDetail['id'], [
				'is_categorized' => 0
			]);
		}

		$insert_split = $this->bankStatementSplitExpenseCategory->create($data);

		return response()->json([
			'result' => 'success',
			'amount_allocated' => $bankStatementDetail['transaction_amount'],
			'amount_remaining' => $amount_remaining,
			'data' => $insert_split
		]);
	}

	public function splitExpenseCategoryUpdate(Request $request)
	{
		$input = $request->all();

		$split = $this->bankStatementSplitExpenseCategory->find($input['split_id']);
		$split_result = is_null($split) ? $input['split_id'] : $split['bank_statement_id'];
		$bank_statement = $this->bankStatement->find($split_result);

		$amount = 0;
		foreach($input['category'] as $category){
			$wallet_id = Taxonomy::where('id', $category['user_expense_id'])->get();

			if(!empty($wallet_id[0]) && $category['amount'] != 0){
				$nested_data['bank_statement_id'] = $bank_statement['id'];
				$nested_data['user_expense_id'] = $category['user_expense_id'];
				$nested_data['description'] = $bank_statement['transaction_description'];
				$nested_data['amount'] = $category['amount'];
				$nested_data['user_id'] = $bank_statement['user_id'];
				$nested_data['bank_account_id'] = $bank_statement['bank_account_id'];
				$amount += $category['amount'];
				$data[] = $nested_data;
			}
		}

		$amount_bank_statement = is_null($split) ? $bank_statement['transaction_amount'] : $split['amount'];

		$split_amount_remaining = $amount_bank_statement - $amount;

		//kondisi untuk data bankstatementdetal, amount_remaining = 0 dan bank statement yang statusnya sudah terkategori
		if($split_amount_remaining < 0 || !isset($data)){
			return response()->json([
				'result' => 'error',
				'data' => ''
			]);
		}

		if(is_null($split)){
			$this->bankStatementSplitExpenseCategory->updateUserExpense($bank_statement['id'], [
				'user_expense_id' => null
			]);
		}

		//insert new data split
		$insert_split = $this->bankStatementSplitExpenseCategory->create($data);

		//delete last id for new data
		if(!is_null($split)){
			$this->bankStatementSplitExpenseCategory->delete($input['split_id']);
		}

		$bankStatementSplitExpenseCategory = BankStatementSplitExpenseCategory::where('bank_statement_id', $bank_statement['id'])->get();

		$amount_split = 0;
		foreach($bankStatementSplitExpenseCategory as $split_category)
		{
			$amount_split += $split_category['amount'];
		}

		//amount_remaining from table bank_statement and bank_statement split expense categories
		$amount_remaining = $bank_statement['transaction_amount'] - $amount_split;

		//mengubah status kategori setelah amount_remaining = 0
		if($amount_remaining < 1){
			$this->bankStatementSplitExpenseCategory->updateBankStatement($bank_statement['id'], [
				'is_categorized' => 1
			]);
		}else{
			$this->bankStatementSplitExpenseCategory->updateBankStatement($bank_statement['id'], [
				'is_categorized' => 0
			]);
		}

		return response()->json([
			'result' => 'success',
			'last_amount' => $split['amount'],
			'amount_remaining' => $amount_remaining,
			'data' => $insert_split
		]);
	}

	public function splitExpenseCategoryReset(Request $request)
	{
		$input = $request->all();

		//check split table and bank_statement
		$split = $this->bankStatementSplitExpenseCategory->find($input['split_id']);
		$bank_statement = $this->bankStatement->find($split['bank_statement_id']);

		//check split id
		if(!$split){
			return response()->json([
				'result' => 'error',
				'data' => ''
			]);
		}

		//update bank_statement for is_categorized 0=uncategorized 1=is_categorized
		$this->bankStatementSplitExpenseCategory->updateBankStatement($bank_statement['id'], [
			'is_categorized' => 0
		]);

		//delete last id for new data
		$this->bankStatementSplitExpenseCategory->delete($input['split_id']);

		return response()->json([
			'result' => 'success',
			'data' => ''
		]);
	}

	//set category v2
	public function expensesSetCategoryV2(Request $request){
		$input = $request->all();
		$user_id = Auth::user()->id;

		$bankStatementDetail = $this->bankStatement->find($input['bank_statement_id']);

		//kondisi untuk data bankstatementdetal, amount_remaining = 0 dan bank statement yang statusnya sudah terkategori
		if(!$bankStatementDetail || $bankStatementDetail['is_categorized'] == 1 || $bankStatementDetail['user_expense_id'] != null){
			return response()->json([
				'result' => 'error',
				'data' => ''
			]);
		}

		$insert_category_split = BankStatementSplitExpenseCategory::create([
			'bank_statement_id' => $bankStatementDetail['id'],
			'user_expense_id' => $input['user_expense_id'],
			'description' => $bankStatementDetail['transaction_description'],
			'amount' => $bankStatementDetail['transaction_amount'],
			'user_id' => $bankStatementDetail['user_id'],
			'bank_account_id' => $bankStatementDetail['bank_account_id'],
			'created_at' => Carbon::now(),
			'created_by' => $user_id,
			'updated_at' => Carbon::now(),
			'updated_by' => $user_id,
			'record_flag' => 'N'
		]);
		DB::commit();

		$this->bankStatementSplitExpenseCategory->updateBankStatement($bankStatementDetail['id'], [
			'is_categorized' => 1
		]);
		
		return response()->json([
			'result' => 'success',
			'amount_allocated' => '',
			'amount_remaining' => '',
			'data' => $insert_category_split
		])->setStatusCode(200);	
	}

}
