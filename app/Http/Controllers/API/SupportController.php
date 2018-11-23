<?php namespace App\Http\Controllers\API;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\Config;
use App\Models\Masukan;
use App\User;

class SupportController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */

	public function __construct(Masukan $masukan, Config $config)
	{
		$this->masukan = $masukan;
		$this->config = $config;
	}

	public function index()
	{
		
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

	// codingan Achmad Andriansyah untuk membuat support application
	public function support(Request $request)
	{
		$filter_group_support = $request->only('group');

		$input = $request->all();
		
		if($input['group'] == 'rate_inflation') {

			$app_support = $this->config->where('key', 'rate_inflation_v2')->first();	

		} else {
			
			$app_support = $this->config->where('group', $filter_group_support)->get();

		}

		return response()->json([
			'result' => 'success',
			'data' => !empty($app_support) ? $app_support : null
		]);
	}

	//coding Achmad Andriansyah
	public function masukan(Request $request)
	{
		$input = $request->all();

		$email_validation = User::where('email',$input['email'])->first();

		if(!is_null($email_validation)){
			$create_masukan = $this->masukan->create([
				'user_id' => $email_validation['id'],
				'email' => isset($input['email']) ? $input['email'] : '-',
				'name' => isset($input['name']) ? $input['name'] : '-',
				'description' => isset($input['description']) ? $input['description'] : '-'
			]);
		}

		return response()->json([
			'result' => is_null($email_validation) ? 'error' : 'success',
			'data' => is_null($email_validation) ? null : $create_masukan
		]);
	}

}
