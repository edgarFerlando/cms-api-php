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
use App\Repositories\WalletTransaction\WalletTransactionRepository as WalletTransaction;
use Carbon\Carbon;
use Auth;
use App\Models\ActiveVersionDetail;

class WalletTransactionController extends Controller {

	protected $cfpSchedule;
	protected $user;

    public function __construct(WalletTransaction $walletTransaction, UserInterface $user) {
        //$this->cfpSchedule = $cfpSchedule;
        $this->user = $user;
        $this->walletTransaction = $walletTransaction;
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
		$taxo_wallet_module_title = $request->input('taxo_wallet_module_title');
        $detail_id = $request->input('detail_id');
        $note = $request->input('note');
        $transaction_date = trim($request->input('transaction_date')) == ''?'':Carbon::parse($request->input('transaction_date'))->format('Y-m-d');
        $category_type_id = $request->input('category_type_id'); //dd($request->input('amount'));
        $amount = trim($request->input('amount')) == '' || $request->input['amount'] === 0?'':unformat_money($request->input('amount'));
    
		$user_id = $request->input('user_id');
		try {
			$data = $this->walletTransaction->create([
				'user_id' => $user_id,
				'taxo_wallet_module_title' => $taxo_wallet_module_title,
				'detail_id' => $detail_id,
	            'amount' => $amount,
	            'note' => $note,
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

	public function update(Request $request)
	{ 
		$id = $request->input('id');
		$transaction_type_id = $request->input('transaction_type_id');
		$taxo_wallet_module_title = $request->input('taxo_wallet_module_title');
        $detail_id = $request->input('detail_id');
        $note = $request->input('note');
        $transaction_date = trim($request->input('transaction_date')) == ''?'':Carbon::parse($request->input('transaction_date'))->format('Y-m-d');
        $category_type_id = $request->input('category_type_id'); //dd($request->input('amount'));
        $amount = trim($request->input('amount')) == '' || $request->input['amount'] === 0?'':unformat_money($request->input('amount'));
		$user_id = $request->input('user_id');
		try {
			$transaction = $this->walletTransaction->find($id);
			if(!is_null($transaction)){
				$data = $this->walletTransaction->update($id, [
					'user_id' => $user_id,
					'taxo_wallet_module_title' => $taxo_wallet_module_title,
					'detail_id' => $detail_id,
					'amount' => $amount,
					'note' => $note,
					'transaction_date' => $transaction_date
				]);
				return response()->json([
					'result' => 'success',
					'data' => $data
				]);
			}else{
				return response()->json([
					'result' => 'error',
					'alerts' => 'data not found'
				]);
			}
			
        } catch (ValidationException $e) {
            return response()->json([
					'result' => 'error',
					'alerts' => $e->getErrors()
			]);
        }
	}

	public function balance(Request $request){
		$data = $this->walletTransaction->balance($request);
        return response()->json([
			'result' => 'success',
			'data' => $data
		]);
	}

	public function transactions(Request $request){
		$input = $request->all();

		$maxApprovedPlanAnalysisVersion = ActiveVersionDetail::where('active_version_key', 'financialCheckup_cashflowAnalysis')
            ->where('user_id', $input['user_id'])
            ->where('status', 'approved')->max('version');
		
		$input['record_flag_is_not'] = 'D';
		$input['cashflow_analysis_version'] = $maxApprovedPlanAnalysisVersion;
		$data = $this->walletTransaction->allBy($input);
        return response()->json([
			'result' => 'success',
			'data' => $data
		]);
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

	public function update_junk(Request $request)
	{ 
		$transaction_type_id = $request->input('transaction_type_id');
		//$amount = unformat_money($request->input('amount'));
        $category_id = $request->input('category_id');
        
        $notes = $request->input('notes');

        $transaction_date = trim($request->input('transaction_date')) == ''?'':Carbon::parse($request->input('transaction_date'))->format('Y-m-d');
        $category_type_id = $request->input('category_type_id'); //dd($request->input('amount'));
        $amount = trim($request->input('amount')) == '' || $request->input['amount'] === 0?'':unformat_money($request->input('amount'));
       //dd($amount);
		$user_id = $request->input('user_id');
		try {
			$data = $this->walletTransaction->update($request->input('id'), [
				'user_id' => $user_id,
				'transaction_type' => $transaction_type_id,
	            'amount' => $amount,
	            'walletTransaction_category' => $category_id,
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
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function updateJUNK()
	{

		try {
			$this->cfpSchedule->update(Input::get('id'), Input::all());
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

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy() //delete belom selesai , terakhir ngerjain tanggal 10 oct 2017
	{
		try {
			if(!Input::has('user_id'))
				return response()->json([
					'result' => 'error',
					'alerts' => [
						'id' => 'user not set'
					]
			]);

			$user_id = Auth::check()?Auth::user()->id:(Input::has('user_id')?Input::get('user_id'):'');
			$walletTransaction = $this->walletTransaction->find(Input::get('id'));
	        if($walletTransaction['id']){
	        	$walletTransaction->update([
					'deleted_by' => $user_id,
					'deleted_at' => Carbon::now(),
					'record_flag' => 'D'
				]);
	            return response()->json([
					'result' => 'success'
				]);
	        }

	        return response()->json([
					'result' => 'error',
					'alerts' => [
						'id' => 'data not found'
					]
			]);
			
        } catch (ValidationException $e) {
            return response()->json([
					'result' => 'error',
					'alerts' => $e->getErrors()
			]);
        }
		
	}

}
