<?php namespace App\Http\Controllers\Backend;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Repositories\Product\ProductInterface;
use App\Repositories\Taxonomy\TaxonomyInterface;
use App\Repositories\ProductAttribute\ProductAttributeInterface;
use App\Repositories\ProductAttributeOption\ProductAttributeOptionInterface;
use App\Repositories\ProductAttributePostType\ProductAttributePostTypeInterface;
use Redirect;
use View;
use Input;
use Validator;
use Response;
use Notification;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use App\Repositories\Product\ProductRepository as Product;
use App\Repositories\Taxonomy\TaxonomyRepository as ProductCategory;
use App\Repositories\ProductAttribute\ProductAttributeRepository as ProductAttribute;
use App\Repositories\ProductAttributePostType\ProductAttributePostTypeRepository as ProductAttributePostType;
use App\Exceptions\Validation\ValidationException;
use Config;

use App\Repositories\Role\RoleRepository as Role;
use Entrust;

use App\Models\ProductSpecialOffer;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\ProductImage;

use App\Repositories\Booking\BookingInterface;

class ProductController extends Controller {

    protected $role;
	protected $product;
    protected $productCategory;
    protected $productAttribute;
    protected $productAttributePostType;
    protected $productImage;
    protected $booking;

    public function __construct(Role $role, ProductInterface $product, TaxonomyInterface $productCategory, ProductAttributeInterface $productAttribute, ProductAttributeOptionInterface $productAttributeOption, ProductAttributePostTypeInterface $productAttributePostType, BookingInterface $booking) {
        //View::share('active', 'blog');
        $this->role = $role;
        $this->product = $product;
        $this->productCategory = $productCategory;
        $this->productAttribute = $productAttribute;
        $this->productAttributeOption = $productAttributeOption;
        $this->productAttributePostType = $productAttributePostType;
        $this->productImage = new ProductImage;
        $this->booking = $booking;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index() { 
        setActive2('admin/product?post_type=hotel', [ 'post_type' ]);
        $post_type = Input::get('post_type');

        switch ($post_type) {
            case 'hotel':
                $attr = [ 
                        'title' => trans('app.hotel')
                    ];
                if(!Entrust::can(['read_product_hotel'])){
                    $attr += [ 
                        'unauthorized_message' => trans('app.unauthorized_message')
                    ];
                    return view('backend.auth.unauthorized', compact('attr'));
                }
                break;
            
            case 'playground':
                $attr = [ 
                        'title' => trans('app.playground')
                    ];
                if(!Entrust::can(['read_product_playground'])){
                    $attr += [ 
                        'unauthorized_message' => trans('app.unauthorized_message')
                    ];
                    return view('backend.auth.unauthorized', compact('attr'));
                }
                break;
            case 'trip':
                $attr = [ 
                        'title' => trans('app.trip')
                    ];
                if(!Entrust::can(['read_product_trip'])){
                    $attr += [ 
                        'unauthorized_message' => trans('app.unauthorized_message')
                    ];
                    return view('backend.auth.unauthorized', compact('attr'));
                }
                break;
            case 'merchant':
                $attr = [ 
                        'title' => trans('app.merchant')
                    ];
                if(!Entrust::can(['read_product_merchant'])){
                    $attr += [ 
                        'unauthorized_message' => trans('app.unauthorized_message')
                    ];
                    return view('backend.auth.unauthorized', compact('attr'));
                }
                break;
        }
        
        
        $page = Input::get('page', 1);
        $perPage = config_db_cached('settings::backend_per_page');

        $filter = Input::all(); 
        unset($filter['_token']);
        $pagiData = $this->product->paginate($page, $perPage, $filter);

        $products = new LengthAwarePaginator($pagiData->items, $pagiData->totalItems, $perPage, $page, [
            'path' => Paginator::resolveCurrentPath()
        ]);

        $products->setPath("");
        $products->appends($filter);
        $productCategory_options[''] = '-'; 
        $productCategory_options += renderLists($this->productCategory->getTermsByPostType($post_type)->toHierarchy());
        return view('backend.product.'.$post_type.'.index', compact('products', 'post_type', 'productCategory_options'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create() { 
        $post_type = Input::get('post_type');
        switch ($post_type) {
            case 'hotel':
                return $this->create_hotel($post_type);
                break;
            
            case 'playground':
                return $this->create_playground($post_type);
                break;
            case 'trip':
                return $this->create_trip($post_type);
                break;
            case 'merchant':
                return $this->create_merchant($post_type);
                break;
        }

        /*
        $productCategory_options[''] = '-'; 
        $productCategory_options += renderLists($this->productCategory->getTermsByPostType($post_type)->toHierarchy());
        //dd($productCategory_options);
        $attribute_options = $this->productAttributePostType->findByPostType($post_type);
        //dd($attribute_options);
        $stars_options = [
            0 => '-',
            1 => stars_char(1),
            2 => stars_char(2),
            3 => stars_char(3),
            4 => stars_char(4),
            5 => stars_char(5)
        ];
        return view('backend.product.create', compact('productCategory_options', 'attribute_options', 'post_type', 'stars_options'));*/
    }

    public function create_hotel($post_type){
        $attr = [ 
                'title' => trans('app.add_hotel')
            ];
        if(!Entrust::can(['create_product_hotel'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $productCategory_options[''] = '-'; 
        $productCategory_options += renderLists($this->productCategory->getTermsByPostType($post_type)->toHierarchy());
        //dd($productCategory_options);
        $attribute_options = $this->productAttributePostType->findByPostType($post_type);
        //dd($attribute_options);
        $stars_options = [
            0 => '-',
            1 => stars_char(1),
            2 => stars_char(2),
            3 => stars_char(3),
            4 => stars_char(4),
            5 => stars_char(5)
        ];
        return view('backend.product.hotel.create', compact('productCategory_options', 'attribute_options', 'post_type', 'stars_options'));
    }

    public function create_playground($post_type){
        $attr = [ 
                'title' => trans('app.add_playground')
            ];
        if(!Entrust::can(['create_product_playground'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $productCategory_options[''] = '-'; 
        $productCategory_options += renderLists($this->productCategory->getTermsByPostType($post_type)->toHierarchy());
        //dd($productCategory_options);
        $attribute_options = $this->productAttributePostType->findByPostType($post_type);
        return view('backend.product.playground.create', compact('productCategory_options', 'attribute_options', 'post_type'));
    }

    public function create_trip($post_type){
        $attr = [ 
                'title' => trans('app.add_trip')
            ];
        if(!Entrust::can(['create_product_trip'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        
        $productCategory_options[''] = '-'; 
        $productCategory_options += renderLists($this->productCategory->getTermsByPostType($post_type)->toHierarchy());
        //dd($productCategory_options);
        $attribute_options = $this->productAttributePostType->findByPostType($post_type);
        return view('backend.product.trip.create', compact('productCategory_options', 'attribute_options', 'post_type'));
    }

    public function create_merchant($post_type){
        $attr = [ 
                'title' => trans('app.add_merchant')
            ];
        if(!Entrust::can(['create_product_merchant'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $productCategory_options[''] = '-'; 
        $productCategory_options += renderLists($this->productCategory->getTermsByPostType($post_type)->toHierarchy());
        //dd($productCategory_options);
        $attribute_options = $this->productAttributePostType->findByPostType($post_type);
        return view('backend.product.merchant.create', compact('productCategory_options', 'attribute_options', 'post_type'));
    }
    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store() {
        $post_type = Input::get('post_type');
        try {
            $this->product->create(Input::all());
            Notification::success( trans('app.product_added') );
            return Redirect::route(getLang().'.admin.product.index', ['post_type' => $post_type]);
        } catch (ValidationException $e) {
            return Redirect::route(getLang().'.admin.product.create', ['post_type' => $post_type])->withInput()->withErrors($e->getErrors());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function show($id) {
        $product = $this->product->findWithVariations($id);
        $post_type = $product->post_type;
        return view('backend.product.'.$post_type.'.show', compact('product'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function edit($id) {
        $post_type = Input::get('post_type');
        //$product = $this->product->findWithVariations2($id);
        /*$tags = null;

        foreach ($product->tags as $tag) {
            $tags .= ',' . $tag->name;
        }

        $tags = substr($tags, 1);*/
        /*$stars_options = [
            0 => '-',
            1 => stars_char(1),
            2 => stars_char(2),
            3 => stars_char(3),
            4 => stars_char(4),
            5 => stars_char(5)
        ];
        $productCategory_options[''] = '-';
        $productCategory_options += renderLists($this->productCategory->getTermsByPostType($product->post_type)->toHierarchy());
        $attribute_options = $this->productAttributePostType->findByPostType($product->post_type);
        return view('backend.product.edit', compact('stars_options', 'product','productCategory_options', 'attribute_options'));
*/
        switch ($post_type) {
            case 'hotel':
                return $this->edit_hotel($id);
                break;
            
            case 'playground':
                return $this->edit_playground($id);
                break;
            case 'trip':
                return $this->edit_trip($id);
                break;
            case 'merchant':
                return $this->edit_merchant($id);
                break;
        }
    }

    public function edit_hotel($id){
        $attr = [ 
                'title' => trans('app.hotel')
            ];
        if(!Entrust::can(['update_product_hotel'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $product = $this->product->findWithVariations2($id);
        $stars_options = [
            0 => '-',
            1 => stars_char(1),
            2 => stars_char(2),
            3 => stars_char(3),
            4 => stars_char(4),
            5 => stars_char(5)
        ];
        $productCategory_options[''] = '-';
        $productCategory_options += renderLists($this->productCategory->getTermsByPostType($product->post_type)->toHierarchy());
        $attribute_options = $this->productAttributePostType->findByPostType($product->post_type);
        return view('backend.product.hotel.edit', compact('stars_options', 'product','productCategory_options', 'attribute_options'));
    }

    public function edit_playground($id){
        $attr = [ 
                'title' => trans('app.playground')
            ];
        if(!Entrust::can(['update_product_playground'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $product = $this->product->findWithVariations2($id);
        $productCategory_options[''] = '-';
        $productCategory_options += renderLists($this->productCategory->getTermsByPostType($product->post_type)->toHierarchy());
        $attribute_options = $this->productAttributePostType->findByPostType($product->post_type);
        return view('backend.product.playground.edit', compact('product','productCategory_options', 'attribute_options'));
    }

    public function edit_trip($id){
        $attr = [ 
                'title' => trans('app.trip')
            ];
        if(!Entrust::can(['update_product_trip'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $product = $this->product->findWithVariations2($id);
        $productCategory_options[''] = '-';
        $productCategory_options += renderLists($this->productCategory->getTermsByPostType($product->post_type)->toHierarchy());
        $attribute_options = $this->productAttributePostType->findByPostType($product->post_type);
        return view('backend.product.trip.edit', compact('product','productCategory_options', 'attribute_options'));
    }

    public function edit_merchant($id){
        
        $attr = [ 
                'title' => trans('app.merchant')
            ];
        if(!Entrust::can(['update_product_merchant'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $product = $this->product->findWithVariations2($id);
        $productCategory_options[''] = '-';
        $productCategory_options += renderLists($this->productCategory->getTermsByPostType($product->post_type)->toHierarchy());
        $attribute_options = $this->productAttributePostType->findByPostType($product->post_type);
        return view('backend.product.merchant.edit', compact('product','productCategory_options', 'attribute_options'));
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  int $id
     * @return Response
     */
    public function update($id) {
        $all = Input::all();
        //$all['start_date'] = strtotime('m/d/Y', Input::get('start_date'));
        //dd($all);

        $post_type = Input::get('post_type');
        try {
            $this->product->update($id, Input::all());
            Notification::success( trans('app.product_updated') );
            return Redirect::route(getLang().'.admin.product.index', ['post_type' => $post_type]);
        } catch (ValidationException $e) {
            return langRedirectRoute('admin.product.edit', [ 'id' => $id, 'post_type' => $post_type] )->withInput()->withErrors($e->getErrors());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return Response
     */
    public function destroy($id) {
        
        $post_type = Input::get('post_type');
        $this->product->delete($id);
        Notification::success( trans('app.product_deleted') );
        return langRedirectRoute('admin.product.index', ['post_type' => $post_type]);
    }

    public function confirmDestroy($id) {
        $attr = [ 
                'title' => trans('app.product')
            ];
        if(!Entrust::can(['delete_product'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $product = $this->product->find($id);
        $is_related = [];

        if($this->booking->hasProduct($id)) $is_related[] = 'booking';

        return view('backend.product.confirm-destroy', compact('product'));
    }

    public function togglePublish($id) {

        return $this->product->togglePublish($id);
    }

    public function specialOffer($id) {
        $specialOffers_raw = ProductSpecialOffer::where('product_id', $id)->get(); 
        $specialOffers = [];
        if($specialOffers_raw){
            foreach($specialOffers_raw as $specialOffer_raw){
                $specialOffers[$specialOffer_raw->product_sku_id] = $specialOffer_raw['special_offer'];
            }
        }
        $product = $this->product->findWithVariations($id); 
        return view('backend.product.specialOffer', compact('product', 'specialOffers'));
    }

    public function doSpecialOffer() {
        $post_type = Input::get('post_type');
        $product_id = Input::get('product_id');
        $special_offers = Input::get('special_offer');
        foreach($special_offers as $sku_id => $special_offer){
            $rules['special_offer.'.$sku_id] = 'required|numeric';
            $setAttributeNames['special_offer.'.$sku_id] = trans('app.special_offer');
        }
            
        $v = Validator::make(Input::all(), $rules);
        $v->setAttributeNames($setAttributeNames);
        if ($v->fails())
        {  
            return Redirect::Route('admin.product.special-offer', [ 'id' => $product_id ])->withInput()->withErrors($v->errors());
        }

        ProductSpecialOffer::where('product_id', $product_id)->delete();
        foreach($special_offers as $sku_id => $special_offer){
            if($special_offer > 0)
                $data[] = [
                    'product_id' => $product_id,
                    'product_sku_id' => $sku_id,
                    'special_offer' => $special_offer
                ];
        }
        if(isset($data))
            ProductSpecialOffer::insert($data);
        Notification::success( trans('app.special_offer_added') );
            return Redirect::route(getLang().'.admin.product.index', ['post_type' => $post_type]);
    }

    public function bulkUploadProductAttributeOption() { 
        $attr = [ 
                'title' => trans('app.product_attribute_option')
            ];
        if(!Entrust::can(['upload_bulk_product_attribute_option'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $productAttribute_options[''] = '-'; 
        $productAttribute_options += $this->productAttribute->Lists('name', 'id');
        return view('backend.product.bulkUploadProductAttributeOption', compact('productAttribute_options'));
    }

    public function bulkUploadMaster() { 
        $post_type = Input::get('post_type');

        if(is_null($post_type)){
            return Redirect::route('admin.dashboard');
        }else{
            $productCategory_options[''] = '-'; 
            $productCategory_options += renderLists($this->productCategory->getTermsByPostType($post_type)->toHierarchy());
            $disableOptions = json_encode(array_keys(renderLists($this->productCategory->getTermsBy([ 'level' => 0 , 'post_type' => $post_type ])->toHierarchy())));
            return view('backend.product.bulkUploadMaster', compact('post_type', 'productCategory_options', 'disableOptions'));
        }
        
    }

    public function doBulkUploadProductAttributeOption(){ 
        $rules = array(
            'excel_file' => 'required',
        );

        $validator = Validator::make(Input::all(), $rules);
        // process the form
        if ($validator->fails()) 
        { 
            return Redirect::route('admin.product.bulk-upload.product-attribute-option')->withErrors($validator);
        }
        else 
        {
            try { 
                Excel::selectSheets('Product Attribute Option')->load(Input::file('excel_file'), function ($reader) {
                    $after = [];
                    $ff_with_locale = [ 'name' ];
                    $cell_required = [
                        'product_attribute',
                        'name'
                    ];
                    
                    $cell_map = [];
                    $row_errors = [];
                    $data_errors = [];
                    $data_orig = $reader->toArray();
                    foreach ($reader->toArray() as $row_num => $row) {
                        $cell_idx = 1;
                        foreach ($row as $ff => $cell) {
                            $locale = is_cell_excel_contain_locale($ff_with_locale, $ff);
                            if( ( in_array($ff, $cell_required) && trim($cell) == '' ) || ( in_array_strpos($ff, $cell_required) !== false && trim($cell) == '' )  ){
                                $row_errors[$row_num][] = [ 'cell' => num2char($cell_idx), 'error' => 'required' ];
                                
                                //$data_errors[$row_num] = $row;
                            }

                            if($locale){ 
                                $after[$row_num][str_replace('_'.$locale, '', $ff)][$locale] = strval($cell);
                            }else{ 
                                $after[$row_num][$ff] = strval($cell);
                            }
                            $cell_map[$ff] = num2char($cell_idx);
                            $cell_idx++;
                        }
                    }

                    foreach(array_keys($row_errors) as $row_error){ //unset error rows, agar tidak diproses lebih jauh
                        unset($after[$row_error]);
                    }
                    
                    //$this->data_errors = $data_errors;    
                    if(count($after) > 0)
                        Notification::success( trans('app.data_updated') );
                    else
                        Notification::error( trans('app.file_is_empty') );

                    if($after)//data yang akan diproses lebih lanjut, karena sudah melewati validasi required
                        $errors_on_process = $this->productAttributeOption->bulkProductAttributeOption_process($cell_map, $after);
                    
                   
                    if(!empty($errors_on_process)){
                        //$data_errors += $errors_on_process['data'];
                        $row_errors += $errors_on_process['rows'];
                    }

                    ksort($row_errors);
                    $data_succeeded = array_diff_key($data_orig, $row_errors);//get id(s) of succeeded data
                    $data_errors = array_diff_key($data_orig, $data_succeeded);//get id(s) of errors data

                    if(!empty($row_errors)){ //jika ada error akan dikembalikan
                        $this->downloadFailedUpload([
                            'file_name' => 'Product Attribute Option Failed',
                            'sheet_name' => 'Product Attribute Option', 
                            'header_hilights' => 'B1:D1', 
                            'data_errors' => $data_errors, 
                            'row_errors' => $row_errors
                        ]);
                    }
                   
                });
                return Redirect::route('admin.product.bulk-upload.product-attribute-option');//->withInput(['data_errors' => $this->data_errors]);
            } catch (\Exception $e) {
                Notification::success($e->getMessage());
                return Redirect::route('admin.product.bulk-upload.product-attribute-option');
            }
        } 
    }

    public function doBulkUploadMaster(){ 
        $post_type = Input::get('post_type');
        switch ($post_type) {
            case 'hotel':
                    return $this->doBulkUploadMaster_hotel($post_type);
                break;
            case 'playground':
                    return $this->doBulkUploadMaster_playground($post_type);
                break;
        }
    }

    public function doBulkUploadMaster_hotel($post_type){  
        $rules = array(
            'excel_file' => 'required',
        );

        $validator = Validator::make(Input::all(), $rules);
        // process the form
        if ($validator->fails()) 
        { 
            return Redirect::route('admin.product.bulk-upload.master', [ 'post_type' => $post_type ])->withErrors($validator);
        }
        else 
        {
            try { 
                Excel::selectSheets('Master')->load(Input::file('excel_file'), function ($reader) {
                    $after = [];
                    $ff_with_locale = [ 'hotel_main_facilities', 'description', 'meta_keywords', 'meta_description' ];
                    $cell_required = [
                        'location',
                        'hotel_name',
                        'meta_keywords',
                        'meta_description'
                    ];
                    
                    $cell_map = [];
                    $row_errors = [];
                    $data_errors = [];
                    $data_orig = $reader->toArray();
                    foreach ($reader->toArray() as $row_num => $row) {
                        $cell_idx = 1;
                        foreach ($row as $ff => $cell) {
                            $locale = is_cell_excel_contain_locale($ff_with_locale, $ff);
                            if( ( in_array($ff, $cell_required) && trim($cell) == '' ) || ( in_array_strpos($ff, $cell_required) !== false && trim($cell) == '' )  ){
                                $row_errors[$row_num][] = [ 'cell' => num2char($cell_idx), 'error' => 'required' ];
                                
                                //$data_errors[$row_num] = $row;
                            }

                            if($locale){ 
                                $after[$row_num][str_replace('_'.$locale, '', $ff)][$locale] = strval($cell);
                            }else{ 
                                $after[$row_num][$ff] = strval($cell);
                            }
                            $cell_map[$ff] = num2char($cell_idx);
                            $cell_idx++;
                        }
                    }
//dd($data_errors);
//dd($cell_map);
                    foreach(array_keys($row_errors) as $row_error){ //unset error rows, agar tidak diproses lebih jauh
                        unset($after[$row_error]);
                    }
                    
                    //$this->data_errors = $data_errors;    
                    if(count($after) > 0)
                        Notification::success( trans('app.product_updated') );
                    else
                        Notification::error( trans('app.file_is_empty') );

                    if($after)//data yang akan diproses lebih lanjut, karena sudah melewati validasi required
                        $errors_on_process = $this->product->bulkMasterHotel_process($cell_map, $after);
                    
                    
                    if(!empty($errors_on_process)){
                        //$data_errors += $errors_on_process['data'];
                        $row_errors += $errors_on_process['rows'];
                    }

                    //foreach(array_keys($row_errors) as $row_error){ //remove yang error saja, dari data orig
                    //    unset($data_orig[$row_error]);
                    //}
                    ksort($row_errors);
                    $data_succeeded = array_diff_key($data_orig, $row_errors);//get id(s) of succeeded data
                    $data_errors = array_diff_key($data_orig, $data_succeeded);//get id(s) of errors data
                    //dd($row_errors);
                    //dd($data_errors);

                    if(!empty($row_errors)){ //jika ada error akan dikembalikan
                        $this->downloadFailedUpload([
                            'file_name' => 'Master Failed',
                            'sheet_name' => 'Master', 
                            'header_hilights' => 'B1:P1', 
                            'data_errors' => $data_errors, 
                            'row_errors' => $row_errors
                        ]);
                    }
                   
                });
                return Redirect::route('admin.product.bulk-upload.master', [ 'post_type' => $post_type ]);//->withInput(['data_errors' => $this->data_errors]);
            } catch (\Exception $e) {
                Notification::success($e->getMessage());
                return Redirect::route('admin.product.bulk-upload.master', [ 'post_type' => $post_type ]);
            }
        } 
    }

    public function doBulkUploadMaster_playground($post_type){  
        $rules = array(
            'excel_file' => 'required',
        );

        $validator = Validator::make(Input::all(), $rules);
        // process the form
        if ($validator->fails()) 
        { 
            return Redirect::route('admin.product.bulk-upload.master', [ 'post_type' => $post_type ])->withErrors($validator);
        }
        else 
        {
            try { 
                Excel::selectSheets('Master')->load(Input::file('excel_file'), function ($reader) {
                    $after = [];
                    $ff_with_locale = [ 'description', 'meta_keywords', 'meta_description' ];
                    $cell_required = [
                        'location',
                        'playground_name',
                        'meta_keywords',
                        'meta_description'
                    ];
                    
                    $cell_map = [];
                    $row_errors = [];
                    $data_errors = [];
                    $data_orig = $reader->toArray();
                    foreach ($reader->toArray() as $row_num => $row) {
                        $cell_idx = 1;
                        foreach ($row as $ff => $cell) {
                            $locale = is_cell_excel_contain_locale($ff_with_locale, $ff);
                            if( ( in_array($ff, $cell_required) && trim($cell) == '' ) || ( in_array_strpos($ff, $cell_required) !== false && trim($cell) == '' )  ){
                                $row_errors[$row_num][] = [ 'cell' => num2char($cell_idx), 'error' => 'required' ];
                                
                                //$data_errors[$row_num] = $row;
                            }

                            if($locale){ 
                                $after[$row_num][str_replace('_'.$locale, '', $ff)][$locale] = strval($cell);
                            }else{ 
                                $after[$row_num][$ff] = strval($cell);
                            }
                            $cell_map[$ff] = num2char($cell_idx);
                            $cell_idx++;
                        }
                    }
//dd($data_errors);
//dd($cell_map);
                    foreach(array_keys($row_errors) as $row_error){ //unset error rows, agar tidak diproses lebih jauh
                        unset($after[$row_error]);
                    }
                    
                    //$this->data_errors = $data_errors;    
                    if(count($after) > 0)
                        Notification::success( trans('app.product_updated') );
                    else
                        Notification::error( trans('app.file_is_empty') );


                    if($after)//data yang akan diproses lebih lanjut, karena sudah melewati validasi required
                        $errors_on_process = $this->product->bulkMasterPlayground_process($cell_map, $after);
                    
                    
                    if(!empty($errors_on_process)){
                        //$data_errors += $errors_on_process['data'];
                        $row_errors += $errors_on_process['rows'];
                    }

                    //foreach(array_keys($row_errors) as $row_error){ //remove yang error saja, dari data orig
                    //    unset($data_orig[$row_error]);
                    //}
                    ksort($row_errors);
                    $data_succeeded = array_diff_key($data_orig, $row_errors);//get id(s) of succeeded data
                    $data_errors = array_diff_key($data_orig, $data_succeeded);//get id(s) of errors data
                    //dd($row_errors);
                    //dd($data_errors);

                    if(!empty($row_errors)){ //jika ada error akan dikembalikan
                        $this->downloadFailedUpload([
                            'file_name' => 'Master Failed', 
                            'sheet_name' => 'Master', 
                            'header_hilights' => 'B1:P1', 
                            'data_errors' => $data_errors, 
                            'row_errors' => $row_errors
                        ]);
                    }
                   
                });
                return Redirect::route('admin.product.bulk-upload.master', [ 'post_type' => $post_type ]);//->withInput(['data_errors' => $this->data_errors]);
            } catch (\Exception $e) {
                Notification::success($e->getMessage());
                return Redirect::route('admin.product.bulk-upload.master', [ 'post_type' => $post_type ]);
            }
        } 
    }

    public function downloadProductAttributeOption(){
        $filter = Input::all(); 

        $rules = array(
            'product_attribute' => 'required'
            //'name' => 'required'
        );

        $setAttributeNames['name'] = trans('app.name');
        $setAttributeNames['product_attribute'] = trans('app.product_attribute');

        $v = Validator::make($filter, $rules);
        
        $v->setAttributeNames($setAttributeNames);
        if ($v->fails()) 
        { 
            return Redirect::route('admin.product.bulk-upload.product-attribute-option')->withInput()->withErrors($v);
        }
        else 
        {
            try { 
                unset($filter['_token']);
                $items = $this->productAttributeOption->paginate(1, 0, $filter);
                $data = [];
                if(count($items->items)){
                    foreach($items->items as $idx => $item){
                        $data[$idx] = [
                            'id' => $item->id,
                            'product_attribute' => $item->productAttribute->name,
                            'name_en' => $item->translate('en')->name,
                            'name_id' => $item->translate('id')->name
                        ];
                    }
                }else{
                    $data[] = [
                        'id' => '',
                        'product_attribute' => '',
                        'name_en' => '',
                        'name_id' => ''
                    ];
                }
                
                Excel::create('Product Attribute Option', function($excel) use($data) {

                    $excel->sheet('Product Attribute Option', function($sheet) use($data) {
                        $sheet->cells('B1:D1', function($cells) {
                            $cells->setBackground('#FFFF00');
                        });
                        $sheet->fromArray($data);
                    });

                })->export('xlsx');
        } catch (\Exception $e) {
                Notification::success($e->getMessage());
                return Redirect::route('admin.product.bulk-upload.product-attribute-option');
            }
        } 
    }

    public function downloadMaster(){
        $post_type = Input::get('post_type');
        switch ($post_type) {
            case 'hotel':
                    return $this->downloadMaster_hotel();
                break;
            case 'playground':
                    return $this->downloadMaster_playground();
                break;
        }
    }

    public function downloadMaster_hotel(){
        $filter = Input::all(); 

        $rules = array(
            'product_category' => 'required',
            //'title' => 'required'
        );

        $setAttributeNames['product_category'] = trans('app.location');
        $setAttributeNames['title'] = trans('app.hotel_name');

        $v = Validator::make($filter, $rules);
        
        $v->setAttributeNames($setAttributeNames);
        if ($v->fails()) 
        { 
            return Redirect::route('admin.product.bulk-upload.master', [ 'post_type' => $filter['post_type'] ])->withInput()->withErrors($v);
        }
        else 
        {
            try { 
                unset($filter['_token']);
                //$filter['product_category'] = 42;
                $items = $this->product->paginate(1, 0, $filter);//, 'product_category' => 102 ]);
                $data = [];
                if(count($items->items)){
                    foreach($items->items as $idx => $item){
                        $productMeta_t = build_t_meta_input($item->productMetas); 
                        $hotel_star = isset($productMeta_t->hotel_star)?$productMeta_t->hotel_star[getLang()]:'';
                        $hotel_address = isset($productMeta_t->hotel_address)?$productMeta_t->hotel_address[getLang()]:'';
                        $hotel_main_facilities_en = isset($productMeta_t->hotel_main_facilities)?$productMeta_t->hotel_main_facilities['en']:'';
                        $hotel_main_facilities_id = isset($productMeta_t->hotel_main_facilities)?$productMeta_t->hotel_main_facilities['id']:'';
                        $hotel_latitude = isset($productMeta_t->hotel_latitude)?$productMeta_t->hotel_latitude[getLang()]:'';
                        $hotel_longitude = isset($productMeta_t->hotel_longitude)?$productMeta_t->hotel_longitude[getLang()]:'';
                        $t_product = build_t_product($item->productTranslations);
                        $data[$idx] = [
                            'id' => $item->id,
                            'location' => $item->productCategory->title,
                            //'name' => $item->title,
                            //'description_en' => $t_product->body['en'],
                            //'description_id' => $t_product->body['id'],
                            //'is_published' => $item->is_published

                            'hotel_name' => $item->title,
                            'hotel_star' => $hotel_star,
                            'hotel_address' => $hotel_address,
                            'hotel_main_facilities_en' => $hotel_main_facilities_en,
                            'hotel_main_facilities_id' => $hotel_main_facilities_id,
                            'hotel_latitude' => $hotel_latitude,
                            'hotel_longitude' => $hotel_longitude,
                            'description_en' => $t_product->body['en'],
                            'description_id' => $t_product->body['id'],
                            'meta_keywords_en' => $t_product->meta_keywords['en'],
                            'meta_keywords_id' => $t_product->meta_keywords['id'],
                            'meta_description_en' => $t_product->meta_description['en'],
                            'meta_description_id' => $t_product->meta_description['id'],
                            'is_published' => $item->is_published
                        ];
                    }
                }else{
                    $data[] = [
                        'id' => '',
                        'location' => '',
                        'hotel_name' => '',
                        'hotel_star' => '',
                        'hotel_address' => '',
                        'hotel_main_facilities_en' => '',
                        'hotel_main_facilities_id' => '',
                        'hotel_latitude' => '',
                        'hotel_longitude' => '',
                        'description_en' => '',
                        'description_id' => '',
                        'meta_keywords_en' => '',
                        'meta_keywords_id' => '',
                        'meta_description_en' => '',
                        'meta_description_id' => '',
                        'is_published' => ''
                    ];
                }
                
                Excel::create('Master', function($excel) use($data) {

                    $excel->sheet('Master', function($sheet) use($data) {
                        $sheet->cells('B1:P1', function($cells) {
                            $cells->setBackground('#FFFF00');
                        });
                        $sheet->fromArray($data);

                    });

                })->export('xlsx');
        } catch (\Exception $e) {
                Notification::success($e->getMessage());
                return Redirect::route('admin.product.bulk-upload.master', [ 'post_type' => $post_type ]);
            }
        } 
    }

    public function downloadMaster_playground(){
        $filter = Input::all(); 

        $rules = array(
            'product_category' => 'required',
            //'title' => 'required'
        );

        $setAttributeNames['product_category'] = trans('app.location');
        $setAttributeNames['title'] = trans('app.hotel_name');

        $v = Validator::make($filter, $rules);
        
        $v->setAttributeNames($setAttributeNames);
        if ($v->fails()) 
        { 
            return Redirect::route('admin.product.bulk-upload.master', [ 'post_type' => $filter['post_type'] ])->withInput()->withErrors($v);
        }
        else 
        {
            try { 
                unset($filter['_token']);
                $items = $this->product->paginate(1, 0, $filter);//, 'product_category' => 102 ]);
                $data = [];
                if(count($items->items)){
                    foreach($items->items as $idx => $item){
                        $productMeta_t = build_t_meta_input($item->productMetas);
                        //$hotel_star = isset($productMeta_t->hotel_star)?$productMeta_t->hotel_star[getLang()]:'';
                        $playground_address = isset($productMeta_t->playground_address)?$productMeta_t->playground_address[getLang()]:'';
                        //$hotel_main_facilities_en = isset($productMeta_t->hotel_main_facilities)?$productMeta_t->hotel_main_facilities['en']:'';
                        //$hotel_main_facilities_id = isset($productMeta_t->hotel_main_facilities)?$productMeta_t->hotel_main_facilities['id']:'';
                        $playground_latitude = isset($productMeta_t->playground_latitude)?$productMeta_t->playground_latitude[getLang()]:'';
                        $playground_longitude = isset($productMeta_t->playground_longitude)?$productMeta_t->playground_longitude[getLang()]:'';
                        $t_product = build_t_product($item->productTranslations);
                        $data[$idx] = [
                            'id' => $item->id,
                            'location' => $item->productCategory->title,
                            'playground_name' => $item->title,
                            //'hotel_star' => $hotel_star,
                            'playground_address' => $playground_address,
                            //'hotel_main_facilities_en' => $hotel_main_facilities_en,
                            //'hotel_main_facilities_id' => $hotel_main_facilities_id,
                            'playground_latitude' => $playground_latitude,
                            'playground_longitude' => $playground_longitude,
                            'description_en' => $t_product->body['en'],
                            'description_id' => $t_product->body['id'],
                            'meta_keywords_en' => $t_product->meta_keywords['en'],
                            'meta_keywords_id' => $t_product->meta_keywords['id'],
                            'meta_description_en' => $t_product->meta_description['en'],
                            'meta_description_id' => $t_product->meta_description['id'],
                            'is_published' => $item->is_published
                        ];
                    }
                }else{
                    $data[] = [
                        'id' => '',
                        'location' => '',
                        'playground_name' => '',
                        //'playground_star' => '',
                        'playground_address' => '',
                        //'hotel_main_facilities_en' => '',
                        //'hotel_main_facilities_id' => '',
                        'playground_latitude' => '',
                        'playground_longitude' => '',
                        'description_en' => '',
                        'description_id' => '',
                        'meta_keywords_en' => '',
                        'meta_keywords_id' => '',
                        'meta_description_en' => '',
                        'meta_description_id' => '',
                        'is_published' => ''
                    ];
                }
                
                Excel::create('Master', function($excel) use($data) {

                    $excel->sheet('Master', function($sheet) use($data) {
                        $sheet->cells('B1:M1', function($cells) {
                            $cells->setBackground('#FFFF00');
                        });
                        $sheet->fromArray($data);

                    });

                })->export('xlsx');
        } catch (\Exception $e) {
                Notification::success($e->getMessage());
                return Redirect::route('admin.product.bulk-upload.master', [ 'post_type' => $post_type ]);
            }
        } 
    }

    public function bulkUploadVariant() { 
        $post_type = Input::get('post_type');
        if(is_null($post_type)){
            return Redirect::route('admin.dashboard');
        }else{
            $productCategory_options[''] = '-'; 
            $productCategory_options += renderLists($this->productCategory->getTermsByPostType($post_type)->toHierarchy());
            $disableOptions = json_encode(array_keys(renderLists($this->productCategory->getTermsBy([ 'level' => 0 , 'post_type' => $post_type ])->toHierarchy())));
            return view('backend.product.bulkUploadVariant', compact('post_type', 'productCategory_options', 'disableOptions'));
        }
        
    }

    public function doBulkUploadVariant(){ 
        $post_type = Input::get('post_type');
        switch ($post_type) {
            case 'hotel':
                    return $this->doBulkUploadVariant_hotel($post_type);
                break;
            case 'playground':
                    return $this->doBulkUploadVariant_playground($post_type);
                break;
        }
    }

    
    public function doBulkUploadVariant_hotel($post_type){   
        $rules = array(
            'excel_file' => 'required',
        );

        $validator = Validator::make(Input::all(), $rules);
        if ($validator->fails()) 
        { 
            return Redirect::route('admin.product.bulk-upload.variant', [ 'post_type' => $post_type ])->withErrors($validator);
        }
        else 
        {
            try { 
              
                Excel::selectSheets('Variant')->load(Input::file('excel_file'), function ($reader) {
                    $after = [];
                    /*$cell_required = [
                        'hotel_name',
                        'room_image_path',
                        'variant_room_type',
                        'price',
                        'availability'
                    ];*/

                    $cell_required = [
                        'hotel_name',
                        'room_image_path',
                        'variant_room_type',
                        'price'
                    ];
                    
                    $cell_map = [];
                    $row_errors = [];
                    $data_errors = [];
                    $data_orig = $reader->toArray();
                    foreach ($reader->toArray() as $row_num => $row) {
                        $cell_idx = 1;
                        foreach ($row as $ff => $cell) {
                            if(in_array($ff, $cell_required) && trim($cell) == ''){
                                $row_errors[$row_num][] = [ 'cell' => num2char($cell_idx), 'error' => 'required' ];
                                //$data_errors[$row_num] = $row;
                            }
                            $after[$row_num][$ff] = strval($cell);

                            $cell_map[$ff] = num2char($cell_idx);
                            $cell_idx++;
                        }
                    }
                    //dd($cell_map);
                    foreach(array_keys($row_errors) as $row_error){ //unset error rows
                        unset($after[$row_error]);
                    }
                   
                    if(count($after) > 0)
                        Notification::success( trans('app.product_updated') );
                    else
                        Notification::error( trans('app.file_is_empty') );

                    //dd($after);

                    if($after)
                        $errors_on_process = $this->product->bulkVariantHotel_process($cell_map, $after);
                    
                    if(!empty($errors_on_process)){
                        //$data_errors += $errors_on_process['data'];
                        $row_errors += $errors_on_process['rows'];
                    }
                    ksort($row_errors);
                    $data_succeeded = array_diff_key($data_orig, $row_errors);//get id(s) of succeeded data
                    $data_errors = array_diff_key($data_orig, $data_succeeded);//get id(s) of errors data

                    if(!empty($row_errors)){
                        $this->downloadFailedUpload([
                            'file_name' => 'Variant Failed', 
                            'sheet_name' => 'Variant', 
                            'header_hilights' => 'B1:I1', 
                            'data_errors' => $data_errors, 
                            'row_errors' => $row_errors
                        ]);
                    }
                   
                });
                    return Redirect::route('admin.product.bulk-upload.variant', [ 'post_type' => $post_type ]);
            } catch (\Exception $e) {
                Notification::success($e->getMessage());
                return Redirect::route('admin.product.bulk-upload.variant', [ 'post_type' => $post_type ]);
            }
        } 
    } 

    public function doBulkUploadVariant_playground($post_type){   
        $rules = array(
            'excel_file' => 'required',
        );

        $validator = Validator::make(Input::all(), $rules);
        if ($validator->fails()) 
        { 
            return Redirect::route('admin.product.bulk-upload.variant', [ 'post_type' => $post_type ])->withErrors($validator);
        }
        else 
        {
            try { 
              
                Excel::selectSheets('Variant')->load(Input::file('excel_file'), function ($reader) {
                    $after = [];

                    $cell_required = [
                        'playground_name',
                        'variant_type',
                        'price'
                    ];
                    
                    $cell_map = [];
                    $row_errors = [];
                    $data_errors = [];
                    $data_orig = $reader->toArray();
                    foreach ($reader->toArray() as $row_num => $row) {
                        $cell_idx = 1;
                        foreach ($row as $ff => $cell) {
                            if(in_array($ff, $cell_required) && trim($cell) == ''){
                                $row_errors[$row_num][] = [ 'cell' => num2char($cell_idx), 'error' => 'required' ];
                                //$data_errors[$row_num] = $row;
                            }
                            $after[$row_num][$ff] = strval($cell);

                            $cell_map[$ff] = num2char($cell_idx);
                            $cell_idx++;
                        }
                    }

                    foreach(array_keys($row_errors) as $row_error){ //unset error rows
                        unset($after[$row_error]);
                    }
                   
                    if(count($after) > 0)
                        Notification::success( trans('app.product_updated') );
                    else
                        Notification::error( trans('app.file_is_empty') );

                    //dd($after);
                    //dd($cell_map);

                    if($after)
                        $errors_on_process = $this->product->bulkVariantPlayground_process($cell_map, $after);
                    
                    if(!empty($errors_on_process)){
                        //$data_errors += $errors_on_process['data'];
                        $row_errors += $errors_on_process['rows'];
                    }
                    ksort($row_errors);
                    $data_succeeded = array_diff_key($data_orig, $row_errors);//get id(s) of succeeded data
                    $data_errors = array_diff_key($data_orig, $data_succeeded);//get id(s) of errors data
                    if(!empty($row_errors)){
                        $this->downloadFailedUpload([
                            'file_name' => 'Variant Failed', 
                            'sheet_name' => 'Variant', 
                            'header_hilights' => 'B1:F1', 
                            'data_errors' => $data_errors, 
                            'row_errors' => $row_errors
                        ]);
                    }
                   
                });
                    return Redirect::route('admin.product.bulk-upload.variant', [ 'post_type' => $post_type ]);
            } catch (\Exception $e) {
                Notification::success($e->getMessage());
                return Redirect::route('admin.product.bulk-upload.variant', [ 'post_type' => $post_type ]);
            }
        } 
    } 

    public function downloadVariant(){
        $post_type = Input::get('post_type');
        switch ($post_type) {
            case 'hotel':
                    return $this->downloadVariant_hotel();
                break;
            case 'playground':
                    return $this->downloadVariant_playground();
                break;
        }
    }

    public function downloadVariant_hotel(){
        $filter = Input::all(); 

        $rules = array(
            'product_category' => 'required',
            //'title' => 'required'
        );

        $setAttributeNames['product_category'] = trans('app.location');
        $setAttributeNames['title'] = trans('app.hotel_name');

        $v = Validator::make($filter, $rules);
        
        $v->setAttributeNames($setAttributeNames);
        if ($v->fails()) 
        { 
            return Redirect::route('admin.product.bulk-upload.variant', [ 'post_type' => $filter['post_type'] ])->withInput()->withErrors($v);
        }
        else 
        {
            try { 
                unset($filter['_token']);
                $items = $this->product->paginate(1, 0, $filter); //all
                //$items = $this->product->paginate(1,0,['post_type' => 'hotel', 'product_category' => 44 ]);
                $data = [];
                foreach($items->items as $idx => $item){ 
                    if(count($item->productSkus)){ 
                        foreach($item->productSkus as $idx => $sku){
                            $data[] = [
                                'variation_id' => $sku->productVariation->id,
                                //'product_id' => $item->id,
                                'hotel_name' => $item->title,
                                //'product_attribute_option_id' => $sku->productVariation->product_attribute_option_id,
                                'room_image_path' => $sku->room_image,
                                'variant_room_type' => $sku->productVariation->productAttributeOption->name,
                                'price' => $sku->price,
                                'weekend_price' => $sku->weekend_price,
                                'special_offer' => $sku->productSpecialOffer?$sku->productSpecialOffer->special_offer:'',
                                'availability' => $sku->qty,
                                'room_info' => $sku->room_info
                            ];
                        }
                    }else{
                        $data[] = [
                            'variation_id' => '',
                            //'product_id' => $item->id,
                            'hotel_name' => $item->title,
                            'room_image_path' => 'uploads/Hotel/sample.jpg',
                            'variant_room_type' => '',
                            'price' => '',
                            'weekend_price' => '',
                            'special_offer' => '',
                            'availability' => '',
                            'room_info' => ''
                        ];
                    }
                }

                Excel::create('Variant', function($excel) use($data) {
                    $excel->sheet('Variant', function($sheet) use($data) {
                        /*$sheet->cells('A1:B1', function($cells) {
                            $cells->setBackground('#FFFF00');
                        });
                        $sheet->cells('D1:J1', function($cells) {
                            $cells->setBackground('#FFFF00');
                        });*/
                        $sheet->cells('B1:I1', function($cells) {
                            $cells->setBackground('#FFFF00');
                        });
                        $sheet->fromArray($data);

                    });

                })->export('xlsx');
        } catch (\Exception $e) {
                Notification::success($e->getMessage());
                return Redirect::route('admin.product.bulk-upload.variant', [ 'post_type' => $post_type ]);
            }
        } 
    }

    public function downloadVariant_playground(){
        $filter = Input::all(); 

        $rules = array(
            'product_category' => 'required',
            //'title' => 'required'
        );

        $setAttributeNames['product_category'] = trans('app.location');
        $setAttributeNames['title'] = trans('app.hotel_name');

        $v = Validator::make($filter, $rules);
        
        $v->setAttributeNames($setAttributeNames);
        if ($v->fails()) 
        { 
            return Redirect::route('admin.product.bulk-upload.variant', [ 'post_type' => $filter['post_type'] ])->withInput()->withErrors($v);
        }
        else 
        {
            try { 
                unset($filter['_token']);
                $items = $this->product->paginate(1, 0, $filter); //all
                $data = [];
                foreach($items->items as $idx => $item){ 
                    if(count($item->productSkus)){ 
                        foreach($item->productSkus as $idx => $sku){
                            $data[] = [
                                'variation_id' => $sku->productVariation->id,
                                'playground_name' => $item->title,
                                //'room_image_path' => $sku->room_image,
                                'variant_ages' => $sku->productVariation->productAttributeOption->name,
                                'price' => $sku->price,
                                'weekend_price' => $sku->weekend_price,
                                //'special_offer' => $sku->productSpecialOffer?$sku->productSpecialOffer->special_offer:'',
                                'availability' => $sku->qty,
                                //'room_info' => $sku->room_info
                            ];
                        }
                        
                    }else{
                        $data[] = [
                            'variation_id' => '',
                            'playground_name' => $item->title,
                            //'room_image_path' => 'uploads/Playground/sample.jpg',
                            'variant_ages' => '',
                            'price' => '',
                            'weekend_price' => '',
                            //'special_offer' => '',
                            'availability' => '',
                            //'room_info' => ''
                        ];
                    }
                }

                Excel::create('Variant', function($excel) use($data) {
                    $excel->sheet('Variant', function($sheet) use($data) {
                        $sheet->cells('B1:F1', function($cells) {
                            $cells->setBackground('#FFFF00');
                        });
                        $sheet->fromArray($data);

                    });

                })->export('xlsx');
        } catch (\Exception $e) {
                Notification::success($e->getMessage());
                return Redirect::route('admin.product.bulk-upload.variant', [ 'post_type' => $post_type ]);
            }
        } 
    }

    public function bulkUploadProductGallery() {
        $post_type = Input::get('post_type');
        if(is_null($post_type)){
            return Redirect::route('admin.dashboard');
        }else{
            $productCategory_options[''] = '-'; 
            $productCategory_options += renderLists($this->productCategory->getTermsByPostType($post_type)->toHierarchy());
            $disableOptions = json_encode(array_keys(renderLists($this->productCategory->getTermsBy([ 'level' => 0 , 'post_type' => $post_type ])->toHierarchy())));
            return view('backend.product.bulkUploadProductGallery', compact('post_type', 'productCategory_options', 'disableOptions'));
        }
        
    }

    public function doBulkUploadProductGallery(){ 
        $post_type = Input::get('post_type');
        switch ($post_type) {
            case 'hotel':
                    return $this->doBulkUploadProductGallery_hotel($post_type);
                break;
            case 'playground':
                    return $this->doBulkUploadProductGallery_playground($post_type);
                break;
        }
    }

    public function doBulkUploadProductGallery_hotel($post_type){
        $rules = array(
            'excel_file' => 'required',
        );

        $validator = Validator::make(Input::all(), $rules);
        
        if ($validator->fails()) 
        { 
            return Redirect::route('admin.product.bulk-upload.product-gallery', [ 'post_type' => $post_type ])->withErrors($validator);
        }
        else 
        {
            try { 
                
                Excel::selectSheets('Product Gallery')->load(Input::file('excel_file'), function ($reader) {
                    $after = [];
                    $cell_required = [
                        'hotel_name',
                        'image_path'
                    ];
                    
                    $cell_map = [];
                    $row_errors = [];
                    $data_errors = [];
                    $data_orig = $reader->toArray();
                    foreach ($reader->toArray() as $row_num => $row) {
                        $cell_idx = 1;
                        foreach ($row as $ff => $cell) {
                            if(in_array($ff, $cell_required) && trim($cell) == ''){
                                $row_errors[$row_num][] = [ 'cell' => num2char($cell_idx), 'error' => 'required' ];
                                //$data_errors[$row_num] = $row;
                            }
                            
                            $after[$row_num][$ff] = strval($cell);

                            $cell_map[$ff] = num2char($cell_idx);
                            $cell_idx++;
                        }
                    }
                        
                    foreach(array_keys($row_errors) as $row_error){ //unset error rows
                        unset($after[$row_error]);
                    }

                    if(count($after) > 0)
                        Notification::success( trans('app.product_updated') );
                    else
                        Notification::error( trans('app.file_is_empty') );

                    if($after)
                        $errors_on_process = $this->product->bulkProductGalleryHotel_process($cell_map, $after); 

                    if(!empty($errors_on_process)){
                        //$data_errors += $errors_on_process['data'];
                        $row_errors += $errors_on_process['rows'];
                    }
                    
                    ksort($row_errors);
                    $data_succeeded = array_diff_key($data_orig, $row_errors);//get id(s) of succeeded data
                    $data_errors = array_diff_key($data_orig, $data_succeeded);//get id(s) of errors data
                    
                    if(!empty($data_errors)){
                        $this->downloadFailedUpload([
                            'file_name' => 'Product Gallery Failed', 
                            'sheet_name' => 'Product Gallery', 
                            'header_hilights' => 'B1:C1', 
                            'data_errors' => $data_errors, 
                            'row_errors' => $row_errors
                        ]);
                    }
                });
                    return Redirect::route('admin.product.bulk-upload.product-gallery', [ 'post_type' => $post_type ]);
            } catch (\Exception $e) {
                Notification::success($e->getMessage());
                return Redirect::route('admin.product.bulk-upload.product-gallery', [ 'post_type' => $post_type ]);
            }
        } 
    }

    public function doBulkUploadProductGallery_playground($post_type){
        $rules = array(
            'excel_file' => 'required',
        );

        $validator = Validator::make(Input::all(), $rules);
        
        if ($validator->fails()) 
        { 
            return Redirect::route('admin.product.bulk-upload.product-gallery', [ 'post_type' => $post_type ])->withErrors($validator);
        }
        else 
        {
            try { 
                
                Excel::selectSheets('Product Gallery')->load(Input::file('excel_file'), function ($reader) {
                    $after = [];
                    $cell_required = [
                        'playground_name',
                        'image_path'
                    ];
                    
                    $cell_map = [];
                    $row_errors = [];
                    $data_errors = [];
                    $data_orig = $reader->toArray();
                    foreach ($reader->toArray() as $row_num => $row) {
                        $cell_idx = 1;
                        foreach ($row as $ff => $cell) {
                            if(in_array($ff, $cell_required) && trim($cell) == ''){
                                $row_errors[$row_num][] = [ 'cell' => num2char($cell_idx), 'error' => 'required' ];
                                //$data_errors[$row_num] = $row;
                            }
                            
                            $after[$row_num][$ff] = strval($cell);

                            $cell_map[$ff] = num2char($cell_idx);
                            $cell_idx++;
                        }
                    }
                        
                    foreach(array_keys($row_errors) as $row_error){ //unset error rows
                        unset($after[$row_error]);
                    }

                    if(count($after) > 0)
                        Notification::success( trans('app.product_updated') );
                    else
                        Notification::error( trans('app.file_is_empty') );

                    if($after)
                        $errors_on_process = $this->product->bulkProductGalleryPlayground_process($cell_map, $after); 

                    if(!empty($errors_on_process)){
                        //$data_errors += $errors_on_process['data'];
                        $row_errors += $errors_on_process['rows'];
                    }
                    
                    ksort($row_errors);
                    $data_succeeded = array_diff_key($data_orig, $row_errors);//get id(s) of succeeded data
                    $data_errors = array_diff_key($data_orig, $data_succeeded);//get id(s) of errors data
                    
                    if(!empty($data_errors)){
                        $this->downloadFailedUpload([
                            'file_name' => 'Product Gallery Failed', 
                            'sheet_name' => 'Product Gallery', 
                            'header_hilights' => 'B1:C1', 
                            'data_errors' => $data_errors, 
                            'row_errors' => $row_errors
                        ]);
                    }
                });
                    return Redirect::route('admin.product.bulk-upload.product-gallery', [ 'post_type' => $post_type ]);
            } catch (\Exception $e) {
                Notification::success($e->getMessage());
                return Redirect::route('admin.product.bulk-upload.product-gallery', [ 'post_type' => $post_type ]);
            }
        } 
    }

    public function downloadProductGallery(){
        $post_type = Input::get('post_type');
        switch ($post_type) {
            case 'hotel':
                    return $this->downloadProductGallery_hotel();
                break;
            case 'playground':
                    return $this->downloadProductGallery_playground();
                break;
        }
    }

    public function downloadProductGallery_hotel(){
        $filter = Input::all(); 

        $rules = array(
            'product_category' => 'required',
            //'title' => 'required'
        );

        $setAttributeNames['product_category'] = trans('app.location');
        $setAttributeNames['title'] = trans('app.hotel_name');

        $v = Validator::make($filter, $rules);
        
        $v->setAttributeNames($setAttributeNames);
        if ($v->fails()) 
        { 
            return Redirect::route('admin.product.bulk-upload.product-gallery', [ 'post_type' => $filter['post_type'] ])->withInput()->withErrors($v);
        }
        else 
        {
            try { 
                unset($filter['_token']);
                $items = $this->product->paginate(1, 0, $filter);
                $data = [];
                foreach($items->items as $idx => $item){
                    if(count($item->productImages)){
                        foreach($item->productImages as $image){
                            $data[] = [
                                'id' => $image->id,
                                //'product_id' => $image->product_id,
                                'hotel_name' => $item->title,
                                'image_path' => $image->image_path
                            ];
                        }
                    }else{
                        $data[] = [
                            'id' => '',
                            //'product_id' => $item->id,
                            'hotel_name' => $item->title,
                            'image_path' => 'uploads/Hotel/sample.jpg'
                        ];
                    }
                }

                Excel::create('Product Gallery', function($excel) use($data) {

                    $excel->sheet('Product Gallery', function($sheet) use($data) {
                        $sheet->cells('B1:C1', function($cells) {
                            $cells->setBackground('#FFFF00');
                        });
                        $sheet->fromArray($data);

                    });

                })->export('xlsx');
        } catch (\Exception $e) {
                Notification::success($e->getMessage());
                return Redirect::route('admin.product.bulk-upload.product-gallery', [ 'post_type' => $post_type ]);
            }
        } 
    }

    public function downloadProductGallery_playground(){
        $filter = Input::all(); 

        $rules = array(
            'product_category' => 'required',
            //'title' => 'required'
        );

        $setAttributeNames['product_category'] = trans('app.location');
        $setAttributeNames['title'] = trans('app.hotel_name');

        $v = Validator::make($filter, $rules);
        
        $v->setAttributeNames($setAttributeNames);
        if ($v->fails()) 
        { 
            return Redirect::route('admin.product.bulk-upload.product-gallery', [ 'post_type' => $filter['post_type'] ])->withInput()->withErrors($v);
        }
        else 
        {
            try { 
                unset($filter['_token']);
                $items = $this->product->paginate(1, 0, $filter);
                $data = [];
                foreach($items->items as $idx => $item){
                    if(count($item->productImages)){
                        foreach($item->productImages as $image){
                            $data[] = [
                                'id' => $image->id,
                                //'product_id' => $image->product_id,
                                'playground_name' => $item->title,
                                'image_path' => $image->image_path
                            ];
                        }
                    }else{
                        $data[] = [
                            'id' => '',
                            //'product_id' => $item->id,
                            'playground_name' => $item->title,
                            'image_path' => 'uploads/Playground/sample.jpg'
                        ];
                    }
                }

                Excel::create('Product Gallery', function($excel) use($data) {

                    $excel->sheet('Product Gallery', function($sheet) use($data) {
                        $sheet->cells('B1:C1', function($cells) {
                            $cells->setBackground('#FFFF00');
                        });
                        $sheet->fromArray($data);

                    });

                })->export('xlsx');
        } catch (\Exception $e) {
                Notification::success($e->getMessage());
                return Redirect::route('admin.product.bulk-upload.product-gallery', [ 'post_type' => $post_type ]);
            }
        } 
    }

    public function downloadFailedUpload($data){
        $file_name = $data['file_name']; 
        $sheet_name = $data['sheet_name']; 
        $header_hilights = $data['header_hilights'];
        $data_errors = $data['data_errors'];
        $row_errors = $data['row_errors'];
        $error_bg = [
            'exist' => '#F7DBDC',
            'not_exist' => '#D9D7F2',
            'required' => '#F8F1DC',
            'required2' => '#FF9900'
        ];
        Excel::create($file_name, function($excel) use($data_errors, $row_errors, $sheet_name, $header_hilights, $error_bg) {
            $excel->sheet($sheet_name, function($sheet) use($data_errors, $row_errors, $header_hilights, $error_bg) {
                $sheet->cells($header_hilights, function($cells) {
                    $cells->setBackground('#FFFF00');
                });
                
                foreach(array_values($row_errors) as $idx => $row_error){ 
                    foreach($row_error as $cell_error){
                        $sheet->cells($cell_error['cell'].( $idx+2 ), function($cells) use ($cell_error, $error_bg) {
                            $cells->setBackground($error_bg[$cell_error['error']]);
                        });
                    }
                    
                } 
                $sheet->fromArray($data_errors);

            });
        })->export('xlsx');

    }
}