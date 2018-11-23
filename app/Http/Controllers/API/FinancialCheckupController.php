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

class FinancialCheckupController extends Controller {

	protected $cfpSchedule;
	protected $user;

    public function __construct(Wallet $wallet, UserInterface $user) {
        //$this->cfpSchedule = $cfpSchedule;
        $this->user = $user;
        $this->wallet = $wallet;
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

}
