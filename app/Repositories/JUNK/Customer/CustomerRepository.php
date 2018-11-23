<?php namespace App\Repositories\Customer;

use App\Customer;
use Config;
use Response;
use Request;
use App\Repositories\RepositoryAbstract;
use App\Repositories\CrudableInterface as CrudableInterface;
use App\Repositories\RepositoryInterface as RepositoryInterface;
use App\Exceptions\Validation\ValidationException;
use App\Repositories\AbstractValidator as Validator;
use LaravelLocalization;
use Notification;
use Input;
use Hash;

class CustomerRepository extends RepositoryAbstract implements CustomerInterface, CrudableInterface {

    /**
     * @var
     */
    protected $perPage;
    /**
     * @var \Page
     */
    protected $customer;
    /**
     * Rules
     *
     * @var array
     */
    protected static $rules;

    protected static $attributeNames;

    /**
     * @param Customer $customer
     */
    public function __construct(Customer $customer) {
        $this->perPage = Config::get('holiday.per_page');
        $this->customer = $customer;
        $rules_n_attributeNames = $this->rules();
        self::$rules = $rules_n_attributeNames['rules'];
        self::$attributeNames = $rules_n_attributeNames['attributeNames'];
    }

    public function rules(){
        $rules = array();
        //dd(Request::get('_method'));
        $setAttributeNames = array();
        $rules['name'] = 'required|max:255';
        //dd($request->get('_method'));
        if(Request::get('_method') == 'PATCH'){
            if(trim(Request::get('password')) != '')
                $rules['password'] = 'confirmed|min:8';
        }else{
            $rules['email'] = 'required|email|unique:customers';
            $rules['password'] = 'required|confirmed|min:8';
        }
        $setAttributeNames['name'] = trans('app.name');
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

        return $this->customer->orderBy('created_at', 'DESC')->get();
    }

    /**
     * @return mixed
     */
    public function lists() {
        return $this->customer->all()->lists('title', 'id');
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

        $query = $this->customer->orderBy('created_at', 'DESC');
        $customers = $query->skip($limit * ($page - 1))->take($limit)->get();
        $result->totalItems = $this->totalCustomers();
        $result->items = $customers->all();

        return $result;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function find($id) {

        return $this->customer->find($id);
    }

    /**
     * @param $attributes
     * @return bool|mixed
     * @throws \App\Exceptions\Validation\ValidationException
     */
    public function create($attributes) {
        if($this->isValid($attributes)) {
            $attributes['password'] = Hash::make($attributes['password']);
            $this->customer->fill($attributes)->save();
            return true;
        }
        throw new ValidationException('Customer validation failed', $this->getErrors());
    }

    /**
     * @param $id
     * @param $attributes
     * @return bool|mixed
     * @throws \App\Exceptions\Validation\ValidationException
     */
    public function update($id, $attributes) {
        if($this->isValid($attributes)) {
            $this->customer = $this->find($id);
            if($attributes['password'] == '')
                unset($attributes['password']);
            else
                $attributes['password'] = Hash::make($attributes['password']);
            $this->customer->fill($attributes)->save();
            return true;
        }

        throw new ValidationException('Customer validation failed', $this->getErrors());
    }

    /**
     * @param $id
     * @return mixed|void
     */
    public function delete($id) {

        $this->customer->findOrFail($id)->delete();
    }

    /**
     * Get total page count
     * @param bool $all
     * @return mixed
     */
    protected function totalCustomers() {
        return $this->customer->count();
    }
}
