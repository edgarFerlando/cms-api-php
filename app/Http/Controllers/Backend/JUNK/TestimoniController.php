<?php namespace App\Http\Controllers\Backend;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Repositories\Testimonial\TestimonialInterface;
use Redirect;
use View;
use Input;
use Validator;
use Response;
use Notification;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use App\Repositories\Testimonial\TestimonialRepository as Testimonial;
use App\Exceptions\Validation\ValidationException;
use Config;
use Entrust;

class TestimoniController extends Controller {

	protected $testimonial;

    public function __construct(TestimonialInterface $testimonial) {
        $this->testimonial = $testimonial;
    }
	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		$attr = [ 
                'title' => trans('app.testimonial')
            ];
        /* if(!Entrust::can(['read_product_attribute'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        } */
        $page = Input::get('page', 1);
        $perPage = config_db_cached('settings::backend_per_page');
        $pagiData = $this->testimonial->paginate($page, $perPage, true);
        $testimonials = new LengthAwarePaginator($pagiData->items, $pagiData->totalItems, $perPage, $page, [
            'path' => Paginator::resolveCurrentPath()
        ]);
        $testimonials->setPath('');
        //dd($testimonials->count());
		return view('backend.testimoni.index', compact('testimonials'));
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		$attr = [ 
                'title' => trans('app.testimonial')
            ];
        if(!Entrust::can(['create_testimonial'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        return view('backend.testimoni.create');
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
            $this->testimonial->create(Input::all());
            Notification::success( trans('app.testimonial_added') );
            return langRedirectRoute('admin.testimoni.index');
        } catch (ValidationException $e) {
            return langRedirectRoute('admin.testimoni.create')->withInput()->withErrors($e->getErrors());
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
                'title' => trans('app.product_attribute')
            ];
        /*
        if(!Entrust::can(['read_testimonial'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        */
        $testimonial = $this->testimonial->find($id);
        return view('backend.testimoni.show', compact('testimonial'));
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
                'title' => trans('app.testimonial')
            ];
        if(!Entrust::can(['update_testimonial'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $testimonial = $this->testimonial->find($id);
        //dd($testimonial);
        return view('backend.testimoni.edit', compact('testimonial'));
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
            $this->testimonial->update($id, Input::all());
            Notification::success(trans('app.testimonial_updated'));
            return langRedirectRoute('admin.testimoni.index');
        } catch (ValidationException $e) {
            return langRedirectRoute('admin.testimoni.edit', [ 'id' => $id ] )->withInput()->withErrors($e->getErrors());
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
                'title' => trans('app.testimonial')
            ];
        if(!Entrust::can(['delete_testimonial'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $this->testimonial->delete($id);
        Notification::success(trans('app.testimonial_deleted'));
        return langRedirectRoute('admin.testimoni.index');
	}

	public function confirmDestroy($id) {
        $attr = [ 
                'title' => trans('app.testimonial')
            ];
        if(!Entrust::can(['delete_testimonial'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $testimonial = $this->testimonial->find($id);

        return view('backend.testimoni.confirm-destroy', compact('productAttribute'));
    }

}
