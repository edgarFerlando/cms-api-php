<?php namespace App\Repositories\Reference;

use App\Models\Reference;

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
use Carbon\Carbon;
use Request;
use Route;
use Input;

use Validator as Valid;


class ReferenceRepository extends RepositoryAbstract implements ReferenceInterface, CrudableInterface {

    protected $perPage;
    protected $reference;
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
    public function __construct(Reference $reference) {

        //$config = Config::get('holiday');
        //$this->perPage = $config['per_page'];
        $this->reference = $reference;

        $rules_n_attributeNames = $this->rules();
        self::$rules = $rules_n_attributeNames['rules'];
        self::$attributeNames = $rules_n_attributeNames['attributeNames'];
    }

    public function rules(){
        $_rules = array();
        $setAttributeNames = array();
        
        $_rules['name'] = 'required';
        $_rules['email'] = 'required|email';

        $method = Request::method();

        switch($method)
        {
            case 'POST'://create
                //unique branch code dari tabel taxonomy_metas saja
                $_rules['code'] = 'required|unique:references,code';
            break;
            case 'PATCH'://update
                $ref_id = Route::current()->getParameter('reference');
                $old_data = Reference::where('id', $ref_id)->first();
                if(!is_null($old_data) && $old_data->code == Input::get('code')){
                    $_rules['code'] = 'required';
                }else{
                    $_rules['code'] = 'required|unique:references,code';
                }
            break;
            default:
                $_rules['code'] = 'required|unique:references,code';
            break;
        }

        $setAttributeNames['code'] = trans('app.code');
        $setAttributeNames['name'] = trans('app.name');
        $setAttributeNames['email'] = trans('app.email');

        return [
            'rules' => $_rules,
            'attributeNames' => $setAttributeNames
        ]; 

    }

    public function all() {
        return $this->reference->orderBy('created_by', 'DESC')->get();
    }

    public function lists($value,$key) {
        return $this->reference->orderBy('created_by', 'DESC')->lists($value, $key);
    }

    public function paginate($page = 1, $limit = 10, $filter = array()) {

        $result = new \StdClass;
        $result->page = $page;
        $result->limit = $limit;
        $result->totalItems = 0;
        $result->items = array();

        $query = $this->reference->orderBy('created_by', 'DESC');
        //dd($query);
        if(is_array($filter)){
            foreach($filter as $ff => $term){
                if(trim($term) != ''){
                    switch ($ff) {
                        case 'code':
                            $query->where('code', $term);
                        break;
                    }
                }
            }
        }

        //$query->join('users as uc', 'uc.id', '=', 'references.created_by', 'left');
        //$query->join('users as uu', 'uu.id', '=', 'references.updated_by', 'left');

        //\DB::enableQueryLog();

        $references = $query->skip($limit * ($page - 1))->take($limit)->get();

        //dd(\DB::getQueryLog());
        //dd($categoryCodes);

        $result->totalItems = $this->totalReferences($filter);
        $result->items = $references->all();

        return $result;
    }

    public function find($id) {

        $rules['id'] = 'required|exists:references,id';

        $data['id'] = $id;

        $validator = Valid::make($data, $rules);

        if ($validator->fails())
        { 
            //dd($validator->errors()->first('id'));
            $data['id_data'] = $validator->errors()->first('id');
            return $data;
        }

        return $this->reference->findOrFail($id);
    }

    public function create($attributes) {

        if($this->isValid($attributes)) {

            $user_id = Auth::user()->id;

            $reference = $this->reference->create([
                'code' => $attributes['code'],
                'name' => $attributes['name'],
                'company' => $attributes['company'],
                'email' => $attributes['email'],
                'phone' => $attributes['phone'],
                'created_by' => $user_id, 
                'created_at' => Carbon::now(), 
                'updated_by' => $user_id, 
                'updated_at' => Carbon::now(), 
                'record_flag' => 'N'
            ]);
            //dd($schedule);
            return $reference->id;
        }
        throw new ValidationException('Reference attribute validation failed', $this->getErrors());
    }

    public function update($id, $attributes) {

        $attributes['id'] = $id;

        $rules['id'] = 'required|exists:references,id';

        $validator = Valid::make($attributes, $rules);

        if ($validator->fails())
        { 
            throw new ValidationException('Reference attribute validation failed', $validator->errors());
        }

        if($this->isValid($attributes)) {
            $t_attributes = array();

            $user_id = Auth::user()->id;

            $this->reference = $this->find($id);
            $t_attributes = [
                'code' => $attributes['code'],
                'name' => $attributes['name'],
                'company' => $attributes['company'],
                'email' => $attributes['email'],
                'phone' => $attributes['phone'],
                'updated_by' => $user_id, 
                'updated_at' => Carbon::now(), 
                'record_flag' => 'U'
            ];
            $this->reference->fill($t_attributes)->save();
            return true;
        }
        throw new ValidationException('Reference attribute validation failed', $this->getErrors());
    }

    public function delete($id) {

        $rules['id'] = 'required|exists:references,id';

        $attributes['id'] = $id;

        $validator = Valid::make($attributes, $rules);

        if ($validator->fails())
        { 
            throw new ValidationException('Reference attribute validation failed', $validator->errors());
        }

        $reference = $this->reference->findOrFail($id);
        $reference->delete();
    }

    protected function totalReferences($filter = array()) {
        $query = $this->reference->select('cfp_clients.id');

        if(is_array($filter)){
            foreach($filter as $ff => $term){
                if(trim($term) != ''){
                    switch ($ff) {
                        case 'code':
                            $query->where('code', $term);
                        break;
                    }
                }
            }
        }
        return $query->count();
    }
}
