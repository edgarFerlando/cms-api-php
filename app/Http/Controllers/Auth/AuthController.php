<?php namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\Registrar;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use Illuminate\Http\Request;

use Auth;

// use Socialize;
// use App\User;

class AuthController extends Controller {

	
	/**
	 * Create a new authentication controller instance.
	 *
	 * @param  \Illuminate\Contracts\Auth\Guard  $auth
	 * @param  \Illuminate\Contracts\Auth\Registrar  $registrar
	 * @return void
	 */
	public function __construct(Guard $auth, Registrar $registrar)
	{
		$this->auth = $auth;
		$this->registrar = $registrar;

		$this->middleware('guest', ['except' => 'getLogout']);
	}
        
    public function getRegister()
	{
		return view('auth.register');
	}

	public function getLogout()
	{
		$this->auth->logout();

		return redirect(property_exists($this, 'redirectAfterLogout') ? $this->redirectAfterLogout : '/en');
	}


	public function login()
	{ 
		return view('frontend.auth.login');
	}
	
	public function doLogin(Request $request)
	{ 
		$credentials = $request->only('email', 'password');
		Auth::validate($credentials, [
				'email' => 'required|email', 'password' => 'required',
		]);
			
		if (Auth::attempt($credentials))
		{
			return response()->json([
					'action' => 'redirect',
					'url' => url('/')
			]);
		}
		
		return response()->json([
				'action' => 'none',
				//'url' => URL::route('auth.login'),
				'alerts' => [
						$this->getFailedLoginMessage()
				]
		]);
	}





/*

	public function redirectToProvider()
	{
	    return Socialize::with('facebook')->redirect();
	}

	public function handleProviderCallback()
	{
	    $user = Socialize::with('facebook')->user();

	    // return $user; //->token;
		$email = $user->getEmail();


	    $alert_text = 'Login gagal';
		$user = User::whereRaw('LOWER(email) = ?', [ strtolower($email)])
		->where('record_flag','!=', 'D')
		->whereHas('role_user', function($q){
			$q->whereHas('role', function($q2){
				$q2->where('name', 'client');
			});
		})
		->with(['cfpClient.cfp.userMetas', 'userMetas'])
		->first();
	
		if(!is_null($user) && $user->is_active != 1){
			$alert_text = 'Mohon cek email dan aktifkan akun Anda terlebih dahulu';
			$user = NULL;
		}


		$credentials['record_flag'] = '';
		if (!is_null($user) && Auth::attempt($credentials))
		{	

			//save firebase token
			if(!is_null($request->input('firebase_token'))){
			//if(isset(Input::get('firebase_token'))){
				$user->firebase_token = $request->input('firebase_token');
				$user->save();
			}

			$userMeta = (array)userMeta($user->userMetas);
			if(isset($userMeta['branch'])){ 
				$userMeta['branch_name'] = Taxonomy::where('id', $userMeta['branch'])->pluck('title');
			}
			
			//check already submit goals and grade
			$goal_grade_count = $this->goalGrade->where(['user_id' => $user->id])->count();
			$grades = $this->grade->select('id', 'grade_name', 'ages', 'thumb_path', 'button_label')->with('goals')->get();
			$goals = $this->goal->select('id', 'goal_name', 'icon_path', 'thumb_path', 'position_under_grade_id')->get();

			$user_raw = $user->toArray();
			$user_raw = array_merge($user_raw, $userMeta);
			$user_raw['cfp'] = null;
			if(!is_null($user->cfpClient)){
				$userMetaCFP = (array)userMeta($user->cfpClient->cfp->userMetas, [ 'photo' => ['type' => 'image'] ]); //var_dump($userMetaCFP); exit;
				$user_raw['cfp'] = $user_raw['cfp_client']['cfp'];
				$user_raw['cfp'] = array_merge($user_raw['cfp'], $userMetaCFP);
			}

			$global_settings = [
				'client_max_number_notifications' => config_db_cached('settings::client_max_number_notifications')
			];

			//token JWT
			$user_raw['jwt_token'] = $this->user->getJWTToken($credentials_jwt);

			unset($user_raw['cfp_client']);
			unset($user_raw['user_metas']);
			unset($user_raw['cfp']['user_metas']);
			return response()->json([
					'result' => 'success',
					'alerts' => '', 
					'data' => $user_raw,
					'grades' => $grades,
					'goals' => $goals,
					'goal_grade' => $goal_grade_count > 0 ? true:false,
					'global_settings' => $global_settings
			]);
		}
		
		return response()->json([
				'result' => 'error',
				'alerts' => $alert_text,
				'data' => null
		]);


	}
*/

}
