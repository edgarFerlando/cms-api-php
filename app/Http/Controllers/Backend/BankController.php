<?php namespace App\Http\Controllers\Backend;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Repositories\Bank\BankRepository as Bank;
use Entrust;
use Config;
use Input;
use Validator;
use Response;
use Notification;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use App\Exceptions\Validation\ValidationException;
use Carbon\Carbon;

class BankController extends Controller {

	protected $bank;

	function __construct(Bank $bank)
	{
		$this->bank = $bank;
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		$attr = [ 
            'title' => trans('app.bank')
        ];
        $filter = Input::all(); 
        unset($filter['_token']);
        $page = Input::get('page', 1);
        $perPage = config_db_cached('settings::backend_per_page');
        //$filter['cfp_id'] = Auth::user()->id;
        $pagiData = $this->bank->paginate($page, $perPage, $filter);

        $banks = new LengthAwarePaginator($pagiData->items, $pagiData->totalItems, $perPage, $page, [
            'path' => Paginator::resolveCurrentPath()
        ]);
        $banks->setPath("");
		$banks->appends($filter);
		
		return view('backend.bank.index', compact('banks'));
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		$attr = [ 
			'title' => trans('app.bank')
		];
		if(!Entrust::can(['add_bank'])){
			$attr += [ 
				'unauthorized_message' => trans('app.unauthorized_message')
			];
			return view('backend.auth.unauthorized', compact('attr'));
		}
		return view('backend.bank.create');
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		try {
			$input = Input::all();
            $this->bank->create($input);
            Notification::success( trans('app.bank_added') );
            return redirect('en/admin/bank');
        } catch (ValidationException $e) { //dd($e->getErrors());
            return view('backend.bank.create')->withErrors($e->getErrors());
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
		$data = $this->bank->find($id);
		$data['title'] = $data->title;
		$data['slug'] = $data->slug;
		$data['featured_image'] = $data->featured_image;
        $data['is_status'] = $data->is_status;
		
		return view('backend.bank.show', compact('data'));
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
			'title' => trans('app.bank')
		];
		if(!Entrust::can(['edit_bank'])){
			$attr += [ 
				'unauthorized_message' => trans('app.unauthorized_message')
			];
			return view('backend.auth.unauthorized', compact('attr'));
		}
		$data = $this->bank->find($id);
		return view('backend.bank.edit', compact('data'));
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
            $input = Input::all();
            $this->bank->update($id, $input);
            Notification::success( trans('app.bank_updated') );
            return redirect('en/admin/bank');
        } catch (ValidationException $e) {
            return view('backend.cfpSchedule.dayoffedit', [ 'id' => $id ])->withErrors($e->getErrors());
        }
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */

	public function confirmDestroy($id)
	{
		$attr = [ 
			'title' => trans('app.bank')
		];
		if(!Entrust::can(['delete_bank'])){
			$attr += [ 
				'unauthorized_message' => trans('app.unauthorized_message')
			];
			return view('backend.auth.unauthorized', compact('attr'));
		}
		$data = $this->bank->find($id);
		return view('backend.bank.confirm-destroy', compact('data'));
	}

	public function destroy($id)
	{
		$attr = [ 
            'title' => trans('app.bank')
        ];
        if(!Entrust::can(['delete_bank'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        if($this->bank->delete($id)){
            Notification::success(trans('app.bank_deleted'));
        }else{
            Notification::error(trans('app.delete_failed'));
        }
        
        return redirect('en/admin/bank');
	}

}
