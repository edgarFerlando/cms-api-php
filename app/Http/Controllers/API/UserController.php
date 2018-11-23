<?php namespace App\Http\Controllers\API;

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
use Carbon\Carbon;
use App\Repositories\User\UserInterface;
use DB;
use App\Repositories\Taxonomy\TaxonomyInterface;

class UserController extends Controller {
    protected $goalGrade;
    protected $grade;
    protected $goal;

    public function __construct(UserInterface $user, TaxonomyInterface $taxonomy) {
    	$this->goalGrade = new GoalGrade;
    	$this->goal = new Goal;
    	$this->grade = new Grade;
    	$this->user = $user;
    	$this->taxonomy = $taxonomy;
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

	public function doLogin(Request $request)
	{ 
		$alerts = [];
		$password = base64_decode($request->input('password'));// jadi isinya $request->input('password') adalah misal password di masukin ke base64_decode

		$request->request->add(['password' => $password]);
		//dd($password);
		$credentials = $request->only('email', 'password');
		Auth::validate($credentials, [
				'email' => 'required|email', 'password' => 'required',
		]);
			
		if (Auth::attempt($credentials))
		{
			$user = $this->user->findWithMyCFP($request->input('email'));
			
			$userMeta = userMeta($user->userMetas);
			//check already submit goals and grade
			$goal_grade_count = $this->goalGrade->where(['user_id' => $user->id])->count();
			$grades = $this->grade->select('id', 'grade_name', 'ages', 'thumb_path', 'button_label')->with('goals')->get();
			$goals = $this->goal->select('id', 'goal_name', 'icon_path', 'thumb_path', 'position_under_grade_id')->get();
			$user_raw = $user->toArray();
			$user_raw = array_merge($user_raw, (array)$userMeta);
			$user_raw['cfp'] = $user_raw['cfp_client']['cfp'];
			unset($user_raw['cfp_client']);
			unset($user_raw['user_metas']);
			return response()->json([
					'result' => 'success',
					'alerts' => '', 
					'data' => $user_raw,
					//'cfp' => ,
					'grades' => $grades,
					'goals' => $goals,
					'goal_grade' => $goal_grade_count > 0 ? true:false
			]);
		}
		
		return response()->json([
				'result' => 'error',
				'alerts' => 'Login failed.',
				'data' => null
		]);
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

	public function getBranches()
	{							 
		$branches_raw = $this->taxonomy->getTermsByPostType('branch')->toHierarchy();
		$branches = [];
		foreach ($branches_raw as $branch_raw) {
			$branches[] = $branch_raw;
		}
		return response()->json([
				'result' => 'success',
				'data' => $branches
		]);
	}
}
