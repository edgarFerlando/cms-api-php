<?php namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use GuzzleHttp;
use DB;
//use GuzzleHttp\Event\ErrorEvent;

class PasswordController extends Controller {

	/*
	|--------------------------------------------------------------------------
	| Password Reset Controller
	|--------------------------------------------------------------------------
	|
	| This controller is responsible for handling password reset requests
	| and uses a simple trait to include this behavior. You're free to
	| explore this trait and override any methods you wish to tweak.
	|
	*/

	use ResetsPasswords;

	protected $redirectPath = '/';
	protected $api_chat_url = '';

	/**
	 * Create a new password controller instance.
	 *
	 * @param  \Illuminate\Contracts\Auth\Guard  $auth
	 * @param  \Illuminate\Contracts\Auth\PasswordBroker  $passwords
	 * @return void
	 */
	public function __construct(Guard $auth, PasswordBroker $passwords)
	{
		$this->auth = $auth;
		$this->passwords = $passwords;
		//$this->middleware('guest');
	}

	public function getEmail()
	{	
		$transRoute = [
            'route' => 'forgotpassword',
            'attrs' => []
        ];
		return view('auth.password', compact('transRoute'));
	}

	public function getReset($token = null){ 
		if (is_null($token))
		{
			throw new NotFoundHttpException;
		}
		
		$transRoute = [
            'route' => 'reset-password/{token}',
            'attrs' => [ 'token' => $token ]
        ];
		return view('auth.reset', compact('transRoute'))->with('token', $token);
	}

	public function postReset(Request $request)
	{ 

		//$api_chat_url = config_db_cached('settings::api_chat_url');

		// $app_id = config_db_cached('settings::api_chat_app_id');
		/**
		 | ------------------------------------------------------------
		 | 01 Agustus 2018
		 |
		 | Dilakukan perubahan config_db_cached menjadi config_db
		 |
		 */
		$app_id = config_db('settings::api_chat_app_id');
		
		//$token = config_db_cached('settings::api_chat_token');
		$token = config_db('settings::api_chat_token');
		
		$this->validate($request, [
			'token' => 'required',
			'email' => 'required|email',
			'password' => 'required|confirmed',
		]);

		$credentials = $request->only(
			'email', 'password', 'password_confirmation', 'token'
		);
		DB::beginTransaction();
		$response = $this->passwords->reset($credentials, function($user, $password)
		{
			$this->api_chat_url = 'http://apps.applozic.com/rest/ws/user/set/password?userId='.$user->id.'&password='.$password;
			//dd($api_chat_url);
			$user->password = bcrypt($password);

			$user->save();

			//$this->auth->login($user);//tidak perlu di login kan 
		});
		
		
		switch ($response)
		{ 
			case PasswordBroker::PASSWORD_RESET:
				//reset third party chat vendor
				$client = new GuzzleHttp\Client();
				try { 
					$request = $client->createRequest('GET',
						$this->api_chat_url, 
						[
							'headers' => [
								'Apz-AppId' => $app_id,
								'Apz-Token' => $token
							]
						]
					);
					$response = $client->send($request);					
					//dd($response->getBody()->getContents());
					$resp = $response->json();
					if($resp['response'] == 'success'){
						DB::commit();//jika third partynya berhasil maka commit
						return view('auth.reset-success');
					}
				} catch(\Exception $e){


					if(method_exists($e, 'getResponse')){
						//$req = $e->getRequest();
						$resp = $e->getResponse(); 
						$statusCode = $resp->getStatusCode();

						return redirect()->back()->withErrors(['email' => $resp->getReasonPhrase()]);
					}else{ //di skip karena user tidak memiliki akun di third party api chat
						DB::commit();//jika third partynya berhasil maka commit
						return view('auth.reset-success');
					}
				}
			break;
			default:
				//return view('auth.reset', compact('transRoute'))->with('token', $token);
				return redirect()->back()
				->withInput($request->only('email'))
				->withErrors(['email' => trans($response)]);
			break;
		}
	}
}
