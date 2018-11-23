<?php namespace App\Repositories\Booking;

use App\Models\BookingHeader;
use App\Models\BookingDetail;
use App\Models\BookingConfirmedDetail;
use App\Models\Referral;
use App\Models\Affiliate;
use Config;
use Response;
use Str;
use App\Repositories\RepositoryAbstract;
use App\Repositories\CrudableInterface as CrudableInterface;
use App\Repositories\RepositoryInterface as RepositoryInterface;
use App\Exceptions\Validation\ValidationException;
use App\Repositories\AbstractValidator as Validator;
use LaravelLocalization;
use Notification;

use App\User;
use App\Repositories\User\UserRepository;

use App\Repositories\ProductVariation\ProductVariationRepository;
use App\ProductVariation;

use App\ProductSku;

use Auth;
use Cart;
use App\Models\UserMeta;
use Carbon\Carbon;
use Validator as InlineValidator;
use Redirect;
use Session;


class BookingRepository extends RepositoryAbstract implements BookingInterface, CrudableInterface {

    protected $perPage;
    protected $booking;
    protected $user;
    protected $referral;
    protected $affiliate;
    protected $productVariation;
    /**
     * Rules
     *
     * @var array
     */
    protected static $rules;
    protected static $attributeNames;

    /**
     * @param Booking $booking
     */
    public function __construct(BookingHeader $booking) {

        $config = Config::get('holiday');
        $this->perPage = $config['per_page'];
        $this->booking = $booking;
        $this->bookingDetail = new BookingDetail;
        $this->bookingConfirmedDetail = new BookingConfirmedDetail;
        $this->user = new UserRepository(new User);
        $this->referral = new Referral;
        $this->affiliate = new Affiliate;
        $this->productVariation = new ProductVariationRepository(new ProductVariation);

        $rules_n_attributeNames = $this->rules();
        self::$rules = $rules_n_attributeNames['rules'];
        self::$attributeNames = $rules_n_attributeNames['attributeNames'];
    }

    public function rules(){
        $_rules = array();
        $setAttributeNames = array();
        if(!Auth::check()){
            $_rules['full_name'] = 'required';
            $_rules['phone'] = 'required|numeric|digits_between:6,12';
            $_rules['email'] = 'required|email';
            $setAttributeNames['full_name'] = trans('app.full_name');
            $setAttributeNames['phone'] = trans('app.phone_number');
            $setAttributeNames['email'] = trans('app.email');
        }else{
            $existing_metas_q = UserMeta::where('user_id', Auth::user()->id)->get();
           
            if(count($existing_metas_q) == 0 ){
                $_rules['full_name'] = 'required';
                $_rules['phone'] = 'required|numeric|digits_between:6,12';
                $setAttributeNames['full_name'] = trans('app.full_name');
                $setAttributeNames['phone'] = trans('app.phone_number');
            }
        }
        
        return [
            'rules' => $_rules,
            'attributeNames' => $setAttributeNames
        ];
    }

    /**
     * @return mixed
     */
    public function all() {
        return $this->booking->with('bookingOption')->orderBy('created_at', 'DESC')->get();
    }

    /**
     * @param $limit
     * @return mixed
     */
    public function getLastBooking($limit) {
        return $this->booking->orderBy('created_at', 'desc')->take($limit)->offset(0)->get();
    }

    /**
     * @return mixed
     */
    public function lists() {
        return $this->booking->all()->lists('name', 'id');
    }

    /**
     * Get paginated booking
     *
     * @param int $page Number of booking per page
     * @param int $limit Results per page
     * @param boolean $all Show published or all
     * @return StdClass Object with $items and $totalItems for pagination
     */
    public function paginate($page = 1, $limit = 10, $all = false) {

        $result = new \StdClass;
        $result->page = $page;
        $result->limit = $limit;
        $result->totalItems = 0;
        $result->items = array();

        $query = $this->booking->with(['user', 'bookingDetails', 'bookingStatus.bookingStatusTranslation'])->orderBy('created_at', 'DESC');

        if(!$all) {
            $query->where('is_published', 1);
        }

        $booking = $query->skip($limit * ($page - 1))->take($limit)->get();

        $result->totalItems = $this->totalBooking($all);
        $result->items = $booking->all();

        return $result;
    }

    /**
     * Get paginated booking
     *
     * @param int $page Number of booking per page
     * @param int $limit Results per page
     * @param boolean $all Show published or all
     * @return StdClass Object with $items and $totalItems for pagination
     */
    public function paginateByUser($page = 1, $limit = 10, $all = false, $filter) {
        $user = Auth::user();
        $result = new \StdClass;
        $result->page = $page;
        $result->limit = $limit;
        $result->totalItems = 0;
        $result->items = array();
        $details_id = array();
        $post_type = $filter['post_type'];

        $query = $this->booking->with(['user', 
                                        'bookingDetails', 
                                        'tripBookingDetails', 
                                        'tripBookingDetails.productVariation.productSku', 
                                        'tripBookingDetails.productVariation.productAttribute', 
                                        'tripBookingDetails.productVariation.productAttributeOption.productAttributeOptionTranslation', 
                                        'tripBookingDetails.productVariation.product.productMetas',
                                        'tripBookingDetails.productVariation.product.productTranslation',
                                        'tripBookingDetails.productVariation.product.productImages',
                                        'bookingStatus.bookingStatusTranslation'])->orderBy('created_at', 'DESC')->where('user_id', $user->id);

        if($post_type != '' && $post_type == 'trip'){
            $query->whereHas('tripBookingDetails', function($q) use ($post_type){
                $q->where('post_type', $post_type);
            });
        }

        if($filter['status'] != '')
        {
            $query->where('status_id', $filter['status']);
        }

        if(!$all) {
            $query->where('is_published', 1);
        }

        //dd($query);
        $bookingheaders = $query->get();
        //dd($booking);
        //[1] mendapat semua id booking detail
        foreach ($bookingheaders as $bookingheader) {
            foreach ($bookingheader->tripBookingDetails as $key => $value) {
                $details_id[$key] = $value->id;
            }
        }     
        
        $booking = BookingDetail::with('productVariation.productSku', 
                                        'productVariation.productAttribute', 
                                        'productVariation.productAttributeOption.productAttributeOptionTranslation', 
                                        'productVariation.product.productMetas',
                                        'productVariation.product.productTranslation',
                                        'productVariation.product.productImages')
        ->whereIn('id', $details_id)->skip($limit * ($page - 1))->take($limit)->get();

        //dd($booking);
        $result->totalItems = $bookingheaders[0]->tripBookingDetails->count();
        $result->items = $booking->all();

        return $result;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function find($id) {
        return $this->booking->with(['user.userMetas', 
                                        'bookingDetails.productVariation.product.productTranslation', 
                                        'bookingDetails.productVariation.productAttributeOption.productAttributeOptionTranslation', 
                                        'playgroundBookingDetails.productVariation.product.productTranslation', 
                                        'playgroundBookingDetails.productVariation.productAttributeOption.productAttributeOptionTranslation', 
                                        'tripBookingDetails.productVariation.product.productTranslation', 
                                        'tripBookingDetails.productVariation.product.productMetas.productMetaTranslation',
                                        'tripBookingDetails.productVariation.productAttributeOption.productAttributeOptionTranslation', 
                                        'merchantBookingDetails.productVariation.product.productTranslation', 
                                        'merchantBookingDetails.productVariation.productAttributeOption.productAttributeOptionTranslation', 
                                        'paymentConfirmation'])->findOrFail($id);
    }

    public function findByUser($id)
    {
        $user = Auth::user();
        return $this->booking->with(['user.userMetas', 'bookingDetails.productVariation.product.productTranslation', 'bookingDetails.productVariation.productAttributeOption.productAttributeOptionTranslation', 'playgroundBookingDetails.productVariation.product.productTranslation', 'playgroundBookingDetails.productVariation.productAttributeOption.productAttributeOptionTranslation', 'tripBookingDetails.productVariation.product.productTranslation', 'tripBookingDetails.productVariation.productAttributeOption.productAttributeOptionTranslation', 'merchantBookingDetails.productVariation.product.productTranslation', 'merchantBookingDetails.productVariation.productAttributeOption.productAttributeOptionTranslation', 'paymentConfirmation'])->where('user_id', $user->id)->findOrFail($id);
    }

    public function findByUserId(){
        $user = Auth::user();
        return $this->booking->with(['bookingDetails', 'tripBookingDetails', 'bookingStatus'])->where('user_id', $user->id)->get();
    }

    /**
     * @param $attributes
     * @return bool|mixed
     * @throws \App\Exceptions\Validation\ValidationException
     */
    public function create($attributes) {
        //dd($attributes);
        if($this->isValid($attributes) && ( count($attributes['items']) || count($attributes['playgroundItems']) || count($attributes['tripItems']) || count($attributes['merchantItems']) )) {
            
            if(Auth::check()){
                 $t_attributes['user_id'] = Auth::user()->id;
                 $existing_metas_q = UserMeta::where('user_id', Auth::user()->id)->get(); 
                 $existing_metas = userMeta($existing_metas_q);
                 $to = Auth::user()->email;
                 $name = Auth::user()->name;// != ''?Auth::user()->name:(isset($attributes['full_name'])?$attributes['full_name']:$existing_metas->full_name);
                
                 if($name == ''){ //dd($existing_metas);
                    $full_name = isset($attributes['full_name'])?$attributes['full_name']:$existing_metas->full_name;
                    $user = User::find($t_attributes['user_id']);
                    $user->name = $full_name;
                    $user->save();
                    $name = $full_name;
                    Auth::user()->setAttribute('name', $name);//update session user name
                 }
            }else{
                //check user is already registered
                $user = $this->user->findByEmail($attributes['email']);
                //if(is_null($user)){
                if(!is_null($user)){//registered, so need password to login
                    throw new ValidationException(trans('app.already_registered'), ['user_status' => 'already_registered', 'email' => $attributes['email'] ]); //Redirect::route(getLang().'.login', ['redirectTo' => trans('routes.mycart')]);
                }else{

                    $Length = 10;
                    $random_password = substr(str_shuffle(md5(time())), 0, $Length);
                    $data = [
                        'name' => $attributes['full_name'],
                        'email' => $attributes['email'],
                        'password' => bcrypt($random_password),
                        'role' => Config::get('holiday.new_user_default_role')
                    ];
                    $user = $this->user->create($data);

                    $new_user_default_role = Config::get('holiday.new_user_default_role');
                    $user->roles()->sync([$new_user_default_role]);

                    sendEmailWithTemplate([
                        'email_template_module_id' => 1,
                        'to' => $data['email'],
                        'replace_vars' => [
                            '{email}' => $data['email'],
                            '{password}' => $random_password
                        ]
                    ]);
                }

                //$t_attributes['booking_id'] = 'belom diset';
                $t_attributes['user_id'] = $user->id;
                $to = $attributes['email'];
                $name = $attributes['full_name'];
            }

            //create or update usermeta
            $existing_metas_q = UserMeta::where('user_id', $t_attributes['user_id'])->get();
            $existing_metas = userMeta($existing_metas_q);
            $meta_map = [
                'full_name' => 'full_name',
                'phone' => 'phone'
            ];

            foreach($meta_map as $ff_name => $meta_key){
                if(isset($attributes[$ff_name])){
                    if(isset($existing_metas->$meta_key)){

                        UserMeta::where('user_id', $t_attributes['user_id'])
                        ->where('meta_key', $meta_key)
                        ->update(['meta_value' => $attributes[$ff_name]]);

                        //if($meta_key == 'full_name')
                        //    User::where('id', $t_attributes['user_id'])
                         //   ->update(['name' => $attributes[$ff_name]]);
                    }else{
                        UserMeta::create([
                            'user_id' => $t_attributes['user_id'],
                            'meta_key' => $meta_key,
                            'meta_value' => $attributes[$ff_name] 
                        ]);
                    }
                }
            }
            
            
            //header
            //$now = Carbon::now();
            //$payment_deadline_minutes = config_db_cached('settings::payment_deadline');
            //$t_attributes['created_at'] = $now;
            //$t_attributes['payment_deadline'] = $now->addMinutes($payment_deadline_minutes);

            $bookingHeader = $this->booking->create($t_attributes);

            //detail
            //dd($attributes);
            $items_detail = []; 
            if(count($attributes['items']))
            foreach($attributes['items'] as $item){
                $items_detail [] = [
                    'post_type' => 'hotel',
                    'booking_header_id' => $bookingHeader->id,
                    'variation_id' => $item['id'],
                    'check_in' => carbon_format_store($item['attributes']['checkin']),
                    'check_out' => carbon_format_store($item['attributes']['checkout']),
                    'no_of_rooms' => $item['quantity'],
                    'qty' => 0,
                    'price' => $item['price'],
                    'weekend_price' => $item['attributes']['weekend_price']
                ];
            }

            if(count($attributes['playgroundItems']))
            foreach($attributes['playgroundItems'] as $item){
                $items_detail [] = [
                    'post_type' => 'playground',
                    'booking_header_id' => $bookingHeader->id,
                    'variation_id' => $item['id'],
                    'check_in' => carbon_format_store($item['attributes']['playground_visit_date']),
                    'check_out' => '0000-00-00',
                    'no_of_rooms' => 0,
                    'qty' => $item['quantity'],
                    'price' => $item['price'],
                    'weekend_price' => $item['attributes']['weekend_price']
                ];
            }
            
            if(count($attributes['tripItems']))
            foreach($attributes['tripItems'] as $item){
                $visit_date = date('m/d/Y', strtotime($item['attributes']['trip_visit_date']));
                $items_detail [] = [
                    'post_type' => 'trip',
                    //'booking_header_id' => $bookingHeader->id,
                    'variation_id' => $item['id'],
                    'check_in' => carbon_format_store($visit_date),
                    'check_out' => '0000-00-00',
                    'no_of_rooms' => 0,
                    'qty' => $item['quantity'],
                    'price' => $item['price'],
                    'weekend_price' => $item['attributes']['weekend_price']
                ];

                $variant = $this->productVariation->find($item['id']);
                $stok = $variant->productSku->qty - (int)$item['quantity'];

                ProductSku::find($variant->productSku->id)->update([
                        'qty' => $stok
                    ]);
                
            }

            //dd($stok);
            if(count($attributes['merchantItems']))
            foreach($attributes['merchantItems'] as $item){
                $items_detail [] = [
                    'post_type' => 'merchant',
                    'booking_header_id' => $bookingHeader->id,
                    'variation_id' => $item['id'],
                    'check_in' => carbon_format_store($item['attributes']['merchant_visit_date']),
                    'check_out' => '0000-00-00',
                    'no_of_rooms' => 0,
                    'qty' => $item['quantity'],
                    'price' => $item['price'],
                    'weekend_price' => $item['attributes']['weekend_price']
                ];
            }
            //dd($items_detail);
            $this->bookingDetail->insert($items_detail);

            $details = $attributes['items'];//Cart::getContent(); 
            $playgroundDetails = $attributes['playgroundItems'];
            $tripDetails = $attributes['tripItems'];
            $merchantDetails = $attributes['merchantItems'];
            $cart_items = view('emails.booking.detail', compact('details', 'playgroundDetails', 'tripDetails', 'merchantDetails'))->render();
            //dd($item_detailxxx);

            //get referal
            if(!is_null(session::get('affiliate_code'))){
                $affiliate = $this->affiliate->select('id')->where('code', session::get('affiliate_code'))->first();
                if(count($affiliate)){
                    $this->referral->insert([
                        'affiliate_id' => $affiliate->id,
                        'booking_id' => $bookingHeader->id
                    ]);
                }
                
            }

            sendEmailWithTemplate([
                'email_template_module_id' => 2,
                'to' => $to,
                'replace_vars' => [
                    '{name}' => $name,
                    '{details}' => $cart_items
                ]
            ]);

            return true;
        }elseif(count($attributes['items']) <= 0 && count($attributes['playgroundItems']) <= 0 && count($attributes['tripItems']) <= 0){
            throw new ValidationException(trans('app.cart_is_empty'), $this->getErrors());
        }
        throw new ValidationException(trans('app.validation_failed'), $this->getErrors());
    }


    /**
     * @param $id
     * @param $attributes
     * @return bool|mixed
     * @throws \App\Exceptions\Validation\ValidationException
     */
    public function update($id, $attributes) {
        if($this->isValid($attributes)) {
            $t_attributes = array();
            $this->booking = $this->find($id);
            foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
                $t_attributes[$locale] = [
                    'name' => $attributes['name'][$locale]
                ];
            }
            $this->booking->fill($t_attributes)->save();
            return true;
        }
        throw new ValidationException('Product attribute validation failed', $this->getErrors());
    }

    public function updateOrder($id, $attributes) {
        $rules = [];
        if($attributes['status'] == 2){
            $rules = [ 
                'booking_no' => 'required' 
            ];
        }

        //dd($attributes);

        $v = InlineValidator::make($attributes, $rules);
        //$setAttributeNames['weekend_date'] = trans('app.weekend_date');
        //$setAttributeNames['description'] = trans('app.description');
        //$v->setAttributeNames($setAttributeNames);
        if ($v->fails())
        {  
            throw new ValidationException('Product attribute validation failed', $v->errors());
            //return Redirect::Route('admin.settings.weekend-days')->withInput()->withErrors($v->errors());
        }


        $t_attributes = [
            'booking_no' => $attributes['booking_no'],
            'status_id' => $attributes['status']
        ];

        if($attributes['status'] == 2){ //confirmed
            $now = Carbon::now();
            $payment_deadline_minutes = config_db_cached('settings::payment_deadline');
            //$t_attributes['updated_at'] = $now;
            $t_attributes['payment_deadline'] = $now->addMinutes($payment_deadline_minutes);
        }
        $this->booking = $this->find($id);
        $this->booking->fill($t_attributes)->save();

        $details_raw = $this->booking->allBookingDetails;
        //$details = detailsCartFormated($details_raw);

        //save booking confirmed detail
        //$items_detail = []; 
        //dd($details_raw);
        foreach($details_raw as $item){//new
            /*switch ($attributes['post_type'][$item->id]) {
                case 'hotel':
                    $item_detail = [
                        'post_type' => $item->post_type,
                        'booking_detail_id' => $item->id,
                        'booking_header_id' => $item->booking_header_id,
                        'variation_id' => $item->variation_id,
                        'check_in' => carbon_format_store($attributes['check_in'][$item->id],'Y-m-d', 'd M Y'),
                        'check_out' => carbon_format_store($attributes['check_out'][$item->id],'Y-m-d', 'd M Y'),
                        'no_of_rooms' => $attributes['no_of_rooms'][$item->id],
                        'price' => unformat_money($attributes['price'][$item->id]),
                        'weekend_price' => unformat_money($attributes['weekend_price'][$item->id]),
                        'remark' => $attributes['remark'][$item->id]
                    ];
                break;

                case 'playground':
                    $item_detail = [
                        'post_type' => $item->post_type,
                        'booking_detail_id' => $item->id,
                        'booking_header_id' => $item->booking_header_id,
                        'variation_id' => $item->variation_id,
                        'check_in' => carbon_format_store($attributes['check_in'][$item->id],'Y-m-d', 'd M Y'),
                        'check_out' => carbon_format_store($attributes['check_out'][$item->id],'Y-m-d', 'd M Y'),
                        'no_of_rooms' => $attributes['no_of_rooms'][$item->id],
                        'price' => unformat_money($attributes['price'][$item->id]),
                        'weekend_price' => unformat_money($attributes['weekend_price'][$item->id]),
                        'remark' => $attributes['remark'][$item->id]
                    ];
                break;
            }*/

            switch ($attributes['post_type'][$item->id]) {
                case 'hotel':
                    $item_detail = [
                        'check_in' => carbon_format_store($attributes['check_in'][$item->id],'Y-m-d', 'd M Y'),
                        'check_out' => carbon_format_store($attributes['check_out'][$item->id],'Y-m-d', 'd M Y'),
                        'no_of_rooms' => $attributes['no_of_rooms'][$item->id],
                        'qty' => 0,
                        'price' => unformat_money($attributes['price'][$item->id]),
                        'weekend_price' => unformat_money($attributes['weekend_price'][$item->id]),
                        'remark' => $attributes['remark'][$item->id],
                    ];
                break;
                case 'playground':
                    $item_detail = [
                        'check_in' => carbon_format_store($attributes['playground_visit_date'][$item->id],'Y-m-d', 'd M Y'),
                        'check_out' => '0000-00-00',
                        'no_of_rooms' => 0,
                        'qty' => $attributes['no_of_people'][$item->id],
                        'price' => unformat_money($attributes['price'][$item->id]),
                        'weekend_price' => unformat_money($attributes['weekend_price'][$item->id]),
                        'remark' => $attributes['remark'][$item->id],
                            ];
                break;
                case 'trip':
                    $item_detail = [
                        'check_in' => carbon_format_store($attributes['trip_visit_date'][$item->id],'Y-m-d', 'd M Y'),
                        'check_out' => '0000-00-00',
                        'no_of_rooms' => 0,
                        'qty' => $attributes['no_of_people'][$item->id],
                        'price' => unformat_money($attributes['price'][$item->id]),
                        'weekend_price' => unformat_money($attributes['weekend_price'][$item->id]),
                        'remark' => $attributes['remark'][$item->id],
                            ];
                break;
                case 'merchant':
                    $item_detail = [
                        'check_in' => carbon_format_store($attributes['merchant_visit_date'][$item->id],'Y-m-d', 'd M Y'),
                        'check_out' => '0000-00-00',
                        'no_of_rooms' => 0,
                        'qty' => $attributes['no_of_people'][$item->id],
                        'price' => unformat_money($attributes['price'][$item->id]),
                        'weekend_price' => unformat_money($attributes['weekend_price'][$item->id]),
                        'remark' => $attributes['remark'][$item->id],
                            ];
                break;
            }

            if(isset($attributes['booking_confirmed_detail_id'][$item->id])){
                $item_detail += [
                    'updated_at' => Carbon::now(),
                    'updated_by' => Auth::user()->id
                ];

                $this->bookingConfirmedDetail->where('id', '=', $attributes['booking_confirmed_detail_id'][$item->id])->update($item_detail);
            }else{
                $item_detail += [
                    'post_type' => $item->post_type,
                    'booking_detail_id' => $item->id,
                    'booking_header_id' => $item->booking_header_id,
                    'variation_id' => $item->variation_id,
                    'created_at' => Carbon::now(),
                    'created_by' => Auth::user()->id
                ];

                $this->bookingConfirmedDetail->insert($item_detail);
            }
        }
        
        //if(!isset($attributes['booking_confirmed_detail_id']))//jika bukan update
         //   $this->bookingConfirmedDetail->insert($items_detail);

        if($attributes['status'] == 2){ //confirmed
            $confirmedDetails_raw = $this->booking->bookingConfirmedDetails;
            $details = detailsCartFormated($confirmedDetails_raw, true);

            $playgroundConfirmedDetails_raw = $this->booking->playgroundBookingConfirmedDetails;
            $playgroundDetails = detailsCartFormated($playgroundConfirmedDetails_raw, true);

            $tripConfirmedDetails_raw = $this->booking->tripBookingConfirmedDetails;
            $tripDetails = detailsCartFormated($tripConfirmedDetails_raw, true);

            $merchantConfirmedDetails_raw = $this->booking->merchantBookingConfirmedDetails;
            $merchantDetails = detailsCartFormated($merchantConfirmedDetails_raw, true);

            $cart_items = view('emails.booking.detail', compact('details','playgroundDetails','tripDetails','merchantDetails'))->render();
            //dd($cart_items);
            sendEmailWithTemplate([
                'email_template_module_id' => 3,//Booking : confirmed
                'to' => $this->booking->user->email,
                'replace_vars' => [
                    '{name}' => $this->booking->user->name,
                    '{booking_no}' => $attributes['booking_no'],
                    '{details}' => $cart_items,
                    '{deadline}' => fulldate_trans($t_attributes['payment_deadline'])
                    //'{payment_info}' => paymentInfoHTML(Input::all())
                ]
            ]);
        }
        return true;
    }

    /**
     * @param $id
     * @return mixed|void
     */
    public function delete($id) {
        $booking = $this->booking->findOrFail($id);
        $booking->delete();
    }

    /**
     * Get total booking count
     * @param bool $all
     * @return mixed
     */
    protected function totalBooking($all = false) {
        return $this->booking->count();
    }

    public function findByEmailAndBookingNo($email, $booking_no){ 
        return $this->booking->with(['user', 'bookingStatus', 'bookingDetails.productVariation.product.productTranslation', 'bookingDetails.productVariation.productAttributeOption.productAttributeOptionTranslation', 'playgroundBookingDetails.productVariation.product.productTranslation', 'playgroundBookingDetails.productVariation.productAttributeOption.productAttributeOptionTranslation'])->where('booking_no', $booking_no)->whereHas('user', function($q) use ($email){
            $q->where('email', $email);
        })->first();
    }

    public function hasProduct($product_id){
        $hasProduct = $this->bookingDetail->whereHas('productVariation', function($q) use($product_id){
            $q->where('product_id', $product_id);
        })->count();
        if($hasProduct > 0)
            return true;
        else
            return false;
    }
}
