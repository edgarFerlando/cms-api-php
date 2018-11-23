<?php namespace App\Repositories\Role;

use App\Models\Role;
use Config;
use Response;
use App\Repositories\RepositoryAbstract;
use App\Repositories\CrudableInterface as CrudableInterface;
use App\Repositories\RepositoryInterface as RepositoryInterface;
use App\Exceptions\Validation\ValidationException;
use App\Repositories\AbstractValidator as Validator;
use LaravelLocalization;
use Notification;

class RoleRepository extends RepositoryAbstract implements RoleInterface, CrudableInterface {


    protected $perPage;
    protected $role;
    /**
     * Rules
     *
     * @var array
     */
    protected static $rules;
    protected static $attributeNames;

    /**
     * @param Role $role
     */
    public function __construct(Role $role) {

        $config = Config::get('holiday');
        $this->perPage = $config['per_page'];
        $this->role = $role;

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

        //return $this->role->with('tags')->orderBy('created_at', 'DESC')->where('is_published', 1)->get();
        return $this->role->orderBy('created_at', 'DESC')->where('is_published', 1)->get();
    }

    /**
     * @param $limit
     * @return mixed
     */
    public function getLastRole($limit) {

        return $this->role->orderBy('created_at', 'desc')->take($limit)->offset(0)->get();
    }

    /**
     * @return mixed
     */
    public function lists($name, $key) {

        //return $this->role->get()->lists('title', 'id');
        return $this->role->all()->lists($name, $key);
    }

    public function userByRole($id) {
        //\DB::enableQueryLog();

        return $this->role->find($id)->users()->with('userMetas')->get();

        //dd(\DB::getQueryLog());
    }

    /**
     * Get paginated roles
     *
     * @param int $page Number of roles per page
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

        //$query = $this->role->with('tags')->orderBy('created_at', 'DESC');
        $query = $this->role->orderBy('created_at', 'DESC');

        if(!$all) {
            $query->where('is_published', 1);
        }

        $roles = $query->skip($limit * ($page - 1))->take($limit)->get();

        $result->totalItems = $this->totalRoles($all);
        $result->items = $roles->all();

        return $result;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function find($id) {
        return $this->role->findOrFail($id);
    }

    /**
     * @param $slug
     * @return mixed
     */
     public function getBySlug($slug, $isPublished = false) {
        if($isPublished === true)
           return $this->role->select('roles.id', 'role_translations.slug')
            ->join('role_translations', 'roles.id', '=', 'role_translations.role_id')
            ->where('slug', $slug)->where('is_published', true)->firstOrFail();

        return $this->role->select('roles.id', 'role_translations.slug')
            ->join('role_translations', 'roles.id', '=', 'role_translations.role_id')
            ->where('slug', $slug)->firstOrFail();
    }

    /**
     * @param $attributes
     * @return bool|mixed
     * @throws \App\Exceptions\Validation\ValidationException
     */
    public function create($attributes) {

        if($this->isValid($attributes)) {
            $role = $this->role->create($attributes);
            if(!isset($attributes['perms']))
                $attributes['perms'] = [];
            $role->savePermissions($attributes['perms']);
            return true;
        }
        throw new ValidationException('User role validation failed', $this->getErrors());
    }

    /**
     * @param $id
     * @param $attributes
     * @return bool|mixed
     * @throws \App\Exceptions\Validation\ValidationException
     */
    public function update($id, $attributes) {
        if($this->isValid($attributes)) {
            $role = $this->find($id);
            $role->update($attributes);
            if(!isset($attributes['perms']))
                $attributes['perms'] = [];
            $role->savePermissions($attributes['perms']);
            return true;
        }

        throw new ValidationException('User role validation failed', $this->getErrors());
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
        $this->role->findOrFail($id)->delete();
    }

    /**
     * @param $id
     * @return mixed
     */
    public function togglePublish($id) {

        $role = $this->role->find($id);

        $role->is_published = ($role->is_published) ? false : true;
        $role->save();

        return Response::json(array('result' => 'success', 'changed' => ($role->is_published) ? 1 : 0));
    }

    /**
     * @param $id
     * @return string
     */
    function getUrl($id) {

        $role = $this->role->findOrFail($id);
        return url('role/' . $id . '/' . $role->slug, $parameters = array(), $secure = null);
    }

    /**
     * Get total role count
     * @param bool $all
     * @return mixed
     */
    protected function totalRoles($all = false) {

        if(!$all) {
            return $this->role->where('is_published', 1)->count();
        }

        return $this->role->count();
    }
}
