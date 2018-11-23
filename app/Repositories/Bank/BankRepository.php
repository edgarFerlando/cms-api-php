<?php 

namespace App\Repositories\Bank;

use App\Repositories\CrudableInterface as CrudableInterface;
use App\Repositories\RepositoryInterface as RepositoryInterface;
use App\Exceptions\Validation\ValidationException;
use App\Repositories\AbstractValidator as Validator;
use LaravelLocalization;
use Carbon\Carbon;
use Notification;
use Auth;
use Str;
use Config;
use Response;
use App\Models\Bank;
use App\Repositories\RepositoryAbstract;

class BankRepository extends RepositoryAbstract implements BankInterface, CrudableInterface {

    protected $perPage;
    protected $bank;
    protected static $rules;
    protected static $attributeNames;

    public function __construct(Bank $bank)
    {
        $config = Config::get('bank');
        $this->perPage = $config['per_page'];
        $this->bank = $bank;

        $rules_n_attributeNames = $this->rules();
        self::$rules = $rules_n_attributeNames['rules'];
        self::$attributeNames = $rules_n_attributeNames['attributeNames'];
    }

    public function rules()
    {
        $_rules = array();
        $setAttributeNames = array();
        
        $_rules['title'] = 'required';
        $_rules['slug'] = 'required';
        $_rules['featured_image'] = 'required';

        $setAttributeNames['title'] = trans('app.title');
        $setAttributeNames['slug'] = trans('app.slug');
        $setAttributeNames['featured_image'] = trans('app.featured_image');

        return [
            'rules' => $_rules,
            'attributeNames' => $setAttributeNames
        ];
    }

    public function all() {
        return $this->bank->orderBy('created_at', 'DESC')->get();
    }

    public function find($id) {
        return $this->bank->find($id);
    }

    public function paginate($page = 1, $limit = 10, $filter = array()) {

        $result = new \StdClass;
        $result->page = $page;
        $result->limit = $limit;
        $result->totalItems = 0;
        $result->items = array();

        $query = $this->bank->select('*');

        $bank = $query->skip($limit * ($page - 1))->take($limit)->get();

        $result->totalItems = $this->totalBank($filter);
        $result->items = $bank->all();

        foreach ($result->items as $key => $item) {
            $result->items[$key]->codes = $item;
        };

        return $result;
    }

    public function create($attributes)
    {
        if($this->isValid($attributes)) {
            $t_attributes = array();
            $t_attributes['title'] = $attributes['title'];
            $t_attributes['slug'] = $attributes['slug'];
            $t_attributes['featured_image'] = isset($attributes['featured_image'])?getImagePath($attributes['featured_image']):'';
            $t_attributes['color'] = $attributes['color'][0];
            $t_attributes['is_status'] = $attributes['is_status'];
            $t_attributes['created_by'] = Auth::user()->id;
            $t_attributes['created_at'] = date("Y-m-d H:i:s");
            $t_attributes['record_flag'] = 'N';

            $this->bank->fill($t_attributes)->save();

            return true;
        }
        throw new ValidationException('Bank attribute validation failed', $this->getErrors());
    }

    public function update($id, $attributes) {
        if($this->isValid($attributes)) {
            $t_attributes = array();
            $this->bank = $this->find($id);

            $t_attributes['title'] = $attributes['title'];
            $t_attributes['slug'] = $attributes['slug'];
            $t_attributes['featured_image'] = isset($attributes['featured_image'])?getImagePath($attributes['featured_image']):'';
            $t_attributes['color'] = $attributes['color'][0];
            $t_attributes['is_status'] = $attributes['is_status'];
            $t_attributes['updated_by'] = Auth::user()->id;
            $t_attributes['updated_on'] = date("Y-m-d H:i:s");
            $t_attributes['record_flag'] = 'U';

            $this->bank->fill($t_attributes)->save();
            return true;
        }
        throw new ValidationException('Bank attribute validation failed', $this->getErrors());
    }

    public function delete($id) {
        $bank = $this->bank->findOrFail($id);
        if($bank)
            $bank->delete();
            return true;

        return false;
    }

    protected function totalBank($all = false) {
        return $this->bank->count();
    }

}