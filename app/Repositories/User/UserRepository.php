<?php namespace App\Repositories\User;

use App\User;
//use App\Models\Role;
use Config;
use Response;
use App\Repositories\RepositoryAbstract;
use App\Repositories\CrudableInterface as CrudableInterface;
use App\Repositories\RepositoryInterface as RepositoryInterface;
use App\Exceptions\Validation\ValidationException;
use App\Repositories\AbstractValidator as Validator;
use LaravelLocalization;
use Notification;
use Input;
use Hash;
use Auth;

use App\Models\UserMeta;
use App\Models\Role;
use App\Taxonomy;
use App\Models\CfpClient;
use App\Models\UserCodeCounter;
use App\Models\CfpCodeCounter;
//use App\Repositories\CfpClient\CfpClientRepository;
use DB;
use Carbon\Carbon;
use Route;
use JWTAuth;

class UserRepository extends RepositoryAbstract implements UserInterface, CrudableInterface {

    /**
     * @var
     */
    protected $perPage;
    /**
     * @var \Page
     */
    protected $user;
    /**
     * Rules
     *
     * @var array
     */
    protected static $rules;

    protected static $attributeNames;

    /**
     * @param User $user
     */
    public function __construct(User $user) {
        $this->perPage = Config::get('holiday.per_page');
        $this->user = $user;
        $this->cfpClient = new CfpClient;//new CfpClientRepository(new CfpClient);
        $rules_n_attributeNames = $this->rules();
        self::$rules = $rules_n_attributeNames['rules'];
        self::$attributeNames = $rules_n_attributeNames['attributeNames'];
    }

    public function rules(){
        $rules = array();
        $setAttributeNames = array();
        
        $default_role_id_client = config_db_cached('settings::default_role_id_client');
        $default_role_id_cfp = config_db_cached('settings::default_role_id_cfp');
        //dd(Input::all());
        $rules['name'] = 'required|max:255';
        if(Input::has('role'))
            $rules['role'] = 'required|exists:roles,id';

        if(Input::has('role') && ( Input::get('role') == $default_role_id_client || Input::get('role') == $default_role_id_cfp))
            $rules['branch'] = 'numeric|taxo_exist:branch,2';//'required|numeric|taxo_exist:branch,2';
            
        if(Input::has('role') && ( Input::get('role') == $default_role_id_client)){ 
            $rules['branch'] .= '|cfp_available';
            $rules['cutoff_date'] = 'numeric|min:1'; 
        }
            

        if(strpos(Route::currentRouteName(), 'admin.user.store') !== false){
            $rules['password'] = 'required';
            $rules['email'] = 'required|email|unique:users';
        }
        if(trim(Input::get('password')) != ''){
            $rules['password'] = 'confirmed';
            $rules['password_confirmation'] = 'same:password';
        }
//dd(Input::has('request_by'));
        

        if(Input::has('request_by') && Input::get('request_by') == 'api'){
            $rules['goal'] = '';
            
            if(trim(Input::get('date_of_birth')) != ''){ 
                $min_age = 0;//belom dimasukin ke config
                $before_date = Carbon::now()->subYears($min_age);//at least 10 year old
                //dd($before_date);
                $rules['date_of_birth'] = 'date_format:"Y-m-d"|before:'.$before_date;//  dd($before_date);
                // /dd(Input::get('date_of_birth'));
                //dd(strtotime($before_date));
               // if(Input::get('date_of_birth') > strtotime($before_date))
                //$setAttributeNames['date_of_birth'] = $min_age;
            }

            $user = User::where('id', Input::get('id'))->with(['userMetas', 'roles'])->first();
            foreach($user->roles as $role){
                $role_name = $role->display_name;
                $role_id = $role->id;
            } 

            if($role_id == $default_role_id_client){
                $rules['branch'] = 'cfp_available';
                $rules['cutoff_date'] = 'numeric'; 
            }

        }else{
            if(trim(Input::get('date_of_birth')) != ''){
                $min_age = 10;//belom dimasukin ke config
                $before_date = Carbon::now()->subYears($min_age);//at least 10 year old
                $rules['date_of_birth'] = 'date_format:"d M Y"|before:'.$before_date;
                //if($before_date < $min_age)
                    //$setAttributeNames['date_of_birth'] = $min_age;
            }
        }
    
        //if(Route::currentRouteName() == 'en.admin.user.create'){
        //    $rules['password'] = 'confirmed';
      //  }

        $setAttributeNames['name'] = trans('app.first_name');
        $setAttributeNames['password'] = trans('app.password');
        $setAttributeNames['role'] = trans('app.role');
        return [
            'rules' => $rules,
            'attributeNames' => $setAttributeNames
        ];
    }

    public function rulesPassword(){
        $rules = array();
        $setAttributeNames = array();
        $rules['password'] = 'required';
        if(trim(Input::get('password')) != '')
            $rules['password'] = 'required|confirmed';
            $rules['password_confirmation'] = 'required|same:password';
        $setAttributeNames['password'] = trans('app.password');
        return [
            'rules' => $rules,
            'attributeNames' => $setAttributeNames
        ];
    }

    public function rulesAddIBankAccount(){
        $rules = array();
        $setAttributeNames = array();

        $rules['user_id'] = 'required';
        $rules['bank_code'] = 'required';
        $rules['account_no'] = 'required';
        $rules['ibank_uid'] = 'required';
        $rules['ibank_pin'] = 'required';         

        //$setAttributeNames[''] = trans('app.password');
        $setAttributeNames['user_id'] = 'User Id';
        $setAttributeNames['bank_code'] = 'Bank Code';
        $setAttributeNames['account_no'] = 'Account No';
        $setAttributeNames['ibank_uid'] = 'User ID';
        $setAttributeNames['ibank_pin'] = 'Pin';
        return [
            'rules' => $rules,
            'attributeNames' => $setAttributeNames
        ];
    }

    /**
     * @return mixed
     */
    public function all() {

        return $this->user->orderBy('created_at', 'DESC')->get();
    }

    /**
     * @return mixed
     */
    public function lists($name = 'title', $id = 'id') {
        return $this->user->all()->lists($name, $id);
    }

    /**
     * @return mixed
     */
    public function listRoles($name, $id, $role) {
        return $this->user->with('roles')->whereHas('roles', function($q) use ($role){
            $q->where('id', $role);
        })->lists($name, $id);
    }

    public function listByRole($name, $id, $role) {//belum kelar
        return $this->user->with('roles')->whereHas('roles', function($q) use ($role){
            $q->whereRaw('LOWER(name) = ?', [ strtolower($role) ]);
        })->lists($name, $id);
    }

    /**
     * Get paginated menu groups
     *
     * @param int $page Number of pages per page
     * @param int $limit Results per page
     * @param boolean $all Show published or all
     * @return StdClass Object with $items and $totalItems for pagination
     */

    public function paginate($page = 1, $limit = 10, $filter = array()){//$all = false) {

        $result = new \StdClass;
        $result->page = $page;
        $result->limit = $limit;
        $result->totalItems = 0;
        $result->items = array();

        $query = $this->user
        ->select('users.*', 'users.id as user_id','uc.name as created_by_name', 'uu.name as updated_by_name')
        ->with([
            'cfp_clients' => function($q){
                $q->whereHas('client', function($q2){
                    $q2->where('is_active', 1);
                });
             },
            'roles', 
            'goalGrade', 
            'userMetas',
            'userMeta_branch' => function($q){
               $q->with('branch');
            }])
        ->orderBy('created_at', 'DESC');

        $query->join('users as uc', 'uc.id', '=', 'users.created_by', 'left');
        $query->join('users as uu', 'uu.id', '=', 'users.updated_by', 'left');

        if(is_array($filter)){
            foreach($filter as $ff => $term){
                if(trim($term) != ''){
                    switch ($ff) {
                        /*case 'with' :
                            $query->whereHas('productSpecialOffers', function($q) {
                                $q->havingRaw('COUNT(DISTINCT `product_id`) > 0');
                            });
                        break;*/
                        case 'record_flag_is_not':
                            $query->where('users.record_flag', '!=', $term);
                        break;
                        case 'user_code':
                            $query->whereHas('userMetas', function($q) use ($term) {
                                $q
                                ->where('meta_key', 'user_code')
                                ->where('meta_value', 'like', '%'.$term.'%');
                            });
                        break;
                        case 'branch_code':
                            $query->whereHas('userMetas', function($q) use ($term) {
                                $q
                                ->where('meta_key', 'branch')
                                ->where('meta_value', 'like', '%'.$term.'%');
                            });
                        break;
                        case 'name':
                            $query->whereHas('userMetas', function($q) use ($term) {
                                $q
                                ->where('meta_key', 'name');
                                //->where('meta_value', 'like', '%'.$term.'%');

                                $q->whereRaw('LOWER(meta_value) like ?', [ '%'.strtolower($term).'%' ]);
                            });
                        break;
                        case 'role':
                            $query->whereHas('role_user', function($q) use ($term) {
                                $q
                                ->where('role_id', $term);
                            });
                        break;
                        case 'status':
                            $query->where('users.is_active', $term);
                        break;
                    }
                }
            }
        }

        $users = $limit == 0? $query->get():$query->skip($limit * ($page - 1))->take($limit)->get();
        
        $result->totalItems = $this->totalUsers($filter);
        $result->items = $users->all(); //dd($result);
        return $result;
    }

    public function paginateTourguide($id, $page = 1, $limit = 10, $all = false) {

        $result = new \StdClass;
        $result->page = $page;
        $result->limit = $limit;
        $result->totalItems = 0;
        $result->items = array();

        $query = Role::find($id)->users()->with('userMetas')->orderBy('created_at', 'DESC');
        $users = $query->skip($limit * ($page - 1))->take($limit)->get();
        $result->totalItems = Role::find($id)->users()->with('userMetas')->count();
        $result->items = $users->all();

        return $result;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function find($id) {

        return $this->user
        ->select('users.*','uc.name as created_by_name', 'uu.name as updated_by_name', 'ud.name as deleted_by_name')
        ->with(['roles','userMetas', 'userMeta_branch'])
        ->join('users as uc', 'uc.id', '=', 'users.created_by', 'left')
        ->join('users as uu', 'uu.id', '=', 'users.updated_by', 'left')
        ->join('users as ud', 'ud.id', '=', 'users.deleted_by', 'left')
        ->find($id);

    }
    public function findNonDeleted($id) {
        return $this->user
        ->where('record_flag','!=','D')
        ->where('id',$id)
        ->first();
    }

    public function findWithMetas($id) {
        $user = $this->find($id);
        $userMeta = (array)userMeta($user->userMetas);
        $user_raw = $user->toArray();
        $user_raw = array_merge($user_raw, $userMeta);
        /*$user_raw['cfp'] = null;
        if(!is_null($user->cfpClient)){
            $userMetaCFP = (array)userMeta($user->cfpClient->cfp->userMetas, [ 'photo' => ['type' => 'image'] ]); //var_dump($userMetaCFP); exit;
            
            
            $user_raw['cfp'] = $user_raw['cfp_client']['cfp']; //var_dump($user_raw['cfp_client']['cfp']['user_metas']); exit;
            //$userMetaCFP = (array)userMeta($user_raw['cfp']['user_metas']); var_dump($userMetaCFP); exit;
            $user_raw['cfp'] = array_merge($user_raw['cfp'], $userMetaCFP);
        }*/

        //unset($user_raw['cfp_client']);
        unset($user_raw['user_metas']);
        //unset($user_raw['cfp']['user_metas']);
        
        return $user_raw;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function findByName($name, $role) {
        return $this->user->with(['roles','trips.productCategory'])->where('name', 'like', '%'.$name.'%')->whereHas('roles', function($q) use ($role){
            $q->where('id', $role);
        })->get();
    }

    public function findWithGoals($id) {
        return $this->user->with('goalGrade.goal', 'goalGrade.grade', 'goalGrade.createdBy', 'goalGrade.updatedBy')->find($id);
    }

    public function findByName2($name, $role_id) {
        return $this->user->with(['roles'])->whereRaw('LOWER(name) like ?', [ "%" . strtolower($name) . "%"])->whereHas('roles', function($q) use ($role_id){
            $q->where('id', $role_id);
        })->get();
    }

    public function findWithMyCFP($client_email) {
        return $this->user
        ->with(['cfpClient.cfp', 'userMetas'])->whereRaw('LOWER(email) = ?', [ strtolower($client_email)])
        ->first();
        //\DB::enableQueryLog();
       /*return $this->cfpClient->whereHas('cfpByClientID', function($q) use ($client_email){
            $q->whereRaw('LOWER(email) = ?', [ strtolower($client_email)]);
        })->get();*/
//dd(\DB::getQueryLog());

        
    }

    public function cfpClients($attributes) {
        $user_id = $attributes['user_id'];
        $modules_shown = !isset($attributes['modules'])?[]:$attributes['modules'];

        //predefined modules
        if(!empty($modules_shown)){
            $modules_shown = [ 'cfp_clients' ];
        }

        $all_with = [
            'cfp_clients' => function($query) {
                $query->with(['userMetas','client']);
            }
        ];

        if(!empty($modules_shown)){
            foreach (array_diff(array_keys($all_with), $modules_shown) as $module_shown) {
                unset($all_with[$module_shown]);
            }
        }

        return User::select('users.id as user_id', 'firebase_token')->with($all_with)->where('id', $user_id)->first();

        /*return $this->user
        ->with(['cfpClient.cfp', 'userMetas'])->whereRaw('LOWER(email) = ?', [ strtolower($client_email)])
        ->first();*/
    }
    
    public function autoassign_cfp($attributes){

        //cek dari table cfp client
        $cfpclient_cfps = DB::select('select cfp_id, count(cfp_id) from cfp_clients where cfp_id in (
            select users.id from users 
            left join user_metas on user_metas.user_id = users.id
            left join role_user on role_user.user_id = users.id
            where user_metas.meta_key = \'branch\' and 
            user_metas.meta_value = \''.$attributes['branch'].'\' and 
            users.is_active = \'1\' and
            role_user.role_id = '.$attributes['default_role_id_cfp'].'
        ) group by cfp_id order by count asc, cfp_id asc');

        /*$cfpclient_cfps_safe = [];
        foreach ($cfpclient_cfps as $key => $value) 
        {
            $cfpclient_cfps_safe[$value->cfp_id] = $value->count;

        }  */
        $cfpclient_cfps_safe = changekeyandval($cfpclient_cfps, 'cfp_id', 'count'); 
        //dd($cfpclient_cfps_safe);
       //dd($suitable_cfp);
        //gabungkan dengan table users tapi skip untuk cfp id yang di temukan dari kueri diatas
        //if(is_null($suitable_cfp)){
            $user_cfps = collect(DB::select('
                select users.id as cfp_id, 0 as count from users 
                left join user_metas on user_metas.user_id = users.id
                left join role_user on role_user.user_id = users.id
                where user_metas.meta_key = \'branch\' and 
                user_metas.meta_value = \''.$attributes['branch'].'\' and 
                users.is_active = \'1\' and
                role_user.role_id = '.$attributes['default_role_id_cfp'].'
                and users.id not in (\'599\', \'639\')
                order by users.id asc'));
        //}

        $user_cfps_safe = changekeyandval($user_cfps, 'cfp_id', 'count'); 
        //dd($user_cfps_safe);

        $cfp_all = [];
        foreach ($user_cfps_safe as $user_cfp_id => $user_cfp_count) {
            $cfp_all[$user_cfp_id] = in_array($user_cfp_id, array_keys($cfpclient_cfps_safe))?$cfpclient_cfps_safe[$user_cfp_id]:$user_cfp_count;
        }

        /*$cfp_all = [
            94 => 0,
            132 =>1,
            45 => 5
        ];*/    
        //diurutkan berdasarkan value(jumlah), lalu key (cfp id)
        ksort($cfp_all);
        arsort($cfp_all);
        asort($cfp_all);
        //dd($cfp_all);
        $suitable_cfp_id = array_keys($cfp_all)[0];
        //$suitable_cfp_all = array_diff($cfpclient_cfps_safe, $user_cfps_safe);       
        //dd($suitable_cfp_all);
        //$resultxxx=array_intersect($suitable_cfp,$suitable_cfp_base);
        //dd($resultxxx);
        
        //tambahkan log history cfp yang pernah terassign ke client tertentu
        if($suitable_cfp_id){
            //$suitable_cfp_id = $suitable_cfp->cfp_id;

            $cfp_client = CfpClient::where('client_id', $attributes['client_id']);

            $t_attributes = [
                'client_id' => $attributes['client_id'],
                'cfp_id' => $suitable_cfp_id,
                'notes' => 'Auto assigned by system',
                'updated_by' => $attributes['user_id'], 
                'updated_at' => Carbon::now(),
            ];
            if($cfp_client->count() == 0){
                $t_attributes += [
                    'created_by' => $attributes['user_id'], 
                    'created_at' => Carbon::now(), 
                    'record_flag' => 'N'
                ];
                $this->cfpClient->fill($t_attributes)->save();
            }else{
                $t_attributes += [
                    'record_flag' => 'U'
                ];
                $cfp_client->update($t_attributes);
            }
        }









        /**
         * ----------------------------------------------
         * Membuat relasi Client dan CFE by Reference
         * ----------------------------------------------
         * 23 Juli 2018 
         * Gugun Dwi Permana
         * 
         * Script dalam tag ini digunakan untuk memilih CFE berdasarkan reference
         * Pilihan Customor sendiri untuk memilih CFP nya
         * bukan berdasarkan auto assign.
         *
         * Systemnya pertama di tabel CFP_CLIENTS awalnya dipilihkan CFP nya, secara auto asign
         * tapi dengan script di bawah ini maka CFP akan di update dengan CFP pilihan client
         *
         **/


        // Cek apakah CLIENT memilih CFP nya sendiri dengan REFERENCE_CODE? jika iya hasil = 1
        $count = DB::table('user_metas')
            ->where('user_id', '=', $attributes['client_id'])
            ->where('meta_key', '=' ,'reference_code')
            ->where('meta_value', '<>', '')
            ->count();

        if($count == '1') {

            /**
             * -----------------------------------------------------------------
             * Mengambil ID_REFERENCE nya.
             * dari tabel USER_METAS berdasarkan USER_ID (client)
             *
             * Script query dibawah berfungsi untuk mengambil REFERENCE_CODE dari USER_ID
             *
             **/

            $client = UserMeta::where('user_id', '=', $attributes['client_id'])
                ->where('meta_key', '=' ,'reference_code')
                ->where('meta_value', '<>', '')->get();

            // di berikan fungsi trim, lower
            $reference_code = str_replace(' ', '', strtolower($client[0]->meta_value));


            /**
             * -----------------------------------------------------------------
             * Cek apakan ID_REFERENCE yang dipilih CLIENT dimiliki satu CFE
             *
             * Kita cari di tabel USER_METAS, ROLE_USER.
             * Apakah REFERENCE_CODE trsebut dimiliki CFE
             *
             * Script query di bawah digunakan untuk mengambil CFP yang memiliki reference_code tersebut
             *
             **/
            
            $fcp_count = DB::table('user_metas')
                    ->join('role_user', 'user_metas.user_id', '=', 'role_user.user_id')
                    ->join('roles', 'role_user.role_id', '=', 'roles.id')
                    ->where('user_metas.meta_key', '=', 'reference_code')
                    ->where('user_metas.meta_value', '=', $reference_code)
                    ->where('roles.display_name', '=', 'Certified Financial Planner')
                    ->count();


            // Jika $cfp_id tidak lebih besar dari 0 (artinya REFERENCE_CODE memiliki CFP)
            if($fcp_count > 0) {

                $fcp = DB::table('user_metas')
                    ->join('role_user', 'user_metas.user_id', '=', 'role_user.user_id')
                    ->join('roles', 'role_user.role_id', '=', 'roles.id')
                    ->where('user_metas.meta_key', '=', 'reference_code')
                    ->where('user_metas.meta_value', '=', $reference_code)
                    ->where('roles.display_name', '=', 'Certified Financial Planner')
                    ->get();

                // ini CFP yang memiliki REFERENCE_CODE tersebut
                $cfp_id = $fcp[0]->user_id;


                /**
                 * -----------------------------------------------------------------
                 * Pasangkan CLINET dengan CFE yang memiliki ID_REFERENCE tersebut
                 *
                 **/

                $cfp_client = CfpClient::where('client_id', $attributes['client_id']);
                   
                $t_attributes = [
                    'cfp_id'    => $cfp_id,
                    'notes'     => 'Assign by Reference Code'
                ];

                $cfp_client->update($t_attributes);

            } else {

                $cfp_client = CfpClient::where('client_id', $attributes['client_id']);
                   
                $t_attributes = [
                    'notes'     => 'CFP dari Reference Code '.$reference_code.' Tidak ditemukan, Auto assigned by system'
                ];

                $cfp_client->update($t_attributes);

            }
        }

        /**
         * ----------------------------------------------
         * END Relasi Client CFP by Reference Code
         *
         **/


    }

    public function autoassign_cfp_register($attributes){

        //cek dari table cfp client
        $cfpclient_cfps = DB::select('select cfp_id, count(cfp_id) from cfp_clients where cfp_id in (
            select users.id from users 
            left join user_metas on user_metas.user_id = users.id
            left join role_user on role_user.user_id = users.id
            where 
            users.is_active = \'1\' and
            role_user.role_id = '.$attributes['default_role_id_cfp'].'
        ) group by cfp_id order by count asc, cfp_id asc');

        /*$cfpclient_cfps_safe = [];
        foreach ($cfpclient_cfps as $key => $value) 
        {
            $cfpclient_cfps_safe[$value->cfp_id] = $value->count;

        }  */
        $cfpclient_cfps_safe = changekeyandval($cfpclient_cfps, 'cfp_id', 'count'); 
        //dd($cfpclient_cfps_safe);
       //dd($suitable_cfp);
        //gabungkan dengan table users tapi skip untuk cfp id yang di temukan dari kueri diatas
        //if(is_null($suitable_cfp)){
            $user_cfps = collect(DB::select('
                select users.id as cfp_id, 0 as count from users 
                left join user_metas on user_metas.user_id = users.id
                left join role_user on role_user.user_id = users.id
                where 
                users.is_active = \'1\' and
                role_user.role_id = '.$attributes['default_role_id_cfp'].'
                and users.id not in (\'599\', \'639\')
                order by users.id asc'));
        //}

        $user_cfps_safe = changekeyandval($user_cfps, 'cfp_id', 'count'); 
        //dd($user_cfps_safe);

        $cfp_all = [];
        foreach ($user_cfps_safe as $user_cfp_id => $user_cfp_count) {
            $cfp_all[$user_cfp_id] = in_array($user_cfp_id, array_keys($cfpclient_cfps_safe))?$cfpclient_cfps_safe[$user_cfp_id]:$user_cfp_count;
        }

        /*$cfp_all = [
            94 => 0,
            132 =>1,
            45 => 5
        ];*/    
        //diurutkan berdasarkan value(jumlah), lalu key (cfp id)
        ksort($cfp_all);
        arsort($cfp_all);
        asort($cfp_all);
        //dd($cfp_all);
        $suitable_cfp_id = array_keys($cfp_all)[0];
        //$suitable_cfp_all = array_diff($cfpclient_cfps_safe, $user_cfps_safe);       
        //dd($suitable_cfp_all);
        //$resultxxx=array_intersect($suitable_cfp,$suitable_cfp_base);
        //dd($resultxxx);
        
        //tambahkan log history cfp yang pernah terassign ke client tertentu
        if($suitable_cfp_id){
            //$suitable_cfp_id = $suitable_cfp->cfp_id;

            $cfp_client = CfpClient::where('client_id', $attributes['client_id']);

            $t_attributes = [
                'client_id' => $attributes['client_id'],
                'cfp_id' => $suitable_cfp_id,
                'notes' => 'Auto assigned by system',
                'updated_by' => $attributes['user_id'], 
                'updated_at' => Carbon::now(),
            ];
            if($cfp_client->count() == 0){
                $t_attributes += [
                    'created_by' => $attributes['user_id'], 
                    'created_at' => Carbon::now(), 
                    'record_flag' => 'N'
                ];
                $this->cfpClient->fill($t_attributes)->save();
            }else{
                $t_attributes += [
                    'record_flag' => 'U'
                ];
                $cfp_client->update($t_attributes);
            }
        }









        /**
         * ----------------------------------------------
         * Membuat relasi Client dan CFE by Reference
         * ----------------------------------------------
         * 23 Juli 2018 
         * Gugun Dwi Permana
         * 
         * Script dalam tag ini digunakan untuk memilih CFE berdasarkan reference
         * Pilihan Customor sendiri untuk memilih CFP nya
         * bukan berdasarkan auto assign.
         *
         * Systemnya pertama di tabel CFP_CLIENTS awalnya dipilihkan CFP nya, secara auto asign
         * tapi dengan script di bawah ini maka CFP akan di update dengan CFP pilihan client
         *
         **/


        // Cek apakah CLIENT memilih CFP nya sendiri dengan REFERENCE_CODE? jika iya hasil = 1
        $count = DB::table('user_metas')
            ->where('user_id', '=', $attributes['client_id'])
            ->where('meta_key', '=' ,'reference_code')
            ->where('meta_value', '<>', '')
            ->count();

        if($count == '1') {

            /**
             * -----------------------------------------------------------------
             * Mengambil ID_REFERENCE nya.
             * dari tabel USER_METAS berdasarkan USER_ID (client)
             *
             * Script query dibawah berfungsi untuk mengambil REFERENCE_CODE dari USER_ID
             *
             **/

            $client = UserMeta::where('user_id', '=', $attributes['client_id'])
                ->where('meta_key', '=' ,'reference_code')
                ->where('meta_value', '<>', '')->get();

            // di berikan fungsi trim, lower
            $reference_code = str_replace(' ', '', strtolower($client[0]->meta_value));


            /**
             * -----------------------------------------------------------------
             * Cek apakan ID_REFERENCE yang dipilih CLIENT dimiliki satu CFE
             *
             * Kita cari di tabel USER_METAS, ROLE_USER.
             * Apakah REFERENCE_CODE trsebut dimiliki CFE
             *
             * Script query di bawah digunakan untuk mengambil CFP yang memiliki reference_code tersebut
             *
             **/
            
            $fcp_count = DB::table('user_metas')
                    ->join('role_user', 'user_metas.user_id', '=', 'role_user.user_id')
                    ->join('roles', 'role_user.role_id', '=', 'roles.id')
                    ->where('user_metas.meta_key', '=', 'reference_code')
                    ->where('user_metas.meta_value', '=', $reference_code)
                    ->where('roles.display_name', '=', 'Certified Financial Planner')
                    ->count();


            // Jika $cfp_id tidak lebih besar dari 0 (artinya REFERENCE_CODE memiliki CFP)
            if($fcp_count > 0) {

                $fcp = DB::table('user_metas')
                    ->join('role_user', 'user_metas.user_id', '=', 'role_user.user_id')
                    ->join('roles', 'role_user.role_id', '=', 'roles.id')
                    ->where('user_metas.meta_key', '=', 'reference_code')
                    ->where('user_metas.meta_value', '=', $reference_code)
                    ->where('roles.display_name', '=', 'Certified Financial Planner')
                    ->get();

                // ini CFP yang memiliki REFERENCE_CODE tersebut
                $cfp_id = $fcp[0]->user_id;


                /**
                 * -----------------------------------------------------------------
                 * Pasangkan CLINET dengan CFE yang memiliki ID_REFERENCE tersebut
                 *
                 **/

                $cfp_client = CfpClient::where('client_id', $attributes['client_id']);
                   
                $t_attributes = [
                    'cfp_id'    => $cfp_id,
                    'notes'     => 'Assign by Reference Code'
                ];

                $cfp_client->update($t_attributes);

            } else {

                $cfp_client = CfpClient::where('client_id', $attributes['client_id']);
                   
                $t_attributes = [
                    'notes'     => 'CFP dari Reference Code '.$reference_code.' Tidak ditemukan, Auto assigned by system'
                ];

                $cfp_client->update($t_attributes);

            }
        }

        /**
         * ----------------------------------------------
         * END Relasi Client CFP by Reference Code
         *
         **/


    }








    /**
     * @param $attributes
     * @return bool|mixed
     * @throws \App\Exceptions\Validation\ValidationException
     */
    public function create($attributes) {
        
        if($this->isValid($attributes)) {
            DB::beginTransaction();
            $user_id = isset($attributes['user_id'])?$attributes['user_id']:Auth::user()->id;
            $attributes['is_active'] = isset($attributes['is_active']) ? 1 : 0;
            $attributes['certificate_no'] = isset($attributes['certificate_no']) ? $attributes['certificate_no'] : 0;
            $attributes['description'] = $attributes['description'];
            $attributes['email'] = strtolower($attributes['email']); 
            $default_role_id_client = config_db_cached('settings::default_role_id_client');
            $default_role_id_cfp = config_db_cached('settings::default_role_id_cfp');

            $meta_map = [];
            /* if(trim($attributes['reference_code']) == ''){
                $attributes['reference_code'] = 0;
             }

             if(trim($attributes['branch']) == ''){
                $attributes['branch'] = 0;
            }*/

            $attributes['reference_code'] = !isset($attributes['reference_code'])?0:(trim($attributes['reference_code']) == ''?0:$attributes['reference_code']);
            $attributes['branch'] = !isset($attributes['branch'])?0:(trim($attributes['branch']) == ''?0:$attributes['branch']);
            $attributes['bca_acc'] = !isset($attributes['bca_acc'])?0:(trim($attributes['bca_acc']) == ''?0:$attributes['bca_acc']);

                        //dd($attributes['user_code']);
            //dd($attributes['post_type']);
            //$this->user = $this->find($id);
            if($attributes['password'] == '')
                unset($attributes['password']);
            else
                $attributes['password'] = Hash::make($attributes['password']);

            //dd($is_active);
            //$attributes['is_active'] = $is_active;
            $attributes += [
                'created_by' => $user_id,
                'created_at' => Carbon::now(),
                'updated_by' => $user_id,
                'updated_at' => Carbon::now()
            ];

            $userData = $this->user->create($attributes);

            //dd($userData->id);
            $id = $userData->id;
            
            $this->user = $this->find($id);

            if($attributes['role'] != '')
            {
                $this->user->roles()->sync([$attributes['role']]);
            }
            else
            {
                $this->user->roles()->sync([]);
            }

            //$attributes['branch'] = !isset($attributes['branch'])?0:($attributes['branch'] == ' '?0:$attributes['branch']);
            //$attributes['bca_acc'] = !isset($attributes['bca_acc'])?0:($attributes['bca_acc'] == ' '?0:$attributes['bca_acc']);
            //dd($attributes);
            
           
            if(isset($attributes['branch']) && $attributes['branch'] != 0){
            //if($attributes['is_active'] == 1){//harus activated
                if($attributes['role'] === $default_role_id_client ){ //harus user client
                    $term = Taxonomy::where('id', $attributes['branch'])->with('taxoMetas')->first();
                    $taxoMeta = userMeta($term->taxoMetas);
                    $branch_code = $taxoMeta->branch_code;
                    $date_code = carbon_now_format('dmY');
                    //++$date_code = new Carbon('13-11-2017');
                    //++$date_code = $date_code->format('dmY');//tes
                    //dd($date_code);
                    //dd($branch_code);
                    //dd($date_code);
                    $userCodeCounter = UserCodeCounter::where('branch_code', $branch_code)->where('date_code', $date_code)->first();
                    $usercode_last_number = is_null($userCodeCounter)?1:$userCodeCounter->last_number+1;
                    //dd($usercode_last_number);
                    $user_code = gen_client_code($branch_code, $date_code, $usercode_last_number);
                    //$attributes['user_code'] = $user_code;
                    $this->updateUserCodeCounter($branch_code, $date_code, $usercode_last_number);//update version, langsung update supaya tidak terlewat
                    $meta_map += ['user_code' => [ 'meta_key' => 'user_code' , 'meta_value' => $user_code , 'type' => 'text' ]];
                }

                if($attributes['role'] === $default_role_id_cfp ){ //harus user cfp
                    $term = Taxonomy::where('id', $attributes['branch'])->with('taxoMetas')->first();
                    $taxoMeta = userMeta($term->taxoMetas);
                    $branch_code = $taxoMeta->branch_code;
                    //$date_code = carbon_now_format('dmY');
                    $cfpCodeCounter = CfpCodeCounter::where('branch_code', $branch_code)->first();
                    $cfpcode_last_number = is_null($cfpCodeCounter)?1:$cfpCodeCounter->last_number+1;
                    
                    $cfp_code = gen_cfp_code($branch_code, $cfpcode_last_number);
                    //$attributes['user_code'] = $cfp_code;
                    $this->updateCFPCodeCounter($branch_code, $cfpcode_last_number);//update version, langsung update supaya tidak terlewat
                    $meta_map += ['user_code' => [ 'meta_key' => 'user_code' , 'meta_value' => $cfp_code , 'type' => 'text' ]];
                }

                //update branch
                if($attributes['role'] == $default_role_id_client){
                    $t_attributes = [
                        'user_id' => $user_id,
                        'client_id' => $id,
                        'notes' => 'Auto assigned by system',
                        'branch' => $attributes['branch'],
                        'default_role_id_cfp' => $default_role_id_cfp,
                        'record_flag' => 'N'
                    ];

                    $this->autoassign_cfp($t_attributes);
                }
            }

            $meta_map += [
                'full_name'         => [ 'meta_key' => 'full_name'      , 'meta_value' => $attributes['full_name']        , 'type' => 'text' ],
                'name'              => [ 'meta_key' => 'name'           , 'meta_value' => $attributes['name']             , 'type' => 'text' ],
                'last_name'         => [ 'meta_key' => 'last_name'      , 'meta_value' => $attributes['last_name']        , 'type' => 'text' ],
                'phone'             => [ 'meta_key' => 'phone'          , 'meta_value' => $attributes['phone']            , 'type' => 'text' ],
                'gender'            => [ 'meta_key' => 'gender'         , 'meta_value' => $attributes['gender']           , 'type' => 'text' ],
                'date_of_birth'     => [ 'meta_key' => 'date_of_birth'  , 'meta_value' => $attributes['date_of_birth']    , 'type' => 'dateFormatYmd' ],
                'address'           => [ 'meta_key' => 'address'        , 'meta_value' => $attributes['address']          , 'type' => 'text' ],
                'branch'            => [ 'meta_key' => 'branch'         , 'meta_value' => $attributes['branch']           , 'type' => 'text' ],
                'reference_code'    => [ 'meta_key' => 'reference_code' , 'meta_value' => $attributes['reference_code']   , 'type' => 'text' ],
                'photo'             => [ 'meta_key' => 'photo'          , 'meta_value' => $attributes['photo']            , 'type' => 'image' ],
                'bca_acc'           => [ 'meta_key' => 'bca_acc'        , 'meta_value' => $attributes['bca_acc']          , 'type' => 'text' ]
            ];

            userMeta_store($id, $meta_map, false);
            //++$this->updateUserCodeCounter($branch_code, $date_code, $usercode_last_number);//update version

            DB::commit();
            return true;
        }
        //dd($this->getErrors());
        throw new ValidationException('User validation failed', $this->getErrors());
    }

    public function userMetaStoreJUNKK($meta_map, $attributes){
        foreach($meta_map as $ff_name => $meta_key){

            if($meta_key == 'user_thumbnail')
            {
                $attributes[$ff_name] = getImagePath($attributes['user_thumbnail']);
            }

            if($meta_key == 'user_image')
            {
                $attributes[$ff_name] = getImagePath($attributes['user_image']);
                //dd($post[$ff_name]);
            }

            if($meta_key == 'ktp_image')
            {
                $attributes[$ff_name] = getImagePath($attributes['ktp_image']);
            }

            if($meta_key == 'name')
            {
                $attributes['first_name'] = $attributes['name'];
            }

            if($meta_key == 'date_of_birth')
            {
                $attributes[$ff_name] = carbon_format_store($attributes['date_of_birth'], $format = 'Y-m-d', $from_format = 'd M Y');
            }

            UserMeta::create([
                'user_id' => $id,
                'meta_key' => $meta_key,
                'meta_value' => $attributes[$ff_name] 
            ]);   
        }
    }

    public function updateUserCodeCounter($branch_code, $date_code, $last_number){
        $user_id = Auth::id();
        if($last_number == 1){
            UserCodeCounter::insert([
                'branch_code' => $branch_code,
                'date_code' => $date_code,
                'last_number' => $last_number,
                'created_by' => $user_id, 
                'created_at' => Carbon::now(), 
                'updated_by' => $user_id, 
                'updated_at' => Carbon::now(),
            ]);
        }else{
            UserCodeCounter::where('branch_code', $branch_code)->where('date_code', $date_code)->update([
                'last_number' => $last_number,
                'updated_by' => $user_id, 
                'updated_at' => Carbon::now(),
            ]);
        }
    }

    private function updateCfpCodeCounter($branch_code, $last_number){
        $user_id = Auth::id();
        if($last_number == 1){
            CfpCodeCounter::insert([
                'branch_code' => $branch_code,
                'last_number' => $last_number,
                'created_by' => $user_id, 
                'created_at' => Carbon::now(), 
                'updated_by' => $user_id, 
                'updated_at' => Carbon::now(),
            ]);
        }else{
            CfpCodeCounter::where('branch_code', $branch_code)->update([
                'last_number' => $last_number,
                'updated_by' => $user_id, 
                'updated_at' => Carbon::now(),
            ]);
        }
    }

    function genUserCode($params){


        $term = Taxonomy::where('id', $attributes['branch'])->with('taxoMetas')->first();
        $taxoMeta = userMeta($term->taxoMetas);
        $branch_code = $taxoMeta->branch_code;
        $date_code = carbon_now_format('dmY');
        //++$date_code = new Carbon('13-11-2017');
        //++$date_code = $date_code->format('dmY');//tes
        //dd($date_code);
        //dd($branch_code);
        //dd($date_code);
        $userCodeCounter = UserCodeCounter::where('branch_code', $branch_code)->where('date_code', $date_code)->first();
        $usercode_last_number = is_null($userCodeCounter)?1:$userCodeCounter->last_number+1;
        //dd($usercode_last_number);
        $user_code = gen_client_code($branch_code, $date_code, $usercode_last_number);
        $attributes['user_code'] = $user_code;
        $this->updateUserCodeCounter($branch_code, $date_code, $usercode_last_number);//update version, langsung update supaya tidak terlewat
        $meta_map += ['user_code' => 'user_code'];
    }

    public function createApi($attributes) {

        if($this->isValid($attributes)) {

                $meta_map = [
                    'full_name' => 'full_name',
                    'name' => 'name',
                    'last_name' => 'last_name',
                    'phone' => 'phone',
                    'gender' => 'gender',
                    'date_of_birth' => 'date_of_birth',
                    /*'country' => 'country',
                    'city' => 'city',
                    'post_code' => 'post_code',*/
                    'address' => 'address'
                    /*'user_thumbnail' => 'user_thumbnail',
                    'user_image' => 'user_image',
                    'ktp_image' => 'ktp_image'*/
                ];

                foreach($meta_map as $ff_name => $meta_key){

                        if($meta_key == 'user_thumbnail')
                        {
                            $attributes[$ff_name] = getImagePath($attributes['user_thumbnail']);
                        }

                        if($meta_key == 'user_image')
                        {
                            $attributes[$ff_name] = getImagePath($attributes['user_image']);
                            //dd($post[$ff_name]);
                        }

                        if($meta_key == 'ktp_image')
                        {
                            $attributes[$ff_name] = getImagePath($attributes['ktp_image']);
                        }

                        if($meta_key == 'name')
                        {
                            $attributes['first_name'] = $attributes['name'];
                        }
                }

            return true;
        }

        throw new ValidationException('User validation failed', $this->getErrors());
    }

    public function update($id, $attributes) { // harus dismakan cara nya dengan taxonomy library create
        if($this->isValid($attributes)) {
            DB::beginTransaction();
            //dd($attributes['post_type']);
            $user_id = isset($attributes['user_id'])?$attributes['user_id']:Auth::user()->id;

            $default_role_id_client = config_db_cached('settings::default_role_id_client');
            $default_role_id_cfp = config_db_cached('settings::default_role_id_cfp');

            $existing_metas_q = UserMeta::where('user_id', $id)->get();
            $attributes['is_active'] = isset($attributes['is_active']) ? 1 : 0;
            $attributes['certificate_no'] = isset($attributes['certificate_no']) ? $attributes['certificate_no'] : 0;
            $attributes['description'] = $attributes['description'];
            $existing_metas = userMeta($existing_metas_q);
            $meta_map = [];

            $attributes['reference_code'] = !isset($attributes['reference_code'])?0:(trim($attributes['reference_code']) == ''?0:$attributes['reference_code']);
            $attributes['branch'] = !isset($attributes['branch'])?0:(trim($attributes['branch']) == ''?0:$attributes['branch']);
            $attributes['bca_acc'] = !isset($attributes['bca_acc'])?0:(trim($attributes['bca_acc']) == ''?0:$attributes['bca_acc']);
            
            $this->user = $this->find($id);
            if($attributes['password'] == '')
                unset($attributes['password']);
            else
                $attributes['password'] = Hash::make($attributes['password']);

            $attributes += [
                'updated_by' => $user_id,
                'updated_at' => Carbon::now()
            ];

            $this->user->fill($attributes)->save();

            if($attributes['role'] != '')
            {
                $this->user->roles()->sync([$attributes['role']]);
            }
            else
            {
                $this->user->roles()->sync([]);
            }
                
            // dd($meta_map);
            
            //if($attributes['is_active'] == 1){ 
            if(isset($attributes['branch']) && $attributes['branch'] != 0){ 
                if(!isset($existing_metas->user_code) && $attributes['role'] == $default_role_id_client){
                    //$meta_map += [ 'user_code' => 'user_code' ];
                    $term = Taxonomy::where('id', $attributes['branch'])->with('taxoMetas')->first();
                    $taxoMeta = userMeta($term->taxoMetas);
                    $branch_code = $taxoMeta->branch_code;
                    $date_code = carbon_now_format('dmY');
                    $userCodeCounter = UserCodeCounter::where('branch_code', $branch_code)->where('date_code', $date_code)->first();
                    $usercode_last_number = is_null($userCodeCounter)?1:$userCodeCounter->last_number+1;
                    
                    $user_code = gen_client_code($branch_code, $date_code, $usercode_last_number);
                    //$attributes['user_code'] = $user_code;
                    $this->updateUserCodeCounter($branch_code, $date_code, $usercode_last_number);//update version, langsung update supaya tidak terlewat
                    $meta_map += ['user_code' => [ 'meta_key' => 'user_code' , 'meta_value' => $user_code , 'type' => 'text' ]];
                }

                if(!isset($existing_metas->user_code) && $attributes['role'] == $default_role_id_cfp){
                    //$meta_map += [ 'user_code' => 'user_code' ];
                    $term = Taxonomy::where('id', $attributes['branch'])->with('taxoMetas')->first();
                    $taxoMeta = userMeta($term->taxoMetas);
                    $branch_code = $taxoMeta->branch_code;
                    //$date_code = carbon_now_format('dmY');
                    $userCodeCounter = CfpCodeCounter::where('branch_code', $branch_code)->first();
                    $usercode_last_number = is_null($userCodeCounter)?1:$userCodeCounter->last_number+1;
                    
                    $user_code = gen_cfp_code($branch_code, $usercode_last_number);
                    //$attributes['user_code'] = $user_code;
                    $this->updateCfpCodeCounter($branch_code, $usercode_last_number);//update version, langsung update supaya tidak terlewat
                    $meta_map += ['user_code' => [ 'meta_key' => 'user_code' , 'meta_value' => $user_code , 'type' => 'text' ]];
                }

                //update branch
                //dd($existing_metas->branch.' != '.$attributes['branch']);
                //merupakan role client dan branch yang dipilih tidak sama dengan branch sebelumnya
                if(isset($attributes['branch']) && $attributes['branch'] && $attributes['role'] == $default_role_id_client && ( !isset($existing_metas->branch) || $existing_metas->branch != $attributes['branch'])){
                    $t_attributes = [
                        'user_id' => $user_id,
                        'client_id' => $id,
                        'notes' => 'Auto assigned by system',
                        'branch' => $attributes['branch'],
                        'default_role_id_cfp' => $default_role_id_cfp,
                        'record_flag' => 'U'
                    ];

                    $this->autoassign_cfp($t_attributes);
                }
            }

            $meta_map += [
                'full_name'         => [ 'meta_key' => 'full_name'      , 'meta_value' => $attributes['full_name']        , 'type' => 'text' ],
                'name'              => [ 'meta_key' => 'name'           , 'meta_value' => $attributes['name']             , 'type' => 'text' ],
                'last_name'         => [ 'meta_key' => 'last_name'      , 'meta_value' => $attributes['last_name']        , 'type' => 'text' ],
                'phone'             => [ 'meta_key' => 'phone'          , 'meta_value' => $attributes['phone']            , 'type' => 'text' ],
                'gender'            => [ 'meta_key' => 'gender'         , 'meta_value' => $attributes['gender']           , 'type' => 'text' ],
                'date_of_birth'     => [ 'meta_key' => 'date_of_birth'  , 'meta_value' => $attributes['date_of_birth']    , 'type' => 'dateFormatYmd' ],
                'address'           => [ 'meta_key' => 'address'        , 'meta_value' => $attributes['address']          , 'type' => 'text' ],
                'branch'            => [ 'meta_key' => 'branch'         , 'meta_value' => $attributes['branch']           , 'type' => 'text' ],
                'reference_code'    => [ 'meta_key' => 'reference_code' , 'meta_value' => $attributes['reference_code']   , 'type' => 'text' ],
                'photo'             => [ 'meta_key' => 'photo'          , 'meta_value' => $attributes['photo']            , 'type' => 'image' ],
                'bca_acc'           => [ 'meta_key' => 'bca_acc'        , 'meta_value' => $attributes['bca_acc']          , 'type' => 'text' ]
            ];

            userMeta_store($id, $meta_map);

            

            DB::commit();

            return true;
        }

        throw new ValidationException('User validation failed', $this->getErrors());
    }

    public function liteUpdate($id, $attributes) { // harus dismakan cara nya dengan taxonomy library create
        if($this->isValid($attributes)) {
            DB::beginTransaction();
            //dd($attributes['post_type']);
            $user_id = $id;//isset($attributes['id'])?$attributes['id']:Auth::user()->id;
            $user = User::where('id', $id)->with(['userMetas', 'roles'])->first();
            foreach($user->roles as $role){
                $role_name = $role->display_name;
                $role_id = $role->id;
            } 
            $existing_metas = userMeta($user->userMetas);

            //$existing_metas_q = UserMeta::where('user_id', $id)->get();
            //$attributes['is_active'] = isset($attributes['is_active']) ? 1 : 0;
            //$existing_metas = userMeta($existing_metas_q);
            $meta_map = [];

            //if(trim($attributes['reference_code']) == ''){
            //    $attributes['reference_code'] = 0;
            //}
            $attributes['full_name']  =  $attributes['name'].' '.$attributes['last_name'];
            // dd($meta_map);
            $default_role_id_client = config_db_cached('settings::default_role_id_client');
            $default_role_id_cfp = config_db_cached('settings::default_role_id_cfp');
            //if($attributes['is_active'] == 1){
                if(!isset($existing_metas->user_code) && $role_id == $default_role_id_client){
                    //$meta_map += [ 'user_code' => 'user_code' ];
                    $term = Taxonomy::where('id', $attributes['branch'])->with('taxoMetas')->first();
                    $taxoMeta = userMeta($term->taxoMetas);
                    $branch_code = $taxoMeta->branch_code;
                    $date_code = carbon_now_format('dmY');
                    $userCodeCounter = UserCodeCounter::where('branch_code', $branch_code)->where('date_code', $date_code)->first();
                    $usercode_last_number = is_null($userCodeCounter)?1:$userCodeCounter->last_number+1;
                    
                    $user_code = gen_client_code($branch_code, $date_code, $usercode_last_number);
                    //$attributes['user_code'] = $user_code;
                    $this->updateUserCodeCounter($branch_code, $date_code, $usercode_last_number);//update version, langsung update supaya tidak terlewat
                    $meta_map += ['user_code' => [ 'meta_key' => 'user_code' , 'meta_value' => $user_code , 'type' => 'text' ]];
                }

                $check_cfp_count = DB::table('cfp_clients')
                        ->where('client_id', '=', $id)
                        ->count();

                //update branch
                if($check_cfp_count == 0 && isset($attributes['branch']) && $attributes['branch'] && $role_id == $default_role_id_client && ( !isset($existing_metas->branch) || $existing_metas->branch != $attributes['branch'])){
                    $t_attributes = [
                        'user_id' => $user_id,
                        'client_id' => $id,
                        'notes' => 'Auto assigned by system',
                        'branch' => $attributes['branch'],
                        'default_role_id_cfp' => $default_role_id_cfp,
                        'record_flag' => 'U'
                    ];



                    /**
                     | -------------------------------------------------
                     | Pengisian REFERENCE_CODE di tahap updaten user
                     | -------------------------------------------------
                     | Saat register menggunakan facebook,
                     | user tidak bisa input reference_code yang biasanya
                     | di lakukan saat register manual.
                     | Untuk menangani hal itu, maka di form update account
                     | ditambahkan parameter untuk user memasukan reference_code
                     |
                     */

                    // cek di user_metas dengan user_id ini, apakah sudah memiliki reference_code, kalau sudah lakukan update reference_code

                   $count = DB::table('user_metas')
                        ->where('user_id', '=', $id)
                        ->where('meta_key', '=' ,'reference_code')
                        ->where('meta_value', '<>', '')
                        ->count();

                    if($count == '1') {

                        // cek apakah ada parameter reference_code yang dikirim dari depan
                        if(isset($attributes['reference_code'])) {
                            $client = UserMeta::where('user_id', '=', $id)
                                ->where('meta_key', '=' ,'reference_code');

                            $t_attributes_2 = [
                                'meta_value'     => $attributes['reference_code']
                            ];

                            $client->update($t_attributes_2);
                        }

                   } else {
                        // kalau belum tambah

                        $meta_map += [
                        'reference_code'    
                            => [ 'meta_key' => 'reference_code', 
                                'meta_value' => isset($attributes['reference_code']) ? $attributes['reference_code'] : '', 
                                'type' => 'text' ]
                        ];
                        
                        userMeta_store($id, $meta_map);
                    }



                    $this->autoassign_cfp($t_attributes);

                }

            $meta_map += [
                'full_name'         => [ 'meta_key' => 'full_name'      , 'meta_value' => $attributes['full_name']        , 'type' => 'text' ],
                'name'              => [ 'meta_key' => 'name'           , 'meta_value' => $attributes['name']             , 'type' => 'text' ],
                'last_name'         => [ 'meta_key' => 'last_name'      , 'meta_value' => $attributes['last_name']        , 'type' => 'text' ],
                'phone'             => [ 'meta_key' => 'phone'          , 'meta_value' => $attributes['phone']            , 'type' => 'text' ],
                'gender'            => [ 'meta_key' => 'gender'         , 'meta_value' => $attributes['gender']           , 'type' => 'text' ],
                'date_of_birth'     => [ 'meta_key' => 'date_of_birth'  , 'meta_value' => $attributes['date_of_birth']    , 'type' => 'dateFormatYmd' ],
                'address'           => [ 'meta_key' => 'address'        , 'meta_value' => $attributes['address']          , 'type' => 'text' ],
                'branch'            => [ 'meta_key' => 'branch'         , 'meta_value' => $attributes['branch']           , 'type' => 'text' ],
                //'goal'              => [ 'meta_key' => 'goal'           , 'meta_value' => $attributes['goal']             , 'type' => 'text' ],
                //'bca_acc'           => [ 'meta_key' => 'bca_acc'        , 'meta_value' => $attributes['bca_acc']          , 'type' => 'text' ]
                // 'reference_code'    => [ 'meta_key' => 'reference_code' , 'meta_value' => $attributes['reference_code']   , 'type' => 'text' ],
                //'photo'             => [ 'meta_key' => 'photo'          , 'meta_value' => $attributes['photo']            , 'type' => 'image' ]
            ];

            userMeta_store($id, $meta_map);

            $this->user = $this->find($id);

            $attributes += [
                'updated_by' => $user_id,
                'updated_at' => Carbon::now()
            ];

            $this->user->fill($attributes)->save();

            //safe goal disini

            DB::commit();

            $user = $this->findWithMyCFP($user->email);;
            $userMeta = (array)userMeta($user->userMetas);
			if(isset($userMeta['branch'])){ 
				$userMeta['branch_name'] = Taxonomy::where('id', $userMeta['branch'])->pluck('title');
            }
            
			$user_raw = $user->toArray();
			$user_raw = array_merge($user_raw, $userMeta);
			$user_raw['cfp'] = null;
			if(!is_null($user->cfpClient)){
				$userMetaCFP = (array)userMeta($user->cfpClient->cfp->userMetas, [ 'photo' => ['type' => 'image'] ]); //var_dump($userMetaCFP); exit;
				
				
				$user_raw['cfp'] = $user_raw['cfp_client']['cfp']; //var_dump($user_raw['cfp_client']['cfp']['user_metas']); exit;
				//$userMetaCFP = (array)userMeta($user_raw['cfp']['user_metas']); var_dump($userMetaCFP); exit;
				$user_raw['cfp'] = array_merge($user_raw['cfp'], $userMetaCFP);
			}

			unset($user_raw['cfp_client']);
			unset($user_raw['user_metas']);
            unset($user_raw['cfp']['user_metas']);
            

            return $user_raw;
        }

        throw new ValidationException('User validation failed', $this->getErrors());
    }

    /**
     * @param $id
     * @param $attributes
     * @return bool|mixed
     * @throws \App\Exceptions\Validation\ValidationException
     */

    public function updatePassword($id, $attributes) {

        $rules_n_attributeNames = $this->rulesPassword();
        self::$rules = $rules_n_attributeNames['rules'];
        self::$attributeNames = $rules_n_attributeNames['attributeNames'];

        if($this->isValid($attributes)) {
            $this->user = $this->find($id);
            if($attributes['password'] == '')
                unset($attributes['password']);
            else
                $attributes['password'] = Hash::make($attributes['password']);
            $this->user->fill($attributes)->save();

/*
            $user = $this->user->find($id);

            $user->email = $request->get('email');
            if($request->get('password'))
            {
                $user->password = $request->get('password');
            }
            $user->save();*/

            if($attributes['role'] != '')
            {
                $this->user->roles()->sync([$attributes['role']]);
            }
            else
            {
                $this->user->roles()->sync([]);
            }



            return true;
        }

        throw new ValidationException('User validation failed', $this->getErrors());
    }

    public function updateIBankAccount($id, $attributes) {

        $rules_n_attributeNames = $this->rulesAddIBankAccount();
        self::$rules = $rules_n_attributeNames['rules'];
        self::$attributeNames = $rules_n_attributeNames['attributeNames'];

        if($this->isValid($attributes)) {
            if($attributes['bank_code'] == 'bca'){
                $this->user->where('id', $id)->update([
                    'bca_account_no' => $attributes['account_no'],
                    'bca_ibank_uid' => $attributes['ibank_uid'],
                    'bca_ibank_pin' => $attributes['ibank_pin'],
                ]);
            } else if ($attributes['bank_code'] == 'mandiri'){
                $this->user->where('id', $id)->update([
                    'mandiri_account_no' => $attributes[''],
                    'mandiri_ibank_uid' => $attributes['ibank_uid'],
                    'mandiri_ibank_pin' => $attributes['ibank_pin'],
                ]);
            } else {
                return false;
            }
            return true;
        }
        
        throw new ValidationException('User validation failed', $this->getErrors());
    }

    /**
     * @param $id
     * @return mixed|void
     */
   public function delete_junk($id) {

        $this->user->findOrFail($id)->delete();
    }

    public function delete($id) {
        $user_id = Auth::user()->id;
        $user = $this->user->find($id);
        if($user){
            $user->record_flag = 'D';
            $user->deleted_by = $user_id;
            $user->deleted_at = Carbon::now();
            $user->save();
            return true;
        }
        throw new ValidationException('User delete failed', $this->getErrors());
    }

     /*public function softDelete($id) {
        $this->user->findOrFail($id)->softDeletes();
    }*/


    /**
     * Get total page count
     * @param bool $all
     * @return mixed
     */
    protected function totalUsersJUNK() {
        return $this->user->count();
    }

    protected function totalUsers($filter = array()) {
        $query = 
            $this->user->with([
                'roles', 
                'goalGrade', 
                'userMetas',
                'userMeta_branch' => function($q){
                   $q->with('branch');
                }]);

        if(is_array($filter)){
            foreach($filter as $ff => $term){
                if(trim($term) != ''){
                    switch ($ff) {
                        /*case 'with' :
                            $query->whereHas('productSpecialOffers', function($q) {
                                $q->havingRaw('COUNT(DISTINCT `product_id`) > 0');
                            });
                        break;*/
                        case 'record_flag_is_not':
                            $query->where('users.record_flag', '!=', $term);
                        break;
                        case 'user_code':
                            $query->whereHas('userMetas', function($q) use ($term) {
                                $q
                                ->where('meta_key', 'user_code')
                                ->where('meta_value', 'like', '%'.$term.'%');
                            });
                        break;
                        case 'branch_code':
                            $query->whereHas('userMetas', function($q) use ($term) {
                                $q
                                ->where('meta_key', 'branch')
                                ->where('meta_value', 'like', '%'.$term.'%');
                            });
                        break;
                        case 'name':
                            $query->whereHas('userMetas', function($q) use ($term) {
                                $q
                                ->where('meta_key', 'name');
                                //->where('meta_value', 'like', '%'.$term.'%');

                                $q->whereRaw('LOWER(meta_value) like ?', [ '%'.strtolower($term).'%' ]);
                            });
                        break;
                        case 'role':
                            $query->whereHas('role_user', function($q) use ($term) {
                                $q
                                ->where('role_id', $term);
                            });
                        break;
                        case 'status':
                            $query->where('users.is_active', $term);
                        break;
                    }
                }
            }
        }
        

        return $query->count();
    }

    public function findByEmail($email){
        return $this->user->where('email', $email)->first();
    }

    public function generateCode($id){
        $user = User::where('id', $id)->with(['userMetas', 'roles'])->first();
        foreach($user->roles as $role){
           $role_name = $role->display_name;
           $role_id = $role->id;
        } 
       // $existing_metas_q = UserMeta::where('user_id', $id)->get();
        $existing_metas = userMeta($user->userMetas); //dd($existing_metas);
        $branch = isset($existing_metas->branch) && $existing_metas->branch != 0 ?$existing_metas->branch:'';
        if($branch === '')
            return 'branch_not_set';
        //dd($branch);
        $default_role_id_client = config_db_cached('settings::default_role_id_client');
        if(!isset($existing_metas->user_code) && $role_id == $default_role_id_client){
            //$meta_map += [ 'user_code' => 'user_code' ];
            $term = Taxonomy::where('id', $branch)->with('taxoMetas')->first();
            $taxoMeta = userMeta($term->taxoMetas);
            $branch_code = $taxoMeta->branch_code;
            $date_code = carbon_now_format('dmY');
            $userCodeCounter = UserCodeCounter::where('branch_code', $branch_code)->where('date_code', $date_code)->first();
            $usercode_last_number = is_null($userCodeCounter)?1:$userCodeCounter->last_number+1;
            
            $user_code = gen_client_code($branch_code, $date_code, $usercode_last_number);
            UserMeta::create([
                'user_id' => $id,
                'meta_key' => 'user_code',
                'meta_value' => $user_code
            ]);
            $this->updateUserCodeCounter($branch_code, $date_code, $usercode_last_number);//update version, langsung update supaya tidak terlewat
        }
    }
    
    public function getJWTToken($attributes) {
		$password = base64_decode($attributes->get('password'));// jadi isinya $request->input('password') adalah misal password di masukin ke base64_decode
		
		//$request->request->add(['password' => $password]);
		//$credentials = $request->only('email', 'password');
		
		$credentials = [
			'email' => $attributes->get('email'),
            'password' => $password,
            'record_flag' => ''
		];
		//dd($credentials);
		try {
			if (!$token = JWTAuth::attempt($credentials)) {
				if($attributes->get('request_from') == 'doLogin')
					return ['error' => 'invalid_credentials'];

				return response()->json(['error' => 'invalid_credentials'], 401);
			}
		} catch (JWTException $e) {
			// something went wrong
			if($attributes->get('request_from') == 'doLogin')
				return ['error' => 'could_not_create_token'];
			return response()->json(['error' => 'could_not_create_token'], 500);
		}
		// if no errors are encountered we can return a JWT
		// response for use parameter token 
		//return response()->json(compact('token'));

		// response for header token 
		if($attributes->get('request_from') == 'doLogin')
				return $token;

		return response()->json(compact('token'))->header('Authorization','Bearer '.$token);
	}
}
