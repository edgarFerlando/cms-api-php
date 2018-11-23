<?php namespace App\Repositories\ProductVariation;

use App\ProductVariation;
use Config;
use Response;
use Str;
use App\Repositories\RepositoryAbstract;
use App\Repositories\CrudableInterface as CrudableInterface;
use App\Repositories\RepositoryInterface as RepositoryInterface;
use App\Exceptions\Validation\ValidationException;
use App\Repositories\AbstractValidator as Validator;
use LaravelLocalization;
use Notification;


class ProductVariationRepository extends RepositoryAbstract implements ProductVariationInterface, CrudableInterface {

    protected $perPage;
    protected $productVariation;
    /**
     * Rules
     *
     * @var array
     */
    protected static $rules;
    protected static $attributeNames;

    /**
     * @param ProductVariation $productVariation
     */
    public function __construct(ProductVariation $productVariation) {

        $config = Config::get('holiday');
        $this->perPage = $config['per_page'];
        $this->productVariation = $productVariation;

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
        return $this->productVariation->with('productVariationOption')->orderBy('created_at', 'DESC')->get();
    }

    /**
     * @param $limit
     * @return mixed
     */
    public function getLastProductVariation($limit) {

        return $this->productVariation->orderBy('created_at', 'desc')->take($limit)->offset(0)->get();
    }

    /**
     * @return mixed
     */
    public function lists() {
        return $this->productVariation->all()->lists('name', 'id');
    }

    /**
     * Get paginated productVariations
     *
     * @param int $page Number of productVariations per page
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

        $query = $this->productVariation->orderBy('created_at', 'DESC');

        if(!$all) {
            $query->where('is_published', 1);
        }

        $productVariations = $query->skip($limit * ($page - 1))->take($limit)->get();

        $result->totalItems = $this->totalProductVariations($all);
        $result->items = $productVariations->all();

        return $result;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function find($id) {
        return $this->productVariation->with(['product', 'productSku', 'productAttributeOption.productAttributeOptionTranslation'])->findOrFail($id);
    }

    public function findByProduct($id) {
        return $this->productVariation->with(['product', 'product.productTranslation', 'product.productMetas.productMetaTranslation', 'product.productImages', 'productSku', 'productAttributeOption.productAttributeOptionTranslation'])->findOrFail($id);
    }
    /**
     * @param $attributes
     * @return bool|mixed
     * @throws \App\Exceptions\Validation\ValidationException
     */
    public function create($attributes) {
        if($this->isValid($attributes)) {
            $t_attributes = array();
            foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
                $t_attributes[$locale] = [
                    'name' => $attributes['name'][$locale]
                ];
            }
            $this->productVariation->fill($t_attributes)->save();

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
            $this->productVariation = $this->find($id);
            foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
                $t_attributes[$locale] = [
                    'name' => $attributes['name'][$locale]
                ];
            }
            $this->productVariation->fill($t_attributes)->save();
            return true;
        }
        throw new ValidationException('Product attribute validation failed', $this->getErrors());
    }

    /**
     * @param $id
     * @return mixed|void
     */
    public function delete($id) {
        $productVariation = $this->productVariation->findOrFail($id);
        $productVariation->delete();
    }

    /**
     * Get total productVariation count
     * @param bool $all
     * @return mixed
     */
    protected function totalProductVariations($all = false) {
        return $this->productVariation->count();
    }

    public function findProductAttributeOption($id){
        return $this->productVariation->with(['productAttributeOption.productAttributeOptionTranslation'])->findOrFail($id);
    }
}
