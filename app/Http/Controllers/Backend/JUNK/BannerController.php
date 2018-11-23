<?php namespace App\Http\Controllers\Backend;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Repositories\Banner\BannerInterface;
//use App\Repositories\Taxonomy\TaxonomyInterface;
use Redirect;
use View;
use Input;
use Validator;
use Response;
use Notification;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use App\Repositories\Banner\BannerRepository as Banner;
use App\Repositories\Taxonomy\TaxonomyRepository as Category;
use App\Exceptions\Validation\ValidationException;
use Config;
use Entrust;

use App\Models\BannerGroup;
use App\Repositories\Menu\MenuInterface;

class BannerController extends Controller {

	protected $banner;
    protected $group;
    protected $menu;

    public function __construct(BannerInterface $banner, MenuInterface $menu) {
        $this->banner = $banner;
        $this->group = new BannerGroup;
        $this->menu = $menu;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index() {
        $attr = [ 
                'title' => trans('app.banner')
            ];
        if(!Entrust::can(['read_banner'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $page = Input::get('page', 1);
        $perPage = config_db_cached('settings::backend_per_page');
        $pagiData = $this->banner->paginate($page, $perPage, true);

        $banners = new LengthAwarePaginator($pagiData->items, $pagiData->totalItems, $perPage, $page, [
            'path' => Paginator::resolveCurrentPath()
        ]);

        $banners->setPath("");

        return view('backend.banner.index', compact('banners'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create() {
        $attr = [ 
                'title' => trans('app.banner')
            ];
        if(!Entrust::can(['create_banner'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
    	$options = $this->menu->getMenuOptions();
        $group_options[''] = '-';
        $group_options += $this->group->lists('name', 'id');
        return view('backend.banner.create', compact('group_options', 'options'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store() {
        try {
            $this->banner->create(Input::all());
            Notification::success( trans('app.data_added') );
            return langRedirectRoute('admin.banner.index');
        } catch (ValidationException $e) {
            return langRedirectRoute('admin.banner.create')->withInput()->withErrors($e->getErrors());
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
                'title' => trans('app.banner')
            ];
        if(!Entrust::can(['read_banner'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $banner = $this->banner->find($id);
        return view('backend.banner.show', compact('banner'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function edit($id) {
        $attr = [ 
                'title' => trans('app.banner')
            ];
        if(!Entrust::can(['update_banner'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $banner = $this->banner->find($id);
        /*$tags = null;

        foreach ($banner->tags as $tag) {
            $tags .= ',' . $tag->name;
        }
        $tags = substr($tags, 1);*/
        $options = $this->menu->getMenuOptions();
        $group_options[''] = '-';
        $group_options += $this->group->lists('name', 'id');
        return view('backend.banner.edit', compact('banner','group_options', 'options'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int $id
     * @return Response
     */
    public function update($id) {
        try {
            $this->banner->update($id, Input::all());
            Notification::success( trans('app.data_updated') );
            return langRedirectRoute('admin.banner.index');
        } catch (ValidationException $e) {
            return langRedirectRoute('admin.banner.edit', [ 'id' => $id ] )->withInput()->withErrors($e->getErrors());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return Response
     */
    public function destroy($id) {

        $this->banner->delete($id);
        Notification::success( trans('app.data_deleted') );
        return langRedirectRoute('admin.banner.index');
    }

    public function confirmDestroy($id) {
        $attr = [ 
                'title' => trans('app.banner')
            ];
        if(!Entrust::can(['delete_banner'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $banner = $this->banner->find($id);
        return view('backend.banner.confirm-destroy', compact('banner'));
    }

    public function togglePublish($id) {

        return $this->banner->togglePublish($id);
    }

}