<?php namespace App\Http\Controllers\Backend;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

class PaymentController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		//
		$payments = \DB::table('payment_bcava_transaction')
			->select('payment_bcava_transaction.id', 
				'users.name', 
				'users.email', 
				'payment_bcava_transaction.transaction_date', 
				'payment_bcava_transaction.transaction_expire', 
				'payment_bcava_transaction.customer_account', 
				'payment_bcava_insert.insert_status', 
				'payment_bcava_insert.insert_message', 
				'payment_bcava_insert.insert_id', 
				'payment_bcava_insert.status', 
				'payment_status.payment_status', 
				'payment_status.payment_message', 
				'payment_status.flag_type', 
				'payment_status.payment_reff_id')
			->leftJoin('payment_bcava_insert', 'payment_bcava_transaction.id', '=', 'payment_bcava_insert.transaction_id')
			->leftJoin('payment_status', 'payment_bcava_transaction.id', '=', 'payment_status.transaction_id')
			->leftJoin('users', 'payment_bcava_transaction.user_id', '=', 'users.id')
			->distinct()
			->orderByRaw('payment_bcava_transaction.id DESC')
			->get();

// 		dd($payments);

		return view('backend.payment.index', compact('payments'));

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

}
