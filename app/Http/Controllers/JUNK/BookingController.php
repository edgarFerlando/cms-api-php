<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Input;
use App\ProductSku;
use Cart;
use PlaygroundCart;
use TripCart;
use MerchantCart;
use Redirect;
use Notification;

use App\Exceptions\Validation\ValidationException;

//use App\Repositories\ProductVariation\ProductVariationRepository as ProductVariation;
use App\Repositories\Booking\BookingInterface;
use App\Repositories\ProductVariation\ProductVariationInterface;

use App\ProductVariation;

use App\User;
use Auth;
use Validator;
use App\Models\BookingConfirmation;
use App\Models\BankAccount;
//use App\Models\TripCart;
//use App\Models\MerchantCart;
use App\Models\CustomerBankAccount;

class BookingController extends Controller {

	protected $productVariation;
	protected $product;
	protected $booking;

	public function __construct(BookingInterface $booking, ProductVariationInterface $productVariation) {
		$this->productVariation = $productVariation;
        $this->booking = $booking;
        $this->bookingConfirmation = new BookingConfirmation;
    }
	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		//
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		//
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		$get = Input::all();
		$get['items'] = Cart::getContent();
		$get['playgroundItems'] = PlaygroundCart::getContent();
		$get['tripItems'] = TripCart::getContent();
		$get['merchantItems'] = MerchantCart::getContent();
		//dd($get);
		if(isset($get['tripItems']))
		{
			foreach ($get['tripItems'] as $item) {
				$variant = $this->productVariation->find($item->id);
				
				$max_p = $variant->productSku->qty;

				$rules = [
					    'quantity.'.$item->id => "required|integer|max:$max_p"
					        ];

				$setAttributeNames['quantity.'.$item->id] = trans('app.stock');
				//dd($item);
				$v = Validator::make(['quantity.'.$item->id => $item->quantity], $rules);
				
			    $v->setAttributeNames($setAttributeNames);
			    //dd($v);
			    if ($v->fails())
			    {
			        Notification::container('frontend')->error( 'Stock not allow' );
			        return Redirect::route(getLang().'.booking')->withInput()->withErrors($v->errors());
			    }
			}
		}

		if(isset($get['merchantItems']))
		{
			foreach ($get['merchantItems'] as $item) {
				$variant = $this->productVariation->find($item->id);
				
				$max_p = $variant->productSku->qty;

				$rules = [
					    'quantity.'.$item->id => "required|integer|max:$max_p"
					        ];

				$setAttributeNames['quantity.'.$item->id] = trans('app.stock');
				//dd($item);
				$v = Validator::make(['quantity.'.$item->id => $item->quantity], $rules);
				
			    $v->setAttributeNames($setAttributeNames);
			    //dd($v);
			    if ($v->fails())
			    {
			        Notification::container('frontend')->error( 'Stock not allow' );
			        return Redirect::route(getLang().'.booking')->withInput()->withErrors($v->errors());
			    }
			}
		}
		
		//dd($get['merchantItems']);

        try {
            $this->booking->create($get);
            Cart::clear();
            PlaygroundCart::clear();
            TripCart::clear();
            MerchantCart::clear();
            //Notification::success( trans('app.booking_added') );
            Notification::container('frontend')->success( trans('app.booking_added') );
            return Redirect::route(getLang().'.page.show', [ 'slug' => trans('routes.thankyou') ]);
        } catch (ValidationException $e) {
        	if(is_array($e->getErrors()) && isset($e->getErrors()['user_status'])){
        		return Redirect::route(getLang().'.login', ['redirectTo' => trans('routes.booking')])->withInput(['email' => $e->getErrors()['email'] ]);
        	}else{
        		Notification::container('frontend')->error($e->getMessage());
            	return Redirect::route(getLang().'.booking')->withInput()->withErrors($e->getErrors());
        	}
        	
        }
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		//
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		//
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		//
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		//
	}

	public function adaptToLocale($items, $post_type = 'hotel'){ 
		foreach($items as $idx => $item){ 
			$variation = $this->productVariation->find($item['id']);
			$variations_raw = $variation->productSku->productVariations;
			$variations = [];
			foreach($variations_raw as $variation_raw){
				$variations[$variation_raw->productAttribute->product_attribute_key] = $variation_raw->productAttributeOption->productAttributeOptionTranslation->name;
			}
			//dd($post_type);
			switch ($post_type) {
				case 'hotel':
					$variations['checkin'] = $item['attributes']['checkin'];
					$variations['checkout'] = $item['attributes']['checkout'];
					$variations['weekend_price'] = $item['attributes']['weekend_price'];

					Cart::remove($item->id);// kalo ga di remove maka akan nambah terus
					if(intval($item['quantity']) != 0)
						Cart::add(array(
						    'id' => $item['id'],
						    'name' => $variation->product->productTranslation->title,
						    'price' => $variation->productSku->price,
						    'quantity' => $item['quantity'],
						    'attributes' => $variations
						));

					return Cart::getContent();
					break;
				case 'playground':
					$variations['playground_visit_date'] = $item['attributes']['playground_visit_date'];
					$variations['weekend_price'] = $item['attributes']['weekend_price'];
					//dd($variations);
					PlaygroundCart::remove($item->id);// kalo ga di remove maka akan nambah terus
					if(intval($item['quantity']) != 0)
						PlaygroundCart::add(array(
						    'id' => $item['id'],
						    'name' => $variation->product->productTranslation->title,
						    'price' => $variation->productSku->price,
						    'quantity' => $item['quantity'],
						    'attributes' => $variations
						));

					return PlaygroundCart::getContent();
					break;
				case 'trip':
					$variations['start_date'] = $item['attributes']['start_date'];
					$variations['end_date'] = $item['attributes']['end_date'];
					$variations['trip_visit_date'] = $item['attributes']['trip_visit_date'];
					$variations['weekend_price'] = $item['attributes']['weekend_price'];

					TripCart::remove($item->id);// kalo ga di remove maka akan nambah terus
					if(intval($item['quantity']) != 0)
						TripCart::add(array(
						    'id' => $item['id'],
						    'name' => $variation->product->productTranslation->title,
						    'price' => $variation->productSku->price,
						    'quantity' => $item['quantity'],
						    'attributes' => $variations
						));

					return TripCart::getContent();
					break;
				case 'merchant':
					$variations['merchant_visit_date'] = $item['attributes']['merchant_visit_date'];
					$variations['weekend_price'] = $item['attributes']['weekend_price'];

					MerchantCart::remove($item->id);// kalo ga di remove maka akan nambah terus
					if(intval($item['quantity']) != 0)
						MerchantCart::add(array(
						    'id' => $item['id'],
						    'name' => $variation->product->productTranslation->title,
						    'price' => $variation->productSku->price,
						    'quantity' => $item['quantity'],
						    'attributes' => $variations
						));

					return MerchantCart::getContent();
					break;
			}

			
		}

		
	}

	public function detail()
	{	
		$transRoute = [
            'route' => 'booking',
            'attrs' => []
        ];

		/*$details = Cart::getContent();
            $item_detail = view('emails.booking.detail', compact('details'))->render();
            dd($item_detail);*/
		$old_details = Cart::getContent(); //dd($details);
		$old_playgroundDetails = PlaygroundCart::getContent(); //dd($old_playgroundDetails);
		$old_tripDetails = TripCart::getContent();
		$old_merchantDetails = MerchantCart::getContent();
		//sesuaikan locale nya
		//PlaygroundCart::clear();
		//dd($old_merchantDetails);

		$details = $this->adaptToLocale($old_details, 'hotel'); //dd($Details);
		$playgroundDetails = $this->adaptToLocale($old_playgroundDetails, 'playground'); //dd($playgroundDetails);
		$tripDetails = $this->adaptToLocale($old_tripDetails, 'trip');
		$merchantDetails = $this->adaptToLocale($old_merchantDetails, 'merchant');
		//dd($tripDetails);
		$userMeta = []; 
		if(Auth::check()){
			$user = User::with(['userMetas'])->find(Auth::user()->id);
			$userMeta = userMeta($user->userMetas);
		}

		return view('frontend.booking.detail', compact('transRoute', 'details', 'playgroundDetails', 'tripDetails', 'merchantDetails', 'userMeta'));
	}

	public function addToCartX(){
		$post_type = Input::get('post_type');
		switch ($post_type) {
			case 'hotel':
					$this->addToCartHotel();
				break;
			
			case 'playground' :
					$this->addToCartPlayground();
				break;
		}
	}

	public function addToCart(){
		$get = Input::all();
		//dd($get);
		$rules = [];
		//check variant is valid
		
		switch ($get['post_type']) {
			case 'hotel':
					$rules = [
			            'checkin' => 'required|date|date_format:m/d/Y',
			            'checkout' => 'required|date|date_format:m/d/Y'
			        ];		
			        $setAttributeNames['checkin'] = trans('app.check_in');
        			$setAttributeNames['checkout'] = trans('app.check_out');	

        			$redirect_url = $get['hotel_url'].build_get_param(['checkin' => $get['checkin'], 'checkout' => $get['checkout']]);
				break;
			
			case 'playground' :
					$rules = [
			            'playground_visit_date' => 'required|date|date_format:m/d/Y'
			        ];

			        $setAttributeNames['playground_visit_date'] = trans('app.date');

			        $redirect_url = $get['playground_url'].build_get_param(['playground_visit_date' => $get['playground_visit_date']]);

				break;

			case 'trip' :
					
					//batas stok
					$max_p = (int)$get['max_p'];

					$get['qty'] = $get['no_of_people_t'];

					if(!TripCart::isEmpty()){
						//get content di cart
						$tripItems = TripCart::getContent();
						//penggabungan quantity dan no_of_people_t					
						$qty = $tripItems[$get['variation_id']]->quantity + $get['no_of_people_t'];
						$get['no_of_people_t'] = $qty;
					}
					

					$rules = [
			            'no_of_people_t' => "required|integer|max:$max_p"
			        ];

			        $setAttributeNames['no_of_people_t'] = trans('app.qty');
			        //dd($get);
			        $redirect_url = $get['trip_url'].build_get_param(['no_of_people_t' => $get['no_of_people_t']]);

				break;

			case 'merchant' :
					
					$max_p = (int)$get['no_of_people'];

					$get['qty'] = $get['no_of_people'];

					//get content di cart
					

					if(!MerchantCart::isEmpty()){	
						$merchantItems = MerchantCart::getContent();					
						//penggabungan quantity dan no_of_people_t
						$qty = $merchantItems[$get['variation_id']]->quantity + $get['no_of_people'];
						$get['no_of_people'] = $qty;
					}

					$rules = [
			            'no_of_people' => "required|integer|max:$max_p"
			        ];

			        $setAttributeNames['no_of_people'] = trans('app.qty');

			        $redirect_url = $get['merchant_url'];
			        //dd($redirect_url);
				break;
		}

		$v = Validator::make($get, $rules);
        $v->setAttributeNames($setAttributeNames);
        //dd($v);
        if ($v->fails())
        {	
        	if($v->errors()->first('no_of_people_t')){
        		return [ 'notification' => '<div data-alert class="alert-box alert">'.$v->errors()->first('no_of_people_t').'</div>' ];
        	}

        	if($v->errors()->first('no_of_people')){
        		return [ 'notification' => '<div data-alert class="alert-box alert">'.$v->errors()->first('no_of_people').'</div>' ];
        	}
        	/*
        	return [ 
        		'success' => 0,
        		'errors' => $v->errors()
        	];*/
            return [ 'redirect_url' => $redirect_url ];
        }

        $variation = $this->productVariation->find($get['variation_id']);

		switch ($get['post_type']) {
			case 'hotel':
        			$data_cart = [
					    'id' => $get['variation_id'],
					    'name' => $variation->product->title,
					    'price' => $variation->productSku->price,
					    'quantity' => $get['no_of_rooms'],
					    'attributes' => [
					    	'room_type' => $variation->productAttributeOption->productAttributeOptionTranslation->name,
					        'checkin' => $get['checkin'],
					    	'checkout' => $get['checkout'],
					    	'weekend_price' => $variation->productSku->weekend_price
					    ]
					];
					Cart::add($data_cart);
				break;
			
			case 'playground' :
			        $data_cart = [
					    'id' => $get['variation_id'],
					    'name' => $variation->product->title,
					    'price' => $variation->productSku->price,
					    'quantity' => $get['no_of_people'],
					    'attributes' => [
					    	'ages' => $variation->productAttributeOption->productAttributeOptionTranslation->name,
					        'playground_visit_date' => $get['playground_visit_date'],
					    	'weekend_price' => $variation->productSku->weekend_price
					    ]
					];
					PlaygroundCart::add($data_cart);
				break;

			case 'trip' :
			        $data_cart = [
					    'id' => $get['variation_id'],
					    'name' => $variation->product->title,
					    'price' => $variation->productSku->price,
					    'quantity' => $get['qty'],
					    'attributes' => [
					    	'variants' => $variation->productAttributeOption->productAttributeOptionTranslation->name,
					        'trip_visit_date' => $get['trip_visit_date'],
					        'start_date' => $get['start_date'],
					        'end_date' => $get['end_date'],
					    	'weekend_price' => $variation->productSku->weekend_price
					    ]
					];
					TripCart::add($data_cart);
				break;

			case 'merchant' :
			        $data_cart = [
					    'id' => $get['variation_id'],
					    'name' => $variation->product->title,
					    'price' => $variation->productSku->price,
					    'quantity' => $get['qty'],
					    'attributes' => [
					    	'variants' => $variation->productAttributeOption->productAttributeOptionTranslation->name,
					        'merchant_visit_date' => $get['merchant_visit_date'],
					    	'weekend_price' => $variation->productSku->weekend_price
					    ]
					];
					//dd($data_cart);
					MerchantCart::add($data_cart);
				break;
		}

		
		return [ 'notification' => '<div data-alert class="alert-box success">'.trans('app.item_cart_added').'</div>' ];
	}

	public function addToCartHotelXX(){
		$get = Input::all();

		$v = Validator::make($get, [
            'checkin' => 'required|date|date_format:m/d/Y',
            'checkout' => 'required|date|date_format:m/d/Y'
        ]);

        $setAttributeNames['checkin'] = trans('app.check_in');
        $setAttributeNames['checkout'] = trans('app.check_out');
        $v->setAttributeNames($setAttributeNames);
        if ($v->fails())
        {
            //return Redirect($get['hotel_url'].build_get_param(['checkin' => $get['checkin'], 'checkout' => $get['checkout']]))->withInput()->withErrors($v->errors());
            return [ 'redirect_url' => $get['hotel_url'].build_get_param(['checkin' => $get['checkin'], 'checkout' => $get['checkout']]) ];
        }


		//check variant is valid
		$variation = $this->productVariation->find($get['variation_id']);

		Cart::add(array(
		    'id' => $get['variation_id'],
		    'name' => $variation->product->title,
		    'price' => $variation->productSku->price,
		    'quantity' => $get['no_of_rooms'],
		    'attributes' => array(
		    	'room_type' => $variation->productAttributeOption->productAttributeOptionTranslation->name,
		        'checkin' => $get['checkin'],
		    	'checkout' => $get['checkout'],
		    	'weekend_price' => $variation->productSku->weekend_price
		    )
		));

		return [ 'notification' => '<div data-alert class="alert-box success">'.trans('app.item_cart_added').'</div>' ];
	}

	public function updateCart(){
		$get = Input::all(); //dd($get);
		$items = Cart::getContent();
		foreach($items as $idx => $item){
			Cart::remove($item->id);
			if(isset($get['no_of_rooms']) && intval($get['no_of_rooms'][$idx]) != 0)
				Cart::add(array(
				    'id' => $item->id,
				    'name' => $item->name,
				    'price' => $item->price,
				    'quantity' => $get['no_of_rooms'][$idx],
				    'attributes' => array(
				    	'room_type' => $item->attributes->room_type,
				        'checkin' => $item->attributes->checkin,
				    	'checkout' => $item->attributes->checkout,
				    	'weekend_price' => $item->attributes->weekend_price
				    )
				));
		}
		
		$playgroundItems = PlaygroundCart::getContent();
		foreach($playgroundItems as $idx => $item){
			PlaygroundCart::remove($item->id);
			if( isset($get['no_of_people']) && intval($get['no_of_people'][$idx]) != 0)
			PlaygroundCart::add(array(
			    'id' => $item->id,
			    'name' => $item->name,
			    'price' => $item->price,
			    'quantity' => $get['no_of_people'][$idx],
			    'attributes' => array(
			    	'ages' => $item->attributes->ages,
			        'playground_visit_date' => $item->attributes->playground_visit_date,
			    	'weekend_price' => $item->attributes->weekend_price
			    )
			));
		}

		$tripItems = TripCart::getContent();
		//dd($tripItems);
		foreach($tripItems as $idx => $item){			
			
			$variant = $this->productVariation->find($item->id);//ProductVariation::with(['product', 'productSku', 'productAttributeOption.productAttributeOptionTranslation'])->findOrFail($item->id);
			
			$max_p = $variant->productSku->qty;

			$rules = [
			    'no_of_people_t.'.$idx => "required|integer|max:$max_p"
			        ];

			$setAttributeNames['no_of_people_t.'.$idx] = trans('app.qty');

			//dd($item);
			//dd($get);//$variant->productSku->qty);

			$v = Validator::make($get, $rules);
			//dd($v);
	        $v->setAttributeNames($setAttributeNames);
	        if ($v->fails())
	        {
	        	Notification::container('frontend')->error( 'Stock not allow' );
	            return Redirect::route(getLang().'.booking')->withInput()->withErrors($v->errors());
	        }

			TripCart::remove($item->id);
			if( isset($get['no_of_people_t']) && intval($get['no_of_people_t'][$idx]) != 0)
			TripCart::add(array(
			    'id' => $item->id,
			    'name' => $item->name,
			    'price' => $item->price,
			    'quantity' => $get['no_of_people_t'][$idx],
			    'attributes' => array(
			    	'variants' => $item->attributes->variants,
			        'trip_visit_date' => $item->attributes->trip_visit_date,
			        'start_date' => $item->attributes->start_date,
					'end_date' => $item->attributes->end_date,
			    	'weekend_price' => $item->attributes->weekend_price
			    )
			));
		}

		$merchantItems = MerchantCart::getContent();
		foreach($merchantItems as $idx => $item){
			$variant = $this->productVariation->find($item->id);//ProductVariation::with(['product', 'productSku', 'productAttributeOption.productAttributeOptionTranslation'])->findOrFail($item->id);
			
			$max_p = $variant->productSku->qty;

			$rules = [
			    'no_of_people.'.$idx => "required|integer|max:$max_p"
			        ];

			$setAttributeNames['no_of_people.'.$idx] = trans('app.qty');

			//dd($item);
			//dd($get);//$variant->productSku->qty);

			$v = Validator::make($get, $rules);
			//dd($v);
	        $v->setAttributeNames($setAttributeNames);
	        if ($v->fails())
	        {
	        	Notification::container('frontend')->error( 'Stock not allow' );
	            return Redirect::route(getLang().'.booking')->withInput()->withErrors($v->errors());
	        }

			MerchantCart::remove($item->id);
			if( isset($get['no_of_people']) && intval($get['no_of_people'][$idx]) != 0)
			MerchantCart::add(array(
			    'id' => $item->id,
			    'name' => $item->name,
			    'price' => $item->price,
			    'quantity' => $get['no_of_people'][$idx],
			    'attributes' => array(
			    	'variants' => $item->attributes->variants,
			        'merchant_visit_date' => $item->attributes->merchant_visit_date,
			    	'weekend_price' => $item->attributes->weekend_price
			    )
			));
		}
		//dd(PlaygroundCart::getContent());
		/*Cart::update($get['sku_id'], array(
		  'quantity' => $get['no_of_rooms']
		));*/
		Notification::container('frontend')->success( trans('app.item_cart_updated') );
		return Redirect::route(getLang().'.booking');
	}

	/*public function order(){
		$get = Input::all();
		$items = Cart::getContent();
	}*/

	public function myBooking(){
		$transRoute = [
            'route' => 'mybooking',
            'attrs' => []
        ];

		$bookings = $this->booking->findByUserId();
		return view('frontend.booking.mybooking', compact('transRoute', 'bookings'));
	}

	public function checkReservation(){
		$post = Input::all();
		return view('frontend.booking.checkReservation');
	}

	public function doCheckReservation(){
		$booking_no = Input::get('booking_no');
		$email = Input::get('email');

		$booking = $this->booking->findByEmailAndBookingNo($email, $booking_no);
        //dd($booking);
		return view('frontend.booking.checkReservation', compact('booking'));
	}

	public function checkConfirmation(){
		//$first_name = 'David';
		//setcookie('first_name',$first_name,time() + (86400 * 7)); // 86400 = 1 day

		//echo 'Hello '.($_COOKIE['first_name']!='' ? $_COOKIE['first_name'] : 'Guest'); // Hello David!
		$transRoute = [
            'route' => 'check_confirmation',
            'attrs' => []
        ];
		//$post = Input::all();
		return view('frontend.booking.checkConfirmation', compact('transRoute'));
	}

	public function doCheckConfirmation(){
		$v = Validator::make(Input::all(), [
            'email' => 'required|email',
            'booking_no' => 'required'
        ]);

        $setAttributeNames['email'] = trans('app.email');
        $setAttributeNames['booking_no'] = trans('app.booking_no');
        $v->setAttributeNames($setAttributeNames);
        if ($v->fails())
        { 
            return Redirect::Route(getLang().'.checkconfirmation')->withInput()->withErrors($v->errors());
        }


		$booking_no = Input::get('booking_no');
		$email = Input::get('email');

		$booking = $this->booking->findByEmailAndBookingNo($email, $booking_no); //dd($booking);
        if(!is_null($booking)){
        	if($booking->status_id == 1){
        		Notification::container('frontend')->error( trans('app.booking_not_confirmed') );
        		return Redirect::route(getLang().'.checkconfirmation')->withInput();
        	}
        	if($booking->status_id == 3){
        		Notification::container('frontend')->error( trans('app.payment_has_been_confirmed') );
        		return Redirect::route(getLang().'.checkconfirmation')->withInput();
        	}
        	/*$grand_total = 0;
			foreach($booking->bookingDetails as $detail){ 
				$nights = carbon_diff($detail->check_out, $detail->check_in);
				$total = $nights * $detail->price * $detail->no_of_rooms;
				$grand_total += $total;
			}
			return view('frontend.booking.confirmation', compact('booking', 'grand_total'));*/
			return Redirect::route(getLang().'.confirmation', [ 'email' => $email, 'booking_no' => $booking_no ]);
		}else{
	    	Notification::container('frontend')->error( trans('app.booking_not_found') );
	    	return Redirect::route(getLang().'.checkconfirmation')->withInput();
	    }
	}

	public function confirmation(){
		$booking_no = Input::get('booking_no');
		$email = Input::get('email');

		$booking = $this->booking->findByEmailAndBookingNo($email, $booking_no); //dd($booking);
        if(!is_null($booking)){
        	if($booking->status_id == 1){
        		Notification::container('frontend')->error( trans('app.booking_not_confirmed') );
        		return Redirect::route(getLang().'.checkconfirmation')->withInput();
        	}
        	if($booking->status_id == 3){
        		Notification::container('frontend')->error( trans('app.payment_has_been_confirmed') );
        		return Redirect::route(getLang().'.checkconfirmation')->withInput();
        	}

        	
        	$grand_total = 0;
			foreach($booking->bookingDetails as $detail){ 
				$nights = carbon_diff($detail->check_out, $detail->check_in);
				$total = $nights * $detail->price * $detail->no_of_rooms;
				$grand_total += $total;
			}

			$bank_account_option[''] = '';
			$bank_account_option += BankAccount::lists('name', 'id');
			$customer_bank_account_option[''] = '';
			$customer_bank_account_option += CustomerBankAccount::lists('name', 'id');
        	return view('frontend.booking.confirmation', compact('booking', 'grand_total', 'bank_account_option', 'customer_bank_account_option'));
        }else{
        	Notification::container('frontend')->error( trans('app.booking_not_found') );
	    	return Redirect::route(getLang().'.checkconfirmation')->withInput();
        }
	}

	public function doConfirmation(){
		$email = Input::get('email');
        $booking_no = Input::get('booking_no');
		//dd(paymentInfoHTML(Input::all()));
		$v = Validator::make(Input::all(), [
            'email' => 'required|email',
            'booking_no' => 'required',
            'amount' => 'required',
            'account_name' => 'required',
            'account_no' => 'required',
            'bank_account' => 'required',
            'customer_bank_account' => 'required'
        ]);
		
        $setAttributeNames['email'] = trans('app.email');
        $setAttributeNames['booking_no'] = trans('app.booking_no');
        $setAttributeNames['amount'] = trans('app.amount');
        $setAttributeNames['account_name'] = trans('app.account_name');
        $setAttributeNames['account_no'] = trans('app.account_no');
        $setAttributeNames['bank_account'] = trans('app.bank_account');
        $setAttributeNames['customer_bank_account'] = trans('app.customer_bank_account');
        $v->setAttributeNames($setAttributeNames);
        if ($v->fails())
        { 
            return Redirect::Route(getLang().'.confirmation', [ 'email' => $email, 'booking_no' => $booking_no ])->withInput()->withErrors($v->errors());
        }

        
        $booking = $this->booking->findByEmailAndBookingNo($email, $booking_no);
        if(!is_null($booking)){
        	if($booking->status_id == 1){
        		Notification::container('frontend')->error( trans('app.booking_not_confirmed') );
        		return Redirect::route(getLang().'.confirmation')->withInput();
        	}
        	if($booking->status_id == 3){
        		Notification::container('frontend')->error( trans('app.payment_has_been_confirmed') );
        		return Redirect::route(getLang().'.confirmation')->withInput();
        	}

	        $this->bookingConfirmation->create([
	        	'email' => $email,
	        	'booking_id' => $booking->id,
	        	'amount' => Input::get('amount'),
	            'account_name' => Input::get('account_name'),
	            'account_no' => Input::get('account_no'),
	            'bank_account_id' => Input::get('bank_account'),
	            'customer_bank_account_id' => Input::get('customer_bank_account')
	        	]);

	        //$update_booking = $this->booking->find($booking->id);
	        //$update_booking->status_id = 3;
			//$update_booking->save();

			//$booking = $this->booking->findByEmailAndBookingID('metalpis@gmail.com', 'xxxxxxxxxx');
			$details_raw = $booking->bookingDetails;
			$details = detailsCartFormated($details_raw);

			$playgroundDetails_raw = $booking->playgroundBookingDetails;
			$playgroundDetails = detailsCartFormated($playgroundDetails_raw);
	        $cart_items = view('emails.booking.detail', compact('details', 'playgroundDetails'))->render();

			sendEmailWithTemplate([
	        	'email_template_module_id' => 4,//Booking : paid
	        	'to' => $booking->user->email,
	        	'replace_vars' => [
	        		'{name}' => $booking->user->name,
	        		'{booking_no}' => $booking->booking_no,
	        		'{details}' => $cart_items,
	        		'{payment_info}' => paymentInfoHTML(Input::all())
	        	]
	        ]);


	        /*

	        if($attributes['status'] == 2){ //confirmed
	            $confirmedDetails_raw = $this->booking->bookingConfirmedDetails;
	            $details = detailsCartFormated($confirmedDetails_raw, true);

	            $playgroundConfirmedDetails_raw = $this->booking->playgroundBookingConfirmedDetails;
	            $playgroundDetails = detailsCartFormated($playgroundConfirmedDetails_raw, true);
	            $cart_items = view('emails.booking.detail', compact('details','playgroundDetails'))->render();
	            //dd($cart_items);
	            sendEmailWithTemplate([
	                'email_template_module_id' => 3,
	                'to' => $this->booking->user->email,
	                'replace_vars' => [
	                    '{name}' => $this->booking->user->name,
	                    '{booking_no}' => $attributes['booking_no'],
	                    '{details}' => $cart_items,
	                    //'{payment_info}' => paymentInfoHTML(Input::all())
	                ]
	            ]);
	        }
	        */

	        Notification::container('frontend')->success( trans('app.booking_paid') );
	    }else{ 
	    	Notification::container('frontend')->error( trans('app.booking_not_found') );
	    	return Redirect::route(getLang().'.confirmation')->withInput();
	    }
        
		return Redirect::route(getLang().'.checkconfirmation');
	}

}
