<?php namespace App\Http\Controllers\API;

//use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\User;
use Response;
use Input;

use Auth;
use Validator;
use Config;
use App\Models\UserMeta;
use App\Models\CfpClient;
use App\Models\GoalGrade;
use App\Models\Grade;
use App\Models\Goal;
use App\Models\AssetRepaymentPaid;
use App\Models\AssetRepaymentPaidoff;
use App\Models\Reminder;
use Carbon\Carbon;
use App\Repositories\User\UserInterface;
use DB;
use App\Repositories\PlanAnalysis\PlanAnalysisRepository;
use App\Models\ActiveVersion;
use App\Repositories\PlanA\PlanARepository;
use App\Repositories\Reminder\ReminderRepository;
use App\Taxonomy;
use App\Models\UserCodeCounter;
use App\Exceptions\Validation\ValidationException;
use JWTAuth;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Foundation\Auth\ResetsPasswords;
use App\Models\ActiveVersionDetail;
use App\Repositories\CashflowAnalysis\CashflowAnalysisRepository;
use App\Repositories\PortfolioAnalysis\PortfolioAnalysisRepository;
use App\Models\EomBalance;
use App\Models\ConvertCash;

use App\Models\Log;


class ClientController extends Controller {

	use ResetsPasswords;
    protected $goalGrade;
    protected $grade;
    protected $goal;
    protected $planAnalysis;
    protected $planA;
	protected $reminder;
	protected $cashflowAnalysis;
	protected $portfolioAnalysis;

	protected $log;

    public function __construct(UserInterface $user, PlanAnalysisRepository $planAnalysis, PlanARepository $planA, ReminderRepository $reminder, PasswordBroker $passwords, CashflowAnalysisRepository $cashflowAnalysis, PortfolioAnalysisRepository $portfolioAnalysis, Log $log) {//, TaxonomyInterface $taxonomy) {
    	$this->goalGrade = new GoalGrade;
    	$this->goal = new Goal;
    	$this->grade = new Grade;
    	$this->user = $user;
    	$this->planAnalysis = $planAnalysis;
    	$this->planA = $planA;
		$this->reminder = $reminder;
		$this->passwords = $passwords;
		$this->cashflowAnalysis = $cashflowAnalysis;
		$this->portfolioAnalysis = $portfolioAnalysis;

		$this->log = $log;
    }

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		$users = User::with('userMetas')->paginate(5);
		return Response::json(array(
	                'error' => false,
	                'users' => $users->toArray(),
	               ),200);
	}

	public function authenticate(Request $request) {
		return $this->user->getJWTToken($request);
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
		$credentials = $request->only('email', 'password');// dd($credentials);
		Auth::validate($credentials, [
				'email' => 'required|email', 'password' => 'required',
		]);

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
	
		if(is_null($user)){
			$alert_text = 'Email Anda belum terdaftar';
			$user = NULL;
		}elseif(Auth::attempt($credentials) == false){
			$alert_text = 'Password Salah';
			$user = NULL;
		}

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

			//if($this->predefinedRemindersCheck($user->id) === false){
				//$this->predefinedRemindersStore($user->id);
			//}

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
				$position = ['position'=>'Executive Financial Planner','cfp'=>'cfp'];
				$user_raw['cfp'] = $user_raw['cfp_client']['cfp'];
				$user_raw['cfp'] = array_merge($user_raw['cfp'], $userMetaCFP);
				$user_raw['cfp'] = array_merge($user_raw['cfp'], $position);
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
					'global_settings' => $global_settings,
					'register_or_login'	=> 'login'
			]);
		}

		return response()->json([
			'result' => 'error',
			'alerts' => $alert_text,
			'data' => null
		]);
	}

	public function doRegister(Request $request) { 
		$success = [
				'email' => '',
				'password' => '',
				'name' => ''
		];

		$alerts = [];

		//$rules['email'] = 'required|email|unique:users';
		$rules['email'] = 'required|email|user_unique_soft_delete';
        $rules['name'] = 'required';
		$rules['password'] = 'required|min:8';

        $attributeNames['name'] = trans('app.first_name');

    	$validator = Validator::make($request->all(), $rules);
    	$validator->setAttributeNames($attributeNames);

		if ($validator->fails())
		{ 
			$alerts = [
				'email' => $validator->errors()->first('email'),
				'password' => $validator->errors()->first('password'),
				'name' => $validator->errors()->first('name')
			];
			return response()->json([
					'result' => 'error', 
					'alerts' => $alerts,
					'data' => ''
			]);
		}
		DB::beginTransaction();
		
		$default_role_id_client = config_db_cached('settings::default_role_id_client');

		$data = $request->all();
		$data['email'] = strtolower($data['email']);
		//dd($data);
		$password = base64_decode($request->input('password'));
		$data['password'] = bcrypt($password);

		//activation code
		$activation_code = str_random(60) . $data['email'];
		$data['activation_code'] = $activation_code;

		//dd($password);
		$user = User::create($data);
		
		$new_user_default_role = $default_role_id_client;

        $user->roles()->sync([$new_user_default_role]);

		$meta_map = [
            'name' => 'name',
            'last_name' => 'last_name',
            'phone' => 'phone',
            'reference_code' => 'reference_code'
            ];

        $data['reference_code'] = $request->has('reference_code') && trim($request->input('reference_code')) != ''?$request->input('reference_code'):'';
         
        foreach($meta_map as $ff_name => $meta_key){
            if(isset($data[$ff_name])){
            	UserMeta::create([
	                'user_id' => $user->id,
	                'meta_key' => $meta_key,
	                'meta_value' => $data[$ff_name] 
	            ]);
            }     
		}
		
		//auto assign CFP
		$id_cfp = config_db_cached('settings::default_role_id_cfp');

		$data_auto_assign = [
			'user_id' => $user['id'],
			'client_id' => $user['id'],
			// 'notes' => 'Auto assigned by system',
			// 'branch' => $attributes['branch'],
			'default_role_id_cfp' => $id_cfp,
			// 'record_flag' => 'N',
			'reference_code' => $data['reference_code']
		];

		$this->user->autoassign_cfp_register($data_auto_assign);

        sendEmailWithTemplate([
        	'email_template_module_id' => 1,//register
        	'to' => $data['email'],
        	'replace_vars' => [
                '{name}' => $data['name'],
        		'{email}' => $data['email'],
                '{activation_link}' => '<a href="'.url('user/activate/'.$activation_code).'">di sini</a>'
        	]
        ]);
		$success['activation_link'] = 'Please check your email and activate';

		//$this->predefinedRemindersStore($user->id);

        DB::commit();

        return response()->json([
				'result' => 'success',
				'alerts' => $success,
				'data' => ''
		]);
    }







    /**
	 | -------------------------------------------------------
	 | Facebook login
	 | -------------------------------------------------------
	 | Script ini di buat untuk proses login / register
	 | menggunakan Sosial media
     |
     */

    public function createOrGetUser(Request $request) { 

    	// untuk menentukan register atau login
    	$register_or_login = "login";

    	/**
		 | --------------------------------------------
		 | Proses memasukan request ke tabel LOG
		 | --------------------------------------------
    	 |
    	 */
    	
	    $input = $request->all();

	    $create_log = $this->log->create([
			'name' => 'Login/Register facebook sent',
			'description' => json_encode($input) // implode(" | ", $input)
		]);

    	/**
		 | --------------------------------------------
		 | Cek apakah user tersebut sudah terdaftar,
		 | Jika belum maka di input terlebih dahulu
		 | --------------------------------------------
    	 |
    	 */

    	$user = User::whereEmail(strtolower($request->input('email')))->first();

    	/**
		 | --------------------------------------------
		 | Register
		 | --------------------------------------------
		 | Jika user tidak ditemukan,
		 | maka akan masuk ke proses register (insert user)
		 | dimana pada proses register ini, is_active di kasih langsung '1'
		 | dan provider serta provider_user_id diisi dengan sosialmedia yang diginakan untuk login
    	 |
    	 */

    	if (!$user) {

    		$register_or_login = "register";

    		$rules['email'] = 'required|email|user_unique_soft_delete';
	        $attributeNames['name'] = trans('app.first_name');

	    	$validator = Validator::make($request->all(), $rules);
	    	$validator->setAttributeNames($attributeNames);

			if ($validator->fails())
			{ 
					
			} else {

	    		$default_role_id_client = config_db_cached('settings::default_role_id_client');

	            // $data = $request->all();

				$data['email'] = strtolower($request->input('email'));			

				$password = 'passwordAg8e011yHdLkmNcDUUihZz'; // base64_decode('fdksajhgjadafs'); //base64_decode($request->input('provider_user_id')); // base64_decode('fdksajhgjadafs'); //$request->input('password'));
				$data['password'] = bcrypt($password);
				
				$data['is_active'] = '2';
				$data['name'] = $request->input('name');

				$data['provider_user_id'] = $request->input('provider_user_id');
				$data['provider'] = $request->input('provider'); // facebook

				/** memastikan lagi kalau email yang sudah masuk tidak akan di masukan lagi... */
				$cekUserByEmail = User::where('email', '=', $data['email'])->first();
				if(!$cekUserByEmail) {

					// Proses Create user baru
					$user = User::create($data);
					

					$create_log = $this->log->create([
						'name' => 'Register facebook',
						'description' => json_encode($user) // implode(" | ", $input)
					]);


					$new_user_default_role = $default_role_id_client;
			        $user->roles()->sync([$new_user_default_role]);

			        // Proses insert untuk detail user
					$meta_map = [
			            'name' => 'name',
			            'last_name' => 'last_name',
			            'phone' => 'phone',
			            'reference_code' => 'reference_code'
			        ];

			        $data['reference_code'] = $request->has('reference_code') && trim($request->input('reference_code')) != ''?$request->input('reference_code'):'';
			         
			        foreach($meta_map as $ff_name => $meta_key){
			            if(isset($data[$ff_name])){
			            	UserMeta::create([
				                'user_id' => $user->id,
				                'meta_key' => $meta_key,
				                'meta_value' => $data[$ff_name] 
				            ]);
			            }
					}
					
					//auto assign CFP
					$id_cfp = config_db_cached('settings::default_role_id_cfp');

					$data_auto_assign = [
						'user_id' => $user['id'],
						'client_id' => $user['id'],
						// 'notes' => 'Auto assigned by system',
						// 'branch' => $attributes['branch'],
						'default_role_id_cfp' => $id_cfp,
						// 'record_flag' => 'N',
						'reference_code' => $data['reference_code']
					];

					$this->user->autoassign_cfp_register($data_auto_assign);
				}
	    	}
        }

        /**
		 | --------------------------------------------
		 | Cek apakah user ada tapi belum terconnect 
		 | Sosial media
		 | --------------------------------------------
		 |
         */

         $user = User::whereEmail(strtolower($request->input('email')))->first();

         /** 
          | ----------------------------------------------------
          | Kondisi IF di komen
          | ----------------------------------------------------
          | Karena update terakhir, setiap login user akan mendapat token yang berbeda
          | tidak seperti password / ID yang tetap.
          | jadi setiap login maka provider_user_id akan selalu di update sesuai yang terbaru
          |
          */

		// if (is_null($user->provider)) {
		 	
            $t_attributes = [
                'provider_user_id'	=> $request->input('provider_user_id'),
                'provider'			=> $request->input('provider') 				// facebook
            ];

            $user->update($t_attributes);

		// }

        /**
		 | --------------------------------------------
		 | Login
		 | --------------------------------------------
    	 |
    	 */

    	$email = strtolower($request->input('email'));
    	$user = User::whereEmail($email)->first();
    	// $password = base64_encode($user->password);

    	// jika password sama dengan provider_user_id
    	$password = $user->provider_user_id;


    	if($user->is_active == '2') {
    		$register_or_login = "register";
    	}

		// pembuatan JWT credential
		/**
		 | ---------------------------------------------------------------------------------
		 | Pembentukan JWT versi pertama, menggunakan email dan password
		 | Sekarang untuk sosialmedia menggunakan email dan token_dari_sosialmedia
		 | ---------------------------------------------------------------------------------
		 | $credentials_jwt = new Request;
		 | $credentials_jwt->request->add(['request_from' => 'doLogin', 'email' => $request->input('email'), 'password' => $password]); // 'fdksajhgjadafs']);
		 |
		 | $request->request->add(['password' => base64_decode($user->password)]);
		 |
		 | $credentials = $request->only('email', 'password');
		 | Auth::validate($credentials, [
		 | 		'email' => 'required|email', 'password' => 'required',
		 | ]);
		 | ---------------------------------------------------------------------------------
		*/

 		/**
 		 | -------------------------------
 		 | Ambil user berdasarkan email
 		 |
 		 */

		$alert_text = 'Login gagal!';
		$user = User::whereRaw('LOWER(email) = ?', [ strtolower($email)])
		->where('record_flag','!=', 'D')
		->whereHas('role_user', function($q){
			$q->whereHas('role', function($q2){
				$q2->where('name', 'client');
			});
		})
		->with(['cfpClient.cfp.userMetas', 'userMetas'])
		->first();

		/**
		 | -------------------------------
		 | Cek apakah user terdaftar
		 | dan provider_user_id yang di kirim sesuai
		 | dengan yang di database
		 |
		 */

		// dd($user->provider_user_id." - ".$request->input('provider_user_id'));

		if (!is_null($user) && $user->provider_user_id == $request->input('provider_user_id'))
		{	
			// Save firebase token
			if(!is_null($request->input('firebase_token'))){
				$user->firebase_token = $request->input('firebase_token');
				$user->save();
			}

			$userMeta = (array)userMeta($user->userMetas);
			if(isset($userMeta['branch'])){ 
				$userMeta['branch_name'] = Taxonomy::where('id', $userMeta['branch'])->pluck('title');
			}
			
			// Check already submit goals and grade
			$goal_grade_count = $this->goalGrade->where(['user_id' => $user->id])->count();
			$grades = $this->grade->select('id', 'grade_name', 'ages', 'thumb_path', 'button_label')->with('goals')->get();
			$goals = $this->goal->select('id', 'goal_name', 'icon_path', 'thumb_path', 'position_under_grade_id')->get();

			$user_raw = $user->toArray();
			$user_raw = array_merge($user_raw, $userMeta);
			$user_raw['cfp'] = null;
			if(!is_null($user->cfpClient)){	
				$userMetaCFP = (array)userMeta($user->cfpClient->cfp->userMetas, [ 'photo' => ['type' => 'image'] ]);
				$position = ['position'=>'Executive Financial Planner','cfp'=>'cfp'];
				$user_raw['cfp'] = $user_raw['cfp_client']['cfp'];
				$user_raw['cfp'] = array_merge($user_raw['cfp'], $userMetaCFP);
				$user_raw['cfp'] = array_merge($user_raw['cfp'], $position);
			}

			$global_settings = [
				'client_max_number_notifications' => config_db_cached('settings::client_max_number_notifications')
			];

			// Token JWT
			// $user_raw['jwt_token'] = $this->user->getJWTToken($credentials_jwt);

			$user = User::where('email','=',$email)->where('provider_user_id','=', $password)->first();
			$user_raw['jwt_token'] = JWTAuth::fromUser($user);

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
					'global_settings' => $global_settings,
					'register_or_login'	=> $register_or_login
			]);
		}
		
		return response()->json([
				'result' => 'error',
				'alerts' => $alert_text,
				'data' => null
		]);

    }

    /**
	 | Setelah mencentang term and condition is_active yang sebelumnya 2 menjadi 1
     */

    function setActive(Request $request){

    	$user_id = $request->input('user_id');

        $user = User::where('id', '=', $user_id)->with(['userMetas', 'roles'])->first();

        if(!is_null($user)){
			
			DB::beginTransaction();
			$user->is_active = 1;
			$user->save();
			DB::commit();

			return response()->json([
				'result' => 'success',
				'data' => null
			]);

        } else {
            return response()->json([
				'result' => 'error',
				'message' => 'User tidak ditemukan.'
			]);
        }
	}



    /**
     | ------------------------------------
	 | 	End Sosial media login
	 |
     */






    public function predefinedRemindersCheck($user_id){
    	$count = Reminder::where('user_id', $user_id)->where('is_predefined', 1)->count();
    	if($count > 0)
    		return true;
    	else
    		return false;
    }

    public function predefinedRemindersStore($user_id){
    	//create predefined reminder
		$date_now = Carbon::now()->format('Y-m-d');
		$date_now_endMonth = Carbon::now()->endOfMonth();
		$predefined_reminders = [
			// [
			// 	'user_id' => $user_id,
			// 	'module_name' => 'free',
			//     'screen_name' => 'add_wallet_transaction',
			//     'reminder_datetime' => $date_now.' 08:00:00',
			//     'is_repeated' => 'daily',
			//     'about' => 'Breakfast',
			//     'note' => '',
			//     'next_reminder_datetime' => '',
			//     'is_predefined' => 1
			// ],
			// [
			// 	'user_id' => $user_id,
			// 	'module_name' => 'free',
			//     'screen_name' => 'add_wallet_transaction',
			//     'reminder_datetime' => $date_now.' 12:00:00',
			//     'is_repeated' => 'daily',
			//     'about' => 'Lunch',
			//     'note' => '',
			//     'next_reminder_datetime' => '',
			//     'is_predefined' => 1
			// ],
			// [
			// 	'user_id' => $user_id,
			// 	'module_name' => 'free',
			//     'screen_name' => 'add_wallet_transaction',
			//     'reminder_datetime' => $date_now.' 17:00:00',
			//     'is_repeated' => 'daily',
			//     'about' => 'Dinner',
			//     'note' => '',
			//     'next_reminder_datetime' => '',
			//     'is_predefined' => 1
			// ],
			// [
			// 	'user_id' => $user_id,
			// 	'module_name' => 'free',
			//     'screen_name' => 'add_wallet_transaction_blank',//karena tidak memiliki taxo wallet id yg fix
			//     'reminder_datetime' => $date_now.' 23:59:59',
			//     'is_repeated' => 'daily',
			//     'about' => 'End of day balance',
			//     'note' => '',
			//     'next_reminder_datetime' => '',
			//     'is_predefined' => 1
			// ],
			[
				'user_id' => $user_id,
				'module_name' => 'free',
			    'screen_name' => 'add_eom',
			    'reminder_datetime' => $date_now_endMonth->format('Y-m-d').' 23:59:59',
			    'is_repeated' => 'monthly',
			    'about' => 'End of month balance',
			    'note' => '',
			    'next_reminder_datetime' => '',
			    'is_predefined' => 1
			]
		];
		$this->reminder->createBulk($predefined_reminders);
    }

	public function jsonStoreAdd(Request $request)
	{
		$password_en = base64_encode('password');
		$password = base64_decode($password_en);
		return response()->json([
				'result' => 'success',
				'alerts' => $password,
				'data' => ''
		]);
	}	
	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		
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
		$users = User::with('userMetas')->where('email', '=', $id)->get();
		return response(array(
	                'error' => false,
	                'users' => $users->toArray(),
	               ),200);
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
	public function update(Request $request)
	{
	}

	public function myAccountUpdate(Request $request)
	{
		try {
			$input = $request->all();
			$id = $input['id']; //dd($input);
			$details = $this->user->liteUpdate($id, $input);
			return response()->json([
				'result' => 'success',
				'data' => $details
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
	public function destroy($id)
	{
		//
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function goalGradeStore(Request $request)
	{ 
		$errors = [];
		$user_id = $request->input('user_id');
		$grade = $request->input('grade');
		$goals = $request->input('goals');
			
		if(!is_array($grade)){
			if(count($goals) > 0){
				foreach ($goals as $goal_id) {
					$this->goalGrade->create([
						'user_id' => $user_id,
						'grade_id' => $grade,
						'goal_id' => $goal_id,
						'created_by' => $user_id, 
						'created_at' => Carbon::now(), 
						'updated_by' => $user_id, 
						'updated_at' => Carbon::now(), 
						'record_flag' => 'N'
					]);
				}
			}else{
				$errors['goals'] = 'Must be array';
			}
		}else{
			$errors['grade'] = 'Cannot be array';
		}

		$alerts = $errors;
		if(count($alerts) > 0){
			return response()->json([
				'result' => 'error',
				'alerts' => $alerts, 
				'data' => []
			]);
		}else{
			return response()->json([
				'result' => 'success'
			]);
		}
	}

	public function getGrades()
	{
		$grades = $this->grade->all();
		return response()->json([
				'grades' => $grades
			]);
	}

	public function progressBar(Request $request){
		$user_id = $request->get('client_id');
		$params = [
		  'user_id' => $user_id,
		  'modules' => [ 'a_plans', 'b_plans']
		];
		$planAnalysis = $this->planAnalysis->showByVersion($params);
		$portfolioAnalysis_activeVersion = ActiveVersion::where('user_id', $user_id)->where('key', 'financialCheckup_portfolioAnalysis')->first();
		$portfolioAnalysis_version = is_null($portfolioAnalysis_activeVersion)?'':$portfolioAnalysis_activeVersion->version; //dd($portfolioAnalysis_version);
		$plan_counter = 0;
		$plans = [];
		$plannya_apa_aja = [];
		if(isset($planAnalysis['a_plans']) && count($planAnalysis['a_plans'])){
			foreach ($planAnalysis['a_plans'] as $item_plan_a) {
				$plan = $item_plan_a->plan_a[0];
				if($plan->status == 1){
					// ada index nol karena emang di dalam array, meskipun hanya satu
					$plan_id = $plan->id;
					$usia = $plan->umur;
					$usia_pensiun = $plan->umur_pensiun;
					$lama_tahun_investasi = $usia_pensiun-$usia;
					$lama_bulan_investasi = $lama_tahun_investasi*12;
					$bunga_investasi_pertahun = $plan['income_simulation']['bunga_investasi_pertahun'];
					$inv_slug = slugify($plan['income_simulation']['produk'], '_').'_'.$bunga_investasi_pertahun; //dd($inv_slug);
					$plan_number = $plan->plan_number;
					$activation_month = is_null($plan->detail_activation_latest)?'':carbon_parse_view($plan->detail_activation_latest->created_at, 'm'); //dd($activation_date);
					$current_month = Carbon::now()->format('m'); //dd($activation_month);
					$ongoing_investment = $current_month - $activation_month;
					$pendapatan_pensiun = $plan->pendapatan_pensiun;
					$total_asset = 0;
					$cicilan_terbayar = 0;
					$debug_assetRepayIds = [];
					if(!is_null($plan->detail_activation_latest) && !is_null($plan->detail_activation_latest->asset_repayment) && $portfolioAnalysis_version != ''){
						//dd($plan->detail_activation_latest);
						$asset_repayment_id = $plan->detail_activation_latest->asset_repayment_id;//cicilan aset yg terhubung ke plan ini
						$debug_assetRepayIds[] = $asset_repayment_id;
						//cek value yg sudah dibayarkan selama ini
						$asset_repay_paid = AssetRepaymentPaid::where('user_id', $user_id)->where('version', $portfolioAnalysis_version)->where('asset_repayment_id', $asset_repayment_id)->first();
						//dd($asset_repay_paid);
						if(!is_null($asset_repay_paid)){
							$cicilan_terbayar = $asset_repay_paid->cicilan_terbayar;
						}
						$plans[$plan_counter]['plan_activated_at'] = $plan->detail_activation_latest->created_at->format('Y-m-d H:i:s');
					}
					$total_asset = $cicilan_terbayar;
					$inflasi = config_db_cached('settings::rate_inflation');
					$umur = 
					//$lama_tahun_investasi = 30;//$ongoing_investment/12;
					//$lama_bulan_investasi = 360;//$ongoing_investment;
					$pv = $pendapatan_pensiun;
					$deposito_rate = config_db_cached('settings::rate_deposit');
					$pv_raw = $pv;
					$suffix_data_name = 'needinv';
					$need_inv_pv = $pv_raw/(($deposito_rate/100)/12);
					$res['lama_tahun_investasi'] = $lama_tahun_investasi;
					$res['lama_bulan_investasi'] = $lama_bulan_investasi;
					//echo $suffix_data_name.', '.$need_inv_pv.', '.$inflasi.', '.$lama_tahun_investasi.', '.false.'<br />';exit;
					$res += calc_inf_fv($suffix_data_name, $need_inv_pv, $inflasi, $lama_tahun_investasi, false);
					$res[$suffix_data_name]['rate_inv_'.$suffix_data_name] = $deposito_rate;
					//dd($res);
					$simulasi_inv = calc_inv_pv_getPayment2($inv_slug, $bunga_investasi_pertahun, $res);
					$inv_this_month_val = 0; //dd($activation_month);
					$fv_inv = 0;
					if($activation_month != ''){ //dd($simulasi_inv);
						$simul_idx = $ongoing_investment>0?$ongoing_investment-1:0;
						$inv_this_month = $simulasi_inv['simulasi_inv_'.$inv_slug][$simul_idx];//karena simulasinya mulai dari index nol
						//fv_inv_mutual_fund_equity_18
						$inv_this_month_val = $inv_this_month['fv_inv']; 
						$fv_inv = $simulasi_inv['fv_inv_'.$inv_slug];
					}
					$plans[$plan_counter]['plan_id'] = $plan_id;
					$plans[$plan_counter]['plan_number'] = $plan_number;
					$plans[$plan_counter]['plan_name'] = 'Plan '.$plan_number;
					$plans[$plan_counter]['plan_type'] = 'plan_a';
					$plans[$plan_counter]['running_man']['target'] = $item_plan_a->plan_a[0]->fv_kebutuhan_dana;
					$plans[$plan_counter]['running_man']['current'] = "$total_asset"; 
					$plans[$plan_counter]['grandma']['target'] = "$fv_inv";//$item_plan_a->plan_a[0]->income_simulation->total_investasi;
					$plans[$plan_counter]['grandma']['current'] = "$inv_this_month_val";
					//$plans[$plan_number] = $item_plan_a;
					//$plannya_apa_aja[] = $plan_number;
					$plan_counter++;
				}
			}
		}
		if(isset($planAnalysis['b_plans']) && count($planAnalysis['b_plans'])){
			foreach ($planAnalysis['b_plans'] as $item_plan_b) {
				$plan = $item_plan_b->plan_b[0]; //dd($plan);
				if($plan->status == 1){
					
					$plan_id = $plan->id;
					$plan_number = $plan->plan_number;
					$activation_month = is_null($plan->detail_activation_latest)?'':carbon_parse_view($plan->detail_activation_latest->created_at, 'm'); //dd($activation_date);
					//$plan_perbulan = $plan->plan_perbulan;

					$total_asset = 0;
					$cicilan_terbayar = 0;
					if(!is_null($plan->detail_activation_latest) && !is_null($plan->detail_activation_latest->asset_repayment)){
						$asset_repayment_id = $plan->detail_activation_latest->asset_repayment_id;//cicilan aset yg terhubung ke plan ini

						//cek value yg sudah dibayarkan selama ini
						$asset_repay_paid = AssetRepaymentPaid::where('user_id', $user_id)->where('asset_repayment_id', $asset_repayment_id)->first();
						if(!is_null($asset_repay_paid)){
							$cicilan_terbayar = $asset_repay_paid->cicilan_terbayar;
						}

						$plans[$plan_counter]['plan_activated_at'] = $plan->detail_activation_latest->created_at->format('Y-m-d H:i:s');
					}
					$total_asset = $cicilan_terbayar;

					//$plans[$plan_number] = $item_plan_b;
					$plans[$plan_counter]['plan_id'] = $plan_id;
					$plans[$plan_counter]['plan_number'] = $plan_number;
					$plans[$plan_counter]['plan_name'] = 'Plan '.$plan_number;
					$plans[$plan_counter]['plan_type'] = 'plan_b';
					$plans[$plan_counter]['running_man']['target'] = "$plan->kebutuhan_dana";
					$plans[$plan_counter]['running_man']['current'] = "$total_asset"; 
					$plans[$plan_counter]['grandma'] = null;
					//$plannya_apa_aja[] = $plan_number;

					$plan_counter++;
				}
			}
		}
		//dd($plannya_apa_aja);
		//dd((array)$plans);

		return response()->json([
				'result' => 'success',
				'data' => $plans
			]);
	}

	public function portfolio(Request $request){
		if($request->has('client_id')){
			$client_id = $request->get('client_id');
		}else{
			return response()->json([
				'result' => 'error',
				'alerts' => [
					'client_id' => 'not_set'
				]
			]);
		} 

		$user_info = User::where('id', $client_id)->first();

		//query cashflow analysis menggunakan max approved
		$cashflowAnalysis = $this->cashflowAnalysis->showByVersion([
			'user_id' => $client_id,
			'modules' => [ 'asset_repayments', 'insurances' ]
		]);
			
		//query postfolio analysis menggunakan max approved
		$portfolioAnalysis = $this->portfolioAnalysis->showByVersion([
			'user_id' => $client_id,
			'modules' => [ 'asset_repayments_paid', 'asset_repayments_paidoff' ]
		]);
		//gimana jika ini null

		//query postfolio analysis menggunakan max approved
		$planAnalysis = $this->planAnalysis->showByVersion([
			'user_id' => $client_id,
			'modules' => [ 'a_plans' ]
		]);

		//jumlah total cicilan asset
		//$total_asset = [];
		$total_all_asset = 0;
		$total_all_asset_dimiliki = 0;
		$asset_dtls = [];
		if(isset($cashflowAnalysis['asset_repayments'])){
			foreach ($cashflowAnalysis['asset_repayments'] as $asset_repayment) {
				$taxo_id = $asset_repayment['taxo_wallet_id'];
				$taxo_name = $asset_repayment['taxo_wallet_name'];

				if(!isset($asset_dtls[$taxo_id]))
					$asset_dtls[$taxo_id]['asset_value'] = 0;

				if(!isset($asset_dtls[$taxo_id]['taxo_wallet_id'])){
					$asset_dtls[$taxo_id]['taxo_wallet_id'] = $taxo_id;
					$asset_dtls[$taxo_id]['taxo_wallet_name'] = $taxo_name;
				}

				if(!isset($asset_dtls[$taxo_id]['item_count']))
					$asset_dtls[$taxo_id]['item_count'] = 0;

				$total = $asset_repayment['sisa_durasi']*$asset_repayment['cicilan_perbulan'];

				$asset_dtls[$taxo_id]['asset_value'] += $total;//total dari taxo id yang sama

				$total_all_asset += $total;
				$asset_dtls[$taxo_id]['item_count']++;
				$asset_dtls[$taxo_id]['details'][] = [
					'asset_value' => $total,
					'taxo_wallet_id' =>  $taxo_id,
					'taxo_wallet_name' => $taxo_name,
					'asset_type' => 'asset_repayments'
				];

				
			}

		}

		//jumlah total cicilan asset terbayar dan asset lunas
		//$total_asset_paid = [];
		if(isset($portfolioAnalysis['asset_repayments_paid'])){
			foreach ($portfolioAnalysis['asset_repayments_paid'] as $asset_paid) {
				$taxo_id = $asset_paid['asset_repayment']['taxo_wallet_id'];
				$taxo_name = $asset_paid['asset_repayment']['taxo_wallet_name'];
				if(!isset($asset_dtls[$taxo_id]))
					$asset_dtls[$taxo_id]['asset_value'] = 0;
				
				if(!isset($asset_dtls[$taxo_id]['taxo_wallet_id'])){
					$asset_dtls[$taxo_id]['taxo_wallet_id'] = $taxo_id;
					$asset_dtls[$taxo_id]['taxo_wallet_name'] = $taxo_name;
				}
				
				if(!isset($asset_dtls[$taxo_id]['item_count']))
					$asset_dtls[$taxo_id]['item_count'] = 0;

				$total = floatval($asset_paid['cicilan_terbayar']);

				$asset_dtls[$taxo_id]['asset_value'] += $total;
				$total_all_asset += $total;
				$total_all_asset_dimiliki += $total;

				$asset_dtls[$taxo_id]['item_count']++;
				$asset_dtls[$taxo_id]['details'][] = [
					'asset_value' => $total,
					'taxo_wallet_id' =>  $taxo_id,
					'taxo_wallet_name' => $taxo_name,
					'asset_type' => 'asset_repayments_paid'
				];
			}
		}

		if(isset($portfolioAnalysis['asset_repayments_paidoff'])){
			foreach ($portfolioAnalysis['asset_repayments_paidoff'] as $asset_paidoff) {
				$taxo_id = $asset_paidoff['taxo_wallet_id'];
				$taxo_name = $asset_paidoff['taxo_wallet_name'];

				if(!isset($asset_dtls[$taxo_id]))
					$asset_dtls[$taxo_id]['asset_value'] = 0;

				if(!isset($asset_dtls[$taxo_id]['taxo_wallet_id'])){
					$asset_dtls[$taxo_id]['taxo_wallet_id'] = $taxo_id;
					$asset_dtls[$taxo_id]['taxo_wallet_name'] = $taxo_name;
				}

				if(!isset($asset_dtls[$taxo_id]['item_count']))
					$asset_dtls[$taxo_id]['item_count'] = 0;

				$total = floatval($asset_paidoff['nilai_aset']);

				$asset_dtls[$taxo_id]['asset_value'] += $total;
				$total_all_asset += $total;
				$total_all_asset_dimiliki += $total;

				$asset_dtls[$taxo_id]['item_count']++;
				$asset_dtls[$taxo_id]['details'][] = [
					'asset_value' => $total,
					'taxo_wallet_id' =>  $taxo_id,
					'taxo_wallet_name' => $taxo_name,
					'asset_type' => 'asset_repayments_paidoff'
				];
			}
		}
		//jumlah pengurangan total cicilan asset -  total cicilan aset terbayar dan  aset lunas
		$kebutuhan_nilai_pertanggungan = 0;
		$all_insurances = [];
		if(isset($planAnalysis['a_plans'])){
			foreach ($planAnalysis['a_plans'] as $a_plan_raw) {
				$plan_a = $a_plan_raw->plan_a;
				$plan_id = $plan_a[0]->id;

				$kebutuhan_nilai_pertanggungan = $a_plan_raw->plan_protections[0]->kebutuhan_nilai_pertanggungan; 
				foreach ($a_plan_raw->insurance_coverages as $insurance_coverage) {
					$all_insurances[$insurance_coverage->taxo_insurance_type_id]['taxo_insurance_type_id'] = $insurance_coverage->taxo_insurance_type_id;
					$all_insurances[$insurance_coverage->taxo_insurance_type_id]['taxo_insurance_type_name'] = $insurance_coverage->taxo_insurance_type_name;

					if(!isset($all_insurances[$insurance_coverage->taxo_insurance_type_id]['nilai_pertanggungan_yang_diambil'])){
						$all_insurances[$insurance_coverage->taxo_insurance_type_id]['nilai_pertanggungan_yang_diambil'] = 0;
					}

					if(!isset($all_insurances[$insurance_coverage->taxo_insurance_type_id]['kebutuhan_nilai_pertanggungan'])){
						$all_insurances[$insurance_coverage->taxo_insurance_type_id]['kebutuhan_nilai_pertanggungan'] = 0;
					}

					$all_insurances[$insurance_coverage->taxo_insurance_type_id]['nilai_pertanggungan_yang_diambil'] += floatval($insurance_coverage->nilai_pertanggungan);
					$all_insurances[$insurance_coverage->taxo_insurance_type_id]['kebutuhan_nilai_pertanggungan'] += floatval($kebutuhan_nilai_pertanggungan);
					$all_insurances[$insurance_coverage->taxo_insurance_type_id]['details'][] = [
						'plan_id' => $plan_id,
						'taxo_insurance_type_name' => $insurance_coverage->taxo_insurance_type_name,
						'taxo_insurance_type_id' => $insurance_coverage->taxo_insurance_type_id,
						'nilai_pertanggungan_yang_diambil' => floatval($insurance_coverage->nilai_pertanggungan),
						'kebutuhan_nilai_pertanggungan' => floatval($kebutuhan_nilai_pertanggungan)
					];
				}
			}
		}

		//portfolio cash periode nya dimulai dari tanggal saat user itu tercreate
		$user_register = $user_info->created_at->format('Y-m-d');
		$eom_balances_value = EomBalance::where('client_id', $client_id)
		->where('period', '>=', $user_register)->sum('balance');

		//gabung dengan hasil convert cash
		$converted_cash = ConvertCash::where('client_id', $client_id)->sum('jumlah');

		return response()->json([
			'result' => 'success',
			'data' => [
				'total_asset' => $total_all_asset,
				'assets' => array_values($asset_dtls),
				'total_insurance' => $kebutuhan_nilai_pertanggungan,
				'insurances' => array_values($all_insurances),
				'total_cash' => ($eom_balances_value+$converted_cash)
			]
		]);
	}

	function activation_page(){
		return view('auth.reset-success');
	}

	function doActivate($activation_code){
        $user = User::where('activation_code', $activation_code)->with(['userMetas', 'roles'])->first();
        if(!is_null($user)){
			$default_role_id_client = config_db_cached('settings::default_role_id_client');
			
            if($user->is_active == 1)
                return view('frontend.auth.activation-already-activated');
            

			//usermeta
			$userMeta = userMeta($user->userMetas);
			foreach($user->roles as $role){
				$role_name = $role->display_name;
				$role_id = $role->id;
			} 
		
			//generate client code
			if(isset($role_id)){
				if($role_id == $default_role_id_client ){//jika merupakan role client
					DB::beginTransaction();
					$user->is_active = 1;
					$user->save();//update is_active milik user
					DB::commit();
					return view('frontend.auth.activation-success');
				}else{//bukan role client
					return view('frontend.auth.activation-failed');
				}
			}else{//user tidak memiliki role
				return view('frontend.auth.activation-failed');
			}
        }else{//user tidak ditemukan
            return view('frontend.auth.activation-failed');
        }
	}


	public function forgotPassword(Request $request)
	{ 
		$rules['email'] = 'required|email';
		$attributeNames['email'] = trans('app.email');

    	$validator = Validator::make($request->all(), $rules);
    	$validator->setAttributeNames($attributeNames);

        if ($validator->fails())
        { 
			return response()->json([
				'result' => 'error',
				'alerts' => $validator->errors()
			]);
		}
		
		$response = $this->passwords->sendResetLink($request->only('email'), function($m)
		{
			$m->subject($this->getEmailSubject());
		});
		
		switch ($response)
		{
			case PasswordBroker::RESET_LINK_SENT:
				return response()->json([
					'result' => 'success',
					'data' => [ 
						'status' => trans($response)
					]
				]);
			case PasswordBroker::INVALID_USER:
				return response()->json([
					'result' => 'error',
					'data' => [ 
						'email' => 'Alamat email ini tidak terdaftar di FUNDtastic' //trans($response)
					]
				]);
		}
	}

	public function getCfpList()
	{
		$cfps = User::join('role_user', 'users.id', '=', 'role_user.user_id')
		->join('user_metas', 'users.id', '=', 'user_metas.user_id')
		->where('role_id',6)->where('users.is_active',1)->where('meta_key','photo')->select('users.*','role_user','user_metas.meta_key','user_metas.meta_value')->get();

		foreach($cfps as $cfp){
			if($cfp['id'] != 599 && $cfp['id'] != 639){
				$data['id'] = $cfp['id'];
				$data['name'] = $cfp['name'];
				$data['email'] = $cfp['email'];
				$data['certificate_no'] = $cfp['certificate_no'];
				$data['description'] = $cfp['description'];
				$data['photo'] = url().'/'.$cfp['meta_value'];
				$data['position'] = 'Executive Financial Planner';
				$data['cfp'] = 'CFP';
				$nestedData[] = $data;
			}
		}

		return response()->json(
			[
				'result' => 'success',
				'data' => $nestedData
			]
		);
	}

	public function changeCfp(Request $request)
	{
		$input = $request->all();

		//untuk mengecek tabel cfp_client
		$cfp_client = CfpClient::where('client_id', '=', $input['client_id'])->first();

		//untuk mengecek cfp
		$cfp = User::where('id', '=', $input['cfp_id'])->first();

		if(!empty($cfp_client) && !empty($cfp) && $cfp->is_active==1){
			$cfp_client->update(array('cfp_id'=>$input['cfp_id'],'notes'=>'CFP Changed by User','updated_by'=>Auth::user()->id,'updated_at'=>Carbon::now(),'record_flag'=>'U'));
		}

		return response()->json(
			[
				'result' => !empty($cfp_client) && !empty($cfp) && $cfp->is_active == 1 ? 'success' : 'error',
				'data' => !empty($cfp_client) && !empty($cfp) && $cfp->is_active == 1 ? $cfp_client : ''
			]
		);
	}
}
