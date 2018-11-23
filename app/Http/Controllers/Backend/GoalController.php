<?php namespace App\Http\Controllers\Backend;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Repositories\Goal\GoalInterface;
use App\Repositories\Grade\GradeInterface;
use App\Repositories\User\UserInterface;
use Redirect;
use View;
use Input;
use Validator;
use Response;
use Notification;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use App\Repositories\Goal\GoalRepository as Goal;
use App\Repositories\Goal\GradeRepository as Grade;
use App\Exceptions\Validation\ValidationException;
use Config;
use Entrust;

use App\User;

class GoalController extends Controller {

	protected $goal;
	protected $user;
    protected $grade;

    public function __construct(Goal $goal, UserInterface $user, GradeInterface $grade) {
        $this->goal = $goal;
        $this->user = $user;
        $this->grade = $grade;
    }
	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		$attr = [ 
                'title' => trans('app.goal')
            ];
        if(!Entrust::can(['read_goal'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $page = Input::get('page', 1);
        $perPage = config_db_cached('settings::backend_per_page');
        $pagiData = $this->goal->paginate($page, $perPage, true);
        $goals = new LengthAwarePaginator($pagiData->items, $pagiData->totalItems, $perPage, $page, [
            'path' => Paginator::resolveCurrentPath()
        ]);
        $goals->setPath('');
        //dd($testimonials->count());

        //dd($codes);
		return view('backend.goal.index', compact('goals'));
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		$attr = [ 
                'title' => trans('app.goal')
            ];
        
        if(!Entrust::can(['create_goal'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $grade_options[''] = '-';
        $grade_options += $this->grade->lists('grade_name', 'id');
        //dd($categoryCodes);
        return view('backend.goal.create', compact('grade_options'));
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		try {
			//$input = Input::all();
			//dd($input);
            $this->goal->create(Input::all());
            Notification::success( trans('app.goal_added') );
            return langRedirectRoute('admin.goal.index');
        } catch (ValidationException $e) {
            return langRedirectRoute('admin.goal.create')->withInput()->withErrors($e->getErrors());
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
		$attr = [ 
                'title' => trans('app.goal')
            ];
        if(!Entrust::can(['read_goal'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $goal = $this->goal->find($id);
        //dd($scheduleCfp);
        return view('backend.goal.show', compact('goal'));
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		$attr = [ 
                'title' => trans('app.goal')
            ];
        if(!Entrust::can(['update_goal'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $goal = $this->goal->find($id);
        $grade_options[''] = '-';
        $grade_options += $this->grade->lists('grade_name', 'id');
        //dd($scheduleCfp);
        return view('backend.goal.edit', compact('goal', 'grade_options'));
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		try {   
            //dd(Input::all());
            $this->goal->update($id, Input::all());
            Notification::success(trans('app.goal_updated'));
            return langRedirectRoute('admin.goal.index');
        } catch (ValidationException $e) {
            return langRedirectRoute('admin.goal.edit', [ 'id' => $id ] )->withInput()->withErrors($e->getErrors());
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
		$attr = [ 
                'title' => trans('app.goal')
            ];
        if(!Entrust::can(['delete_goal'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $this->goal->delete($id);
        Notification::success(trans('app.goal_deleted'));
        return langRedirectRoute('admin.goal.index');
	}

	public function confirmDestroy($id) {
        $attr = [ 
                'title' => trans('app.goal')
            ];
        if(!Entrust::can(['delete_goal'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $goal = $this->goal->find($id);
        //dd($user);
        return view('backend.goal.confirm-destroy', compact('goal'));
    }

}
