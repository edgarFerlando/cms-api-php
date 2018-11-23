<?php namespace App\Http\Controllers\Backend;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Repositories\InvestmentInformation\InvestmentInformationRepository;
use Redirect;
use View;
use Input;
use Validator;
use Response;
use Notification;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use App\Exceptions\Validation\ValidationException;
use Config;

use App\Repositories\Role\RoleRepository as Role;
use Entrust;

use App\Models\ProductSpecialOffer;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\ProductImage;

use App\Repositories\Booking\BookingInterface;

class InvestmentInformationController extends Controller {
	protected $product;

    public function __construct(InvestmentInformationRepository $investmentInformation) {
        $this->investmentInformation = $investmentInformation;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index() {
        $attr = [ 
                'title' => trans('app.investment_information')
            ];
        if(!Entrust::can(['read_investment_information'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $page = Input::get('page', 1);
        $perPage = config_db_cached('settings::backend_per_page');

        $filter = Input::all(); 
        unset($filter['_token']);

        $pagiData = $this->investmentInformation->paginate($page, $perPage, $filter);
        $totalItems = $pagiData->totalItems;
        $items = new LengthAwarePaginator($pagiData->items, $totalItems, $perPage, $page, [
            'path' => Paginator::resolveCurrentPath()
        ]);

        $items->setPath("");
        $items->appends($filter);

        return view('backend.investmentInformation.index', compact('items', 'totalItems'));
    }

    public function create(){
        $attr = [ 
                'title' => trans('app.add_new')
            ];
        if(!Entrust::can(['create_investment_information'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        
        $stars_options = [
            'no_score' => 'No score',
            'star_5' => stars_char(5),
            'star_5_min' => stars_char(5).'-',
            'star_4_plus' => stars_char(4).'+',
            'star_4' => stars_char(4),
            'star_4_min' => stars_char(4).'-',
            'star_3_plus' => stars_char(3).'+',
            'star_3' => stars_char(3),
            'star_3_min' => stars_char(3).'-',
            'star_2_plus' => stars_char(2).'+',
            'star_2' => stars_char(2),
            'star_2_min' => stars_char(2).'-',
            'star_1_plus' => stars_char(1).'+',
            'star_1' => stars_char(1),
        ];
        return view('backend.investmentInformation.create', compact('stars_options'));
    }
    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store() { 
        $input = Input::all();
        foreach(['nab', 'scoring_3_thn', 'inv_1_hr', 'inv_1_bln', 'inv_1_thn', 'inv_3_thn', 'since_launched', 'fluctuation'] as $item_convert){
            $input[$item_convert] = unformat_money_raw($input[$item_convert]);
        }
        
        try {
            $this->investmentInformation->create($input);
            Notification::success( trans('app.investment_information_added') );
            return Redirect::route(getLang().'.admin.investment-information.index');
        } catch (ValidationException $e) {
            Input::merge($input);
            return Redirect::route(getLang().'.admin.investment-information.create')->withInput()->withErrors($e->getErrors());
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

    public function edit($id){
        $attr = [ 
                'title' => trans('app.investment_information')
            ];
        if(!Entrust::can(['update_investment_information'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $item = $this->investmentInformation->find($id);

        $stars_options = [
            'no_score' => 'No score',
            'star_5' => stars_char(5),
            'star_5_min' => stars_char(5).'-',
            'star_4_plus' => stars_char(4).'+',
            'star_4' => stars_char(4),
            'star_4_min' => stars_char(4).'-',
            'star_3_plus' => stars_char(3).'+',
            'star_3' => stars_char(3),
            'star_3_min' => stars_char(3).'-',
            'star_2_plus' => stars_char(2).'+',
            'star_2' => stars_char(2),
            'star_2_min' => stars_char(2).'-',
            'star_1_plus' => stars_char(1).'+',
            'star_1' => stars_char(1)
        ];
        return view('backend.investmentInformation.edit', compact('item', 'stars_options'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int $id
     * @return Response
     */
    public function update($id) {
        $input = Input::all();
        foreach(['nab', 'scoring_3_thn', 'inv_1_hr', 'inv_1_bln', 'inv_1_thn', 'inv_3_thn', 'since_launched', 'fluctuation'] as $item_convert){
            $input[$item_convert] = unformat_money_raw($input[$item_convert]);
        }
        try {
            $this->investmentInformation->update($id, $input);
            Notification::success( trans('app.investment_information_updated') );
            return Redirect::route(getLang().'.admin.investment-information.index');
        } catch (ValidationException $e) {
            Input::merge($input);
            return langRedirectRoute('admin.investment-information.edit', [ 'id' => $id] )->withInput()->withErrors($e->getErrors());
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
        $this->investmentInformation->delete($id);
        Notification::success( trans('app.investment_information_deleted') );
        return langRedirectRoute('admin.investment-information.index');
    }

    public function confirmDestroy($id) {
        $attr = [ 
                'title' => trans('app.investment_information')
            ];
        if(!Entrust::can(['delete_investment_information'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $item = $this->investmentInformation->find($id);
        //$is_related = [];

        //if($this->booking->hasProduct($id)) $is_related[] = 'booking';

        return view('backend.investmentInformation.confirm-destroy', compact('item'));
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