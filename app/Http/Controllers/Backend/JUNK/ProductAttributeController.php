<?php namespace App\Http\Controllers\Backend;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Repositories\ProductAttribute\ProductAttributeInterface;
use Redirect;
use View;
use Input;
use Validator;
use Response;
use Notification;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use App\Repositories\ProductAttribute\ProductAttributeRepository as ProductAttribute;
use App\Exceptions\Validation\ValidationException;
use Config;
use Entrust;

class ProductAttributeController extends Controller {

    protected $productAttribute;

    public function __construct(ProductAttributeInterface $productAttribute) {
        $this->productAttribute = $productAttribute;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index() {
        $attr = [ 
                'title' => trans('app.product_attribute')
            ];
        if(!Entrust::can(['read_product_attribute'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $page = Input::get('page', 1);
        $perPage = config_db_cached('settings::backend_per_page');
        $pagiData = $this->productAttribute->paginate($page, $perPage, true);
        $productAttributes = new LengthAwarePaginator($pagiData->items, $pagiData->totalItems, $perPage, $page, [
            'path' => Paginator::resolveCurrentPath()
        ]);
        $productAttributes->setPath('');
        return view('backend.productAttribute.index', compact('productAttributes'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create() {
        $attr = [ 
                'title' => trans('app.product_attribute')
            ];
        if(!Entrust::can(['create_product_attribute'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        return view('backend.productAttribute.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store() {
        try {
            $this->productAttribute->create(Input::all());
            Notification::success( trans('app.product_attribute_added') );
            return langRedirectRoute('admin.product.attribute.index');
        } catch (ValidationException $e) {
            return langRedirectRoute('admin.product.attribute.create')->withInput()->withErrors($e->getErrors());
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
                'title' => trans('app.product_attribute')
            ];
        if(!Entrust::can(['read_product_attribute'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $productAttribute = $this->productAttribute->find($id);
        return view('backend.productAttribute.show', compact('productAttribute'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function edit($id) { 
        $attr = [ 
                'title' => trans('app.product_attribute')
            ];
        if(!Entrust::can(['update_product_attribute'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $productAttribute = $this->productAttribute->find($id);
        return view('backend.productAttribute.edit', compact('productAttribute'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int $id
     * @return Response
     */
    public function update($id) {
        try {   
            $this->productAttribute->update($id, Input::all());
            Notification::success(trans('app.product_attribute_updated'));
            return langRedirectRoute('admin.product.attribute.index');
        } catch (ValidationException $e) {
            return langRedirectRoute('admin.product.attribute.edit', [ 'id' => $id ] )->withInput()->withErrors($e->getErrors());
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
                'title' => trans('app.product_attribute')
            ];
        if(!Entrust::can(['delete_product_attribute'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $this->productAttribute->delete($id);
        Notification::success(trans('app.product_attribute_deleted'));
        return langRedirectRoute('admin.product.attribute.index');
    }

    public function confirmDestroy($id) {
        $attr = [ 
                'title' => trans('app.product_attribute')
            ];
        if(!Entrust::can(['delete_product_attribute'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $productAttribute = $this->productAttribute->find($id);
        $is_related = [];
        if($this->productAttribute->hasProductAttributeOption($id)) $is_related[] = 'attribute_options';

        return view('backend.productAttribute.confirm-destroy', compact('productAttribute', 'is_related'));
    }

    public function togglePublish($id) {
        return $this->productAttribute->togglePublish($id);
    }

    public function postType($id) {
        return $this->productAttribute->togglePublish($id);
    }

     public function postTypeStore($id) {
        return $this->productAttribute->togglePublish($id);
    }

}