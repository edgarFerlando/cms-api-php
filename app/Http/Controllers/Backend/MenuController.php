<?php namespace App\Http\Controllers\Backend;

/*use Fully\Http\Controllers\Controller;
use Fully\Repositories\Menu\MenuInterface;
use View;
use Validator;
use Redirect;
use Input;
use Fully\Models\Menu;
use URL;
use Route;
use Request;
use Exception;
use Response;
use Notification;*/
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Redirect;
use Response;
use Request;
use Input;
use Validator;
use Notification;
//use App\Repositories\Menu\MenuInterface;
use App\Repositories\Menu\MenuRepository as Menu;
use App\Repositories\MenuGroup\MenuGroupRepository as MenuGroup;
use App\Exceptions\Validation\ValidationException;
use Entrust;

class MenuController extends Controller {

	protected $menu;
    protected $menuGroup;

    public function __construct(Menu $menu, MenuGroup $menuGroup) {
        $this->menu = $menu;
        $this->menuGroup = $menuGroup;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index($menu_group_id = null) { 
        //$items = $this->menu->all();
        $attr = [ 
                'title' => trans('app.menu')
            ];
        if(!Entrust::can(['read_menu'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $items = $this->menu->findByMenuGroupID($menu_group_id);//ini bisa pakai has many ...
        $menus = $this->menu->getMenuHTML($items);
        $menu_group = $this->menuGroup->find($menu_group_id);
        return view('backend.menu.index', compact('menus', 'menu_group_id', 'menu_group'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create(){//$menu_group_id = null) { 
        $attr = [ 
                'title' => trans('app.menu')
            ];
        if(!Entrust::can(['create_menu'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $options = $this->menu->getMenuOptions();
        $parent_options[''] = '-';
        $parent_options += renderLists($this->menu->all());
        //$menuGroup_options[''] = '-';
        //$menuGroup_options += $this->menuGroup->lists(); 
        return view('backend.menu.create', compact('options', 'parent_options'));//, 'menuGroup_options', 'menu_group_id'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store() {
        try {
            $this->menu->create(Input::all());
            Notification::success( trans('app.menu_added') );
            return Redirect::route('admin.menu_group.items', [ 'menu_group' => Request::get('menu_group') ]);
        } catch (ValidationException $e) {
            return langRedirectRoute('admin.menu.create', [ 'menu_group' => Request::get('menu_group') ])->withInput()->withErrors($e->getErrors());
        }
/*
        $formData = Request::all();
        if ($formData['type'] == 'module') {
            $option = $formData['option'];
            $url = $this->menu->getUrl($option);
            $formData['url'] = $url;
        }

        $host = $_SERVER['SERVER_NAME'];
        $urlInfo = parse_url($formData['url']);

        $rules = array(
            'title' => 'required',
            'url'   => 'required'
        );

        $validation = Validator::make($formData, $rules);

        if ($validation->fails()) {
            return langRedirectRoute('admin.menu.create')->withErrors($validation)->withInput();
        }

        $this->menu->fill($formData);
        $this->menu->order = $this->menu->getMaxOrder() + 1;

        if (isset($urlInfo['host']))
            $url = ($host == $urlInfo['host']) ? $urlInfo['path'] : $formData['url'];
        else
            $url = ($formData['type'] == 'module') ? $formData['url'] : "http://" . $formData['url'];

        $this->menu->lang = getLang();
        $this->menu->url = $url;
        $this->menu->save();

        Notification::success('Menu was successfully added');
        return langRedirectRoute('admin.menu.index');*/
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function show($id) {
        $attr = [ 
                'title' => trans('app.menu')
            ];
        if(!Entrust::can(['read_menu'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $menu = $this->menu->find($id);
        return view('backend.menu.show', compact('menu'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function edit($id) {
        $attr = [ 
                'title' => trans('app.menu')
            ];
        if(!Entrust::can(['update_menu'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $options = $this->menu->getMenuOptions();
        $parent_options[''] = '-';
        $parent_options += renderLists($this->menu->all());
        $menu = $this->menu->find($id);
        return view('backend.menu.edit', compact( 'menu', 'options', 'parent_options'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int $id
     * @return Response
     */
    public function update($id) {

        try {
            $this->menu->update($id, Input::all());
            $menu = $this->menu->find($id);
            Notification::success(trans('app.menu_updated'));
            //return langRedirectRoute('admin.menu.index');
            return Redirect::route('admin.menu_group.items', [ 'menu_group' => $menu->menu_group_id ]);
        } catch (ValidationException $e) {

            return langRedirectRoute('admin.menu.edit', [ 'id' => $id ])->withInput()->withErrors($e->getErrors());
        }



/*
        $formData = Input::all();

        if ($formData['type'] == 'module') {

            $option = $formData['option'];
            $url = $this->menu->getUrl($option);
            $formData['url'] = $url;
        }

        $host = $_SERVER['SERVER_NAME'];
        $urlInfo = parse_url($formData['url']);

        $rules = array(
            'title' => 'required',
            'url'   => 'required'
        );

        $validation = Validator::make($formData, $rules);

        if ($validation->fails()) {
            return langRedirectRoute('admin.menu.create')->withErrors($validation)->withInput();
        }

        $this->menu = $this->menu->find($id);
        $this->menu->fill($formData);

        if (isset($urlInfo['host']))
            $url = ($host == $urlInfo['host']) ? $urlInfo['path'] : $formData['url'];
        else
            $url = ($formData['type'] == 'module') ? $formData['url'] : "http://" . $formData['url'];

        $this->menu->url = $url;
        $this->menu->save();

        Notification::success('Menu was successfully updated');
        return langRedirectRoute('admin.menu.index');*/
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return Response
     */
    public function destroy($id) {

        if ($this->menu->hasChildItems($id)) {
            //throw new Exception("This menu has sub-menus. Can't delete!");
            Notification::info(trans('app.menu_has_submenus'));
            return Redirect::action('Backend\MenuController@index');
        }

        $this->menu = $this->menu->find($id);
        $menu_group_id = $this->menu->menu_group_id;
        $this->menu->delete();
        Notification::success( trans('app.menu_deleted') );
        //return langRedirectRoute('admin.menu.index');
        return Redirect::route('admin.menu_group.items', [ 'menu_group' => $menu_group_id ]);
    }

    public function confirmDestroy($id) {
        $attr = [ 
                'title' => trans('app.menu')
            ];
        if(!Entrust::can(['delete_menu'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $menu = $this->menu->find($id);
        return view('backend.menu.confirm-destroy', compact('menu'));
    }

    public function save() {

        $this->menu->changeParentById($this->menu->parseJsonArray(json_decode(Request::get('json'), true)));
        return Response::json(array('result' => 'success'));
    }

    public function togglePublish($id) {

        $this->menu = $this->menu->find($id);
        $this->menu->is_published = ($this->menu->is_published) ? false : true;
        $this->menu->save();
        return Response::json(array('result' => 'success', 'changed' => ($this->menu->is_published) ? 1 : 0));
    }

}



