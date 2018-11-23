<?php namespace App\Http\Controllers\API;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Repositories\User\UserInterface;
use Redirect;
use View;
use Input;
use Validator;
use Response;

use App\Exceptions\Validation\ValidationException;
use Config;
use Entrust;
use Auth;
use GuzzleHttp;
use Exception;

use Carbon\Carbon;

use App\User;

class NotificationController extends Controller {

	protected $user;

    public function __construct(UserInterface $user) {
		$this->user = $user;		
    }
	
	public function rules(){
		return [
			''
		];
	}
	/**
	 * Push a notification via FCM
	 *
	 * @return Response
	 */
	public function push()
	{		
		try {
			$input = Input::all();			
			$user = $this->user->findNonDeleted($input['to']);
			
			if(is_null($user)){
			    return response()->json([
					'result' => 'error',
					'alerts' => 'Unknown destination.'
				], 400);	
			}
			if(!isset($user->firebase_token)){
			    return response()->json([
					'result' => 'error',
					'alerts' => 'Destination not ready to receive notification.'
				], 400);
			}

			$firebase_payload = $input;
			$firebase_payload['to'] = $user->firebase_token;

			//Triger Firebase to send notif to CFP using GuzzleHttp.			
			$resp = sendPushNotifViaFCM($firebase_payload);

            return response()->json([
				'result' => 'success',
				'data' => $resp
			]);
        } catch (Exception $e) {
            return response()->json([
					'result' => 'error',
					'alerts' => $e->getMessage()
			],400);
        }
	}


}
