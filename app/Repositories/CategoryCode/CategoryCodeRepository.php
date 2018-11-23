<?php namespace App\Repositories\CategoryCode;

use App\Models\CategoryCode;

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


class CategoryCodeRepository extends RepositoryAbstract implements CategoryCodeInterface, CrudableInterface {

    protected $perPage;
    protected $categoryCode;
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
    public function __construct(CategoryCode $categoryCode) {

        $config = Config::get('holiday');
        $this->perPage = $config['per_page'];
        $this->categoryCode = $categoryCode;

        $rules_n_attributeNames = $this->rules();
        self::$rules = $rules_n_attributeNames['rules'];
        self::$attributeNames = $rules_n_attributeNames['attributeNames'];
    }

    public function rules(){
        $_rules = array();
        $setAttributeNames = array();
        
        $_rules['category_name'] = 'required|max:255';
        $setAttributeNames['category_name'] = trans('app.category_name');
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
        return $this->categoryCode->orderBy('created_by', 'DESC')->get();
    }

    /**
     * @return mixed
     */
    public function lists($name, $key) {

        //return $this->role->get()->lists('title', 'id');
        return $this->categoryCode->all()->lists($name, $key);
    }

    public function paginate($page = 1, $limit = 10, $all = false) {

        $result = new \StdClass;
        $result->page = $page;
        $result->limit = $limit;
        $result->totalItems = 0;
        $result->items = array();

        $query = $this->categoryCode->orderBy('created_by', 'DESC');
        //dd($query);
        if(!$all) {
            $query->where('is_published', 1);
        }

        //\DB::enableQueryLog();

        $categoryCodes = $query->skip($limit * ($page - 1))->take($limit)->get();

        //dd(\DB::getQueryLog());
        //dd($categoryCodes);

        $result->totalItems = $this->totalCategoryCodes($all);
        $result->items = $categoryCodes->all();

        foreach ($result->items as $key => $item) {
            $result->items[$key]->categoryCode = $item;
            $userCreate = User::find($item->created_by);
            $result->items[$key]->userCreate = $userCreate->name;

            if(!is_null($item->updated_by))
            {
                $userUpdate = User::find($item->updated_by);
                $result->items[$key]->userUpdate = $userUpdate->name;
            }
            
        };
        
        //dd($result);

        return $result;
    }

    public function find($id) {
        return $this->categoryCode->findOrFail($id);
    }

    public function create($attributes) {
        if($this->isValid($attributes)) {
            $t_attributes = array();
            $t_attributes['category_name'] = $attributes['category_name'];
            $t_attributes['keterangan'] = $attributes['keterangan'];
            $t_attributes['created_by'] = Auth::user()->id;
            $t_attributes['created_on'] = date("Y-m-d H:i:s");
            $t_attributes['record_flag'] = 'N';
            /*
            $t_attributes['testimonial'] = Str::slug($attributes['name'][getLang()], '_');
            foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
                $t_attributes[$locale] = [
                    'name' => $attributes['name'][$locale]
                ];
            }
            //dd($t_attributes);
            */
            $this->categoryCode->fill($t_attributes)->save();

            return true;
        }
        throw new ValidationException('Category Code attribute validation failed', $this->getErrors());
    }

    public function update($id, $attributes) {
        if($this->isValid($attributes)) {
            $t_attributes = array();
            $this->categoryCode = $this->find($id);
            
            $t_attributes['category_name'] = $attributes['category_name'];
            $t_attributes['keterangan'] = $attributes['keterangan'];
            $t_attributes['updated_by'] = Auth::user()->id;
            $t_attributes['updated_on'] = date("Y-m-d H:i:s");
            $t_attributes['record_flag'] = 'U';
            /*
            $t_attributes['testimonial'] = Str::slug($attributes['name'][getLang()], '_');
            foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
                $t_attributes[$locale] = [
                    'name' => $attributes['name'][$locale]
                ];
            }
            */
            $this->categoryCode->fill($t_attributes)->save();
            return true;
        }
        throw new ValidationException('Category code attribute validation failed', $this->getErrors());
    }

    public function delete($id) {
        $categoryCode = $this->categoryCode->findOrFail($id);
        $categoryCode->delete();
    }

    protected function totalCategoryCodes($all = false) {
        return $this->categoryCode->count();
    }
}
