<?php namespace App\Http\Controllers\Backend;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Repositories\Page\PageInterface;
use Redirect;
use View;
use Input;
use Validator;
use Response;
use Notification;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use App\Repositories\Page\PageRepository as Page;
use App\Exceptions\Validation\ValidationException;
use Config;
use Entrust;


class PageController extends Controller {

    protected $page;

    public function __construct(PageInterface $page) {
        $this->page = $page;
        //View::share('active', 'modules');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index() {
        $attr = [ 
                'title' => trans('app.page')
            ];
        if(!Entrust::can(['read_page'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $page = Input::get('page', 1);
        $perPage = config_db_cached('settings::backend_per_page');
        $pagiData = $this->page->paginate($page, $perPage, true);
        $pages = new LengthAwarePaginator($pagiData->items, $pagiData->totalItems, $perPage, $page, [
            'path' => Paginator::resolveCurrentPath()
        ]);
        $pages->setPath('');
        return view('backend.page.index', compact('pages'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create() {
        $attr = [ 
                'title' => trans('app.page')
            ];
        if(!Entrust::can(['create_page'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        return view('backend.page.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store() {
        try {
            $this->page->create(Input::all());
            Notification::success( trans('app.data_added') );
            return langRedirectRoute('admin.page.index');
        } catch (ValidationException $e) {
            return langRedirectRoute('admin.page.create')->withInput()->withErrors($e->getErrors());
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
                'title' => trans('app.page')
            ];
        if(!Entrust::can(['read_page'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $page = $this->page->find($id);
        return view('backend.page.show', compact('page'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function edit($id) { 
        $attr = [ 
                'title' => trans('app.page')
            ];
        if(!Entrust::can(['update_page'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $page = $this->page->find($id);
        return view('backend.page.edit', compact('page'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int $id
     * @return Response
     */
    public function update($id) {
        try {   
            $this->page->update($id, Input::all());
            Notification::success(trans('app.data_updated'));
            return langRedirectRoute('admin.page.index');
        } catch (ValidationException $e) {
            return langRedirectRoute('admin.page.edit', [ 'id' => $id ] )->withInput()->withErrors($e->getErrors());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return Response
     */
    public function destroy($id) {

        $this->page->delete($id);
        Notification::success(trans('app.data_deleted'));
        return langRedirectRoute('admin.page.index');
    }

    public function confirmDestroy($id) {
        $attr = [ 
                'title' => trans('app.page')
            ];
        if(!Entrust::can(['delete_page'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $page = $this->page->find($id);
        return view('backend.page.confirm-destroy', compact('page'));
    }

    public function togglePublish($id) {

        return $this->page->togglePublish($id);
    }
}