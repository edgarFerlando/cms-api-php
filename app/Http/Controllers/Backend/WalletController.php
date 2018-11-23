<?php namespace App\Http\Controllers\Backend;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Repositories\Wallet\WalletInterface;
use App\Repositories\User\UserInterface;
use Redirect;
use View;
use Input;
use Validator;
use Response;
use Notification;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
//use App\Repositories\Wallet\WalletRepository as Wallet;
use App\Exceptions\Validation\ValidationException;
use Config;
use Entrust;

use App\User;
use App\Repositories\Taxonomy\TaxonomyInterface;
use Carbon\Carbon;

class WalletController extends Controller {

	protected $Wallet;
	protected $user;

    public function __construct(WalletInterface $Wallet, UserInterface $user, TaxonomyInterface $taxonomy) {
        $this->Wallet = $Wallet;
        $this->user = $user;
        $this->taxonomy = $taxonomy;
    }
	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{ 
		$attr = [ 
                'title' => trans('app.cfp_wallet')
            ];
        if(!Entrust::can(['read_wallet_transaction'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $page = Input::get('page', 1);
        $perPage = config_db_cached('settings::backend_per_page');
        $pagiData = $this->Wallet->paginate($page, $perPage, true);
        $transactions = new LengthAwarePaginator($pagiData->items, $pagiData->totalItems, $perPage, $page, [
            'path' => Paginator::resolveCurrentPath()
        ]);
        $transactions->setPath(''); 
		return view('backend.wallet.index', compact('transactions'));
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		$attr = [ 
                'title' => trans('app.wallet')
            ];
        
        if(!Entrust::can(['create_wallet_transaction'])){
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

        $wallet_categories_raw = $this->taxonomy->getTermsByPostType('wallet')->toHierarchy();
        $wallet_categories = [];
        $wallet_category_map = [];
        foreach ($wallet_categories_raw as $wallet_category_raw) {
            $wallet_category_map[$wallet_category_raw->title] = $wallet_category_raw->id;
            $wallet_categories[$wallet_category_raw->id] = $wallet_category_raw['children'];
        }
        //dd($wallet_category_map['Simple']);
        //dd($wallet_categories);
        //dd($wallet_category_map);
        //dd(renderLists($wallet_categories['Simple']));
        $transaction_types = $this->taxonomy->getTermsBy([ 'level' => 0 , 'post_type' => 'wallet' ]);
        $wallet_categories_simple_options[' '] = '-';
        $wallet_categories_simple_options += renderLists_antijsonparse($wallet_categories[$wallet_category_map['Simple']]);
        $wallet_categories_detail_options[' '] = '-';
        $wallet_categories_detail_options += renderLists_antijsonparse($wallet_categories[$wallet_category_map['Detail']]);
        $wallet_categories_ori_detail_options[' '] = '-';
        $wallet_categories_ori_detail_options += renderLists($wallet_categories[$wallet_category_map['Detail']]);

        $dataOptions = json_encode((object)[$wallet_category_map['Simple'] => $wallet_categories_simple_options, $wallet_category_map['Detail'] => $wallet_categories_detail_options]);
        $disableOptions = json_encode(array_keys(renderLists($this->taxonomy->getTermsBy([ 'level' => 1 , 'post_type' => 'wallet' ])->toHierarchy())));
        //dd($disableOptions);
        //$xx = rawurlencode(json_encode((object)['simple' => $wallet_categories_simple_options, 'detail' => $wallet_categories_detail_options]));
        //dd(json_decode(urldecode($xx)));

        //dd($wallet_categories_simple_options);

        //$wallet_category = $this->taxonomy->getTermsByPostType('wallet')->toHierarchy()->toArray();
        //dd($wallet_category);
        //$wallet_category_options[''] = '-';
        //$wallet_category_options += renderLists();
        return view('backend.wallet.create', compact('dataOptions', 'transaction_types', 'wallet_categories_ori_detail_options', 'wallet_categories_simple_options', 'wallet_categories_detail_options', 'wallet_categories', 'disableOptions'));
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
            $input = Input::all();
            //$transaction_type = $this->taxonomy->find($input['transaction_type']);
            //$input['transaction_type_id'] = is_null($category_info)?'':$category_info->id; 

            $input['transaction_date'] = trim($input['transaction_date']) == ''?'':Carbon::parse($input['transaction_date'])->format('Y-m-d'); //dd($input['wallet_category']);
            $category_info = trim($input['wallet_category']) == ''?'':$this->taxonomy->findWithParentDetail($input['wallet_category']); //dd($category_info);
            $input['category_type'] = is_null($category_info) || $category_info == '' ?'':$category_info->parentDetail->id;
            $input['amount'] = trim($input['amount']) == '' || trim($input['amount']) == 0?'':unformat_money($input['amount']); //dd($input);
            //dd($input['amount']);
            $this->Wallet->create($input);
            Notification::success( trans('app.wallet_added') );
            return Redirect::route('admin.wallet.index');
        } catch (ValidationException $e) {
            return Redirect::route('admin.wallet.create')->withInput()->withErrors($e->getErrors());
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
                'title' => trans('app.cfp_wallet')
            ];
        if(!Entrust::can(['update_wallet_transaction'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        //dd($id);
        $data = $this->Wallet->find($id); //dd($data);
        $transaction_date_raw = Carbon::parse($data->transaction_date);
        $data['transaction_date'] = $transaction_date_raw->format('d F Y');

        $wallet_categories_raw = $this->taxonomy->getTermsByPostType('wallet')->toHierarchy();
        $wallet_categories = [];
        $wallet_category_map = [];
        foreach ($wallet_categories_raw as $wallet_category_raw) {
            $wallet_category_map[$wallet_category_raw->title] = $wallet_category_raw->id;
            $wallet_categories[$wallet_category_raw->id] = $wallet_category_raw['children'];
        }
        //dd($wallet_category_map['Simple']);
        //dd($wallet_categories);
        //dd($wallet_category_map);
        //dd(renderLists($wallet_categories['Simple']));
        $transaction_types = $this->taxonomy->getTermsBy([ 'level' => 0 , 'post_type' => 'wallet' ]);
        $wallet_categories_simple_options[' '] = '-';
        $wallet_categories_simple_options += renderLists_antijsonparse($wallet_categories[$wallet_category_map['Simple']]);
        $wallet_categories_detail_options[' '] = '-';
        $wallet_categories_detail_options += renderLists_antijsonparse($wallet_categories[$wallet_category_map['Detail']]);
        $wallet_categories_ori_detail_options[' '] = '-';
        $wallet_categories_ori_detail_options += renderLists($wallet_categories[$wallet_category_map['Detail']]);

        $dataOptions = json_encode((object)[$wallet_category_map['Simple'] => $wallet_categories_simple_options, $wallet_category_map['Detail'] => $wallet_categories_detail_options]);
        $disableOptions = json_encode(array_keys(renderLists($this->taxonomy->getTermsBy([ 'level' => 1 , 'post_type' => 'wallet' ])->toHierarchy())));
        //dd($disableOptions);
        //$xx = rawurlencode(json_encode((object)['simple' => $wallet_categories_simple_options, 'detail' => $wallet_categories_detail_options]));
        //dd(json_decode(urldecode($xx)));

        //dd($wallet_categories_simple_options);

        //$wallet_category = $this->taxonomy->getTermsByPostType('wallet')->toHierarchy()->toArray();
        //dd($wallet_category);
        //$wallet_category_options[''] = '-';
        //$wallet_category_options += renderLists();
        //return view('backend.wallet.create', compact('dataOptions', 'transaction_types', 'wallet_categories_ori_detail_options', 'wallet_categories_simple_options', 'wallet_categories_detail_options', 'wallet_categories', 'disableOptions'));

        //$data['cfp_id'] = $data->cfp_id.'__'.$data->cfp->name; 
        //$data['client_id'] = $data->client_id.'__'.$data->client->name; //dd($data);
        /*$userCustomers[] = '-';
        $userCustomers += $this->user->listRoles('name', 'id', 5);

        $userCfps[] = '-';
        $userCfps += $this->user->listRoles('name', 'id', 6);*/
        //dd($testimonial);
        return view('backend.wallet.edit', compact('data', 'dataOptions', 'transaction_types', 'wallet_categories_ori_detail_options', 'wallet_categories_simple_options', 'wallet_categories_detail_options', 'wallet_categories', 'disableOptions'));//, 'userCustomers', 'userCfps'));
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
            $input = Input::all();
            $input['transaction_date'] = trim($input['transaction_date']) == ''?'':Carbon::parse($input['transaction_date'])->format('Y-m-d'); //dd($input['wallet_category']);
            $category_info = trim($input['wallet_category']) == ''?'':$this->taxonomy->findWithParentDetail($input['wallet_category']); //dd($category_info);
            $input['category_type'] = is_null($category_info) || $category_info == '' ?'':$category_info->parentDetail->id;
            $input['amount'] = trim($input['amount']) == '' || trim($input['amount']) == 0?'':unformat_money($input['amount']);
            $this->Wallet->update($id, $input);
            Notification::success(trans('app.wallet_updated'));
            return Redirect::route('admin.wallet.index');
        } catch (ValidationException $e) {
            return Redirect::route('admin.wallet.edit', [ 'id' => $id ] )->withInput()->withErrors($e->getErrors());
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
                'title' => trans('app.wallet')
            ];
        if(!Entrust::can(['delete_wallet_transaction'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $this->Wallet->delete($id);
        Notification::success(trans('app.wallet_deleted'));
        return Redirect::route('admin.wallet.index');
	}

	public function confirmDestroy($id) {
        $attr = [ 
                'title' => trans('app.wallet')
            ];
        if(!Entrust::can(['delete_wallet_transaction'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $data = $this->Wallet->find($id);
        //$user = $this->user->find($Wallet->client_id);
        //dd($user);
        return view('backend.wallet.confirm-destroy', compact('data'));
    }

}
