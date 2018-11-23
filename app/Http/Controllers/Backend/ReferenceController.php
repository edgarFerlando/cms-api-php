<?php namespace App\Http\Controllers\Backend;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Repositories\Reference\ReferenceInterface;
use App\Repositories\User\UserInterface;
use Redirect;
use View;
use Input;
use Validator;
use Response;
use Notification;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use App\Repositories\Reference\ReferenceRepository as Reference;
use App\Exceptions\Validation\ValidationException;
use Config;
use Entrust;

use App\User;
use Carbon\Carbon;
use Auth;

class ReferenceController extends Controller {

	protected $reference;
	protected $user;

    public function __construct(Reference $reference, UserInterface $user) {
        $this->reference = $reference;
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
                'title' => trans('app.reference')
            ];
        if(!Entrust::can(['read_reference'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $page = Input::get('page', 1);
        $perPage = config_db_cached('settings::backend_per_page');
        $pagiData = $this->reference->paginate($page, $perPage, true);
        $references = new LengthAwarePaginator($pagiData->items, $pagiData->totalItems, $perPage, $page, [
            'path' => Paginator::resolveCurrentPath()
        ]);
        $references->setPath('');
        //dd($testimonials->count());

        //dd($codes);
		return view('backend.reference.index', compact('references'));
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		$attr = [ 
                'title' => trans('app.reference')
            ];
        
        if(!Entrust::can(['create_reference'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        //dd($categoryCodes);
        return view('backend.reference.create');
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		try {
            $this->reference->create(Input::all());
            Notification::success( trans('app.data_added') );
            return langRedirectRoute('admin.reference.index');
        } catch (ValidationException $e) {
            return langRedirectRoute('admin.reference.create')->withInput()->withErrors($e->getErrors());
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
                'title' => trans('app.reference')
            ];
        
        if(!Entrust::can(['read_reference'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        
		$reference = $this->reference->find($id);
		//dd($reference['id']);
        return view('backend.reference.show', compact('reference'));
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
                'title' => trans('app.reference')
            ];
        if(!Entrust::can(['update_reference'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $reference = $this->reference->find($id);
        //dd($scheduleCfp);
        return view('backend.reference.edit', compact('reference'));
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
            $this->reference->update($id, Input::all());
            Notification::success(trans('app.reference_updated'));
            return langRedirectRoute('admin.reference.index');
        } catch (ValidationException $e) {
            return langRedirectRoute('admin.reference.edit', [ 'id' => $id ] )->withInput()->withErrors($e->getErrors());
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
                'title' => trans('app.reference')
            ];
        if(!Entrust::can(['delete_reference'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $this->reference->delete($id);
        Notification::success(trans('app.reference_deleted'));
        return langRedirectRoute('admin.reference.index');
	}

	public function confirmDestroy($id) {
        $attr = [ 
                'title' => trans('app.reference')
            ];
        if(!Entrust::can(['delete_reference'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $reference = $this->reference->find($id);
        //dd($user);
        return view('backend.reference.confirm-destroy', compact('reference'));
    }

}
