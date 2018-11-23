<?php namespace App\Http\Controllers\Auth;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\Guard;
use Auth;
use Redirect;
use URL;
use Notification;
use Validator;
use App\User;
use App\Repositories\User\UserRepository as UserRepo;
use App\Models\UserMeta;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

use Config;
use Mail;
use Input;
use DB;

class AuthCustomController extends Controller {

	protected $auth;

    protected $userRepo;

	protected $redirectTo = '/';

	public function __construct(Guard $auth, UserRepo $userRepo) {
        $this->userRepo = $userRepo;
		$this->auth = $auth;
        $this->redirectTo = LangUrl(Input::exists('redirectTo')?Input::get('redirectTo'):'/');
    }

	public function logout()
	{
	    //clear session ref untuk affiliasi
	    $this->auth->logout();

		return redirect(property_exists($this, 'redirectAfterLogout') ? $this->redirectAfterLogout : '/en');
	}
	
	public function login()
	{ 	
		$transRoute = [
            'route' => 'login',
            'attrs' => []
        ];
		return view('frontend.auth.login', compact('transRoute'));
	}
	
	public function doLogin(Request $request)
	{ 
		$credentials = $request->only('email', 'password');
		Auth::validate($credentials, [
				'email' => 'required|email', 'password' => 'required',
		]);

		//$credentials['is_active'] = 1;
		if (Auth::attempt($credentials, $request->has('remember')))
		{ 
			//clear cart
			if($this->redirectTo != LangUrl(trans('routes.booking'))){
				Cart::clear();
	            PlaygroundCart::clear();
	        }

			return Redirect::to($this->redirectTo);//return redirect()->intended($this->redirectPath());
		}
		Notification::container('frontend')->error($this->getFailedLoginMessage());
		//Notification::container('frontend')->alert($this->getFailedLoginMessage());

		$args = [];
		if(Input::exists('redirectTo')){
			$args['redirectTo'] = Input::get('redirectTo');
		}

		return redirect(route(getLang().'.login', $args ))
					->withInput($request->only('email', 'remember'));
	}
	
	protected function getFailedLoginMessage()
	{
		return trans('app.credentials_do_not_match');
	}

	public function redirectPath()
	{
		if (property_exists($this, 'redirectPath'))
		{
			return $this->redirectPath;
		}

		return property_exists($this, 'redirectTo') ? $this->redirectTo : '/';
	}

	/**
	 * Get the path to the login route.
	 *
	 * @return string
	 */
	public function loginPath()
	{
		return property_exists($this, 'loginPath') ? $this->loginPath : '/login';
	}

	public function register() {
        return view('frontend.auth.register');
    }

    public function doRegister(Request $request) {
        $rules['email'] = 'required|email|unique:users';
    	$validator = Validator::make($request->all(), $rules);
		if ($validator->fails())
		{ 
			return redirect(route(getLang().'.register'))->withInput()->withErrors($validator->errors());
		}
		$data = $request->all();
		$Length = 10;
        $random_password = substr(str_shuffle(md5(time())), 0, $Length);
		$data['password'] = bcrypt($random_password);
		$user = User::create($data);
		$new_user_default_role = Config::get('holiday.new_user_default_role');
        $user->roles()->sync([$new_user_default_role]);
        //send mail
        sendEmailWithTemplate([
        	'email_template_module_id' => 1,
        	'to' => $data['email'],
        	'replace_vars' => [
        		'{email}' => $data['email'],
        		'{password}' => $random_password
        	]
        ]);
		Notification::container('frontend')->success( trans('app.msg_register') );
        return redirect(route(getLang().'.login'));
    }
    
    function myProfile(){
    	$transRoute = [
            'route' => 'myprofile',
            'attrs' => []
        ];

    	$user = User::with('userMetas')->find(Auth::user()->id);
    	$userMeta = userMeta($user->userMetas);
        //dd($userMeta);
    	return view('frontend.auth.my-profile', compact('transRoute', 'user', 'userMeta'));
    }

    function myProfileStore(Request $request){
    	$existing_metas_q = UserMeta::where('user_id', Auth::user()->id)->get();
        $post = $request->all();
	    $existing_metas = userMeta($existing_metas_q);

        switch ($post['store_type']) {
            case 'general-info':
                $meta_map = [
                    'full_name' => 'full_name',
                    'phone' => 'phone',
                    'gender' => 'gender',
                    'date_of_birth' => 'date_of_birth',
                    'country' => 'country',
                    'city' => 'city',
                    'post_code' => 'post_code',
                    'address' => 'address'
                ];

                break;
            
            case 'profile-pic':
                $meta_map = [
                    'user_thumbnail' => 'user_thumbnail',
                    'user_image' => 'user_image'
                ];
                Auth::user()->setAttribute('user_image', $post['user_image']);
                break;

            case 'id-info':
                $meta_map = [
                    'ktp_image' => 'ktp_image'
                ];
                break;

            default:
                $meta_map = [
                    'full_name' => 'full_name',
                    'phone' => 'phone',
                    'gender' => 'gender',
                    'date_of_birth' => 'date_of_birth',
                    'country' => 'country',
                    'city' => 'city',
                    'post_code' => 'post_code',
                    'address' => 'address'
                ];
                break;
        }
        
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
        	}else{

                if($meta_key == 'user_thumbnail')
                {
                    $post[$ff_name] = getImagePath($post['user_thumbnail']);
                }

                if($meta_key == 'user_image')
                {
                    $post[$ff_name] = getImagePath($post['user_image']);
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

        $user = User::find(Auth::user()->id);
        if (isset($post['full_name']))
        {
            $user->name = $post['full_name'];
            Auth::user()->setAttribute('name', $post['full_name']);//update session user name
        }
        
        $user->save();

		Notification::container('frontend')->success( trans('app.profile_added') );
            return redirect(langUrl('my-profile'));
    }

    function doActivate($activation_code){
        DB::beginTransaction();
        $user = User::where('activation_code', $activation_code)->first();
        if(!is_null($user)){
            if($user->is_active == 1)
                return view('frontend.auth.activation-already-activated');
            $user->is_active = 1;
            $user->save();
            DB::commit();
            return view('frontend.auth.activation-success');
        }else{
                return view('frontend.auth.activation-failed');
        }
    }

}
