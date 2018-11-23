<?php namespace App\Http\Controllers\Backend;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Repositories\CfpClient\CfpClientInterface;
use App\Repositories\User\UserInterface;
use Redirect;
use View;
use Input;
use Validator;
use Response;
use Notification;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
//use App\Repositories\CfpClient\CfpClientRepository as CfpClient;
use App\Exceptions\Validation\ValidationException;
use Config;
use Entrust;

use App\User;

class CfpClientController extends Controller {

	protected $CfpClient;
	protected $user;

    public function __construct(CfpClientInterface $CfpClient, UserInterface $user) {
        $this->CfpClient = $CfpClient;
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
                'title' => trans('app.cfp_client')
            ];
        if(!Entrust::can(['read_cfp_client'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $page = Input::get('page', 1);
        $perPage = config_db_cached('settings::backend_per_page');
        $filter = Input::all(); 
        unset($filter['_token']); //dd($filter);
        $pagiData = $this->CfpClient->paginate($page, $perPage, $filter);
        $totalItems = $pagiData->totalItems;
        $cfpClients = new LengthAwarePaginator($pagiData->items, $pagiData->totalItems, $perPage, $page, [
            'path' => Paginator::resolveCurrentPath()
        ]);
        $cfpClients->setPath(''); //dd($cfpClients->count());
        $cfpClients->appends($filter);
        //dd($testimonials->count());

        //dd($codes);
		return view('backend.cfpClient.index', compact('cfpClients', 'totalItems'));
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
        //dd(url('/'));
        //dd(langUrl('/'));
		$attr = [ 
                'title' => trans('app.cfp_client')
            ];
        
        if(!Entrust::can(['create_cfp_client'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }

        /*$default_role_client = config_db_cached('settings::default_role_client');
        $default_role_cfp = config_db_cached('settings::default_role_cfp');
        $userCustomers[] = '-';
        $userCustomers += $this->user->listByRole('name', 'id', $default_role_client);

        $userCfps[] = '-';
        $userCfps += $this->user->listByRole('name', 'id', $default_role_cfp);*/
        //dd($categoryCodes);
        return view('backend.cfpClient.create');
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
            $this->CfpClient->create(Input::all());
            Notification::success( trans('app.cfp_client_added') );
            return langRedirectRoute('admin.cfp.client.index');
        } catch (ValidationException $e) {
            return langRedirectRoute('admin.cfp.client.create')->withInput()->withErrors($e->getErrors());
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
		$attr = [ 
                'title' => trans('app.cfp_client')
            ];
        if(!Entrust::can(['update_cfp_client'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $data = $this->CfpClient->find($id); //dd($data);
        $data['cfp_id'] = $data->cfp_id.'__'.$data->cfp->name; 
        $data['client_id'] = $data->client_id.'__'.$data->client->name; //dd($data);
        /*$userCustomers[] = '-';
        $userCustomers += $this->user->listRoles('name', 'id', 5);

        $userCfps[] = '-';
        $userCfps += $this->user->listRoles('name', 'id', 6);*/
        //dd($testimonial);
        return view('backend.cfpClient.edit', compact('data'));//, 'userCustomers', 'userCfps'));
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
            $this->CfpClient->update($id, Input::all());
            Notification::success(trans('app.cfp_client_updated'));
            return langRedirectRoute('admin.cfp.client.index');
        } catch (ValidationException $e) {
            return langRedirectRoute('admin.cfp.client.edit', [ 'id' => $id ] )->withInput()->withErrors($e->getErrors());
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
                'title' => trans('app.cfp_client')
            ];
        if(!Entrust::can(['delete_cfp_client'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $this->CfpClient->delete($id);
        Notification::success(trans('app.cfp_client_deleted'));
        return langRedirectRoute('admin.cfp.client.index');
	}

	public function confirmDestroy($id) {
        $attr = [ 
                'title' => trans('app.cfp_client')
            ];
        if(!Entrust::can(['delete_cfp_schedule'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $CfpClient = $this->CfpClient->find($id);
        //$user = $this->user->find($CfpClient->client_id);
        //dd($user);
        return view('backend.cfpClient.confirm-destroy', compact('CfpClient'));
    }

    function clients(){
        $search_name = Input::get('name');
        $cfp_id = Input::get('cfp_id');
        //\Cache::forever('settings::default_role_client', 'client'); //untuk clear menjadi yang terbaru
        $default_role_id_client = config_db_cached('settings::default_role_id_client');

        $filter  = [
            'client_name' => $search_name
        ];

        if($cfp_id != '')
            $filter['cfp_id'] = $cfp_id;

        $clients_raw = $this->CfpClient->getClientsByNameLike($filter); //var_dump($clients_raw->toArray());
        $clients_arr = [];
        foreach($clients_raw as $client_raw){
            $clients_arr[] = [ 
                'id' => $client_raw->id.'__'.$client_raw->name,
                'name' => $client_raw->name
            ];
        }
        return Response::json([
            'suggestions' => $clients_arr
        ]);
    }

    function cfps(){
        $search_name = Input::get('name');
        $default_role_id_cfp = config_db_cached('settings::default_role_id_cfp');
        $filter  = [
            'cfp_name' => $search_name
        ];
        $clients_raw = $this->CfpClient->getCfpsByNameLike($filter);
        $clients_arr = [];
        foreach($clients_raw as $client_raw){
            $clients_arr[] = [ 
                'id' => $client_raw->id.'__'.$client_raw->name,
                'name' => $client_raw->name
            ];
        }
        return $clients_arr;
    }

}
