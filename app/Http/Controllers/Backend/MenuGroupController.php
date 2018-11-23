<?php namespace App\Http\Controllers\Backend;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Repositories\MenuGroup\MenuGroupInterface;
use Redirect;
use View;
use Input;
use Validator;
use Response;
use Notification;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use App\Repositories\MenuGroup\MenuGroupRepository as MenuGroup;
use App\Exceptions\Validation\ValidationException;
use Config;
use Entrust;



class MenuGroupController extends Controller {

    protected $menuGroup;

    public function __construct(MenuGroupInterface $menuGroup) {
        $this->menuGroup = $menuGroup;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index() {
        $attr = [ 
                'title' => trans('app.menu_group')
            ];
        if(!Entrust::can(['read_menu_group'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $page = Input::get('page', 1);
        $perPage = Config::get('holiday.per_page');
        $pagiData = $this->menuGroup->paginate($page, $perPage, true);
        $menuGroups = new LengthAwarePaginator($pagiData->items, $pagiData->totalItems, $perPage, $page, [
            'path' => Paginator::resolveCurrentPath()
        ]);
        $menuGroups->setPath('');
        return view('backend.menuGroup.index', compact('menuGroups'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create() {
        $attr = [ 
                'title' => trans('app.menu_group')
            ];
        if(!Entrust::can(['create_menu_group'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        return view('backend.menuGroup.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store() {
        try {
            $this->menuGroup->create(Input::all());
            Notification::success( trans('app.menu_group_added') );
            return langRedirectRoute('admin.menu-group.index');
        } catch (ValidationException $e) {
            return langRedirectRoute('admin.menu-group.create')->withInput()->withErrors($e->getErrors());
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
                'title' => trans('app.menu_group')
            ];
        if(!Entrust::can(['read_menu_group'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $menuGroup = $this->menuGroup->find($id);
        return view('backend.menuGroup.show', compact('menuGroup'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function edit($id) { 
        $attr = [ 
                'title' => trans('app.menu_group')
            ];
        if(!Entrust::can(['update_menu_group'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $menuGroup = $this->menuGroup->find($id);
        return view('backend.menuGroup.edit', compact('menuGroup'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int $id
     * @return Response
     */
    public function update($id) {
        try {   
            $this->menuGroup->update($id, Input::all());
            Notification::success(trans('app.menu_group_updated'));
            return langRedirectRoute('admin.menu-group.index');
        } catch (ValidationException $e) {
            return langRedirectRoute('admin.menu-group.edit', [ 'id' => $id ] )->withInput()->withErrors($e->getErrors());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return Response
     */
    public function destroy($id) {

        $this->menuGroup->delete($id);
        Notification::success(trans('app.menu_group_deleted'));
        return langRedirectRoute('admin.menu-group.index');
    }

    public function confirmDestroy($id) {
        $attr = [ 
                'title' => trans('app.menu_group')
            ];
        if(!Entrust::can(['delete_menu_group'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $menuGroup = $this->menuGroup->find($id);
        return view('backend.menuGroup.confirm-destroy', compact('menuGroup'));
    }
}
