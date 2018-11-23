<?php namespace App\Http\Controllers\Backend;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

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
use App\Repositories\Grade\GradeRepository as Grade;
use App\Exceptions\Validation\ValidationException;
use Config;
use Entrust;

use App\User;
use Carbon\Carbon;
use Auth;

class GradeController extends Controller {

	protected $grade;
	protected $user;

    public function __construct(Grade $grade, UserInterface $user) {
        $this->grade = $grade;
        $this->user = $user;
    }
	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		$attr = [ 
                'title' => trans('app.grade')
            ];
        if(!Entrust::can(['read_grade'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $page = Input::get('page', 1);
        $perPage = config_db_cached('settings::backend_per_page');
        $pagiData = $this->grade->paginate($page, $perPage, true);
        $grades = new LengthAwarePaginator($pagiData->items, $pagiData->totalItems, $perPage, $page, [
            'path' => Paginator::resolveCurrentPath()
        ]);
        $grades->setPath('');
        //dd($testimonials->count());

        //dd($codes);
		return view('backend.grade.index', compact('grades'));
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		$attr = [ 
                'title' => trans('app.grade')
            ];
        
        if(!Entrust::can(['create_grade'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        //dd($categoryCodes);
        return view('backend.grade.create');
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
            $grade_name = Input::get('grade_name');
            $grade_ages = Input::get('grade_ages');
            $grade_thumb = Input::get('grade_thumb');
            $grade_button_label = Input::get('grade_button_label');
            $this->grade->create([
                'grade_name' => $grade_name,
                'grade_ages' => $grade_ages,
                'grade_thumb' => $grade_thumb,
                'grade_button_label' => $grade_button_label
            ]);
            Notification::success( trans('app.grade_added') );
            return langRedirectRoute('admin.grade.index');
        } catch (ValidationException $e) {
            return langRedirectRoute('admin.grade.create')->withInput()->withErrors($e->getErrors());
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
                'title' => trans('app.grade')
            ];
        
        if(!Entrust::can(['read_grade'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        
		$grade = $this->grade->find($id);
		//dd($grade['id']);
        return view('backend.grade.show', compact('grade'));
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
                'title' => trans('app.grade')
            ];
        if(!Entrust::can(['update_grade'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $grade = $this->grade->find($id);
        //dd($scheduleCfp);
        return view('backend.grade.edit', compact('grade'));
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
            $this->grade->update($id, Input::all());
            Notification::success(trans('app.grade_updated'));
            return langRedirectRoute('admin.grade.index');
        } catch (ValidationException $e) {
            return langRedirectRoute('admin.grade.edit', [ 'id' => $id ] )->withInput()->withErrors($e->getErrors());
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
                'title' => trans('app.grade')
            ];
        if(!Entrust::can(['delete_grade'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $this->grade->delete($id);
        Notification::success(trans('app.grade_deleted'));
        return langRedirectRoute('admin.grade.index');
	}

	public function confirmDestroy($id) {
        $attr = [ 
                'title' => trans('app.grade')
            ];
        if(!Entrust::can(['delete_grade'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $grade = $this->grade->find($id);
        //dd($user);
        return view('backend.grade.confirm-destroy', compact('grade'));
    }

}
