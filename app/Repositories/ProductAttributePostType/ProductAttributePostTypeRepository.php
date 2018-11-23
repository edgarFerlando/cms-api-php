<?php namespace App\Repositories\ProductAttributePostType;

use App\ProductAttributePostType;
use Config;
use Response;
//use App\Tag;
use App\ProductAttribute;
use Str;
//use Event;
//use Image;
//use File;
use App\Repositories\RepositoryAbstract;
use App\Repositories\CrudableInterface as CrudableInterface;
use App\Repositories\RepositoryInterface as RepositoryInterface;
use App\Exceptions\Validation\ValidationException;
use App\Repositories\AbstractValidator as Validator;
use LaravelLocalization;
use Notification;


class ProductAttributePostTypeRepository extends RepositoryAbstract implements ProductAttributePostTypeInterface, CrudableInterface {

    protected $perPage;
    protected $ProductAttributePostType;
    /**
     * Rules
     *
     * @var array
     */
    protected static $rules;
    protected static $attributeNames;

    /**
     * @param ProductAttributePostType $ProductAttributePostType
     */
    public function __construct(ProductAttributePostType $ProductAttributePostType) {

        $config = Config::get('holiday');
        $this->perPage = $config['per_page'];
        $this->ProductAttributePostType = $ProductAttributePostType;

        $rules_n_attributeNames = $this->rules();
        self::$rules = $rules_n_attributeNames['rules'];
        self::$attributeNames = $rules_n_attributeNames['attributeNames'];
    }

    public function rules(){
        $_rules = array();
        $setAttributeNames = array();
        $_rules['product_attribute'] = 'required';
        $setAttributeNames['product_attribute'] = trans('app.product_attribute');
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

        return $this->ProductAttributePostType
        ->orderBy('product_attribute_options.created_at', 'DESC')->get();

        /*return $this->ProductAttributePostType->select('product_attribute_options.*', 'product_attributes.name as product_attribute_name')
            ->join('product_attributes', 'product_attributes.id', '=', 'product_attribute_options.product_attribute_id')
            ->orderBy('product_attribute_options.created_at', 'DESC')
            ->get();*/
    }

    /**
     * @param $limit
     * @return mixed
     */
    public function getLastProductAttributePostType($limit) {
        return $this->ProductAttributePostType->orderBy('created_at', 'desc')->take($limit)->offset(0)->get();
    }

    /**
     * @return mixed
     */
    public function lists() {
        return $this->ProductAttributePostType->all()->lists('name', 'id');
    }

    /**
     * Get paginated ProductAttributePostTypes
     *
     * @param int $page Number of ProductAttributePostTypes per page
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

        $query = $this->ProductAttributePostType->with(['productAttribute'])->orderBy('created_at', 'DESC');
        /*$query = $this->ProductAttributePostType->select('product_attribute_options.*', 'product_attributes.name as product_attribute_name')
            ->join('product_attributes', 'product_attributes.id', '=', 'product_attribute_options.product_attribute_id')
            ->join('product_attribute_translations', 'product_attribute_translations.product_attribute_id', '=', 'product_attributes.id')
            ->orderBy('product_attribute_options.created_at', 'DESC');*/

        if(!$all) {
            $query->where('is_published', 1);
        }

        $ProductAttributePostTypes = $query->skip($limit * ($page - 1))->take($limit)->get();
        $result->totalItems = $this->totalProductAttributePostTypes($all);
        $result->items = $ProductAttributePostTypes->all();

        return $result;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function find($id) {
        return $this->ProductAttributePostType->findOrFail($id);
    }

    public function findByPostType($post_type) {
        return $this->ProductAttributePostType->with(['productAttribute.productAttributeTranslation', 'productAttribute.productAttributeOption.translations'])->where('post_type', $post_type)->get();
    }

    /**
     * @param $attributes
     * @return bool|mixed
     * @throws \App\Exceptions\Validation\ValidationException
     */
    public function create($attributes) {
        if($this->isValid($attributes)) {
            $t_attributes = array();
            $t_attributes['product_attribute_id'] = $attributes['product_attribute'];
            foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
                $t_attributes[$locale] = [
                    'name' => $attributes['name'][$locale]
                ];
            }
            $this->ProductAttributePostType->fill($t_attributes)->save();

            return true;
        }
        throw new ValidationException('Product attribute option validation failed', $this->getErrors());
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
            $t_attributes['product_attribute_id'] = $attributes['product_attribute'];
            $this->ProductAttributePostType = $this->find($id);
            foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
                $t_attributes[$locale] = [
                    'name' => $attributes['name'][$locale]
                ];
            }
            $this->ProductAttributePostType->fill($t_attributes)->save();

            return true;
        }

        throw new ValidationException('Product attribute option validation failed', $this->getErrors());
    }

    /**
     * @param $id
     * @return mixed|void
     */
    public function delete($id) {

        $ProductAttributePostType = $this->ProductAttributePostType->findOrFail($id);
        $ProductAttributePostType->delete();
    }

    /**
     * @param $id
     * @return mixed
     */
    public function togglePublish($id) {

        $ProductAttributePostType = $this->ProductAttributePostType->find($id);

        $ProductAttributePostType->is_published = ($ProductAttributePostType->is_published) ? false : true;
        $ProductAttributePostType->save();

        return Response::json(array('result' => 'success', 'changed' => ($ProductAttributePostType->is_published) ? 1 : 0));
    }

    /**
     * @param $id
     * @return string
     */
    function getUrl($id) {

        $ProductAttributePostType = $this->ProductAttributePostType->findOrFail($id);
        return url('ProductAttributePostType/' . $id . '/' . $ProductAttributePostType->slug, $parameters = array(), $secure = null);
    }

    /**
     * Get total ProductAttributePostType count
     * @param bool $all
     * @return mixed
     */
    protected function totalProductAttributePostTypes($all = false) {

        if(!$all) {
            return $this->ProductAttributePostType->where('is_published', 1)->count();
        }

        return $this->ProductAttributePostType->count();
    }
}
