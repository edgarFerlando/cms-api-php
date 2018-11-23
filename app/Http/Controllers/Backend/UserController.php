<?php namespace App\Http\Controllers\Backend;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Repositories\User\UserInterface;
use App\Repositories\Role\RoleInterface;
use Redirect;
use View;
use Input;
use Validator;
use Response;
use Notification;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use App\Repositories\User\UserRepository as User;
use App\Exceptions\Validation\ValidationException;
use Config;
use Auth;
use Entrust;
use URL;
use App\User as UserModel;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\Registrar;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use App\Repositories\Role\RoleRepository as Role;
use App\Repositories\Taxonomy\TaxonomyInterface;
use App\Repositories\CashflowAnalysis\CashflowAnalysisRepository;
use App\Repositories\PortfolioAnalysis\PortfolioAnalysisRepository;
use App\Repositories\PlanAnalysis\PlanAnalysisRepository;
use HTML;
use App\Models\ActiveVersionDetail;


class UserController extends Controller {

    protected $user;
    protected $role;
    protected $cashflowAnalysis;
    protected $portfolioAnalysis;
    protected $planAnalysis;

    public function __construct(UserInterface $user, Guard $auth, Registrar $registrar, Role $role, TaxonomyInterface $taxonomy, CashflowAnalysisRepository $cashflowAnalysis, PortfolioAnalysisRepository $portfolioAnalysis, PlanAnalysisRepository $planAnalysis) {
        $this->user = $user;
        $this->auth = $auth;
		$this->registrar = $registrar;
        $this->role = $role;
        $this->taxonomy = $taxonomy;
        $this->cashflowAnalysis =  $cashflowAnalysis;
        $this->portfolioAnalysis =  $portfolioAnalysis;
        $this->planAnalysis =  $planAnalysis;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index() { 
        $attr = [ 
                'title' => trans('app.all_users')
            ];
        if(!Entrust::can(['read_user'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $page = Input::get('page', 1);
        $perPage = config_db_cached('settings::backend_per_page');
        $filter = Input::all(); 
        $filter['record_flag_is_not'] = 'D';
        unset($filter['_token']);
        $pagiData = $this->user->paginate($page, $perPage, $filter); 
        $totalItems = $pagiData->totalItems;
        $users = new LengthAwarePaginator($pagiData->items, $pagiData->totalItems, $perPage, $page, [
            'path' => Paginator::resolveCurrentPath()
        ]); 
        $users->setPath("");
        $users->appends($filter);
        
        $roles[' '] = '-';
        $roles += $this->role->lists('display_name', 'id');

        $branches_raw = $this->taxonomy->getTermsByPostType('branch')->toHierarchy();
        $branch_options[' '] = '-';
        $branch_options += renderLists($branches_raw); 
            
        return view('backend.user.index', compact('users', 'roles', 'branch_options', 'totalItems'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create() {
        //dd(carbon_format_store('09 Dec 2017', $format = 'Y-m-d', $from_format = 'd M Y'));
        $attr = [ 
                'title' => trans('app.user')
            ];
        if(!Entrust::can(['create_user'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $roles[' '] = '-';
        $roles += $this->role->lists('display_name', 'id');

        $branches_raw = $this->taxonomy->getTermsByPostType('branch')->toHierarchy();
        
        $branch_options = renderLists($branches_raw); 
        
        
        $cutoff_date_options = range(0,31);
        $cutoff_date_options[0] = '';
        //$dataOptions = json_encode((object)[$wallet_category_map['Simple'] => $wallet_categories_simple_options, $wallet_category_map['Detail'] => $wallet_categories_detail_options]);
        //$disableOptions = json_encode(array_keys(renderLists($this->taxonomy->getTermsBy([ 'level' => 1 , 'post_type' => 'branch' ])->toHierarchy())));
        return view('backend.user.create', compact('roles', 'branch_options', 'cutoff_date_options'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */

    public function store() {
        try {
            $this->user->create(Input::all());
            Notification::success( trans('app.user_added') );
            return langRedirectRoute('admin.user.index');
        } catch (ValidationException $e) {
            return langRedirectRoute('admin.user.create')->withInput()->withErrors($e->getErrors());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function show($id) {
        $attr = [ 
                'title' => trans('app.user')
            ];
        if(!Entrust::can(['read_user'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $user = $this->user->find($id);
        $userMeta = userMeta($user->userMetas);
        $branch_name = !is_null($user->userMeta_branch)?($user->userMeta_branch->branch != '' ? $user->userMeta_branch->branch->title : '-'):'-';
        

        //$branch_name = '';
        $role_name = '';
        $role_id = '';
        foreach($user->roles as $role){
           $role_name = $role->display_name;
           $role_id = $role->id;
        } 
        //$branch_name = !is_null($user->userMeta_branch)?($user->userMeta_branch->branch != '' ? $user->userMeta_branch->branch->title : ''):'';
        //$userMetas = userMeta($user->userMetas);
        $default_role_id_client = config_db_cached('settings::default_role_id_client');
        $user_code = isset($userMeta->user_code)?$userMeta->user_code:(($role_id == $default_role_id_client)?HTML::link(langRoute('admin.user.edit', array($user->id)),trans('app.generate_code')):'-');
        $gender_name = isset($userMeta->gender)?gender($userMeta->gender):'-';
        $date_of_birth = isset($userMeta->date_of_birth)?dateonly_trans($userMeta->date_of_birth):'-';
        $address = isset($userMeta->address)?$userMeta->address:'-';
        $reference_code = isset($userMeta->reference_code)?$userMeta->reference_code:'-'; 
        $activation_code = isset($userMeta->activation_code)?$userMeta->activation_code:'-'; 

        return view('backend.user.show', compact('user', 'userMeta', 'branch_name', 'user_code', 'role_name', 'gender_name', 'date_of_birth', 'address', 'reference_code', 'activation_code'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function edit($id) { 
        $attr = [ 
                'title' => trans('app.user')
            ];
        if(!Entrust::can(['update_user'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $user = $this->user->find($id);
        $roles[] = '-';
        $roles += $this->role->lists('display_name', 'id');
        $userRoles = $user->roles();

        $userModel = UserModel::with('userMetas')->find($id);
        //dd($user);
        $userMeta = userMeta($userModel->userMetas);

        $branches_raw = $this->taxonomy->getTermsByPostType('branch')->toHierarchy();
        $branch_options[' '] = '-';
        $branch_options += renderLists($branches_raw); 

        $cutoff_date_options = range(0,31);
        $cutoff_date_options[0] = '';

        return view('backend.user.edit', compact('user', 'roles', 'userRoles', 'userMeta', 'branch_options', 'cutoff_date_options'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return Response
     */

    public function editPassword($id) { 
        $user = $this->user->find($id);
        $roles[] = '-';
        $roles += $this->role->lists('display_name', 'id');
        $userRoles = $user->roles();
        return view('backend.user.edit-password', compact('user', 'roles', 'userRoles'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function editTourguide($id) { 
        $attr = [ 
                'title' => trans('app.tour_guide')
            ];
        if(!Entrust::can(['update_tour_guide'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $user = $this->user->find($id);
        $userRoles = $user->roles();
        $userModel = UserModel::with('userMetas')->find($id);
        //dd($user);
        $userMeta = userMeta($userModel->userMetas);
        //dd($userMeta);
        return view('backend.user.edit-tourguide', compact('user', 'userRoles', 'userMeta'));
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  int $id
     * @return Response
     */
    public function update($id) {
        try {   
            $post_type = Input::get('post_type');
            //dd(Input::all());

            $this->user->update($id, Input::all());
            

            if($post_type == 'tour_guide')
            {
                Notification::success(trans('app.tourguide_updated'));
                return redirect(getLang().'/admin/dashboard');
            }
            else
            {
                Notification::success(trans('app.user_updated'));
                return langRedirectRoute('admin.user.index');
            }
                    
        } catch (ValidationException $e) {
            //Input::merge(array('date_of_birth' => Carbon::parse(Input::get('date_of_birth'), 'Y-m-d', 'd M Y')));
            return langRedirectRoute('admin.user.edit', [ 'id' => $id ] )->withInput()->withErrors($e->getErrors());
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int $id
     * @return Response
     */

    public function updatePassword($id) {
        try {   
            $this->user->updatePassword($id, Input::all());
            Notification::success(trans('app.user_updated'));

            return redirect(getLang().'/admin/dashboard');
        } catch (ValidationException $e) {
            return Redirect::to(getLang().'/admin/user/'.Auth::user()->id.'/edit/password')->withInput()->withErrors($e->getErrors());
        }
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return Response
     */
    public function destroy($id) {
        $attr = [ 
                'title' => trans('app.user')
            ];
        if(!Entrust::can(['delete_user'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $this->user->delete($id);
        Notification::success(trans('app.user_deleted'));
        return langRedirectRoute('admin.user.index');
    }

    public function confirmDestroy($id) {

        $user = $this->user->find($id);
        return view('backend.user.confirm-destroy', compact('user'));
    }

    public function generateCode($id) {
        $attr = [ 
                'title' => trans('app.user')
            ];
        if(!Entrust::can(['generate_code_user'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $res = $this->user->generateCode($id);
        if($res == 'branch_not_set')
            Notification::error(trans('app.user_code_generation_failed'));
        else
            Notification::success(trans('app.user_code_generated'));
        return langRedirectRoute('admin.user.index');
    }

    /*public function findByEmail($email){
        where $this->user->where('email', $email)->first();
    }*/

    public function goalsByUser($id)
    {
        $attr = [ 
                'title' => trans('app.user')
            ];
        if(!Entrust::can(['read_goal_grade'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $user = $this->user->findWithGoals($id);
        //dd($user);
        return view('backend.user.goals', compact('user'));
    }

    function clients(){
        $search_name = Input::get('name');
        //\Cache::forever('settings::default_role_client', 'client'); //untuk clear menjadi yang terbaru
        $default_role_id_client = config_db_cached('settings::default_role_id_client');
        $clients_raw = $this->user->findByName2($search_name, $default_role_id_client);
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
        $clients_raw = $this->user->findByName2($search_name, $default_role_id_cfp);
        $clients_arr = [];
        foreach($clients_raw as $client_raw){
            $clients_arr[] = [ 
                'id' => $client_raw->id.'__'.$client_raw->name,
                'name' => $client_raw->name
            ];
        }
        return $clients_arr;
    }

    public function historyFinancialCheckup($id) {
        $attr = [ 
                'title' => trans('app.user')
            ];
        if(!Entrust::can(['read_user'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $user = $this->user->find($id);
        $versions = $this->genVersions($user);

        //is role client
        /*$default_role_id_client = config_db_cached('settings::default_role_id_client');
        $user = $this->user->find($id);
        $role_id = $user->roles()->pluck('id');
        $versions = [];
        if($default_role_id_client == $role_id){
            //$versions = ActiveVersion::where('user_id', $id)->where('key', 'financialCheckup_cashflowAnalysis')->get();
            $version_details = ActiveVersionDetail::where('user_id', $id)->get();
            foreach($version_details as $version_detail){
                $versions[$version_detail->active_version_key]['user_id'] = $id;
                $versions[$version_detail->active_version_key]['alias'] = trans('app.'.$version_detail->active_version_key);
                $versions[$version_detail->active_version_key]['route'] = trans('routes.'.$version_detail->active_version_key);
                //dd($versions[$version_detail->active_version_key]['route']);
                $versions[$version_detail->active_version_key]['versions'][] = $version_detail->version;
            }
            //dd($versions);
        }*/
        return view('backend.user.history-analysis', compact('user', 'versions'));
    }

    public function genVersions($user){
        $default_role_id_client = config_db_cached('settings::default_role_id_client');
        $id = $user->id;
        $role_id = $user->roles()->pluck('id');
        $versions = [];
        if($default_role_id_client == $role_id){
            $version_details = ActiveVersionDetail::where('user_id', $id)->get();
            foreach($version_details as $version_detail){
                $versions[$version_detail->active_version_key]['user_id'] = $id;
                $versions[$version_detail->active_version_key]['alias'] = trans('app.'.$version_detail->active_version_key);
                $versions[$version_detail->active_version_key]['route'] = trans('routes.'.$version_detail->active_version_key);
                $versions[$version_detail->active_version_key]['versions'][] = $version_detail->version;
            }
        }
        return $versions;
    }

    public function showCashflowAnalysis($user_id, $version) {
        $attr = [ 
                'title' => trans('app.user')
            ];
        if(!Entrust::can(['read_user'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $user = $this->user->find($user_id);
        $versions = $this->genVersions($user);

        $data = $this->cashflowAnalysis->showByVersion([
            'user_id' => $user_id,
	        'version' => $version,
        ]);
        $history_title = 'Cashflow analysis - version '.$version;
        //dd($data);  
        return view('backend.user.cashflow-analysis', compact('user', 'versions', 'data', 'history_title'));
    }

    public function showPortfolioAnalysis($user_id, $version) {
        $attr = [ 
                'title' => trans('app.user')
            ];
        if(!Entrust::can(['read_user'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $user = $this->user->find($user_id);
        $versions = $this->genVersions($user);

        $data = $this->portfolioAnalysis->showByVersion([
            'user_id' => $user_id,
	        'version' => $version,
        ]);
        $history_title = 'Portfolio analysis - version '.$version;
        //dd($data);  
        return view('backend.user.portfolio-analysis', compact('user', 'versions', 'data', 'history_title'));
    }

    public function showPlanAnalysis($user_id, $version) {
        $attr = [ 
                'title' => trans('app.user')
            ];
        if(!Entrust::can(['read_user'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $user = $this->user->find($user_id);
        $versions = $this->genVersions($user);
       
        $data = $this->planAnalysis->showByVersion([
            'user_id' => $user_id,
            'version' => $version,
            'version_cashflow_analysis' => 0//ini harus di cek kembali hubungannya apa
        ]); 

        $data_cashflow = $this->cashflowAnalysis->showByVersion([
            'user_id' => $user_id,
	        'version' => $version//ini harus di cek kembali hubungannya apa
        ]);

        $history_title = 'Plan analysis - version '.$version;
        //dd($data);  
        return view('backend.user.plan-analysis', compact('user', 'versions', 'data', 'history_title', 'data_cashflow'));
    }

    public function showFinancialCheckup($id, $version) {
        $attr = [ 
                'title' => trans('app.user')
            ];
        if(!Entrust::can(['read_user'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }

        //is role client
        $default_role_id_client = config_db_cached('settings::default_role_id_client');
        $user = $this->user->find($id);
        $role_id = $user->roles()->pluck('id');
        $versions = [];
        if($default_role_id_client == $role_id){
            $versions = ActiveVersion::where('user_id', $id)->where('key', 'financialCheckup_cashflowAnalysis')->get();
            dd($versions);
        }

        return view('backend.user.history-financial-checkup', compact('user', 'versions'));
    }
}
