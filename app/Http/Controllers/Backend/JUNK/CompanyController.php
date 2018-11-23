<?php namespace App\Http\Controllers\Backend;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Repositories\Company\CompanyInterface;
use App\Repositories\Code\CodeInterface;
use Redirect;
use View;
use Input;
use Validator;
use Response;
use Notification;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use App\Repositories\Company\CompanyRepository as Company;
use App\Exceptions\Validation\ValidationException;
use Config;
use Entrust;

use App\Models\Code;

class CompanyController extends Controller {

	protected $company;
	protected $code;

    public function __construct(Company $company, CodeInterface $code) {
    	$this->company = $company;
        $this->code = $code;  
    }
	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		$attr = [ 
                'title' => trans('app.company')
            ];
        if(!Entrust::can(['read_company'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $page = Input::get('page', 1);
        $perPage = config_db_cached('settings::backend_per_page');
        $pagiData = $this->company->paginate($page, $perPage, true);
        $companies = new LengthAwarePaginator($pagiData->items, $pagiData->totalItems, $perPage, $page, [
            'path' => Paginator::resolveCurrentPath()
        ]);
        $companies->setPath('');
        //dd($testimonials->count());

        //dd($codes);
		return view('backend.company.index', compact('companies'));
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		$attr = [ 
                'title' => trans('app.company')
            ];
        
        if(!Entrust::can(['create_company'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $codes[] = '-';
        $codes += $this->code->lists('code_name', 'code', 6);
        //dd($categoryCodes);
        return view('backend.company.create', compact('codes'));
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
            $this->company->create(Input::all());
            Notification::success( trans('app.company_added') );
            return langRedirectRoute('admin.company.index');
        } catch (ValidationException $e) {
            return langRedirectRoute('admin.company.create')->withInput()->withErrors($e->getErrors());
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
                'title' => trans('app.company')
            ];
        
        if(!Entrust::can(['read_company'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        
		$company = $this->company->find($id);

		$code = Code::find($company->company_type);
		$company->code_name = $code->code_name;
		//dd($code);
        return view('backend.company.show', compact('company'));
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
                'title' => trans('app.company')
            ];
        if(!Entrust::can(['update_company'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $company = $this->company->find($id);
        $codes[] = '-';
        $codes += $this->code->lists('code_name', 'code', 6);
        //dd($testimonial);
        return view('backend.company.edit', compact('company','codes'));
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
            $this->company->update($id, Input::all());
            Notification::success(trans('app.company_updated'));
            return langRedirectRoute('admin.company.index');
        } catch (ValidationException $e) {
            return langRedirectRoute('admin.company.edit', [ 'id' => $id ] )->withInput()->withErrors($e->getErrors());
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
                'title' => trans('app.company')
            ];
        if(!Entrust::can(['delete_company'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $this->company->delete($id);
        Notification::success(trans('app.company_deleted'));
        return langRedirectRoute('admin.company.index');
	}

	public function confirmDestroy($id) {
        $attr = [ 
                'title' => trans('app.company')
            ];
        if(!Entrust::can(['delete_company'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $company = $this->company->find($id);

        return view('backend.company.confirm-destroy', compact('company'));
    }

}
