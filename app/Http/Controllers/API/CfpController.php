<?php namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use Auth;
use App\Repositories\User\UserInterface;
use Response;
use App\User;
use Entrust;

class CfpController extends Controller {
	protected $user;

    public function __construct(UserInterface $user) {
		$this->user = $user;
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
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
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
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update()
	{
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy()
	{
		
	}

	public function doLogin(Request $request)
	{ 
		$alerts = [];
		$email = $request->input('email');
		$password = base64_decode($request->input('password'));// jadi isinya $request->input('password') adalah misal password di masukin ke base64_decode

		//jwt credential
		$credentials_jwt = new Request;
		$credentials_jwt->request->add(['request_from' => 'doLogin', 'email' => $request->input('email'), 'password' => $request->input('password')]);

		$request->request->add(['password' => $password]);
		//dd($password);
		$credentials = $request->only('email', 'password');
		Auth::validate($credentials, [
				'email' => 'required|email', 'password' => 'required',
		]);

		$user = User::whereRaw('LOWER(email) = ?', [ strtolower($email)])
		->whereHas('role_user', function($q){
			$q->whereHas('role', function($q2){
				$q2->where('name', 'certified_financial_planner');
			});
		})
		->with(['userMetas'])
		->first();
			
		if (!is_null($user) && Auth::attempt($credentials) && $user['is_active'] == 1)
		{	
			//save firebase token
			if(!is_null($request->input('firebase_token'))){
				$user->firebase_token = $request->input('firebase_token');
				$user->save();
			}
			$userMeta = (array)userMeta($user->userMetas);

			$user_raw = $user->toArray();
			$user_raw = array_merge($user_raw, $userMeta);

			$global_settings = [
				'cfp_max_number_notifications' => config_db_cached('settings::cfp_max_number_notifications')
			];

			//token JWT
			$user_raw['jwt_token'] = $this->user->getJWTToken($credentials_jwt);

			unset($user_raw['user_metas']);
			return response()->json([
					'result' => 'success',
					'data' => $user_raw,
					'global_settings' => $global_settings
			]);
		}
		
		return response()->json([
				'result' => 'error',
				'alerts' => isset($user) && $user['is_active'] == 0 ? 'Akun Anda tidak aktif' : 'Email atau Password Salah.',
				'data' => null
		]);
	}

	public function cfpClients(Request $request){
		$data = [];
		$data_raw = $this->user->cfpClients($request);
		if(!is_null($data_raw)){
			foreach ($data_raw->cfp_clients as $idx => $client_raw) {
				$userMeta = (array)userMeta($client_raw->userMetas);
				$data[$idx] = [
				 	'email' => $client_raw['client']['email'],
					'user_id' => $client_raw->client_id,
					'firebase_token' => $client_raw->client->firebase_token
				];
				$data[$idx] = array_merge($data[$idx], $userMeta);
			}
		}
        return response()->json([
			'result' => 'success',
			'data' => $data
		]);
	}

	public function cfpDetail(Request $request)
	{
		$input = $request->all();
		$detail = $this->user->find($input['id']);
		
		if(!$detail){
			return response()->json([
				'result' => 'error',
				'data' => ''
			]);
		}


		foreach($detail['userMetas'] as $detail_list){
			if($detail_list['meta_key'] == 'full_name')
			{
				$nested['name'] = $detail_list['meta_value'];
				$data[] = $nested;
			}
		}

		return response()->json([
			'result' => 'success',
			'data' => [
				'id' => is_null($detail->id) ? "" : $detail->id,
				'name' => is_null($data) ? "" : $data[0]['name'],
				'email' => is_null($detail->email) ? "" : $detail->email,
				'nomor_sertifikasi' => is_null($detail->certificate_no) ? "" : $detail->certificate_no,
				'description' => is_null($detail->description) ? "" : $detail->description
			]
		]);
	}

}
