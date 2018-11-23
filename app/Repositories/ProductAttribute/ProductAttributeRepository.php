<?php namespace App\Repositories\ProductAttribute;

use App\ProductAttribute;
use App\ProductAttributeOption;
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


class ProductAttributeRepository extends RepositoryAbstract implements ProductAttributeInterface, CrudableInterface {

    protected $perPage;
    protected $productAttribute;
    protected $productAttributeOption;
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
    public function __construct(ProductAttribute $productAttribute) {

        $config = Config::get('holiday');
        $this->perPage = $config['per_page'];
        $this->productAttribute = $productAttribute;
        $this->productAttributeOption = new productAttributeOption;

        $rules_n_attributeNames = $this->rules();
        self::$rules = $rules_n_attributeNames['rules'];
        self::$attributeNames = $rules_n_attributeNames['attributeNames'];
    }

    public function rules(){
        $_rules = array();
        $setAttributeNames = array();
        foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
            $_rules['name.'.$locale] = 'required|max:255';

            $setAttributeNames['name.' . $locale] = trans('app.name').' [ ' . $properties['native'].' ]';
        }
        return [
            'rules' => $_rules,
            'attributeNames' => $setAttributeNames
        ];
    }

    /**
     * @return mixed
     */
    public function all() {
        return $this->productAttribute->with('productAttributeOption')->orderBy('created_at', 'DESC')->get();
    }

    public function allByPostType($post_type) {
        return $this->productAttribute->with('productAttributeOption')->orderBy('created_at', 'DESC')->get();
    }

    /**
     * @param $limit
     * @return mixed
     */
    public function getLastProductAttribute($limit) {

        return $this->productAttribute->orderBy('created_at', 'desc')->take($limit)->offset(0)->get();
    }

    /**
     * @return mixed
     */
    public function lists() {
        return $this->productAttribute->with(['translations'])->get()->lists('name', 'id');
    }

    /**
     * Get paginated productAttributes
     *
     * @param int $page Number of productAttributes per page
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

        $query = $this->productAttribute->with(['translations'])->orderBy('created_at', 'DESC');

        if(!$all) {
            $query->where('is_published', 1);
        }

        $productAttributes = $query->skip($limit * ($page - 1))->take($limit)->get();

        $result->totalItems = $this->totalProductAttributes($all);
        $result->items = $productAttributes->all();

        return $result;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function find($id) {
        return $this->productAttribute->findOrFail($id);
    }

    public function findByName($name){
        return $this->productAttribute->whereHas('productAttributeTranslation', function($q) use ($name){
            $q->where('name', $name);
        })->first();
    }

    /**
     * @param $attributes
     * @return bool|mixed
     * @throws \App\Exceptions\Validation\ValidationException
     */
    public function create($attributes) {
        if($this->isValid($attributes)) {
            $t_attributes = array();
            $t_attributes['product_attribute_key'] = Str::slug($attributes['name'][getLang()], '_');
            foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
                $t_attributes[$locale] = [
                    'name' => $attributes['name'][$locale]
                ];
            }
            //dd($t_attributes);
            //\DB::enableQueryLog();
            $this->productAttribute->fill($t_attributes)->save();
            //dd(\DB::getQueryLog());

            return true;
        }
        throw new ValidationException('Product attribute validation failed', $this->getErrors());
    }

    /**
     * @param $id
     * @param $attributes
     * @return bool|mixed
     * @throws \App\Exceptions\Validation\ValidationException
     */
    public function update($id, $attributes) {
        if($this->isValid($attributes)) {
            $t_attributes = array();
            $this->productAttribute = $this->find($id);
            $t_attributes['product_attribute_key'] = Str::slug($attributes['name'][getLang()], '_');
            foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
                $t_attributes[$locale] = [
                    'name' => $attributes['name'][$locale]
                ];
            }
            $this->productAttribute->fill($t_attributes)->save();
            return true;
        }
        throw new ValidationException('Product attribute validation failed', $this->getErrors());
    }

    /**
     * @param $id
     * @return mixed|void
     */
    public function delete($id) {
        $productAttribute = $this->productAttribute->findOrFail($id);
        $productAttribute->delete();
    }

    /**
     * Get total productAttribute count
     * @param bool $all
     * @return mixed
     */
    protected function totalProductAttributes($all = false) {
        return $this->productAttribute->count();
    }

    public function hasProductAttributeOption($product_attribute_id){
        $hasItem = $this->productAttributeOption->where('product_attribute_id', $product_attribute_id)->count();
        if($hasItem > 0)
            return true;
        else
            return false;
    }
}
