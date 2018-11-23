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
use App\Repositories\CfpSchedule\CfpScheduleRepository;// as CfpSchedule;
use App\Exceptions\Validation\ValidationException;
use Config;
use Entrust;

use App\User;
use App\Repositories\Reminder\ReminderRepository;
use Carbon\Carbon;
use App\Models\Reminder;
use App\Models\Expense;
use App\Models\ActiveVersion;
use App\Models\CfpSchedule;
use Auth;

class ReminderController extends Controller {

	protected $cfpSchedule;
	protected $user;
	protected $cashflowAnalysis;

    public function __construct(ReminderRepository $reminder, UserInterface $user,CfpScheduleRepository $cfpSchedule) {
        $this->cfpSchedule = $cfpSchedule;
        $this->user = $user;
		$this->reminder = $reminder;
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

	public function store(Request $request)
	{ //dd($request->all());
		$user_id = $request->input('user_id');
		$module_name = $request->input('module_name');
		$screen_name = $request->input('screen_name');
		$reminder_datetime = Carbon::parse($request->input('reminder_datetime'))->format('Y-m-d H:i:s');
		$is_repeated = $request->input('is_repeated');
		$about = $request->input('about');
		$note = $request->has('note')?$request->input('note'):'';
		try {
			$data = $this->reminder->create([
					'user_id' => $user_id,
					'module_name' => $module_name,
				    'screen_name' => $screen_name,
				    'reminder_datetime' => $reminder_datetime,
				    'is_repeated' => $is_repeated,
				    'about' => $about,
				    'note' => $note,
				    'next_reminder_datetime' => '',//uda ga kepake
			]);
            return response()->json([
				'result' => 'success',
				'data' => $data
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
		//$input = Input::all();
		//dd($input);
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

	public function update(Request $request)
	{ 

		$id = $request->input('id');
		$user_id = $request->input('user_id');
		$module_name = $request->input('module_name');
		$screen_name = $request->input('screen_name');
		$reminder_datetime = Carbon::parse($request->input('reminder_datetime'))->format('Y-m-d H:i:s');
		$is_repeated = $request->input('is_repeated');
		$about = $request->input('about');
		$note = $request->has('note')?$request->input('note'):'';

		$new_data = [
			'user_id' => $user_id,
			'module_name' => $module_name,
			'screen_name' => $screen_name,
			'reminder_datetime' => $reminder_datetime,
			'is_repeated' => $is_repeated,
			'about' => $about,
			'note' => $note,
			'next_reminder_datetime' => '',//harusnya diisi otomatis jika is_repeated nya di isi
		];

		//karena saat update module_name schedule butuh cfp module id
		if($request->has('cfp_schedule_id')){
			$new_data['cfp_schedule_id'] = $request->input('cfp_schedule_id');
		}

		try {
			$data = $this->reminder->update($id, $new_data);
            return response()->json([
				'result' => 'success',
				'data' => $data
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
		$user_id = Auth::check()?Auth::user()->id:(Input::has('user_id')?Input::get('user_id'):'');
		try {
			$item = Reminder::where('id', Input::get('id'))->first();
	        if(!is_null($item)){
				$item->update([
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

	public function allReminders(Request $request){
		$user_id = $request->input('user_id');
		$datetime_now = Carbon::now()->format('Y-m-d H:i:s');
		$datetime_now_date = Carbon::now()->format('Y-m-d');
		$datetime_now_d = Carbon::now()->format('d');
		$datetime_now_m = Carbon::now()->format('m');
		$datetime_now_y = Carbon::now()->format('Y');
		$datetime_now_parse = Carbon::parse($datetime_now); 
		//\DB::enableQueryLog();
		$items_raw = Reminder::select('id','user_id','module_name','screen_name','reminder_datetime','is_repeated','is_predefined', 'record_flag')
		->with('reminderMetas')
		->where(function ($q) use ( $datetime_now ){
	    	$q->where('is_repeated', '!=', 'does_not_repeat')
				->orWhere('reminder_datetime', '>=', $datetime_now);
		})
		->where('user_id', $user_id)
		->where('record_flag', '!=', 'D')
		->get();
		//dd(\DB::getQueryLog());
		$items = [];
		$debug_date = [];
		if(!is_null($items_raw)){
			foreach ($items_raw as $idx_item => $item_raw) {
				$items[$idx_item] = $item_raw;
				$reminderMeta = (array)userMeta($item_raw->reminderMetas);
				$reminder_datetime = $item_raw->reminder_datetime;
				$reminder_datetime_parse = Carbon::parse($reminder_datetime);
				switch ($item_raw->is_repeated) {
					case 'daily':
							$reminder_time = $reminder_datetime_parse->format('H:i:s');
							$reminder_datetime_new = $datetime_now_date.' '.$reminder_time;//ubah menjadi tanggal hari ini tapi waktunya tetap milik reminder. Untuk perbandingan
							$reminder_datetime_new_parse = Carbon::parse($reminder_datetime_new); 
							//echo Carbon::parse($reminder_datetime_new)->format('Y-m-d H:i:s').' < '.Carbon::parse($datetime_now)->format('Y-m-d H:i:s').'<br />';
							if(strtotime($reminder_datetime_new) < strtotime($datetime_now)){
								$items[$idx_item]->reminder_datetime = $reminder_datetime_new_parse->addDay()->format('Y-m-d H:i:s');
							}
						break;
					case 'weekly':
							if(strtotime($reminder_datetime) < strtotime($datetime_now)){
								$items[$idx_item]->reminder_datetime = $reminder_datetime_parse->addWeek()->format('Y-m-d H:i:s');
							}
						break;
					case 'monthly':
							if(strtotime($reminder_datetime) < strtotime($datetime_now)){
								$items[$idx_item]->reminder_datetime = $reminder_datetime_parse->addMonth()->format('Y-m-d H:i:s');
							}
						break;
					case 'yearly':
							if(strtotime($reminder_datetime) < strtotime($datetime_now)){
								$items[$idx_item]->reminder_datetime = $reminder_datetime_parse->addYear()->format('Y-m-d H:i:s');
							}
						break;
				}

				$this_item = $items[$idx_item]->toArray();
				unset($this_item['reminder_metas']);
				$items[$idx_item] = array_merge($this_item, $reminderMeta);

				//menambahkan detail schedule jika module_name nya adalah schedule
				if(isset($items[$idx_item]['cfp_schedule_id'])){
					$cfp_schedule_id = $items[$idx_item]['cfp_schedule_id'];
					$cfp_schedule_detail = CfpSchedule::select('title', 'notes')->where('internalid', $cfp_schedule_id)->first();
					$items[$idx_item]['cfp_schedule_detail'] = $cfp_schedule_detail;
				}

				//get expense 
				$default_taxo_wallet_id_on_reminder_action = config_db_cached('settings::default_taxo_wallet_id_on_reminder_action');
				
				$activeVersion = ActiveVersion::where('user_id', $user_id)->where('key', 'financialCheckup_cashflowAnalysis')->first();
				$module_version = is_null($activeVersion)?'':$activeVersion->version;
				
				if($module_version !== ''){
					$expense = Expense::select('taxonomies.title as taxo_wallet_name', 'taxonomies.image as taxo_wallet_ico', 'expenses.*')
					->where('version', $module_version)
					->where('taxo_wallet_id', $default_taxo_wallet_id_on_reminder_action)
					->where('user_id', $user_id)
					->join('taxonomies', 'taxonomies.id', '=', 'expenses.taxo_wallet_id', 'left')->first();
					
					//if predefined with about data : breakfast, lunch, dinner. Sehingga user wajib memiliki waller F&B
					$is_fnb_reminder  = ['breakfast', 'lunch', 'dinner'];//by note
					if(isset($items[$idx_item]['about']) && in_array(strtolower($items[$idx_item]['about']), $is_fnb_reminder) && !is_null($expense)){
//						unset($items[$idx_item]); //Prevent api to return 'breakfast', 'lunch', 'dinner' type of reminders on responses
						$items[$idx_item]['detail'] = [
							'wallet_expense_id' => $expense->id,//sama dengan detail id ( id pada expense)
							'taxo_wallet_name' => $expense->taxo_wallet_name
						];
					}

					if(is_null($expense)){
						$items[$idx_item]['detail'] = [
							'wallet_expense_id' => 0,
							'taxo_wallet_name' => 'Do not have F&B wallet'
						];
					}
				}
			}
		}
		return response()->json([
			'result' => 'success',
			'data' => $items
		]);
	}

}
