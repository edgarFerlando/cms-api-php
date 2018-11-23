<?php namespace App\Http\Controllers\Backend;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Repositories\PortofolioDetail\PortofolioDetailInterface;
use App\Repositories\Portofolio\PortofolioInterface;
use Redirect;
use View;
use Input;
use Validator;
use Response;
use Notification;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use App\Repositories\PortofolioDetail\PortofolioDetailRepository as PortofolioDetail;
use App\Exceptions\Validation\ValidationException;
use Config;
use Entrust;

use App\Models\Portofolio;

class PortofolioDetailController extends Controller {

	protected $portofolioDetail;
	protected $Portofolio;

    public function __construct(PortofolioDetailInterface $portofolioDetail, PortofolioInterface $portofolio) {
        $this->portofolioDetail = $portofolioDetail;
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
                'title' => trans('app.portofolio_detail')
            ];

        //dd($attr);
        /* if(!Entrust::can(['read_product_attribute'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        } */
        $page = Input::get('page', 1);
        $perPage = config_db_cached('settings::backend_per_page');
        
        $pagiData = $this->portofolioDetail->paginate($page, $perPage, true);
        $portofolioDetails = new LengthAwarePaginator($pagiData->items, $pagiData->totalItems, $perPage, $page, [
            'path' => Paginator::resolveCurrentPath()
        ]);
        $portofolioDetails->setPath('');
        //dd($testimonials->count());

        //dd($codes);
		return view('backend.portofolioDetail.index', compact('portofolioDetails'));
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		/*$attr = [ 
                'title' => trans('app.testimonial')
            ];
        if(!Entrust::can(['create_testimonial'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }*/
        $portofolios = $this->portofolio->lists('portofolio_name', 'id');
        //dd($categoryCodes);
        return view('backend.portofolioDetail.create', compact('portofolios'));
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
            $this->portofolioDetail->create(Input::all());
            Notification::success( trans('app.portofolio_detail_added') );
            return langRedirectRoute('admin.detail.portofolio.index');
        } catch (ValidationException $e) {
            return langRedirectRoute('admin.detail.portofolio.create')->withInput()->withErrors($e->getErrors());
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
                'title' => trans('app.portofolio_detail')
            ];
        /*
        if(!Entrust::can(['read_testimonial'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        */
		$portofolioDetail = $this->portofolioDetail->find($id);

		$portofolio = Portofolio::find($portofolioDetail->portofolio_id);
		$portofolioDetail->portofolio_name = $portofolio->portofolio_name;
		//dd($code);
        return view('backend.portofolioDetail.show', compact('portofolioDetail'));
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
                'title' => trans('app.portofolio_detail')
            ];
        /*if(!Entrust::can(['update_testimonial'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }*/
        $portofolioDetail = $this->portofolioDetail->find($id);
        $portofolios = $this->portofolio->lists('portofolio_name', 'id');
        //dd($testimonial);
        return view('backend.portofolioDetail.edit', compact('portofolioDetail','portofolios'));
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
            $this->portofolioDetail->update($id, Input::all());
            Notification::success(trans('app.portofolio_detail_updated'));
            return langRedirectRoute('admin.detail.portofolio.index');
        } catch (ValidationException $e) {
            return langRedirectRoute('admin.detail.portofolio.edit', [ 'id' => $id ] )->withInput()->withErrors($e->getErrors());
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
                'title' => trans('app.portofolio_detail')
            ];
        /*if(!Entrust::can(['delete_testimonial'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }*/
        $this->portofolioDetail->delete($id);
        Notification::success(trans('app.portofolio_detail_deleted'));
        return langRedirectRoute('admin.detail.portofolio.index');
	}

	public function confirmDestroy($id) {
        $attr = [ 
                'title' => trans('app.portofolio_detail')
            ];
        /*if(!Entrust::can(['delete_testimonial'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }*/
        $portofolioDetail = $this->portofolioDetail->find($id);

        return view('backend.portofolioDetail.confirm-destroy', compact('portofolioDetail'));
    }

}
