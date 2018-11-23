<?php namespace App\Http\Controllers\Backend;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Repositories\ProductAttributeOption\ProductAttributeOptionInterface;
use App\Repositories\ProductAttribute\ProductAttributeInterface;
use Redirect;
use View;
use Input;
use Validator;
use Response;
use Notification;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use App\Repositories\ProductAttributeOption\ProductAttributeOptionRepository as ProductAttributeOption;
use App\Repositories\ProductAttribute\ProductAttributeRepository as ProductAttribute;
use App\Exceptions\Validation\ValidationException;
use Config;
use Entrust;

class productAttributeOptionController extends Controller {

	protected $productAttributeOption;
    protected $productAttribute;

    public function __construct(ProductAttributeOptionInterface $productAttributeOption, ProductAttributeInterface $productAttribute) {
        $this->productAttributeOption = $productAttributeOption;
        $this->productAttribute = $productAttribute;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index() {
        $attr = [ 
                'title' => trans('app.product_attribute_option')
            ];
        if(!Entrust::can(['read_product_attribute_option'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $page = Input::get('page', 1);
        $perPage = config_db_cached('settings::backend_per_page');
        $filter = Input::all();
        unset($filter['_token']);

        $pagiData = $this->productAttributeOption->paginate($page, $perPage, $filter);

        $productAttributeOptions = new LengthAwarePaginator($pagiData->items, $pagiData->totalItems, $perPage, $page, [
            'path' => Paginator::resolveCurrentPath()
        ]);

        $productAttributeOptions->setPath("");
        $productAttributeOptions->appends($filter);
        
        $productAttribute_options[''] = '-';
        $productAttribute_options += $this->productAttribute->lists('name', 'id');

        return view('backend.productAttributeOption.index', compact('productAttributeOptions', 'productAttribute_options'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create() {
        $attr = [ 
                'title' => trans('app.product_attribute_option')
            ];
        if(!Entrust::can(['create_product_attribute_option'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $productAttribute_options[''] = '-';
        $productAttribute_options += $this->productAttribute->lists('name', 'id');
        return view('backend.productAttributeOption.create', compact('productAttribute_options'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store() {
        try {
            $this->productAttributeOption->create(Input::all());
            Notification::success( trans('app.data_added') );
            return langRedirectRoute('admin.product.attribute.option.index');
        } catch (ValidationException $e) {
            return langRedirectRoute('admin.product.attribute.option.create')->withInput()->withErrors($e->getErrors());
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
                'title' => trans('app.product_attribute_option')
            ];
        if(!Entrust::can(['read_product_attribute_option'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $productAttributeOption = $this->productAttributeOption->find($id);
        return view('backend.productAttributeOption.show', compact('productAttributeOption'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function edit($id) {
        $attr = [ 
                'title' => trans('app.product_attribute_option')
            ];
        if(!Entrust::can(['update_product_attribute_option'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $productAttributeOption = $this->productAttributeOption->find($id);
        $productAttribute_options[''] = '-';
        $productAttribute_options += $this->productAttribute->lists('name', 'id');
        return view('backend.productAttributeOption.edit', compact('productAttributeOption','productAttribute_options'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int $id
     * @return Response
     */
    public function update($id) {
        try {
            $this->productAttributeOption->update($id, Input::all());
            Notification::success( trans('app.data_updated') );
            return langRedirectRoute('admin.product.attribute.option.index');
        } catch (ValidationException $e) {
            return langRedirectRoute('admin.product.attribute.option.edit', [ 'id' => $id ] )->withInput()->withErrors($e->getErrors());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return Response
     */
    public function destroy($id) {

        $this->productAttributeOption->delete($id);
        Notification::success( trans('app.data_deleted') );
        return langRedirectRoute('admin.product.attribute.option.index');
    }

    public function confirmDestroy($id) {
        $attr = [ 
                'title' => trans('app.product_attribute_option')
            ];
        if(!Entrust::can(['delete_product_attribute_option'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $productAttributeOption = $this->productAttributeOption->find($id);
        return view('backend.productAttributeOption.confirm-destroy', compact('productAttributeOption'));
    }

    public function togglePublish($id) {

        return $this->productAttributeOption->togglePublish($id);
    }

}