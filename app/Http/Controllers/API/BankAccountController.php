<?php 

namespace App\Http\Controllers\API;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

use Auth;
use Redirect;
use View;
use Input;
use Validator;
use Response;
use Config;
use Entrust;
use Carbon\Carbon;
use App\Exceptions\Validation\ValidationException;
use App\Repositories\BankAccount\BankAccountRepository as BankAccount;
use App\Repositories\BankStatement\BankStatementRepository as BankStatement;
use App\Models\BankStatement as BankStatementModel;

class BankAccountController extends Controller {

	protected $bank_account;

    public function __construct(BankAccount $bank_account) {
        $this->bank_account = $bank_account;
	}
	
	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index(Request $request)
	{
		$user_id = $request->input('user_id');
		$page = $request->input('page');
		$perPage = $request->input('perpage');
        $filter = $request->input('filter');

		$pagiData = $this->bank_account->paginate($page, $perPage, $filter);
		
		foreach($pagiData->items as $item){
			$nested['id'] = $item['id'];
			$nested['user_id'] = $item['user_id'];
            $nested['bank_code'] = $item['bank_code'];
            $nested['account_name'] = $item['account_name'];
			$nested['account_no'] = $item['account_no'];
			$cencors_length = strlen($item['ibank_uid']) - 3;
			$cencors = str_repeat('*', $cencors_length);
			$cencors_length_pin = strlen($item['ibank_pin']) - 3;
    		$cencors_pin = str_repeat('*', $cencors_length_pin);
            $nested['ibank_uid'] = substr_replace($item['ibank_uid'], $cencors ,0, $cencors_length);
            $nested['ibank_pin'] = substr_replace($item['ibank_pin'], $cencors_pin ,0, $cencors_length_pin);
            $nested['created_by'] = $item['created_by'];
            $nested['created_at'] = \Carbon\Carbon::parse($item->created_at)->format('Y-m-d H:i:s');
            $nested['updated_by'] = $item['updated_by'];
            $nested['updated_at'] = \Carbon\Carbon::parse($item->updated_at)->format('Y-m-d H:i:s');
            $nested['deleted_by'] = $item['deleted_by'];
            $nested['deleted_at'] = $item->deleted_at;
            $nested['record_flag'] = $item['record_flag'];
            $nested['status'] = $item['status'];
            $nested['is_first_time'] = $item['is_first_time'];
            $nested['featured_image'] = $item['featured_image'];
            $nested['image_color'] = $item['image_color'];

			$items[] = $nested;
		}
		//$pagiData->items

        $totalItems = $pagiData->totalItems;
        $ibank_accounts = new LengthAwarePaginator(empty($items) ? $pagiData->items : $items, $totalItems, $perPage, $page, [
            'path' => Paginator::resolveCurrentPath()
		]);

        $ibank_accounts->setPath("");
		$ibank_accounts->appends($filter); return $ibank_accounts;
        return response()->json([
			'result' => 'success',
			'data' => $ibank_accounts,
			'totalItems' => $totalItems
		]);
	}

	public function store(Request $request)
	{ 
		$user_id = $request->input('user_id');
		//TODO: validate that the $user_id is the same with the information stated on JWT token.

		try {
			$input = $request->all();
			$validator = Validator::make($input, [
				'ibank_uid'=> 'required|alpha_num',
				'ibank_pin'=> 'required|alpha_num'
			]);
			if ($validator->fails()) {
				return response()->json([
					'result' => 'error',
					'alert' => $validator->errors()
				]);
			}
			$update_result = $this->bank_account->create($input);
			
			if($update_result){
				return response()->json([
					'result' => 'success',
					'data' => []
				]);
			} else {
				return response()->json([
					'result' => 'error',
					'data' => []
				]);
			}
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
		$inputs = $request->all();
		try {
			$result = $this->bank_account->update($id,$inputs);
			if($result){
				return response()->json([
					'result' => 'success',
					'data' => []
				]);
			} else {
				return response()->json([
					'result' => 'error',
					'data' => []
				]);
			}	
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

	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy(Request $request){
		// $user_id = Auth::check() ? Auth::user()->id : (Input::has('client_id')?Input::get('client_id'):''); 
		$user_id = Auth::user()->id;
		$bank_account_id = $request->input('id');

		try {
			$bank_account = $this->bank_account->find($bank_account_id);
			$bank_statement = BankStatementModel::query();
			$bank_statement_account_id = $bank_statement->where('bank_account_id','=',$bank_account_id)->first();
	        if(!is_null($bank_account)){
				$bank_account->update([
					'deleted_by' => $user_id,
					'deleted_at' => Carbon::now(),
					'record_flag' => 'D'
				]);
				if($bank_statement_account_id){
					$bank_statement->update([
						'deleted_by' => $user_id,
						'deleted_at' => Carbon::now(),
						'record_flag' => 'D'
					]);
				}
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
