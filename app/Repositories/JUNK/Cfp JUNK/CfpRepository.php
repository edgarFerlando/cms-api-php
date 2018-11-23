<?php namespace App\Repositories\Cfp;

use App\Cfp;
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

use App\Models\CfpClient;
//use App\Repositories\CfpClient\CfpClientRepository;

class CfpRepository extends RepositoryAbstract implements UserInterface, CrudableInterface {

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
        $rules['name'] = 'required|max:255';
        $rules['role'] = 'required|exists:roles,id';
        if(trim(Input::get('password')) != '')
            $rules['password'] = 'confirmed';
            $rules['password_confirmation'] = 'same:password';
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
    public function paginate($page = 1, $limit = 10, $all = false) {

        $result = new \StdClass;
        $result->page = $page;
        $result->limit = $limit;
        $result->totalItems = 0;
        $result->items = array();

        $query = $this->user->with(['roles', 'goalGrade'])->orderBy('created_at', 'DESC');
        $users = $query->skip($limit * ($page - 1))->take($limit)->get();
        $result->totalItems = $this->totalUsers();
        $result->items = $users->all();

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

        return $this->user->with('roles')->find($id);
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

    public function findByName2($name, $role) {
        return $this->user->with(['roles'])->whereRaw('LOWER(name) like ?', [ "%" . strtolower($name) . "%"])->whereHas('roles', function($q) use ($role){
            $q->where('name', $role);
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
                $query->with('userMetas');
            }
        ];

        if(!empty($modules_shown)){
            foreach (array_diff(array_keys($all_with), $modules_shown) as $module_shown) {
                unset($all_with[$module_shown]);
            }
        }

        return User::select('users.id as user_id')->with($all_with)->where('id', $user_id)->first();

        /*return $this->user
        ->with(['cfpClient.cfp', 'userMetas'])->whereRaw('LOWER(email) = ?', [ strtolower($client_email)])
        ->first();*/
    }

    /**
     * @param $attributes
     * @return bool|mixed
     * @throws \App\Exceptions\Validation\ValidationException
     */
    public function create($attributes) {
        //langsung di controller, pake bawaan laravel [ ini dipakai untuk usercontroller saja]
        //dd($attributes);
        //dibawah ini yg pakai selain usercontroller
        /*$user = $this->user->create($attributes);

        if($attributes['role'])
        {
            $user->roles()->sync([$attributes['role']]);
        }
        else
        {
            $user->roles()->sync([]);
        }
        return $user;*/

/*
        if($this->isValid($attributes)) {
            $this->user->fill($attributes)->save();

            return true;
        }
        throw new ValidationException('User validation failed', $this->getErrors());*/
        //dd($attributes);
        if($this->isValid($attributes)) {
            //dd($attributes['post_type']);
            //$this->user = $this->find($id);
            if($attributes['password'] == '')
                unset($attributes['password']);
            else
                $attributes['password'] = Hash::make($attributes['password']);
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

            //dd($userData->id);

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

                        UserMeta::create([
                            'user_id' => $id,
                            'meta_key' => $meta_key,
                            'meta_value' => $attributes[$ff_name] 
                        ]);
                    
                }
/*          
            $user = $this->user->find($id);

            $user->email = $request->get('email');
            if($request->get('password'))
            {
                $user->password = $request->get('password');
            }
            $user->save();*/

            return true;
        }

        throw new ValidationException('User validation failed', $this->getErrors());
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

                        /*UserMeta::create([
                            'user_id' => $id,
                            'meta_key' => $meta_key,
                            'meta_value' => $attributes[$ff_name] 
                        ]);*/
                    
                }

            return true;
        }

        throw new ValidationException('User validation failed', $this->getErrors());
    }

    /**
     * @param $id
     * @param $attributes
     * @return bool|mixed
     * @throws \App\Exceptions\Validation\ValidationException
     */
/*
    public function update($id, $attributes) {
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
/*
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
*/ 

    public function update($id, $attributes) {
        if($this->isValid($attributes)) {
            //dd($attributes['post_type']);
                $existing_metas_q = UserMeta::where('user_id', $id)->get();

                $existing_metas = userMeta($existing_metas_q);
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
                    if(isset($existing_metas->$meta_key)){

                        if($meta_key == 'user_thumbnail')
                        {
                            $attributes[$ff_name] = getImagePath($attributes['user_thumbnail']);
                            //dd($post[$ff_name]);
                            if($meta_key == 'user_thumbnail' && $attributes[$ff_name] == '')
                            {
                                $attributes[$ff_name] = $attributes['old_user_thumbnail'];
                                //dd($post[$ff_name]);
                            }
                        }

                        if($meta_key == 'user_image')
                        {
                            $attributes[$ff_name] = getImagePath($attributes['user_image']);
                            //dd($post[$ff_name]);
                            if($meta_key == 'user_image' && $attributes[$ff_name] == '')
                            {
                                $attributes[$ff_name] = $attributes['old_user_image'];
                                //dd($post[$ff_name]);
                            }
                        }

                        if($meta_key == 'ktp_image')
                        {
                            $attributes[$ff_name] = getImagePath($attributes['ktp_image']);

                            if($meta_key == 'ktp_image' && $attributes[$ff_name] == '')
                            {
                                $attributes[$ff_name] = $attributes['old_ktp_image'];
                            }
                        }


                        UserMeta::where('user_id', $id)
                        ->where('meta_key', $meta_key)
                        ->update(['meta_value' => $attributes[$ff_name]]);

                        if($meta_key == 'name')
                          User::where('id', Auth::user()->id)
                          ->update(['name' => $attributes[$ff_name]]);
                    }else{

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

                        UserMeta::create([
                            'user_id' => $id,
                            'meta_key' => $meta_key,
                            'meta_value' => $attributes[$ff_name] 
                        ]);
                    }
                }
            

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
    /**
     * @param $id
     * @return mixed|void
     */
    public function delete($id) {

        $this->user->findOrFail($id)->delete();
    }

    /**
     * Get total page count
     * @param bool $all
     * @return mixed
     */
    protected function totalUsers() {
        return $this->user->count();
    }

    public function findByEmail($email){
        return $this->user->where('email', $email)->first();
    }
    
}
