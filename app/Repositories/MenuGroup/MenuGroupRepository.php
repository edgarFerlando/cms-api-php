<?php namespace App\Repositories\MenuGroup;

use App\MenuGroup;
//use App\PageTranslation;
use Config;
use Response;
use App\Repositories\RepositoryAbstract;
use App\Repositories\CrudableInterface as CrudableInterface;
use App\Repositories\RepositoryInterface as RepositoryInterface;
use App\Exceptions\Validation\ValidationException;
use App\Repositories\AbstractValidator as Validator;
use LaravelLocalization;
use Notification;

class MenuGroupRepository extends RepositoryAbstract implements MenuGroupInterface, CrudableInterface {

    /**
     * @var
     */
    protected $perPage;
    /**
     * @var \Page
     */
    protected $menuGroup;
    /**
     * Rules
     *
     * @var array
     */
    protected static $rules; /*[
        'title'   => 'required|min:3',
        'content' => 'required|min:5'];*/

    protected static $attributeNames;

    /**
     * @param MenuGroup $menuGroup
     */
    public function __construct(MenuGroup $menuGroup) {
        $this->perPage = Config::get('holiday.per_page');
        $this->menuGroup = $menuGroup;
        $rules_n_attributeNames = $this->rules();
        self::$rules = $rules_n_attributeNames['rules'];
        self::$attributeNames = $rules_n_attributeNames['attributeNames'];
    }

    public function rules(){
        $rules = array();
        $setAttributeNames = array();
        $rules['title'] = 'required|max:255';
        $setAttributeNames['title'] = trans('app.title');
        return [
            'rules' => $rules,
            'attributeNames' => $setAttributeNames
        ];
    }

    /**
     * @return mixed
     */
    public function all() {

        return $this->menuGroup->orderBy('created_at', 'DESC')->get();
    }

    /**
     * @return mixed
     */
    public function lists() {

        //return $this->menuGroup->lists('title', 'id');
        return $this->menuGroup->all()->lists('title', 'id');
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

        $query = $this->menuGroup->orderBy('created_at', 'DESC');
        $menuGroups = $query->skip($limit * ($page - 1))->take($limit)->get();
        $result->totalItems = $this->totalMenuGroups();
        $result->items = $menuGroups->all();

        return $result;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function find($id) {

        return $this->menuGroup->find($id);
    }

    /**
     * @param $attributes
     * @return bool|mixed
     * @throws \App\Exceptions\Validation\ValidationException
     */
    public function create($attributes) {
        if($this->isValid($attributes)) {
            $this->menuGroup->fill($attributes)->save();

            return true;
        }
        throw new ValidationException('Menu group validation failed', $this->getErrors());
    }

    /**
     * @param $id
     * @param $attributes
     * @return bool|mixed
     * @throws \App\Exceptions\Validation\ValidationException
     */
    public function update($id, $attributes) {
        if($this->isValid($attributes)) {
            $this->menuGroup = $this->find($id);
            $this->menuGroup->fill($attributes)->save();
            return true;
        }

        throw new ValidationException('Menu group validation failed', $this->getErrors());
    }

    /**
     * @param $id
     * @return mixed|void
     */
    public function delete($id) {

        $this->menuGroup->findOrFail($id)->delete();
    }

    /**
     * Get total page count
     * @param bool $all
     * @return mixed
     */
    protected function totalMenuGroups() {
        return $this->menuGroup->count();
    }
}
