<?php namespace App\Repositories\CfpScheduleType;

use App\Models\CfpScheduleType;

use Auth;
use Config;
use Response;
use Illuminate\Support\Str;
use App\Repositories\RepositoryAbstract;
use App\Repositories\CrudableInterface as CrudableInterface;
use App\Repositories\RepositoryInterface as RepositoryInterface;
use App\Exceptions\Validation\ValidationException;
use App\Repositories\AbstractValidator as Validator;
use LaravelLocalization;
use Notification;
use App\User;

use Validator as Valid;
use Carbon\Carbon;


class CfpScheduleTypeRepository extends RepositoryAbstract implements CfpScheduleTypeInterface, CrudableInterface {

    protected $perPage;
    protected $cfpScheduleType;
    /**
     * Rules
     *
     * @var array
     */
    protected static $rules;
    protected static $attributeNames;

    /**
     * @param ProductAttribute $productAttribute
     */
    public function __construct(CfpScheduleType $cfpScheduleType) {

        $config = Config::get('holiday');
        $this->perPage = $config['per_page'];
        $this->cfpScheduleType = $cfpScheduleType;

        $rules_n_attributeNames = $this->rules();
        self::$rules = $rules_n_attributeNames['rules'];
        self::$attributeNames = $rules_n_attributeNames['attributeNames'];
    }

    public function rules(){
        $_rules = array();
        $setAttributeNames = array();
        
        $_rules['client_id'] = 'required';//|exists:users,id';
        $_rules['cfp_id'] = 'required';//|exists:users,id';

        $setAttributeNames['client_id'] = trans('app.client_name');
        $setAttributeNames['cfp_id'] = trans('app.cfp_name');
        /*
        foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
            $_rules['name.'.$locale] = 'required|max:255';

            $setAttributeNames['name.' . $locale] = trans('app.name').' [ ' . $properties['native'].' ]';
        }
        */

        return [
            'rules' => $_rules,
            'attributeNames' => $setAttributeNames
        ]; 

    }

    public function all() {
        return $this->cfpScheduleType->orderBy('created_by', 'DESC')->get();
    }

    public function lists($id_key, $value_key) {
        return $this->cfpScheduleType->all()->lists($value_key, $id_key);
    }

    public function paginate($page = 1, $limit = 10, $all = false) {

        $result = new \StdClass;
        $result->page = $page;
        $result->limit = $limit;
        $result->totalItems = 0;
        $result->items = array();

        $query = $this->cfpScheduleType->select('cfp_clients.*', 'c.name as client_name', 'cfp.name as cfp_name','uc.name as created_by_name', 'uu.name as updated_by_name');
        $query->orderBy('created_by', 'DESC');
        //dd($query);
        if(!$all) {
            $query->where('is_published', 1);
        }

        $query->join('users as c', 'c.id', '=', 'cfp_clients.client_id', 'left');
        $query->join('users as cfp', 'cfp.id', '=', 'cfp_clients.cfp_id', 'left');
        $query->join('users as uc', 'uc.id', '=', 'cfp_clients.created_by', 'left');
        $query->join('users as uu', 'uu.id', '=', 'cfp_clients.updated_by', 'left');

        //\DB::enableQueryLog();

        $cfpScheduleTypes = $query->skip($limit * ($page - 1))->take($limit)->get();

        //dd(\DB::getQueryLog());
        //dd($categoryCodes);

        $result->totalItems = $this->totalcfpScheduleTypes($all);
        $result->items = $cfpScheduleTypes->all();
        /*dd($result);
        foreach ($result->items as $key => $item) {
            $result->items[$key]->cfpScheduleTypes = $item;

            /*$customer_user = User::find($item->client_id);
            $cfp_user = User::find($item->cfp_id);
            $userCreate = User::find($item->created_by);

            $result->items[$key]->clientName = $customer_user->name;
            $result->items[$key]->cfpName = $cfp_user->name;
            $result->items[$key]->userCreate = $userCreate->name;

            if(!is_null($item->updated_by))
            {
                $userUpdate = User::find($item->updated_by);
                $result->items[$key]->userUpdate = $userUpdate->name;
            }*/
      /*  };*/
        
        //dd($result);

        return $result;
    }

    public function find($id) {
        return $this->cfpScheduleType->with(['client', 'cfp'])->findOrFail($id);
    }

    public function findByTypeName($type_name) {
        return $this->cfpScheduleType->where('type_name',$type_name)->first();
    }

    public function findCfpByClientEmail($client_email) {
        return $this->cfpScheduleType->whereHas('cfp', function($q) use ($client_email){
            $q->whereRaw('LOWER(email) = ?', [ strtolower($client_email)]);
        })->get();
    }

    public function create($attributes) { //dd($attributes);
        $rules['client_id'] = 'unique:cfp_clients,client_id';
        $rules['client_id'] = '';
        $attributeNames['client_id'] = trans('app.customer_name');

        $validator = Valid::make($attributes, $rules);
        $validator->setAttributeNames($attributeNames);

        if ($validator->fails())
        { 
            //dd($validator->errors());
            throw new ValidationException('Cfp client attribute validation failed', $validator->errors());
        }

        if($this->isValid($attributes)) {
            /*$t_attributes = array();
            $t_attributes['client_id'] = $attributes['client_id'];
            $t_attributes['cfp_id'] = $attributes['cfp_id'];
            $t_attributes['notes'] = $attributes['notes'];
            $t_attributes['created_by'] = Auth::user()->id;
            $t_attributes['created_on'] = date("Y-m-d H:i:s");
            $t_attributes['record_flag'] = 'N';*/
            /*
            $t_attributes['testimonial'] = Str::slug($attributes['name'][getLang()], '_');
            foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
                $t_attributes[$locale] = [
                    'name' => $attributes['name'][$locale]
                ];
            }*/
            $user_id = Auth::user()->id;
            $client_raw = explode('__', $attributes['client_id']);
            $cfp_raw = explode('__', $attributes['cfp_id']);
            $t_attributes = [
                'client_id' => $client_raw[0],
                'cfp_id' => $cfp_raw[0],
                'notes' => $attributes['notes'],
                'created_by' => $user_id, 
                'created_at' => Carbon::now(), 
                'updated_by' => $user_id, 
                'updated_at' => Carbon::now(),
                'record_flag' => 'N'
            ];
            
            
            //dd($t_attributes);
            $this->cfpScheduleType->fill($t_attributes)->save();

            return true;
        }
        throw new ValidationException('Cfp client attribute validation failed', $this->getErrors());
    }

    public function update($id, $attributes) {
        if($this->isValid($attributes)) {
            $t_attributes = array();
            $this->cfpScheduleType = $this->find($id);


            $user_id = Auth::user()->id;
            $client_raw = explode('__', $attributes['client_id']);
            $cfp_raw = explode('__', $attributes['cfp_id']);
            $t_attributes = [
                'client_id' => $client_raw[0],
                'cfp_id' => $cfp_raw[0],
                'notes' => $attributes['notes'],
                'updated_by' => $user_id, 
                'updated_at' => Carbon::now(),
                'record_flag' => 'U'
            ];


            //dd($attributes);
            /*$t_attributes['customer_id'] = $attributes['customer_id'];
            $t_attributes['cfp_id'] = $attributes['cfp_id'];
            $t_attributes['notes'] = $attributes['notes'];
            $t_attributes['updated_by'] = Auth::user()->id;
            $t_attributes['updated_on'] = date("Y-m-d H:i:s");
            $t_attributes['record_flag'] = 'U';*/
            /*
            $t_attributes['testimonial'] = Str::slug($attributes['name'][getLang()], '_');
            foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
                $t_attributes[$locale] = [
                    'name' => $attributes['name'][$locale]
                ];
            }
            */
            $this->cfpScheduleType->fill($t_attributes)->save();
            return true;
        }
        throw new ValidationException('Cfp client attribute validation failed', $this->getErrors());
    }

    public function delete($id) {
        $cfpScheduleType = $this->cfpScheduleType->findOrFail($id);
        $cfpScheduleType->delete();
    }

    protected function totalcfpScheduleTypes($all = false) {
        return $this->cfpScheduleType->count();
    }
}
