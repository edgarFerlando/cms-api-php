<?php namespace App\Http\Controllers\Backend\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\Registrar;
use Illuminate\Http\Request;
use App\User;
use App\Models\UserMeta;
use Auth;
use Notification;

class AuthController extends Controller {
    
        /**
	 * The Guard implementation.
	 *
	 * @var Guard
	 */
	protected $auth;

	/**
	 * The registrar implementation.
	 *
	 * @var Registrar
	 */
	protected $registrar;

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

		//$this->middleware('guest', ['except' => 'getLogout']);
	}
        
	/**
	 * Show the application registration form.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function getRegister()
	{
		return view('backend.auth.register');
	}

	/**
	 * Handle a registration request for the application.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function postRegister(Request $request)
	{
		$validator = $this->registrar->validator($request->all());

		if ($validator->fails())
		{
			$this->throwValidationException(
				$request, $validator
			);
		}

		$this->auth->login($this->registrar->create($request->all()));

		return redirect($this->redirectPath());
	}
        
        /**
	 * Show the application login form.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function getLogin()
	{ 
		return view('backend.auth.login');
	}

	/**
	 * Handle a login request to the application.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function postLogin(Request $request)
	{
		$this->validate($request, [
			'email' => 'required|email', 'password' => 'required',
		]);
		
		$credentials = $request->only('email', 'password');
		
		$default_role_id_admin = config_db_cached('settings::default_role_id_admin');
		$default_role_id_client = config_db_cached('settings::default_role_id_client');
		$default_role_id_cfp = config_db_cached('settings::default_role_id_cfp');
		$default_role_id_marketer = config_db_cached('settings::default_role_id_marketer');
		
		//check exist dan memiliki role admin / cfp
			 
		/*$user = User::with(['roles'])
		->where('email', $credentials['email'])
		->first();
		$role_id = 0;
		if(!is_null($user)){
			$role_id = $user->roles[0]->id;
		}
		if(!in_array($role_id, [ $default_role_id_cfp, $default_role_id_admin ])){
			return redirect($this->loginPath())
				->withInput($request->only('email', 'remember'))
				->withErrors([
					'email' => 'You',
				]);
		}*/
		
		if ($this->auth->attempt($credentials, $request->has('remember')))
		{ 
			$user_role_id = auth()->user()->roles()->first()->id;
			if(!in_array($user_role_id, [ $default_role_id_cfp, $default_role_id_admin, $default_role_id_marketer ])){
				auth()->logout();
				return redirect($this->loginPath())
					->withInput($request->only('email', 'remember'))
					->withErrors([
						'login' => 'You\'re not authorized to access this page'
					]);
			}else{
				
				$redirectPath = ($user_role_id == $default_role_id_cfp)?getLang() . '/admin/taxonomy/wallet':$this->redirectPath();
				
				return redirect()->intended($redirectPath);
			}
		}

		return redirect($this->loginPath())
					->withInput($request->only('email', 'remember'))
					->withErrors([
						'login' => $this->getFailedLoginMessage(),
					]);
	}
        
        /**
	 * Get the failed login message.
	 *
	 * @return string
	 */
	protected function getFailedLoginMessage()
	{
		return 'These credentials do not match our records.';
	}

	/**
	 * Log the user out of the application.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function getLogout()
	{
		$this->auth->logout();

		return redirect(property_exists($this, 'redirectAfterLogout') ? $this->redirectAfterLogout : '/en/admin');
	}
        
        /**
	 * Get the post register / login redirect path.
	 *
	 * @return string
	 */
	public function redirectPath()
	{
		if (property_exists($this, 'redirectPath'))
		{
			return $this->redirectPath;
		}

		return property_exists($this, 'redirectTo') ? $this->redirectTo : '/en/admin/dashboard';
	}
        
        /**
	 * Get the path to the login route.
	 *
	 * @return string
	 */
	public function loginPath()
	{
		return property_exists($this, 'loginPath') ? $this->loginPath : '/en/admin';
	}

	//profile
	public function myProfile(){
    	$user = User::with('userMetas')->find(Auth::user()->id);
    	//dd($user);
    	$userMeta = userMeta($user->userMetas);
    	//dd($userRole);
    	return view('backend.auth.my-profile', compact('user', 'userMeta'));
    }

    function myProfileStore(Request $request){
    	$existing_metas_q = UserMeta::where('user_id', Auth::user()->id)->get();
    	/*$existing_metas = [];
	    foreach($existing_metas_q as $existing_meta){
	      $existing_metas[$existing_meta->meta_key] = $existing_meta->meta_value;
	    }*/

	    $existing_metas = userMeta($existing_metas_q);
    	$meta_map = [
            'full_name' => 'full_name',
            'first_name' => 'first_name',
            'last_name' => 'last_name',
            'phone' => 'phone',
            'gender' => 'gender',
            'date_of_birth' => 'date_of_birth',
            /*'country' => 'country',
            'city' => 'city',
            'post_code' => 'post_code',*/
            'address' => 'address'
            /*'user_thumbnail' => 'user_thumbnail',
            'user_image' => 'user_image',
            'ktp_image' => 'ktp_image'*/
        ];

        $post = $request->all();
        foreach($meta_map as $ff_name => $meta_key){
        	if(isset($existing_metas->$meta_key)){

        		if($meta_key == 'user_thumbnail')
        		{
        			$post[$ff_name] = getImagePath($post['user_thumbnail']);
        			//dd($post[$ff_name]);
        			if($meta_key == 'user_thumbnail' && $post[$ff_name] == '')
	        		{
	        			$post[$ff_name] = $post['old_user_thumbnail'];
	        			//dd($post[$ff_name]);
	        		}
        		}

        		if($meta_key == 'user_image')
        		{
        			$post[$ff_name] = getImagePath($post['user_image']);
        			//dd($post[$ff_name]);
        			if($meta_key == 'user_image' && $post[$ff_name] == '')
	        		{
	        			$post[$ff_name] = $post['old_user_image'];
	        			//dd($post[$ff_name]);
	        		}
        		}

        		if($meta_key == 'ktp_image')
        		{
        			$post[$ff_name] = getImagePath($post['ktp_image']);

        			if($meta_key == 'ktp_image' && $post[$ff_name] == '')
	        		{
	        			$post[$ff_name] = $post['old_ktp_image'];
	        		}
        		}

        		UserMeta::where('user_id', Auth::user()->id)
        		->where('meta_key', $meta_key)
        		->update(['meta_value' => $post[$ff_name]]);

        		//if($meta_key == 'full_name')
        		//	User::where('id', Auth::user()->id)
	        	//	->update(['name' => $post[$ff_name]]);
        	}else{

        		if($meta_key == 'user_thumbnail')
        		{
        			$post[$ff_name] = getImagePath($post['user_thumbnail']);
        		}

        		if($meta_key == 'user_image')
        		{
        			$post[$ff_name] = getImagePath($post['user_image']);
        			//dd($post[$ff_name]);
        		}

        		if($meta_key == 'ktp_image')
        		{
        			$post[$ff_name] = getImagePath($post['ktp_image']);
        		}

	            UserMeta::create([
	            	'user_id' => Auth::user()->id,
	            	'meta_key' => $meta_key,
	            	'meta_value' => $post[$ff_name] 
	            ]);
	        }
        }
        //dd(Auth::user()->name);
        //if(Auth::user()->name == ''){
        $user = User::find(Auth::user()->id);
        //$user->name = $post['full_name'];
        $user->name = $post['first_name'];
        $user->save();
        //Auth::user()->setAttribute('name', $post['full_name']);//update session user name
        Auth::user()->setAttribute('name', $post['first_name']);//update session user name
        //Auth::user()->setAttribute('user_image', $post['user_image']);
         //}

		Notification::success(trans('app.user_updated'));
        return redirect(getLang().'/admin/profile');
    }

    function getRole()
    {
    	$user = User::with(['userMetas','roles'])->find(Auth::user()->id);
    	$role = 'admin';
    	return $role;
    }

}
