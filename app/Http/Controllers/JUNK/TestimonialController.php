<?php namespace App\Http\Controllers;

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

use App\User;

class TestimonialController extends Controller {


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
		$page = Input::get('page', 1);
        $perPage = config_db_cached('settings::per_page');
        $pagiData = $this->testimonial->paginate($page, $perPage, true);
        $testimonials = new LengthAwarePaginator($pagiData->items, $pagiData->totalItems, $perPage, $page, [
            'path' => Paginator::resolveCurrentPath()
        ]);
        $testimonials->setPath('');

        return view('frontend.testimonial.index', compact('testimonials'));
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
            return view('frontend.auth.unauthorized', compact('attr'));
        }
        return view('frontend.testimonial.create');
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
            return langRedirectRoute('testimonial');
        } catch (ValidationException $e) {
            return langRedirectRoute('testimonial.create')->withInput()->withErrors($e->getErrors());
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
		//
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

}
