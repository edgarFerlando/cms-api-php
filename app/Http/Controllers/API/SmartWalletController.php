<?php 

namespace App\Http\Controllers\API;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

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

use App\Repositories\BankAccount\BankAccountRepository as BankAccount;
use Carbon\Carbon;
use Auth;
use App\Models\ActiveVersionDetail;
use App\Repositories\SmartWallet\SmartWalletInterface;

class SmartWalletController extends Controller {

	protected $cfpSchedule;
	protected $user;
	protected $smartWalet;

    public function __construct(UserInterface $user, SmartWalletInterface $smartWalet) {
		$this->user = $user;
		$this->smartWallet = $smartWalet;
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
	public function destroy(){
		try {
			
        } catch (ValidationException $e) {
            return response()->json([
					'result' => 'error',
					'alerts' => $e->getErrors()
			]);
        }		
	}
}
