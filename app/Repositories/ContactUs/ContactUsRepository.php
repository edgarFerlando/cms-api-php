<?php namespace App\Repositories\ContactUs;

use App\ContactUs;
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


class ContactUsRepository extends RepositoryAbstract implements ContactUsInterface, CrudableInterface {

    protected $perPage;
    protected $contactUs;
    /**
     * Rules
     *
     * @var array
     */
    protected static $rules;
    protected static $attributeNames;

    /**
     * @param ContactUs $contactUs
     */
    public function __construct(ContactUs $contactUs) {

        $config = Config::get('holiday');
        $this->perPage = $config['per_page'];
        $this->contactUs = $contactUs;

        $rules_n_attributeNames = $this->rules();
        self::$rules = $rules_n_attributeNames['rules'];
        self::$attributeNames = $rules_n_attributeNames['attributeNames'];
    }

    public function rules(){
        $_rules = array();
        $setAttributeNames = array();
        foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
            $_rules['detail.'.$locale] = 'required';

            $setAttributeNames['detail.' . $locale] = trans('app.detail').' [ ' . $properties['native'].' ]';
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
        return $this->contactUs->with('contactUsOption')->orderBy('created_at', 'DESC')->get();
    }

    /**
     * @param $limit
     * @return mixed
     */
    public function getLastContactUs($limit) {

        return $this->contactUs->orderBy('created_at', 'desc')->take($limit)->offset(0)->get();
    }

    /**
     * @return mixed
     */
    public function lists() {
        return $this->contactUs->all()->lists('name', 'id');
    }

    /**
     * Get paginated contactUss
     *
     * @param int $page Number of contactUss per page
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

        $query = $this->contactUs->orderBy('created_at', 'DESC');

        if(!$all) {
            $query->where('is_published', 1);
        }

        $contactUss = $query->skip($limit * ($page - 1))->take($limit)->get();

        $result->totalItems = $this->totalContactUss($all);
        $result->items = $contactUss->all();

        return $result;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function find($id) {
        return $this->contactUs->findOrFail($id);
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
            $this->contactUs->fill($t_attributes)->save();

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
            $this->contactUs = $this->find($id);
            foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
                $t_attributes[$locale] = [
                    'name' => $attributes['name'][$locale]
                ];
            }
            $this->contactUs->fill($t_attributes)->save();
            return true;
        }
        throw new ValidationException('Product attribute validation failed', $this->getErrors());
    }

    /**
     * @param $id
     * @return mixed|void
     */
    public function delete($id) {
        $contactUs = $this->contactUs->findOrFail($id);
        $contactUs->delete();
    }

    /**
     * Get total contactUs count
     * @param bool $all
     * @return mixed
     */
    protected function totalContactUss($all = false) {
        return $this->contactUs->count();
    }
}
