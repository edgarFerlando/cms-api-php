<?php namespace App\Http\Controllers\Backend;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Repositories\User\UserInterface;
use App\Repositories\ActionPlanCategory\ActionPlanCategoryInterface;
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
use Entrust;
use Carbon\Carbon;
use App\Models\Reminder;
use App\Models\ActionPlanCategory;

class ActionPlanCategoryController extends Controller {
    protected $user;
    protected $actionPlan;

    public function __construct(UserInterface $user, ActionPlanCategoryInterface $actionPlan) {
        $this->user = $user;
        $this->actionPlanCategory = $actionPlan;
    }
	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		$attr = [ 
                'title' => trans('app.action_plan_category')
            ];
        if(!Entrust::can(['read_action_plan_category'])){
            $attr += [
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $page = Input::get('page', 1);
        $perPage = config_db_cached('settings::backend_per_page');
        $filter = Input::all(); 
        unset($filter['_token']); 
        $pagiData = $this->actionPlanCategory->paginate($page, $perPage, $filter);
        $totalItems = $pagiData->totalItems;
        $items = new LengthAwarePaginator($pagiData->items, $pagiData->totalItems, $perPage, $page, [
            'path' => Paginator::resolveCurrentPath()
        ]);
        $items->setPath("");
        $items->appends($filter);
		return view('backend.actionPlanCategory.index', compact('items', 'totalItems'));
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
        $attr = [ 
                'title' => trans('app.action_plan_category')
            ];
        
        if(!Entrust::can(['create_action_plan_category'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        return view('backend.actionPlanCategory.create');
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
            $this->actionPlanCategory->create($input);
            Notification::success( trans('app.action_plan_category_added') );
            return langRedirectRoute('admin.settings.plan-analysis.action-plan-category.index');
        } catch (ValidationException $e) {
            return langRedirectRoute('admin.settings.plan-analysis.action-plan-category.create')->withInput()->withErrors($e->getErrors());
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
        $attr = [ 
                'title' => trans('app.cfp_schedule')
            ];
        if(!Entrust::can(['read_cfp_schedule'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $cfpSchedule = $this->cfpSchedule->find($id);
        $schedule_start_date_formated = $cfpSchedule->schedule_start_date;
        $today_date = Carbon::now()->format('Y-m-d H:i:s');
        if(strtotime($schedule_start_date_formated) < strtotime($today_date)){
            $is_expired = 1;
        }
        return view('backend.cfpSchedule.show', compact('cfpSchedule', 'is_expired'));
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{   
		$attr = [ 
                'title' => trans('app.action_plan_category')
            ];
        if(!Entrust::can(['update_action_plan_category'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $data = $this->actionPlanCategory->find($id);
        return view('backend.actionPlanCategory.edit', compact('data'));
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{  
		try {   
            $input = Input::all();
            $this->actionPlanCategory->update($id, $input);
            Notification::success(trans('app.action_plan_category_updated'));
            return langRedirectRoute('admin.settings.plan-analysis.action-plan-category.index');
        } catch (ValidationException $e) {
            return langRedirectRoute('admin.settings.plan-analysis.action-plan-category.edit', [ 'id' => $id ] )->withInput()->withErrors($e->getErrors());
        }
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
        $attr = [ 
            'title' => trans('app.triangle')
        ];
        if(!Entrust::can(['delete_action_plan_category'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $this->actionPlanCategory->delete($id);
        Notification::success(trans('app.action_plan_category_deleted'));
        return langRedirectRoute('admin.settings.plan-analysis.action-plan-category.index');
	}

    public function confirmDestroy($id) {
        $attr = [ 
                'title' => trans('app.triangle')
            ];
        if(!Entrust::can(['delete_action_plan_category'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $triangle = $this->actionPlanCategory->find($id);
        return view('backend.actionPlanCategory.confirm-destroy', compact('triangle'));
    }

    function availableTimeSlot(){
        $cfp_working_hour_start = config_db_cached('settings::cfp_working_hour_start');//'09:00:00';
        $cfp_working_hour_end = config_db_cached('settings::cfp_working_hour_end');//'17:00:00';
        $schedule_type = Input::get('schedule_type');
        $cfp_info = Input::get('cfp_id');
        $cfp_info_raw = explode('__', $cfp_info);
        $cfp_id = $cfp_info_raw[0];
        $schedule_start_date = Input::get('schedule_start_date');
        $schedule_id = Input::get('schedule_id');
        $filter = [
            'schedule_type' => $schedule_type,
            'cfp_id' => $cfp_id,
            'schedule_start_date' => $schedule_start_date
        ];

        if(Input::has('schedule_id')){
            $filter['schedule_id'] = $schedule_id;
        }

        switch ($schedule_type) {
            case 'meet_up':
                $duration = 60;
                break;
            default: //video_call, call
                $duration = 30;
                break;
        }

        $schedule_start_date_formated = Carbon::parse($schedule_start_date)->format('Y-m-d');
        $today_date = Carbon::now()->format('Y-m-d'); //echo $schedule_start_date_formated.' < '.$today_date;
        if(strtotime($schedule_start_date_formated) < strtotime($today_date)){ //kemarin dan sebelumnya
            //$notAvailableTimeSlot_time[] = [ $cfp_working_hour_start, Carbon::parse($cfp_working_hour_end)->subMinutes(30)->format('H:i') ];
            $notAvailableTimeSlot_time = getDatesFromRange_raw($cfp_working_hour_start, Carbon::parse($cfp_working_hour_end)->subMinutes(30)->format('H:i'), $duration);
        }else{
            $notAvailableTimeSlots = $this->cfpSchedule->availableTimeSlot($filter);
            $notAvailableTimeSlot_time = [];
            $debug_notAvailableTimeSlot_time = [];
            if(count($notAvailableTimeSlots) > 0){
                foreach ($notAvailableTimeSlots as $notAvailableTimeSlot) {
                    $notAvailableTimeSlot_time[] = [ Carbon::parse($notAvailableTimeSlot->schedule_start_date_plus_spare)->format('H:i'), Carbon::parse($notAvailableTimeSlot->schedule_end_date)->format('H:i') ];
                }
            }
            //echo $schedule_start_date_formated.' = '.$today_date;
            //tambahkan jika tanggal nya adalah hari ini
            if(strtotime($schedule_start_date_formated) == strtotime($today_date)){ 
                $today_time_start = $cfp_working_hour_start; //dd($today_time_start);
                $today_time_end = '';
                $now_datetime_formated = Carbon::now()->format('Y-m-d H:i:s');//'2017-01-03 14:29';
                if(Carbon::parse($now_datetime_formated)->format('i') > 0 && Carbon::parse($now_datetime_formated)->format('i') <= $duration ){
                    $today_time_end = Carbon::parse(Carbon::parse($now_datetime_formated)->format('H:00'))->addMinutes($duration)->format('H:i');
                }else{
                    $today_time_end = Carbon::parse(Carbon::parse($now_datetime_formated)->format('H:'.$duration))->addMinutes($duration)->format('H:i');
                }
                $notAvailableTimeSlot_time[] = [ Carbon::parse($today_time_start)->format('H:i'), $today_time_end ];
            }
        }
        $availableTimeSlot_time = getDatesFromRange($schedule_start_date.' '.$cfp_working_hour_start, $schedule_start_date.' '.$cfp_working_hour_end, $duration, $notAvailableTimeSlot_time);
        
        return [
            'duration' => $duration,
            'cfp_working_hour_start' => $cfp_working_hour_start,//'09:00',
            'cfp_working_hour_end' => $cfp_working_hour_end,//'17:00',
            'time_slots' => $availableTimeSlot_time,
            'notAvailableTimeSlot_time' => $notAvailableTimeSlot_time
        ];
    }

}
