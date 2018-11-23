<?php namespace App\Http\Controllers\API;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Repositories\CfpSchedule\CfpScheduleInterface;
use App\Repositories\User\UserInterface;
use Redirect;
use View;
use Input;
use Validator;
use Response;
use App\Repositories\CfpSchedule\CfpScheduleRepository as CfpSchedule;
use App\Exceptions\Validation\ValidationException;
use Config;
use Entrust;
use Auth;
use GuzzleHttp;

use Carbon\Carbon;

use App\User;
use App\Repositories\CfpScheduleDayOff\CfpScheduleDayOffRepository as CfpScheduleDayOff;

class CfpScheduleController extends Controller {

	protected $cfpSchedule;
	protected $user;
	protected $cfpScheduleDayOff;

    public function __construct(CfpSchedule $cfpSchedule, UserInterface $user, CfpScheduleDayOff $cfpScheduleDayOff) {
        $this->cfpSchedule = $cfpSchedule;
		$this->user = $user;
		$this->cfpScheduleDayOff = $cfpScheduleDayOff;
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
		try {
			$input = Input::all();
			$input['schedule_start_date'] = Carbon::parse($input['schedule_start_date'])->format('d M Y');
			$input['schedule_available_time_slot'] = Carbon::parse($input['schedule_available_time_slot'])->format('d M Y H:i');
			$res = $this->cfpSchedule->create($input);
			
			// //Get firebase_token of CFP from users table $input['cfp_id']
			// $user = $this->user->findNonDeleted($input['cfp_id']);
			// $cfp_firebase_token = $user->firebase_token;
						
			// $firebase_payload = $input['notification'];
			// $firebase_payload['to'] = $cfp_firebase_token;

			// //Triger Firebase to send notif to CFP using GuzzleHttp.			
			// $resp = sendPushNotifViaFCM($firebase_payload);

            return response()->json([
				'result' => 'success',
				'data' => [
					'internalid' => $res['schedule_id'],
					'reminder_id' => isset($res['reminder_id'])?$res['reminder_id']:null,
					'cfp_reminder_id' => isset($res['cfp_reminder_id'])?$res['cfp_reminder_id']:null,
					'cfp_reminder_datetime' => isset($res['cfp_reminder_datetime'])?$res['cfp_reminder_datetime']:null					
				]
			]);
        } catch (ValidationException $e) {
            return response()->json([
					'result' => 'error',
					'alerts' => $e->getErrors()
			]);
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
		$alerts = [
				'id' => null
			];

		$cfpSchedule = $this->cfpSchedule->find($id);
        //dd($cfpSchedule);
        if($cfpSchedule['id']){

        	$alerts['id'] = $cfpSchedule['id'];
        	return response()->json([
				'result' => 'error',
				'alerts' => $alerts, 
				'data' => null
			]);
        }

        return response()->json([
			'result' => 'success',
			'alerts' => $alerts, 
			'data' => $cfpSchedule->toArray()
		]);
	}

	public function getJson()
	{
		
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update()
	{
		try {
			$input = Input::all();
			//$input['internalid'] = $input['id'];//alias untuk internalid, karena pakainya internalid
			$input['schedule_start_date'] = Carbon::parse($input['schedule_start_date'])->format('d M Y');
			$input['schedule_available_time_slot'] = Carbon::parse($input['schedule_available_time_slot'])->format('d M Y H:i');
			$res = $this->cfpSchedule->update($input['id'], $input);
            return response()->json([
				'result' => 'success',
				'data' => [
					'internalid' => $res['schedule_id'],
					'reminder_id' => isset($res['reminder_id'])?$res['reminder_id']:null,
					'cfp_reminder_id' => isset($res['cfp_reminder_id'])?$res['cfp_reminder_id']:null,
					'cfp_reminder_datetime' => isset($res['cfp_reminder_datetime'])?$res['cfp_reminder_datetime']:null
				]
			]);
        } catch (ValidationException $e) {
            return response()->json([
					'result' => 'error',
					'alerts' => $e->getErrors()
			]);
		}
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy()
	{
		$user_id = Auth::check()?Auth::user()->id:(Input::has('client_id')?Input::get('client_id'):''); //dd($user_id);
		try {
			$cfpSchedule = $this->cfpSchedule->find(Input::get('id'));
	        if(!is_null($cfpSchedule)){
				$cfpSchedule->update([
					'deleted_by' => $user_id,
					'deleted_at' => Carbon::now(),
					'record_flag' => 'D'
				]);
	            return response()->json([
					'result' => 'success'
				]);
	        }

	        return response()->json([
					'result' => 'error',
					'alerts' => [
						'id' => 'not exist'
					]
			]);
			
        } catch (ValidationException $e) {
            return response()->json([
					'result' => 'error',
					'alerts' => $e->getErrors()
			]);
        }
		
	}

	public function schedules(Request $request){ 
		$cfpID = $request->get('cfp_id');
		$data_raw = $this->cfpSchedule->findAllBy($request);
		$current_data_raw = $this->cfpSchedule->findCurrentScheduleBy($request); //dd($current_data_raw);
		$data = [];
		$data['current_schedule'] = [];
		$data['schedules_by_month'] = [];
		if(!is_null($current_data_raw)){ //dd($data_raw->cfp_clients->toArray());
			foreach ($current_data_raw as $idx => $schedule_raw) {
				$data['current_schedule'][$idx] = $schedule_raw->toArray();
				$schedule_raw_client = $schedule_raw->client->toArray();
				$schedule_raw_cfp = $schedule_raw->cfp->toArray();
				$client_userMeta = (array)userMeta($schedule_raw->client->usermetas); 
				$cfp_userMeta = (array)userMeta($schedule_raw->cfp->usermetas);
				$data['current_schedule'][$idx]['client'] = array_merge($schedule_raw_client, $client_userMeta);
				$data['current_schedule'][$idx]['cfp'] = array_merge($schedule_raw_cfp, $cfp_userMeta);
				unset($data['current_schedule'][$idx]['client']['usermetas']);
				unset($data['current_schedule'][$idx]['cfp']['usermetas']);
				//dd($data[$idx]);
			}
		}

		//$data['current_schedule'] = [];
		if(!is_null($data_raw)){ //dd($data_raw->cfp_clients->toArray());
			foreach ($data_raw as $idx => $schedule_raw) {
				$data['schedules_by_month'][$idx] = $schedule_raw->toArray();
				$schedule_raw_client = $schedule_raw->client->toArray();
				$schedule_raw_cfp = $schedule_raw->cfp->toArray();
				$client_userMeta = (array)userMeta($schedule_raw->client->usermetas); 
				$cfp_userMeta = (array)userMeta($schedule_raw->cfp->usermetas);
				$data['schedules_by_month'][$idx]['client'] = array_merge($schedule_raw_client, $client_userMeta);
				$data['schedules_by_month'][$idx]['cfp'] = array_merge($schedule_raw_cfp, $cfp_userMeta);
				//unset($data[$idx]->client['usermetas']);
				//array_splice($data[$idx]->client, 'usermetas');
				unset($data['schedules_by_month'][$idx]['client']['usermetas']);
				unset($data['schedules_by_month'][$idx]['cfp']['usermetas']);
				//dd($data[$idx]);
			}
		}

		//dd($data->toArray());
        return response()->json([
			'result' => 'success',
			'data' => $data
		]);
	}

	public function clientSchedules(Request $request){
		//$cfpID = $request->get('cfp_id');
		$data_raw = $this->cfpSchedule->findClientScheduleAllBy($request); //dd($data_raw);
		$current_data_raw = $this->cfpSchedule->findClientCurrentScheduleBy($request); //dd($current_data_raw);
		$data = [];
		$data['current_schedule'] = [];//current schedules
		$data['schedules_by_month'] = [];//this month schedules
		if(!is_null($current_data_raw)){ //dd($data_raw->cfp_clients->toArray());
			foreach ($current_data_raw as $idx => $schedule_raw) {
				$data['current_schedule'][$idx] = $schedule_raw->toArray();
				$schedule_raw_client = $schedule_raw->client->toArray();
				$schedule_raw_cfp = $schedule_raw->cfp->toArray();
				$client_userMeta = (array)userMeta($schedule_raw->client->usermetas); 
				$cfp_userMeta = (array)userMeta($schedule_raw->cfp->usermetas);
				$data['current_schedule'][$idx]['client'] = array_merge($schedule_raw_client, $client_userMeta);
				$data['current_schedule'][$idx]['cfp'] = array_merge($schedule_raw_cfp, $cfp_userMeta);
				unset($data['current_schedule'][$idx]['client']['usermetas']);
				unset($data['current_schedule'][$idx]['cfp']['usermetas']);
				//dd($data[$idx]);
			}
		}

		//$data['current_schedule'] = [];
		if(!is_null($data_raw)){ //dd($data_raw->cfp_clients->toArray());
			foreach ($data_raw as $idx => $schedule_raw) {
				$data['schedules_by_month'][$idx] = $schedule_raw->toArray();
				$schedule_raw_client = $schedule_raw->client->toArray();
				$schedule_raw_cfp = $schedule_raw->cfp->toArray();
				$client_userMeta = (array)userMeta($schedule_raw->client->usermetas); 
				$cfp_userMeta = (array)userMeta($schedule_raw->cfp->usermetas);
				$data['schedules_by_month'][$idx]['client'] = array_merge($schedule_raw_client, $client_userMeta);
				$data['schedules_by_month'][$idx]['cfp'] = array_merge($schedule_raw_cfp, $cfp_userMeta);
				$data['schedules_by_month'][$idx]['client']['reminder'] = null;
				if(isset($schedule_raw->reminders) && count($schedule_raw->reminders)){
					foreach ($schedule_raw->reminders as $reminder) {
						if($reminder->user_id == $schedule_raw_client['id'])
							$data['schedules_by_month'][$idx]['client']['reminder'] = isset($reminder->reminder)?$reminder->reminder:null;
					}
				}

				
				unset($data['schedules_by_month'][$idx]['reminders']);
				unset($data['schedules_by_month'][$idx]['client']['usermetas']);
				unset($data['schedules_by_month'][$idx]['cfp']['usermetas']);
				//dd($data[$idx]);
			}
		}

		//dd($data->toArray());
        return response()->json([
			'result' => 'success',
			'data' => $data
		]);
	}

	function availableTimeSlot(){ 
		$duration = 30;//sengaja di set per 30
        $cfp_working_hour_start = config_db_cached('settings::cfp_working_hour_start');//'09:00:00';
        $cfp_working_hour_end = config_db_cached('settings::cfp_working_hour_end');//'17:00:00';
        $schedule_type = Input::get('schedule_type');
        $cfp_info = Input::get('cfp_id');
        $cfp_info_raw = explode('__', $cfp_info);
        $cfp_id = $cfp_info_raw[0];
        $schedule_start_date = Input::get('schedule_start_date'); //dd(Carbon::parse($schedule_start_date)->format('d F Y'));
  
        $schedule_start_date_formated = Carbon::parse($schedule_start_date)->format('d F Y');
        $schedule_id = Input::get('schedule_id');

        $filter = [
            'schedule_type' => $schedule_type,
            'cfp_id' => $cfp_id,
			'schedule_start_date' => $schedule_start_date_formated,
			'record_flag_is_not' => 'D'
        ];

        if(Input::has('schedule_id')){
            $filter['schedule_id'] = $schedule_id;
		}
		//\DB::enableQueryLog();
		$notAvailableTimeSlots = $this->cfpSchedule->availableTimeSlot($filter); //dd($notAvailableTimeSlots);
		//dd(\DB::getQueryLog());
		//$notAvailableTimeSlots_now = $this->cfpSchedule->findBy($filter);
		$notAvailableTimeSlot_time = notAvailableTimeSlot_time([
			'duration' => $duration,
			'schedule_start_date_formated' => $schedule_start_date_formated,
			'notAvailableTimeSlots' => $notAvailableTimeSlots,
			//'notAvailableTimeSlots_now' => $notAvailableTimeSlots_now,
			'cfp_working_hour_start' => $cfp_working_hour_start,
			'cfp_working_hour_end' => $cfp_working_hour_end
		]);

		$availableTimeSlot_time = getDatesFromRange_api($schedule_start_date_formated.' '.$cfp_working_hour_start, $schedule_start_date_formated.' '.$cfp_working_hour_end, $duration, $notAvailableTimeSlot_time);
       
        return response()->json([
            'duration' => $duration,
            'cfp_working_hour_start' => $cfp_working_hour_start,//'09:00',
            'cfp_working_hour_end' => $cfp_working_hour_end,//'17:00',
            'time_slots' => $availableTimeSlot_time,
            //'notAvailableTimeSlot_time' => $notAvailableTimeSlot_time
        ]);   
	}
	
	public function nationalDayIndonesia(){
		$path = storage_path() . "/json/national_holiday.json";
		$json_holiday = json_decode(file_get_contents($path), true);

		$data_holiday = array();
		foreach($json_holiday['items'] as $holidays){
			$nested_data['id'] = $holidays['id'];
			$nested_data['summary'] = $holidays['summary'];
			$nested_data['date'] = $holidays['start']['date'];
			$nested_data['status'] = $holidays['status'];
			$nested_data['created'] = $holidays['created'];
			$nested_data['updated'] = $holidays['updated'];
			$data_holiday[] = $nested_data;
		}

		return response()->json([
			'result' => 'success',
			'data' => $data_holiday
		]);
	}

	public function cfpScheduleDayOff(Request $request)
	{
		$input = $request->all();
		$cfp_day_offs = $this->cfpScheduleDayOff->whereDayOff([
			'cfp_id' => $input['cfp_id'],
			'is_approval' => 1
		]);

		$cfp_schedule = $this->cfpSchedule->availableTimeSlot($input);
		foreach($cfp_schedule as $cfp_schedule_on){
			$data_schedule[] = Carbon::parse($cfp_schedule_on->schedule_start_date)->format('Y-m-d');
		}

		$dates = array();
		$index = 0;

		foreach($cfp_day_offs as $key => $cfp_day_off){
			$nested['id'] = $cfp_day_off->id;
			$nested['status_approval'] = $cfp_day_off->is_approval == 1 ? 'Cuti Disetujui' : 'Cuti Ditolak' ;
			$nested['start'] = $cfp_day_offs[$key]['cfp_schedule_day_off_start_date'];
			$nested['end'] = $cfp_day_offs[$key]['cfp_schedule_day_off_end_date'];

			$start=$cfp_day_offs[$key]['cfp_schedule_day_off_start_date'];
			$end=$cfp_day_offs[$key]['cfp_schedule_day_off_end_date'];

			$nested['count'] = strtotime($end) - strtotime($start);

			$datediff = strtotime($end) - strtotime($start);
			
			$datediff = floor($datediff/(60*60*24));
			for($i = 0; $i < $datediff+1; $i++){
				if(in_array(date("Y-m-d", strtotime($start . ' + ' . $i . 'day')), $data_schedule) != true){
					$nested['cfp_dayoff'][] = date("Y-m-d", strtotime($start . ' + ' . $i . 'day'));
				}
			}

			$data[] = $nested;
			$index++;
		}
		//exit();

		return response()->json([
			'result' => 'success',
			'data' => !empty($data) ? $data : null
		]);
	}
}
