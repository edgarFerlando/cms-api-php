<?php namespace App\Repositories\ProductMeta;

use App\Models\ProductMeta;
use Config;
use Response;
use App\Repositories\RepositoryAbstract;
use App\Repositories\CrudableInterface as CrudableInterface;
use App\Repositories\RepositoryInterface as RepositoryInterface;
use App\Exceptions\Validation\ValidationException;
use App\Repositories\AbstractValidator as Validator;
use LaravelLocalization;
use Notification;

class ProductMetaRepository extends RepositoryAbstract implements ProductMetaInterface, CrudableInterface {


    protected $perPage;
    protected $productMeta;
    /**
     * Rules
     *
     * @var array
     */
    protected static $rules;
    protected static $attributeNames;

    /**
     * @param ProductMeta $productMeta
     */
    public function __construct(ProductMeta $productMeta) {

        $config = Config::get('holiday');
        $this->perPage = $config['per_page'];
        $this->productMeta = $productMeta;

        $rules_n_attributeNames = $this->rules();
        self::$rules = $rules_n_attributeNames['rules'];
        self::$attributeNames = $rules_n_attributeNames['attributeNames'];
    }

    public function rules(){
        $_rules = array();
        $setAttributeNames = array();
        $_rules['name'] = 'required';
        $_rules['display_name'] = 'required';
        $setAttributeNames['name'] = trans('app.name');
        $setAttributeNames['display_name'] = trans('app.display_name');
        $setAttributeNames['module'] = trans('app.module');
        return [
            'rules' => $_rules,
            'attributeNames' => $setAttributeNames
        ];
    }

    /**
     * @return mixed
     */
    public function all() {

        //return $this->productMeta->with('tags')->orderBy('created_at', 'DESC')->where('is_published', 1)->get();
        return $this->productMeta->orderBy('created_at', 'DESC')->where('is_published', 1)->get();
    }

    /**
     * @param $limit
     * @return mixed
     */
    public function getLastProductMeta($limit) {

        return $this->productMeta->orderBy('created_at', 'desc')->take($limit)->offset(0)->get();
    }

    /**
     * @return mixed
     */
    public function lists($name, $key) {

        //return $this->productMeta->get()->lists('title', 'id');
        return $this->productMeta->all()->lists($name, $key);
    }

    /**
     * Get paginated productMetas
     *
     * @param int $page Number of productMetas per page
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

        //$query = $this->productMeta->with('tags')->orderBy('created_at', 'DESC');
        $query = $this->productMeta->orderBy('created_at', 'DESC');

        if(!$all) {
            $query->where('is_published', 1);
        }

        $productMetas = $query->skip($limit * ($page - 1))->take($limit)->get();

        $result->totalItems = $this->totalProductMetas($all);
        $result->items = $productMetas->all();

        return $result;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function find($id) {
        return $this->productMeta->findOrFail($id);
    }

    /**
     * @param $slug
     * @return mixed
     */
     public function getBySlug($slug, $isPublished = false) {
        if($isPublished === true)
           return $this->productMeta->select('productMetas.id', 'productMeta_translations.slug')
            ->join('productMeta_translations', 'productMetas.id', '=', 'productMeta_translations.productMeta_id')
            ->where('slug', $slug)->where('is_published', true)->firstOrFail();

        return $this->productMeta->select('productMetas.id', 'productMeta_translations.slug')
            ->join('productMeta_translations', 'productMetas.id', '=', 'productMeta_translations.productMeta_id')
            ->where('slug', $slug)->firstOrFail();
    }

    /**
     * @param $attributes
     * @return bool|mixed
     * @throws \App\Exceptions\Validation\ValidationException
     */
    public function create($attributes) {

        if($this->isValid($attributes)) {
            $productMeta = $this->productMeta->create($attributes);
            if(!isset($attributes['perms']))
                $attributes['perms'] = [];
            $productMeta->savePermissions($attributes['perms']);
            return true;
        }
        throw new ValidationException('User productMeta validation failed', $this->getErrors());
    }

    /**
     * @param $id
     * @param $attributes
     * @return bool|mixed
     * @throws \App\Exceptions\Validation\ValidationException
     */
    public function update($id, $attributes) {
        if($this->isValid($attributes)) {
            $productMeta = $this->find($id);
            $productMeta->update($attributes);
            if(!isset($attributes['perms']))
                $attributes['perms'] = [];
            $productMeta->savePermissions($attributes['perms']);
            return true;
        }

        throw new ValidationException('User productMeta validation failed', $this->getErrors());
    }

    /**
     * @param $id
     * @return mixed|void
     */
    public function delete($id) {
        if($id == 1)
        {
            abort(403);
        }
        $this->productMeta->findOrFail($id)->delete();
    }

    /**
     * @param $id
     * @return mixed
     */
    public function togglePublish($id) {

        $productMeta = $this->productMeta->find($id);

        $productMeta->is_published = ($productMeta->is_published) ? false : true;
        $productMeta->save();

        return Response::json(array('result' => 'success', 'changed' => ($productMeta->is_published) ? 1 : 0));
    }

    /**
     * @param $id
     * @return string
     */
    function getUrl($id) {

        $productMeta = $this->productMeta->findOrFail($id);
        return url('productMeta/' . $id . '/' . $productMeta->slug, $parameters = array(), $secure = null);
    }

    /**
     * Get total productMeta count
     * @param bool $all
     * @return mixed
     */
    protected function totalProductMetas($all = false) {

        if(!$all) {
            return $this->productMeta->where('is_published', 1)->count();
        }

        return $this->productMeta->count();
    }
}
