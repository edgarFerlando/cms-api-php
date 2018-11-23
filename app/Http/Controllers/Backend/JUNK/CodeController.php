<?php namespace App\Http\Controllers\Backend;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Repositories\Code\CodeInterface;
use App\Repositories\CategoryCode\CategoryCodeInterface;
use Redirect;
use View;
use Input;
use Validator;
use Response;
use Notification;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use App\Repositories\Code\CodeRepository as Code;
use App\Exceptions\Validation\ValidationException;
use Config;
use Entrust;

use App\Models\CategoryCode;

class CodeController extends Controller {

	protected $code;
	protected $categoryCode;

    public function __construct(CodeInterface $code, CategoryCodeInterface $categoryCode) {
        $this->code = $code;
        $this->categoryCode = $categoryCode;
    }
	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		$attr = [ 
                'title' => trans('app.code')
            ];
        if(!Entrust::can(['read_code'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $page = Input::get('page', 1);
        $perPage = config_db_cached('settings::backend_per_page');
        $pagiData = $this->code->paginate($page, $perPage, true);
        $codes = new LengthAwarePaginator($pagiData->items, $pagiData->totalItems, $perPage, $page, [
            'path' => Paginator::resolveCurrentPath()
        ]);
        $codes->setPath('');
        //dd($testimonials->count());

        //dd($codes);
		return view('backend.code.index', compact('codes'));
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		$attr = [ 
                'title' => trans('app.code')
            ];
        if(!Entrust::can(['create_code'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $categoryCodes[] = '-';
        $categoryCodes += $this->categoryCode->lists('category_name', 'category_code');
        //dd($categoryCodes);
        return view('backend.code.create', compact('categoryCodes'));
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
            $this->code->create(Input::all());
            Notification::success( trans('app.code_added') );
            return langRedirectRoute('admin.code.index');
        } catch (ValidationException $e) {
            return langRedirectRoute('admin.code.create')->withInput()->withErrors($e->getErrors());
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
                'title' => trans('app.code')
            ];
        
        if(!Entrust::can(['read_code'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        
		$code = $this->code->find($id);

		$category = CategoryCode::find($code->category_code);
		$code->category_name = $category->category_name;
		//dd($code);
        return view('backend.code.show', compact('code'));
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
                'title' => trans('app.code')
            ];
        if(!Entrust::can(['update_code'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $code = $this->code->find($id);
        $categoryCodes[] = '-';
        $categoryCodes += $this->categoryCode->lists('category_name', 'category_code');
        //dd($testimonial);
        return view('backend.code.edit', compact('code','categoryCodes'));
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
            $this->code->update($id, Input::all());
            Notification::success(trans('app.code_updated'));
            return langRedirectRoute('admin.code.index');
        } catch (ValidationException $e) {
            return langRedirectRoute('admin.code.edit', [ 'id' => $id ] )->withInput()->withErrors($e->getErrors());
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
                'title' => trans('app.code')
            ];
        if(!Entrust::can(['delete_code'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $this->code->delete($id);
        Notification::success(trans('app.code_deleted'));
        return langRedirectRoute('admin.code.index');
	}

	public function confirmDestroy($id) {
        $attr = [ 
                'title' => trans('app.code')
            ];
        if(!Entrust::can(['delete_code'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $code = $this->code->find($id);

        return view('backend.code.confirm-destroy', compact('code'));
    }

}
