<?php namespace App\Repositories\Permission;

use App\Models\Permission;
use Config;
use Response;
use App\Repositories\RepositoryAbstract;
use App\Repositories\CrudableInterface as CrudableInterface;
use App\Repositories\RepositoryInterface as RepositoryInterface;
use App\Exceptions\Validation\ValidationException;
use App\Repositories\AbstractValidator as Validator;
use LaravelLocalization;
use Notification;

class PermissionRepository extends RepositoryAbstract implements PermissionInterface, CrudableInterface {


    protected $perPage;
    protected $permission;
    /**
     * Rules
     *
     * @var array
     */
    protected static $rules;
    protected static $attributeNames;

    /**
     * @param Permission $permission
     */
    public function __construct(Permission $permission) {

        $config = Config::get('holiday');
        $this->perPage = $config['per_page'];
        $this->permission = $permission;

        $rules_n_attributeNames = $this->rules();
        self::$rules = $rules_n_attributeNames['rules'];
        self::$attributeNames = $rules_n_attributeNames['attributeNames'];
    }

    public function rules(){
        $_rules = array();
        $setAttributeNames = array();
        $_rules['name'] = 'required';
        $_rules['display_name'] = 'required';
        $_rules['module'] = 'required';
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
        return $this->permission->get();
    }

    /**
     * @param $limit
     * @return mixed
     */
    public function getLastPermission($limit) {

        return $this->permission->orderBy('created_at', 'desc')->take($limit)->offset(0)->get();
    }

    /**
     * @return mixed
     */
    public function lists() {

        //return $this->permission->get()->lists('title', 'id');
        return $this->permission->all()->lists('title', 'id');
    }

    /**
     * Get paginated permissions
     *
     * @param int $page Number of permissions per page
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

        //$query = $this->permission->with('tags')->orderBy('created_at', 'DESC');
        $query = $this->permission->orderBy('created_at', 'DESC');

        if(!$all) {
            $query->where('is_published', 1);
        }

        $permissions = $query->skip($limit * ($page - 1))->take($limit)->get();

        $result->totalItems = $this->totalPermissions($all);
        $result->items = $permissions->all();

        return $result;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function find($id) {
        return $this->permission->findOrFail($id);
    }

    /**
     * @param $slug
     * @return mixed
     */
     public function getBySlug($slug, $isPublished = false) {
        if($isPublished === true)
           return $this->permission->select('permissions.id', 'permission_translations.slug')
            ->join('permission_translations', 'permissions.id', '=', 'permission_translations.permission_id')
            ->where('slug', $slug)->where('is_published', true)->firstOrFail();

        return $this->permission->select('permissions.id', 'permission_translations.slug')
            ->join('permission_translations', 'permissions.id', '=', 'permission_translations.permission_id')
            ->where('slug', $slug)->firstOrFail();
    }

    /**
     * @param $attributes
     * @return bool|mixed
     * @throws \App\Exceptions\Validation\ValidationException
     */
    public function create($attributes) {

        if($this->isValid($attributes)) {
            $permission = $this->permission->create($attributes);
            //$role = $this->role->findBy('name', 'admin');
            //$role->perms()->sync([$permission->id], false);
            /*$t_attributes = array();
            $capability_datarow = buildPOST_fromJS($attributes['capability_datarow']);
            $slugs = [];
            foreach($capability_datarow as $datarow){
                //foreach($datarow as $ff_name => $ff){
                    $slugs[$datarow['capability']['val']] = $datarow['is_able']['val'] == 0?false:true;
                //}
            }
            //dd($slugs);
            //$permission = new Permission();
            $permUser = $this->permission->create([ 
                'name'        => strtolower($attributes['name']),
                'slug'        => $slugs,
                'description' => $attributes['description']
            ]);*/

            /*$permUser = $this->permission->create([ 
                'name'        => 'user',
                'slug'        => [          // pass an array of permissions.
                    'create'     => true,
                    'view'       => true,
                    'update'     => true,
                    'delete'     => true,
                    'view.phone' => true
                ],
                'description' => 'manage user permissions'
            ]);*/    

            return true;
        }
        throw new ValidationException('User Permission validation failed', $this->getErrors());
    }

    /**
     * @param $id
     * @param $attributes
     * @return bool|mixed
     * @throws \App\Exceptions\Validation\ValidationException
     */
    public function update($id, $attributes) {
        if($this->isValid($attributes)) {
            /*$t_attributes = array();
            $slugs = [];
            if(isset($attributes['capability_datarow'])){
                $capability_datarow = buildPOST_fromJS($attributes['capability_datarow']);
               
                foreach($capability_datarow as $datarow){
                    //foreach($datarow as $ff_name => $ff){
                        $slugs[$datarow['capability']['val']] = $datarow['is_able']['val'] == 0?false:true;
                    //}
                }
            }
            //dd(json_encode($slugs));
            //dd($slugs);
            //$permission = new Permission();
            $this->permission = $this->find($id);
            $permUser = $this->permission->update([ 
                'slug'        => $slugs,
                'description' => $attributes['description']
            ]);*/

        $permission = $this->find($id);
        $permission->update($attributes);

            /*$permUser = $this->permission->create([ 
                'name'        => 'user',
                'slug'        => [          // pass an array of permissions.
                    'create'     => true,
                    'view'       => true,
                    'update'     => true,
                    'delete'     => true,
                    'view.phone' => true
                ],
                'description' => 'manage user permissions'
            ]);*/    

            return true;
        }

        throw new ValidationException('User Permission validation failed', $this->getErrors());
    }

    /**
     * @param $id
     * @return mixed|void
     */
    public function delete($id) {
        $this->permission = $this->permission->findOrFail($id);
        $this->permission->delete();
    }

    /**
     * @param $id
     * @return mixed
     */
    public function togglePublish($id) {

        $permission = $this->permission->find($id);

        $permission->is_published = ($permission->is_published) ? false : true;
        $permission->save();

        return Response::json(array('result' => 'success', 'changed' => ($permission->is_published) ? 1 : 0));
    }

    /**
     * @param $id
     * @return string
     */
    function getUrl($id) {

        $permission = $this->permission->findOrFail($id);
        return url('permission/' . $id . '/' . $permission->slug, $parameters = array(), $secure = null);
    }

    /**
     * Get total permission count
     * @param bool $all
     * @return mixed
     */
    protected function totalPermissions($all = false) {

        if(!$all) {
            return $this->permission->where('is_published', 1)->count();
        }

        return $this->permission->count();
    }
}
