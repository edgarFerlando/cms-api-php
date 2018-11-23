<?php namespace App\Http\Controllers\API;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use Carbon\Carbon;

use App\Models\PaymentTransaction;
use App\Models\PaymentInsert;
use App\Models\PaymentStatus;
use App\User;

class PaymentController extends Controller {

	// BCA Virtual Account Credential
	protected $channelId; 	// Your Channel ID
	protected $secretKey; 	// Your secretKey
	protected $compCode; 	// Your Company Code
	protected $currency;
	protected $length;
	protected $url_sprint;

	protected $channelIdCreditCard;
	protected $serviceCodeCreditCard;
	protected $url_sprintCreditCard;

	private $paymentTransaction;
	private $paymentInsert;
	private $paymentStatus;

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */

	public function __construct(
		PaymentTransaction $paymentTransaction,
		PaymentInsert $paymentInsert,
		PaymentStatus $paymentStatus
	)
	{
		$this->secretKey = "62fe6e0abbd7c955a1fbddd84c511bc0"; 					// Your secretKey
		$this->compCode = "63111"; 												// Your Company Code
		$this->currency = "IDR";
		$this->length = 16;														// Dari sprint

		$this->channelId = "FUNDTBVA01"; 										// Your Channel ID
		$this->url_sprint = "https://simpg.sprintasia.net/PaymentRegister"; 	// Development
		// "https://simpg.sprintasia.net/PaymentRegister"; 						// Development
		//$URL_insert = "https://pay.sprintasia.net:8899/PaymentRegister"; 		// Staging
		//$URL_insert = "https://pay.sprintasia.net/PaymentRegister"; 			// Production

		$this->channelIdCreditCard = "FUNDTDO01";
		$this->serviceCodeCreditCard = "2001";
		$this->url_sprintCreditCard = "https://securetest.bayarind.id/PaymentRegister"; // Development
		// "https://securetest.bayarind.id/PaymentRegister"; 							// Development
		// Url insert Dev = https://securetest.bayarind.id/PaymentRegister				// Development
		// Url insert Prod = https://secure.bayarind.id/PaymentRegister					// Production



		$this->paymentTransaction = $paymentTransaction;
		$this->paymentInsert = $paymentInsert;
		$this->paymentStatus = $paymentStatus;
	}

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
	public function store(Request $request)
	{
		$user_id = $request->input('user_id');
		// $transaction_date = $request->input('transaction_date');
        // $transaction_expire = $request->input('transaction_expire');
        $transaction_amount = $request->input('transaction_amount');
        $transaction_fee = $request->input('transaction_fee');
        $description = $request->input('description');
        $additional_data = $request->input('additional_data');
        $customer_name = $request->input('customer_name');
        // $customer_account = $request->input('customer_account');

        // Parameter opsional (Jika menggunakan pembayaran Creditcard)
        $customer_email = $request->has('customer_email')?$request->input('customer_email'):'';
        $customer_phone = $request->has('customer_phone')?$request->input('customer_phone'):'';
        $customer_bill_address = $request->has('customer_bill_address')?$request->input('customer_bill_address'):'';
        $customer_bill_city = $request->has('customer_bill_city')?$request->input('customer_bill_city'):'';
        $customer_bill_state = $request->has('customer_bill_state')?$request->input('customer_bill_state'):'';
        $customer_bill_country = $request->has('customer_bill_country')?$request->input('customer_bill_country'):'';
        $customer_bill_zip_code = $request->has('customer_bill_zip_code')?$request->input('customer_bill_zip_code'):'';
        $type = $request->has('type')?$request->input('type'):'';


        $data = $this->paymentTransaction->create([
			'user_id' => $user_id,
			'transaction_date' => Carbon::now(),
	        'transaction_expire' => Carbon::now()->addDays(2),
	        'transaction_amount' => $transaction_amount,
	        'transaction_fee' => $transaction_fee,
	        'currency' => "IDR",
	        'description' => $description,
	        'additional_data' => $additional_data,
	        'customer_name' => $customer_name,
	        // 'customer_account' => $customer_account,

	        'customer_email' => $customer_email,
			'customer_phone' => $customer_phone,
			'customer_bill_address' => $customer_bill_address,
			'customer_bill_city' => $customer_bill_city,
			'customer_bill_state' => $customer_bill_state,
			'customer_bill_country' => $customer_bill_country,
			'customer_bill_zip_code' => $customer_bill_zip_code,
			'type' => $type,

	        'created_at' => Carbon::now()
		]);


		$id = $data->id;

		/* -----------------------------
		 * Update...
		 */

		$payt = PaymentTransaction::where('id','=',$id)->first();
		
		// generate customerAccount
		$length = $this->length - intval(strlen($this->compCode));	
		$customerAccount = $this->compCode.sprintf('%0'.$length.'d',$id);

        $t_attributes = [
            'customer_account'	=> $customerAccount
        ];

        $payt->update($t_attributes);

        /*
		 * -----------------------------
		 */



        /*
		 * -----------------------------
		 * Melakukan insert ke SPRINT
		 *
         */

		// $hasil = $this->storeBcaVa($id);

		$hasil = array (
			"ID" => $id,
			"Type"	=> $type
		);

        return response()->json([
			'result' => 'success',
			'data' => $hasil
		]);

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


	public function storeBcaVa($id) {


		// dd($this->url_sprint);


		$payt = PaymentTransaction::where('id','=',$id)->first();

		/**
		 | ------------------------------------------
		 | Parameter yang dibutuhkan
		 | ------------------------------------------
		 | Get Transaction from database 
		    1. transactionNo      => Alphanumeric (18)
		    2. transactionDate    => DATETIME (yyyy-mm-dd)
		    2. transactionExpire  => DATETIME (yyyy-mm-dd)
		    3. transactionAmount  => Numeric (10)
		    4. currency           => IDR
		    5. description        => TEXT (60)
		    6. additionalData     => TEXT (100)
		    7. customerAccount    => Numeric (23)
		    8. CustomerName       => TEST (30)
		 |
		 */

		$data_from_database = array(
			"indonesian" => "-",
			"english" => "-",

			"transactionNo" => $payt['id'],
			"transactionAmount" => $payt['transaction_amount'],
			// "currency" => "IDR",
			"transactionDate" => $payt['transaction_date'],
			"transactionExpire" => $payt['transaction_expire'],
			"additionalData" => $payt['additional_data'],
			"customerName" => $payt['customer_name'],
			"transactionFee" => $payt['transaction_fee']
		);

		$itemDetail[] = array(
	        "freeTexts" => array(
	            "indonesian"    => $data_from_database['indonesian'],
	            "english"       => $data_from_database['english'],
	        ),
	    );

		// Generate authCode
		$authCode	= hash("sha256", $data_from_database['transactionNo'].$data_from_database['transactionAmount'].$this->channelId.$this->secretKey);

		// generate customerAccount
		$length = $this->length - intval(strlen($this->compCode));	
		$customerAccount = $this->compCode.sprintf('%0'.$length.'d',$data_from_database['transactionNo']);

		// Prepare insert transaction data
		$data = array(
		    "channelId"         => $this->channelId,
		    "currency"          => $this->currency,
		    "transactionNo"     => $data_from_database['transactionNo'],
		    "transactionAmount" => $data_from_database['transactionAmount'],
		    "transactionDate"   => $data_from_database['transactionDate'],
		    "transactionExpire" => $data_from_database['transactionExpire'],
		    "description"       => "Order".$data_from_database['transactionNo'],
		    "itemDetail"        => $itemDetail,
		    "authCode"          => $authCode,
		    "additionalData"    => $data_from_database['additionalData'],
		    "customerAccount"   => $customerAccount,
		    "customerName"      => $data_from_database['customerName'],
		    "transactionFee"	=> $data_from_database['transactionFee']
		);

		if (!function_exists('curl_init')){   // Check existing cURL
		    die('Sorry cURL is not installed!');
		}

		$OPT        = http_build_query($data);

		// cURL setting
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->url_sprint);     // provide the URL to use
		curl_setopt($ch, CURLOPT_POSTFIELDS, $OPT);     // specify data to POST to server
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);    // FALSE, blindly accept any server certificate, without doing any verification
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);    // FALSE, not verify the certificate's name against host
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);    // TRUE to return the transfer as a string of the return value of curl_exec()
		curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);    // TRUE to return the raw output when CURLOPT_RETURNTRANSFER is used
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);   // The number of seconds to wait while trying to connect
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);          // The maximum number of seconds to allow cURL functions to execute

		// cURL execute
		
		$raw_response = curl_exec($ch);
		if(curl_error($ch)){
		    die("CURL Error ::". curl_error($ch));
		}
		curl_close($ch);

		// Decode json to array
		$response   = json_decode($raw_response,TRUE);



		// Save $response['insertStatus'], $response['insertMessage'], response['insertId'] to merchant table
		/**
		 | ---------------------------------------------
		 | Save
		 |
		 */

		 $insert = $this->paymentInsert->create([
		
	        'transaction_id' 		=> $id,					// transactionNo
	        'currency'		 		=> $this->currency,
	        'transaction_amount' 	=> $data_from_database['transactionAmount'],
	        'transaction_date' 		=> $data_from_database['transactionDate'],
	        'insert_status' 		=> $response['insertStatus'],
	        'insert_message' 		=> $response['insertMessage'],
	        'insert_id' 			=> $response['insertId']!=''?$response['insertId']:0,
	        'status' 				=> '',
	        'created_at' 			=> Carbon::now()
		]);

		/*
 		 * ---------------------------------------------
		 */


		/**
		 | ---------------------------------------------
		 | Send Email
		 |
		 */

		 $user = User::where('id', '=', $payt['user_id'])->first();

		 \Mail::send('emails.payment.insert', $data, function ($message) use ($user) {
            $message->from('fundtastican@gmail.com', 'FUNDtastic');

            $message->to($user->email, $user->name)->subject('Payment');
         });
		
		/*
 		 * ---------------------------------------------
		 */


		if( $response['insertStatus'] == "00") { // Validate insertStatus
		    // Redirect to home

			// Set header response
			// header('Content-Type: application/json');

			// Print response
			// echo json_encode( $response );

			return $response;

		    exit;
		} else {
		    // Print error message
		    die($response['insertMessage']);
		}
	}




	public function verifyBcaVa(Request $request) {
		
		/*
		  Title : PHP Example for BCA Virtual Account Payment Flag Process
		  Ver   : 1.0
		  Desc  : 
		  This file is a simple example of BCA Virtual Account Payment Flag.
		  The main role is validating conditions which are:
		    1.  Validate Transaction. Check whether Transaction is exist in database
		    2.  Validate Channel Id. Check whether Channel Id sent by Sprint is matched with our credential
		    3.  Validate Currency. Check wheter Currency sent by Sprint is IDR
		    4.  Validate Transaction Number. Check wheter Transaction Number sent by Sprint is exists in database
		    5.  Validate Transaction Amount. Check wheter Transaction Amount sent by Sprint is matched with transaction saved in database
		    6.  Validate Transaction Status. Check wheter Transaction Amount sent by Sprint is 00.
		    7.  Validate FlagType. Check whether flagType is matched with 11 or 12 or 13
		    8.  Validate Insert ID. Check wheter Insert ID sent by Sprint is matched with transaction saved in database
		    9. Validate authCode. Check wheter authCode sent by Sprint is matched with merchant authCode.
		    10. Validate Transaction Status. Check whether the transaction was cancelled.
		    11.  Validate Transaction Status. Check whether the transaction was expired .
		    12.  Validate Transaction Status. Check whether the transaction was already paid.
		*/


		/**
		 * CEk apakah no transaksi benar
		 */

		if (is_numeric($request->input('transactionNo'))) {
			
		} else {
			return response()->json([
			    'channelId' => $request->input('channelId'),
			    'currency' => $request->input('currency'),
			    'paymentStatus' => '01',
			    'paymentMessage' => 'Invalid Transaction NO',
			    'flagType' => $request->input('flagType'),
			    'paymentReffId' => $request->input('paymentReffId')
			]);
			exit();
		}

		$transaction_data = PaymentInsert::where('transaction_id', '=', $request->input('transactionNo'))->first();

		if ($transaction_data == null) {
			return response()->json([
				'channelId' => $request->input('channelId'),
				'currency' => $request->input('currency'),
				'paymentStatus' => '01',
				'paymentMessage' => 'Invalid Transaction NO',
				'flagType' => $request->input('flagType'),
				'paymentReffId' => $request->input('paymentReffId')
			]);
			exit();
		}



		// Cek untuk invalid customerAccountnya
		$payt = PaymentTransaction::where('id', '=', $request->input('transactionNo'))->first();

		if($payt['customer_account'] != $request->input('customerAccount')) {
			return response()->json([
				'channelId' => $request->input('channelId'),
				'currency' => $request->input('currency'),
				'paymentStatus' => '01',
				'paymentMessage' => 'Invalid customerAccount',
				'flagType' => $request->input('flagType'),
				'paymentReffId' => $request->input('paymentReffId')
			]);
			exit();
		}

		


		// Parsing request data from Sprint
		$data_sprint = array(
		    "channelId"           => $request->input('channelId'),
		    "currency"            => $request->input('currency'),
		    "transactionNo"       => $request->input('transactionNo'),
		    "transactionAmount"   => $request->input('transactionAmount'),
		    "transactionDate"     => $request->input('transactionDate'),
		    "transactionExpired"  => $request->input('transactionExpired'),
		    "transactionStatus"   => $request->input('transactionStatus'),
		    "transactionMessage"  => $request->input('transactionMessage'),
		    "flagType"            => $request->input('flagType'),
		    "insertId"            => $request->input('insertId'),
		    "paymentReffId"       => $request->input('paymentReffId'),
		    "authCode"            => $request->input('authCode'),
		    "additionalData"      => $request->input('additionalData'),
		);

		// Prepare response data
		$response   = array(
		    "channelId"           => $request->input('channelId'),      // Channel ID sent by Sprint
		    "currency"            => $request->input('currency'),       // Currency sent by Sprint (IDR)
		    "paymentStatus"       => "",                          // Payment Status ( 00 => Success , 01,03 => Failed , 02 => isPaid , 04 => isExpired , 05 => isCancelled )
		    "paymentMessage"      => "",                          // Payment Message
		    "flagType"            => $request->input('flagType'),       // Flag Type sent by Sprint
		    "paymentReffId"       => $request->input('paymentReffId'),  // Payment Referrence ID sent by Sprint
		);

		// Generate authCode
		$authCode = hash("sha256", $transaction_data['transaction_id'].$transaction_data['transaction_amount'].$this->channelId.$data_sprint['transactionStatus'].$transaction_data['insert_id'].$this->secretKey);

		/*
		Validation process
		*/

		// Validate Transaction
		if( !$transaction_data ){
		    $response['paymentStatus']  = "01";
		    $response['paymentMessage'] = "Invalid Transaction";
		}

		// Validate Channel Id
		elseif( $data_sprint['channelId'] != $this->channelId ){
		    $response['paymentStatus']  = "01";
		    $response['paymentMessage'] = "Invalid Channel ID";
		}

		// Validate Currency
		elseif( $data_sprint['currency'] != "IDR" ){
		    $response['paymentStatus']  = "01";
		    $response['paymentMessage'] = "Invalid Currency";
		}

		// Validate Transaction No
		elseif( $data_sprint['transactionNo'] != $transaction_data['transaction_id'] ){
		    $response['paymentStatus']  = "01";
		    $response['paymentMessage'] = "Invalid transactionNo Currency";
		}

		// 4.  Validate Transaction Amount
		elseif( $data_sprint['transactionAmount'] != $transaction_data['transaction_amount'] ){
		    $response['paymentStatus']  = "01";
		    $response['paymentMessage'] = "Invalid Transaction Amount";
		}

		// Validate Transaction Status
		elseif( $data_sprint['transactionStatus'] != "00" ){
		    $response['paymentStatus']  = "01";
		    $response['paymentMessage'] = "Invalid Transaction Status";
		}

		// Validate FlagType
		elseif( $request->input('flagType') != "11" && $request->input('flagType') != "12" && $request->input('flagType') != "13" ){
		    $response['paymentStatus']  = "01";
		    $response['paymentMessage'] = "Invalid Flagtype";
		}

		// Validate Insert ID
		elseif( $data_sprint['insertId'] != $transaction_data['insert_id'] ){
		    $response['paymentStatus']  = "01";
		    $response['paymentMessage'] = "Invalid Transaction Status";
		}

		// Validate authCode
		elseif( $data_sprint['authCode'] != $authCode ) {
		    $response['paymentStatus'] = "01";
		    $response['paymentMessage'] = "Invalid authCode";
		}

		// Validate Transaction Status => CANCELLED
		elseif( $transaction_data['status'] == "CANCELLED" ){
		    $response['paymentStatus']  = "05";
		    $response['paymentMessage'] = "Transaction has been cancelled";
		}

		// Validate Transaction Status => EXPIRED
		elseif( $transaction_data['status'] == "EXPIRED" ){
		    $response['paymentStatus']  = "04";
		    $response['paymentMessage'] = "Transaction has been expired";
		}

		// Validate Transaction Status => PAID
		elseif( $transaction_data['status'] == "PAID" ){
		    $response['paymentStatus']  = "02";
		    $response['paymentMessage'] = "Transaction has been paid";
		}

		// Success
		else{
		    $response['paymentStatus']  = "00";
		    $response['paymentMessage'] = "Success";
		    // Update transaction status
		}

		// Save $response['paymentStatus'], $response['paymentMessage'], $response['flagType'], $response['transactionStatus'], $response['transactionMessage'], $response['paymentReffId'] to merchant table


		/**
		 | ---------------------------------------------
		 | Save
		 |
		 */

		 $data = $this->paymentStatus->create([
	        'transaction_id'		=> $request->input('transactionNo'),
			'insert_id'				=> $request->input('insertId'),
			'payment_status'		=> $response['paymentStatus'],
			'payment_message'		=> $response['paymentMessage'],
			'flag_type'				=> $response['flagType'],
			'transaction_status'	=> $request->input('transactionStatus'),
			'transaction_message'	=> $request->input('transactionMessage'),
			'payment_reff_id'		=> $response['paymentReffId'],
			'created_at' 			=> Carbon::now()
		]);


		if($data_sprint['transactionStatus'] == "00" && $response['paymentStatus'] == "00") {

			$payment_insert = PaymentInsert::where('transaction_id', '=', $request->input('transactionNo'))->first();
			 	
	        $t_attributes = [
	            'status'	=> 'PAID',
	        ];

	        $payment_insert->update($t_attributes);
    	}


		/*
 		 * ---------------------------------------------
		 */


		/**
		 | ---------------------------------------------
		 | Send Email
		 |
		 */

		 // select PaymentTransaction By ID

		 // $payt = PaymentTransaction::where('id', '=', $request->input('transactionNo'))->first();

		 // select user

		 $user = User::where('id', '=', $payt['user_id'])->first();

		 \Mail::send('emails.payment.verify', $data_sprint, function ($message) use ($user) {
            $message->from('fundtastican@gmail.com', 'FUNDtastic');

            $message->to($user->email, $user->name)->subject('Payment');
         });
		
		/*
 		 * ---------------------------------------------
		 */



		// Set header response
		// header('Content-Type: application/json');

		// Print response
		// echo json_encode( $response );

		return $response;

	}













	/**
	 | ---------------------------------------------
	 | Credit Card
	 |
	 */

	public function storeCreditcard($id) {

		$payt = PaymentTransaction::where('id','=',$id)->first();

		/*
		Get Transaction from database 
			1. transactionNo        	=> Alphanumeric (18)
			3. transactionDate    		=> DATETIME (yyyy-mm-dd)
			4. transactionExpire  		=> DATETIME (yyyy-mm-dd)
			5. transactionAmount  		=> Numeric (10)
			6. currency           		=> IDR
			7. description        		=> TEXT (60)
			8. customerName       		=> TEXT (50)
			9. customerEmail      		=> Email
			10. customerPhone      		=> Numeric (20)
			11. customerBillAddress		=> TEXT (200)
			12. customerBillCity		=> TEXT (100)
			13. customerBillState		=> TEXT (100)
			14. customerBillCountry		=> Alphanumeric (2)
			15. customerBillZipCode 	=> Alphanumeric (6)
			16. additionalData	   		=> TEXT (100)
		*/
		
		$insert_data = array (
			"transactionNo" => $payt['id'],
			"transactionAmount" => $payt['transaction_amount'],
			"transactionDate" => $payt['transaction_date'],
			"transactionExpire" => $payt['transaction_expire'],
			"customerName" => $payt['customer_name'],
			"customerEmail" => $payt['customer_email'],
			"customerPhone" => $payt['customer_phone'],
			"customerBillAddress" => $payt['customer_bill_address'],
			"customerBillCity" => $payt['customer_bill_city'],
			"customerBillState" => $payt['customer_bill_state'],
			"customerBillCountry" => $payt['customer_bill_country'],
			"customerBillZipCode" => $payt['customer_bill_zip_code'],
			"additionalData" => $payt['additional_data']
		);


		// Generate authCode
		$authCode	= hash("sha256", $insert_data['transactionNo'].$insert_data['transactionAmount'].$this->channelIdCreditCard.$this->secretKey);

		// Prepare insert transaction data
		$data = array(
		"channelId"         	=> $this->channelIdCreditCard,
		"currency"          	=> $this->currency,
		"serviceCode"			=> $this->serviceCodeCreditCard,
		"transactionNo"     	=> $insert_data['transactionNo'],
		"transactionAmount" 	=> $insert_data['transactionAmount'],
		"transactionDate"   	=> $insert_data['transactionDate'],
		"transactionExpire"  	=> $insert_data['transactionExpire'],
		"callbackURL"       	=> \URL::to('/')."/payment/callback_creditcard/".$id, // Fill with your callback URL
		"description"       	=> "Order".$insert_data['transactionNo'],
		"customerName" 			=> $insert_data['customerName'],
		"customerEmail" 		=> $insert_data['customerEmail'],
		"customerPhone" 		=> $insert_data['customerPhone'],
		"customerBillAddress"   => $insert_data['customerBillAddress'],
		"customerBillCity" 		=> $insert_data['customerBillCity'],
		"customerBillState" 	=> $insert_data['customerBillState'],
		"customerBillCountry"   => $insert_data['customerBillCountry'],
		"customerBillZipCode"   => $insert_data['customerBillZipCode'],
		"authCode"            	=> $authCode, 
		"additionalData"      	=> $insert_data['additionalData'],
		);

		//


		if (!function_exists('curl_init')){   // Check existing cURL
		  die('Sorry cURL is not installed!');
		}

		$OPT        = http_build_query($data);

		// cURL setting

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->url_sprintCreditCard);     // provide the URL to use
		curl_setopt($ch, CURLOPT_POSTFIELDS, $OPT);     // specify data to POST to server
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);    // FALSE, blindly accept any server certificate, without doing any verification
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);    // FALSE, not verify the certificate's name against host
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);    // TRUE to return the transfer as a string of the return value of curl_exec()
		curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);    // TRUE to return the raw output when CURLOPT_RETURNTRANSFER is used
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);   // The number of seconds to wait while trying to connect
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);          // The maximum number of seconds to allow cURL functions to execute

		// cURL execute
		$raw_response = curl_exec($ch);
		if(curl_error($ch)){
		  die("CURL Error ::". curl_error($ch));
		}
		curl_close($ch);

		// Decode json to array
		$response   = json_decode($raw_response,TRUE);

		// Save $response['insertStatus'], $response['insertMessage'], response['insertId'] to merchant table

		/**
		 | ---------------------------------------------
		 | Save
		 |
		 */

		 // dd($response);

		 $insert = $this->paymentInsert->create([
		
	        'transaction_id' 		=> $id,					// transactionNo
	        'currency'		 		=> $this->currency,
	        'transaction_amount' 	=> $insert_data['transactionAmount'],
	        'transaction_date' 		=> $insert_data['transactionDate'],
	        'insert_status' 		=> $response['insertStatus'],
	        'insert_message' 		=> $response['insertMessage'],
	        'insert_id' 			=> $response['insertId']!=''?$response['insertId']:0,
	        'status' 				=> '',
	        'created_at' 			=> Carbon::now()
		]);

		/*
 		 * ---------------------------------------------
		 */


		if( $response['insertStatus'] == "00" AND $response['redirectURL'] != "" ) { // Validate insertStatus and redirectURL
		  	// Redirect process
			// header('Location: '.$response['redirectURL']);

        	return response()->json([
				'result' => 'success',
				'URL' => $response['redirectURL']
			]);

			//  exit;
		} else {
		  	// Print error message
		  	return response()->json([
				'result' => 'error',
				'message' => $response['insertMessage']
			]);
		}
	}



	public function verifyCreditcard(Request $request) 
	{

	/*
	  Title : PHP Example for Credit Card Payment Flag Process
	  Ver   : 1.0
	  Desc  : 
	  This file is a simple example of Credit Card Payment Flag.
	  The main role is validating conditions which are:
	    1.  Validate Transaction. Check whether Transaction is exist in database
	    2.  Validate Channel Id. Check whether Channel Id sent by Sprint is matched with our credential
	    3.  Validate Currency. Check wheter Currency sent by Sprint is IDR
	    4.  Validate Transaction Number. Check wheter Transaction Number sent by Sprint is exists in database
	    5.  Validate Transaction Amount. Check wheter Transaction Amount sent by Sprint is matched with transaction saved in database
	    6.  Validate Transaction Status. Check wheter Transaction Amount sent by Sprint is 00.
	    7.  Validate FlagType. Check whether flagType is matched with 11 or 12 or 13
	    8.  Validate Insert ID. Check wheter Insert ID sent by Sprint is matched with transaction saved in database
	    9. Validate authCode. Check wheter authCode sent by Sprint is matched with merchant authCode.
	    10. Validate Transaction Status. Check whether the transaction was cancelled.
	    11.  Validate Transaction Status. Check whether the transaction was expired .
	    12.  Validate Transaction Status. Check whether the transaction was already paid.
	*/

	    if (is_numeric($request->input('transactionNo'))) {
			
		} else {
			return response()->json([
				'channelId' => $request->input('channelId'),
			    'currency' => $request->input('currency'),
			    'paymentStatus' => '01',
			    'paymentMessage' => 'Invalid Transaction NO',
			    'flagType' => $request->input('flagType'),
			    'paymentReffId' => $request->input('paymentReffId')
			]);
			exit();
		}

		$transaction_data = PaymentInsert::where('transaction_id', '=', $request->input('transactionNo'))->first();


		if ($transaction_data == null) {
			return response()->json([
				'channelId' => $request->input('channelId'),
			    'currency' => $request->input('currency'),
			    'paymentStatus' => '01',
			    'paymentMessage' => 'Invalid Transaction NO',
			    'flagType' => $request->input('flagType'),
			    'paymentReffId' => $request->input('paymentReffId')
			]);
			exit();
		}



		// Cek untuk invalid customerAccountnya
		$payt = PaymentTransaction::where('id', '=', $request->input('transactionNo'))->first();

		/*
		customerAccount hanya untuk BCA VA
		if($payt['customer_account'] != $request->input('customerAccount')) {
			return response()->json([
				'channelId' => $request->input('channelId'),
				'currency' => $request->input('currency'),
				'paymentStatus' => '01',
				'paymentMessage' => 'Invalid customerAccount',
				'flagType' => $request->input('flagType'),
				'paymentReffId' => $request->input('paymentReffId')
			]);
			exit();
		}
		*/


		// Parsing request data from Sprint
		$data_sprint = array(
		    "channelId"           => $request->input('channelId'),
		    "currency"            => $request->input('currency'),
		    "transactionNo"       => $request->input('transactionNo'),
		    "transactionAmount"   => $request->input('transactionAmount'),
		    "transactionDate"     => $request->input('transactionDate'),
		    "transactionExpired"  => $request->input('transactionExpired'),
		    "transactionStatus"   => $request->input('transactionStatus'),
		    "transactionMessage"  => $request->input('transactionMessage'),
		    "flagType"            => $request->input('flagType'),
		    "insertId"            => $request->input('insertId'),
		    "paymentReffId"       => $request->input('paymentReffId'),
		    "authCode"            => $request->input('authCode'),
		    "additionalData"      => $request->input('additionalData'),
		);

		// Prepare response data
		$response   = array(
		    "channelId"           => $request->input('channelId'),      // Channel ID sent by Sprint
		    "currency"            => $request->input('currency'),       // Currency sent by Sprint (IDR)
		    "paymentStatus"       => "",                          // Payment Status ( 00 => Success , 01,03 => Failed , 02 => isPaid , 04 => isExpired , 05 => isCancelled )
		    "paymentMessage"      => "",                          // Payment Message
		    "flagType"            => $request->input('flagType'),       // Flag Type sent by Sprint
		    "paymentReffId"       => $request->input('paymentReffId'),  // Payment Referrence ID sent by Sprint
		);

		// Generate authCode
		$authCode = hash("sha256", $transaction_data['transaction_id'].$transaction_data['transaction_amount'].$this->channelIdCreditCard.$data_sprint['transactionStatus'].$transaction_data['insert_id'].$this->secretKey);

		/*
		Validation process
		*/

		// Validate Transaction
		if( !$transaction_data ){
		    $response['paymentStatus']  = "01";
		    $response['paymentMessage'] = "Invalid Transaction";
		}

		// Validate Channel Id
		elseif( $data_sprint['channelId'] != $this->channelIdCreditCard ){
		    $response['paymentStatus']  = "01";
		    $response['paymentMessage'] = "Invalid Channel ID";
		}

		// Validate Currency
		elseif( $data_sprint['currency'] != "IDR" ){
		    $response['paymentStatus']  = "01";
		    $response['paymentMessage'] = "Invalid Currency";
		}

		// Validate Transaction No
		elseif( $data_sprint['transactionNo'] != $transaction_data['transaction_id'] ){
		    $response['paymentStatus']  = "01";
		    $response['paymentMessage'] = "Invalid transactionNo Currency";
		}

		// 4.  Validate Transaction Amount
		elseif( $data_sprint['transactionAmount'] != $transaction_data['transaction_amount'] ){
		    $response['paymentStatus']  = "01";
		    $response['paymentMessage'] = "Invalid Transaction Amount";
		}

		// Validate Transaction Status
		elseif( $data_sprint['transactionStatus'] != "00" ){
		    $response['paymentStatus']  = "01";
		    $response['paymentMessage'] = "Invalid Transaction Status";
		}

		// Validate FlagType
		elseif( $request->input('flagType') != "11" && $request->input('flagType') != "12" && $request->input('flagType') != "13" ){
		    $response['paymentStatus']  = "01";
		    $response['paymentMessage'] = "Invalid Flagtype";
		}

		// Validate Insert ID
		elseif( $data_sprint['insertId'] != $transaction_data['insert_id'] ){
		    $response['paymentStatus']  = "01";
		    $response['paymentMessage'] = "Invalid Transaction Status";
		}

		// Validate authCode
		elseif( $data_sprint['authCode'] != $authCode ) {
		    $response['paymentStatus'] = "01";
		    $response['paymentMessage'] = "Invalid authCode";
		}

		// Validate Transaction Status => CANCELLED
		elseif( $transaction_data['status'] == "CANCELLED" ){
		    $response['paymentStatus']  = "05";
		    $response['paymentMessage'] = "Transaction has been cancelled";
		}

		// Validate Transaction Status => EXPIRED
		elseif( $transaction_data['status'] == "EXPIRED" ){
		    $response['paymentStatus']  = "04";
		    $response['paymentMessage'] = "Transaction has been expired";
		}

		// Validate Transaction Status => PAID
		elseif( $transaction_data['status'] == "PAID" ){
		    $response['paymentStatus']  = "02";
		    $response['paymentMessage'] = "Transaction has been paid";
		}

		// Success
		else{
		    $response['paymentStatus']  = "00";
		    $response['paymentMessage'] = "Success";
		    // Update transaction status
		}

		// Save $response['paymentStatus'], $response['paymentMessage'], $response['flagType'], $response['transactionStatus'], $response['transactionMessage'], $response['paymentReffId'] to merchant table


		/**
		 | ---------------------------------------------
		 | Save
		 |
		 */

		 $data = $this->paymentStatus->create([
	        'transaction_id'		=> $request->input('transactionNo'),
			'insert_id'				=> $request->input('insertId'),
			'payment_status'		=> $response['paymentStatus'],
			'payment_message'		=> $response['paymentMessage'],
			'flag_type'				=> $response['flagType'],
			'transaction_status'	=> $request->input('transactionStatus'),
			'transaction_message'	=> $request->input('transactionMessage'),
			'payment_reff_id'		=> $response['paymentReffId'],
			'created_at' 			=> Carbon::now()
		]);

		/*
 		 * ---------------------------------------------
		 */


		if($data_sprint['transactionStatus'] == "00" && $response['paymentStatus'] == "00") {

			$payment_insert = PaymentInsert::where('transaction_id', '=', $request->input('transactionNo'))->first();
			 	
	        $t_attributes = [
	            'status'	=> 'PAID',
	        ];

	        $payment_insert->update($t_attributes);
    	}


		/**
		 | ---------------------------------------------
		 | Send Email
		 |
		 */

		 // select PaymentTransaction By ID

		 // $payt = PaymentTransaction::where('id', '=', $request->input('transactionNo'))->first();

		 // select user

		 $user = User::where('id', '=', $payt['user_id'])->first();

		 \Mail::send('emails.payment.verify', $data_sprint, function ($message) use ($user) {
            $message->from('fundtastican@gmail.com', 'FUNDtastic');

            $message->to($user->email, $user->name)->subject('Payment');
         });
		
		/*
 		 * ---------------------------------------------
		 */



		// Set header response
		// header('Content-Type: application/json');

		// Print response
		// echo json_encode( $response );

		return $response;
	}

	/** 
	 * Halaman saat user selesai membayar menggunakan Credit Card  ...
	 */	 
	public function callbackCreditcard($id)
	{
		$payment = \DB::table('payment_bcava_transaction')
			->select('payment_bcava_transaction.id', 
				'users.name', 
				'users.email', 
				'payment_bcava_transaction.transaction_date', 
				'payment_bcava_transaction.transaction_expire', 
				'payment_bcava_transaction.customer_account', 
				'payment_bcava_insert.insert_status', 
				'payment_bcava_insert.insert_message', 
				'payment_bcava_insert.insert_id', 
				'payment_bcava_insert.status', 
				'payment_status.payment_status', 
				'payment_status.payment_message', 
				'payment_status.flag_type', 
				'payment_status.payment_reff_id')
			->leftJoin('payment_bcava_insert', 'payment_bcava_transaction.id', '=', 'payment_bcava_insert.transaction_id')
			->leftJoin('payment_status', 'payment_bcava_transaction.id', '=', 'payment_status.transaction_id')
			->leftJoin('users', 'payment_bcava_transaction.user_id', '=', 'users.id')
			->where('payment_bcava_transaction.id', '=', $id)
			->distinct()
			->orderByRaw('payment_bcava_transaction.id DESC')
			->first();

		return view('backend.payment.callback', compact('payment'));
	}

}



	