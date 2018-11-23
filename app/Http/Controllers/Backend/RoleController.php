<?php namespace App\Http\Controllers\Backend;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Repositories\Role\RoleInterface;
use App\Repositories\Permission\PermissionInterface;
use Redirect;
use View;
use Input;
use Validator;
use Response;
use Notification;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use App\Repositories\Role\RoleRepository as Role;
use App\Repositories\Permission\PermissionRepository as Permission;
use App\Exceptions\Validation\ValidationException;
use Config;
use Auth;
use Entrust;

class RoleController extends Controller {

	protected $role;
    protected $permission;

    public function __construct(RoleInterface $role, PermissionInterface $permission) {
        $this->role = $role;
        $this->permission = $permission;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index() {
        $attr = [ 
                'title' => trans('app.role')
            ];
        if(!Entrust::can(['read_role'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $page = Input::get('page', 1);
        $perPage = config_db_cached('settings::backend_per_page');
        $pagiData = $this->role->paginate($page, $perPage, true);

        $roles = new LengthAwarePaginator($pagiData->items, $pagiData->totalItems, $perPage, $page, [
            'path' => Paginator::resolveCurrentPath()
        ]);
        $roles->setPath("");

        return view('backend.role.index', compact('roles'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create() {
        $attr = [ 
                'title' => trans('app.role')
            ];
        if(!Entrust::can(['create_role'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $permissions = $this->permission->all();
        return view('backend.role.create', compact('permissions'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store() {
        try {
            $this->role->create(Input::all());
            Notification::success( trans('app.data_added') );
            return langRedirectRoute('admin.user.role.index');
        } catch (ValidationException $e) {
            return langRedirectRoute('admin.user.role.create')->withInput()->withErrors($e->getErrors());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function show($id) {
        $attr = [ 
                'title' => trans('app.role')
            ];
        if(!Entrust::can(['read_role'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $role = $this->role->find($id);
        return view('backend.role.show', compact('role'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function edit($id) {
        $attr = [ 
                'title' => trans('app.role')
            ];
        if(!Entrust::can(['update_role'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $role = $this->role->find($id);
        if($role->id == 1 && Auth::user()->id != 1)
        {
            abort(403);
        }
        $permissions = $this->permission->all();
        $rolePerms = $role->perms();
        return view('backend.role.edit', compact('role', 'permissions', 'rolePerms'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int $id
     * @return Response
     */
    public function update($id) {
        try {
            $this->role->update($id, Input::all());
            Notification::success( trans('app.data_updated') );
            return langRedirectRoute('admin.user.role.index');
        } catch (ValidationException $e) {
            return langRedirectRoute('admin.user.role.edit', [ 'id' => $id ] )->withInput()->withErrors($e->getErrors());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return Response
     */
    public function destroy($id) {
        $attr = [ 
                'title' => trans('app.role')
            ];
        if(!Entrust::can(['delete_role'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $this->role->delete($id);
        Notification::success( trans('app.user.data_deleted') );
        return langRedirectRoute('admin.user.role.index');
    }

    public function confirmDestroy($id) {

        $role = $this->role->find($id);
        return view('backend.role.confirm-destroy', compact('role'));
    }

    public function togglePublish($id) {

        return $this->role->togglePublish($id);
    }

}