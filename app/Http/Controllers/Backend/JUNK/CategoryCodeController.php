<?php namespace App\Http\Controllers\Backend;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Repositories\CategoryCode\CategoryCodeInterface;
use Redirect;
use View;
use Input;
use Validator;
use Response;
use Notification;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use App\Repositories\CategoryCode\CategoryCodeRepository as CategoryCode;
use App\Exceptions\Validation\ValidationException;
use Config;
use Entrust;

class CategoryCodeController extends Controller {

	protected $categoryCode;

    public function __construct(CategoryCodeInterface $categoryCode) {
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
                'title' => trans('app.category_code')
            ];
        if(!Entrust::can(['read_category_code'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $page = Input::get('page', 1);
        $perPage = config_db_cached('settings::backend_per_page');
        $pagiData = $this->categoryCode->paginate($page, $perPage, true);
        $categoryCodes = new LengthAwarePaginator($pagiData->items, $pagiData->totalItems, $perPage, $page, [
            'path' => Paginator::resolveCurrentPath()
        ]);
        $categoryCodes->setPath('');
        //dd($testimonials->count());

        //dd($categoryCodes);
		return view('backend.categoryCode.index', compact('categoryCodes'));
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		$attr = [ 
                'title' => trans('app.category_code')
            ];
        if(!Entrust::can(['create_category_code'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        return view('backend.categoryCode.create');
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
            $this->categoryCode->create(Input::all());
            Notification::success( trans('app.category_code_added') );
            return langRedirectRoute('admin.category.code.index');
        } catch (ValidationException $e) {
            return langRedirectRoute('admin.category.code.create')->withInput()->withErrors($e->getErrors());
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
                'title' => trans('app.category_code')
            ];
        
        if(!Entrust::can(['read_category_code'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        
		$categoryCode = $this->categoryCode->find($id);
		//dd($categoryCode);
        return view('backend.categoryCode.show', compact('categoryCode'));
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
                'title' => trans('app.category_code')
            ];
        if(!Entrust::can(['update_category_code'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $categoryCode = $this->categoryCode->find($id);
        //dd($testimonial);
        return view('backend.categoryCode.edit', compact('categoryCode'));
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
            $this->categoryCode->update($id, Input::all());
            Notification::success(trans('app.category_code_updated'));
            return langRedirectRoute('admin.category.code.index');
        } catch (ValidationException $e) {
            return langRedirectRoute('admin.category.code.edit', [ 'id' => $id ] )->withInput()->withErrors($e->getErrors());
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
                'title' => trans('app.category_code')
            ];
        if(!Entrust::can(['delete_category_code'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $this->categoryCode->delete($id);
        Notification::success(trans('app.category_code_deleted'));
        return langRedirectRoute('admin.category.code.index');
	}

	public function confirmDestroy($id) {
        $attr = [ 
                'title' => trans('app.category_code')
            ];
        if(!Entrust::can(['delete_category_code'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $categoryCode = $this->categoryCode->find($id);

        return view('backend.CategoryCode.confirm-destroy', compact('categoryCode'));
    }

}
