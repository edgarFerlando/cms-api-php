<?php namespace App\Http\Controllers\Backend;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Repositories\Portofolio\PortofolioInterface;
use Redirect;
use View;
use Input;
use Validator;
use Response;
use Notification;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use App\Repositories\Portofolio\PortofolioRepository as Portofolio;
use App\Exceptions\Validation\ValidationException;
use Config;
use Entrust;

class PortofolioController extends Controller {

	protected $portofolio;

    public function __construct(Portofolio $portofolio) {
        $this->portofolio = $portofolio;
    }
	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		$attr = [ 
                'title' => trans('app.portofolio')
            ];
        if(!Entrust::can(['read_portofolio'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $page = Input::get('page', 1);
        $perPage = config_db_cached('settings::backend_per_page');
        $pagiData = $this->portofolio->paginate($page, $perPage, true);
        $portofolios = new LengthAwarePaginator($pagiData->items, $pagiData->totalItems, $perPage, $page, [
            'path' => Paginator::resolveCurrentPath()
        ]);
        $portofolios->setPath('');
        //dd($testimonials->count());

        //dd($categoryCodes);
		return view('backend.portofolio.index', compact('portofolios'));
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		$attr = [ 
                'title' => trans('app.portofolio')
            ];
        if(!Entrust::can(['create_portofolio'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        return view('backend.portofolio.create');
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
            $this->portofolio->create(Input::all());
            Notification::success( trans('app.portofolio_added') );
            return langRedirectRoute('admin.portofolio.index');
        } catch (ValidationException $e) {
            return langRedirectRoute('admin.portofolio.create')->withInput()->withErrors($e->getErrors());
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
                'title' => trans('app.portofolio')
            ];
        
        if(!Entrust::can(['read_portofolio'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        
		$portofolio = $this->portofolio->find($id);
		//dd($categoryCode);
        return view('backend.portofolio.show', compact('portofolio'));
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
                'title' => trans('app.portofolio')
            ];
        if(!Entrust::can(['update_portofolio'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $portofolio = $this->portofolio->findWithDetail($id);
        //dd($testimonial);
        return view('backend.portofolio.edit', compact('portofolio'));
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
            //$input = Input::all();
            //dd($id);
            $this->portofolio->update($id, Input::all());
            Notification::success(trans('app.portofolio_updated'));
            return langRedirectRoute('admin.portofolio.index');
        } catch (ValidationException $e) {
            return langRedirectRoute('admin.portofolio.edit', [ 'id' => $id ] )->withInput()->withErrors($e->getErrors());
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
                'title' => trans('app.portofolio')
            ];
        if(!Entrust::can(['delete_portofolio'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $this->portofolio->delete($id);
        Notification::success(trans('app.portofolio_deleted'));
        return langRedirectRoute('admin.portofolio.index');
	}

	public function confirmDestroy($id) {
		$attr = [ 
                'title' => trans('app.portofolio'),
                'name' => 'portofolio'
            ];
        if(!Entrust::can(['delete_portofolio'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $portofolio = $this->portofolio->find($id);

        return view('backend.portofolio.confirm-destroy', compact('portofolio', 'attr'));
    }
}
