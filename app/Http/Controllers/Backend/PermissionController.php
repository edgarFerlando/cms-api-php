<?php namespace App\Http\Controllers\Backend;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Repositories\Permission\PermissionInterface;
use Redirect;
use View;
use Input;
use Validator;
use Response;
use Notification;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use App\Repositories\Permission\PermissionRepository as Permission;
use App\Exceptions\Validation\ValidationException;
use Config;

use Entrust;

class PermissionController extends Controller {

	protected $permission;

    public function __construct(PermissionInterface $permission) {
        $this->permission = $permission;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index() {
        $attr = [ 
                'title' => trans('app.permission')
            ];
        if(!Entrust::can(['read_permission'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $page = Input::get('page', 1);
        $perPage = config_db_cached('settings::backend_per_page');
        $pagiData = $this->permission->paginate($page, $perPage, true);

        $permissions = new LengthAwarePaginator($pagiData->items, $pagiData->totalItems, $perPage, $page, [
            'path' => Paginator::resolveCurrentPath()
        ]);
        $permissions->setPath("");

        return view('backend.permission.index', compact('permissions'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create() {
        $attr = [ 
                'title' => trans('app.permission')
            ];
        if(!Entrust::can(['create_permission'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        return view('backend.permission.create', compact('category_options'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store() {
        try {
            $this->permission->create(Input::all());
            Notification::success( trans('app.data_added') );
            return langRedirectRoute('admin.user.permission.index');
        } catch (ValidationException $e) {
            return langRedirectRoute('admin.user.permission.create')->withInput()->withErrors($e->getErrors());
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
                'title' => trans('app.permission')
            ];
        if(!Entrust::can(['read_permission'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $userPermission = $this->permission->find($id);
        return view('backend.permission.show', compact('userPermission'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function edit($id) {
        $attr = [ 
                'title' => trans('app.permission')
            ];
        if(!Entrust::can(['update_permission'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $permission = $this->permission->find($id);
        return view('backend.permission.edit', compact('permission'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int $id
     * @return Response
     */
    public function update($id) {
        try {
            $this->permission->update($id, Input::all());
            Notification::success( trans('app.data_updated') );
            return langRedirectRoute('admin.user.permission.index');
        } catch (ValidationException $e) {
            return langRedirectRoute('admin.user.permission.edit', [ 'id' => $id ] )->withInput()->withErrors($e->getErrors());
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
                'title' => trans('app.permission')
            ];
        if(!Entrust::can(['delete_permission'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $this->permission->delete($id);
        Notification::success( trans('app.data_deleted') );
        return langRedirectRoute('admin.user.permission.index');
    }

    public function confirmDestroy($id) {

        $permission = $this->permission->find($id);
        return view('backend.permission.confirm-destroy', compact('permission'));
    }

    public function togglePublish($id) {

        return $this->permission->togglePublish($id);
    }

}