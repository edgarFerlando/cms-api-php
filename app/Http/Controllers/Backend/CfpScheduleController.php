<?php namespace App\Http\Controllers\Backend;

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
use Notification;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use App\Repositories\CfpSchedule\CfpScheduleRepository as CfpSchedule;
use App\Exceptions\Validation\ValidationException;
use Config;
use Entrust;
use Carbon\Carbon;
use App\Models\Reminder;
use App\Repositories\CfpScheduleDayOff\CfpScheduleDayOffRepository as CfpScheduleDayOff;

use Auth;
//use App\User;

use App\Repositories\CfpScheduleType\CfpScheduleTypeInterface;

class CfpScheduleController extends Controller {

	protected $cfpSchedule;
    protected $cfpScheduleType;
    protected $user;
    protected $cfpScheduleDayOff;

    public function __construct(CfpSchedule $cfpSchedule, UserInterface $user, CfpScheduleTypeInterface $cfpScheduleType, CfpScheduleDayOff $cfpScheduleDayOff) {
        $this->cfpSchedule = $cfpSchedule;
        $this->user = $user;
        $this->cfpScheduleType = $cfpScheduleType;
        $this->cfpScheduleDayOff = $cfpScheduleDayOff;
    }
	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		$attr = [ 
                'title' => trans('app.cfp_schedule')
            ];
        /* if(!Entrust::can(['read_product_attribute'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        } */
        $page = Input::get('page', 1);
        $perPage = config_db_cached('settings::backend_per_page');
        $filter = Input::all(); 
        $filter['record_flag_is_not'] = 'D';
        unset($filter['_token']); //dd($filter);
        $pagiData = $this->cfpSchedule->paginate($page, $perPage, $filter);
        $totalItems = $pagiData->totalItems;
        $cfpSchedules = new LengthAwarePaginator($pagiData->items, $pagiData->totalItems, $perPage, $page, [
            'path' => Paginator::resolveCurrentPath()
        ]);
        $cfpSchedules->setPath("");
        $cfpSchedules->appends($filter);
        //dd($testimonials->count());

        //dd($codes);
        $cfp_schedule_types[''] = '-';
        $cfp_schedule_types += $this->cfpScheduleType->lists('id', 'type_display_name');

		return view('backend.cfpSchedule.index', compact('cfpSchedules', 'cfp_schedule_types', 'totalItems'));
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{


        /*dd(rawurlencode(json_encode([
            '0' =>  [['09:30', '11:00'], ['13:00', '16:30']]
        ])));*/

        $attr = [ 
                'title' => trans('app.cfp_client')
            ];
        
        if(!Entrust::can(['create_cfp_client'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $cfp_schedule_types[''] = '-';
        $cfp_schedule_types += $this->cfpScheduleType->lists('type_name', 'type_display_name');
        return view('backend.cfpSchedule.create', compact('cfp_schedule_types'));

/*		$attr = [ 
                'title' => trans('app.cfp_schedule')
            ];*/
        /*
        if(!Entrust::can(['create_testimonial'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $userCustomers[] = '-';
        $userCustomers += $this->user->listRoles('name', 'id', 5);

        $userCfps[] = '-';
        $userCfps += $this->user->listRoles('name', 'id', 6);*/
        //dd($categoryCodes);
        //return view('backend.cfpSchedule.create', compact('userCustomers','userCfps'));
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		try {
			$input = Input::all(); //dd($input);
            /*switch ($input['schedule_type']) {
                case 'meet_up':
                    $duration = 60;
                    break;
                default: //video_call, call
                    $duration = 30;
                    break;
            }*/
            //dd(Carbon::parse(Input::get('schedule_available_time_slot'))->format('Y-m-d H:i:s'));

            //$schedule_type = $this->cfpScheduleType->findByTypeName($input['schedule_type']); //dipindahkan ke dalam repository supaya terdeteksi saat validation
            //$input['schedule_type_id'] = $schedule_type->id;
            /*$input['schedule_start_date'] = Carbon::parse($input['schedule_available_time_slot'])->format('Y-m-d H:i:s');
            $input['schedule_end_date'] = Carbon::parse($input['schedule_available_time_slot'])->addMinutes($duration)->format('Y-m-d H:i:s');;
			$client_raw = explode('__', $input['client_id']);
            $cfp_raw = explode('__', $input['cfp_id']);
            $input['client_id'] = $client_raw[0];
            $input['cfp_id'] = $cfp_raw[0];*/
            //dd($input);
            $this->cfpSchedule->create($input);
            Notification::success( trans('app.cfp_schedule_added') );
            return langRedirectRoute('admin.cfp.schedule.index');
        } catch (ValidationException $e) { //dd($e->getErrors());
            return langRedirectRoute('admin.cfp.schedule.create')->withInput()->withErrors($e->getErrors());
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
		/*$attr = [ 
                'title' => trans('app.cfp_schedule')
            ];
		$cfpSchedule = $this->cfpSchedule->find($id);

        if(!$cfpSchedule['id'])
        {
    		$userCustomer  = $this->user->find($cfpSchedule->customer_id);
    		$userCfp  = $this->user->find($cfpSchedule->cfp_id);

    		$cfpSchedule->customer_name = $userCustomer->name;
    		$cfpSchedule->cfp_name = $userCfp->name;
        }
		//dd($code);
        return view('backend.cfpSchedule.show', compact('cfpSchedule'));*/
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
                'title' => trans('app.cfp_schedule')
            ];
        /*if(!Entrust::can(['update_testimonial'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }*/
        $data = $this->cfpSchedule->find($id);
        $data['cfp_id'] = $data->cfp_id.'__'.$data->cfp->name; 
        $data['client_id'] = $data->client_id.'__'.$data->client->name;
        $schedule_start_date_raw = Carbon::parse($data->schedule_start_date);
        $data['schedule_start_date'] = $schedule_start_date_raw->format('d F Y');
        $data['schedule_available_time_slot'] = $schedule_start_date_raw->format('d F Y H:i');

        //cannot edit if backdate
        $schedule_start_date_formated = $schedule_start_date_raw->format('Y-m-d H:i:s');
        $today_date = Carbon::now()->format('Y-m-d H:i:s');
        if(strtotime($schedule_start_date_formated) < strtotime($today_date)){
            //return langRedirectRoute('admin.cfp.schedule.index');        
            return langRedirectRoute('admin.cfp.schedule.show', [ 'schedule' => $id ] );
        }

        //dd($data);
        /*$userCustomers[] = '-';
        $userCustomers += $this->user->listRoles('name', 'id', 5);

        $userCfps[] = '-';
        $userCfps += $this->user->listRoles('name', 'id', 6);*/
        //dd($cfpSchedule);
        $cfp_schedule_types[''] = '-';
        $cfp_schedule_types += $this->cfpScheduleType->lists('type_name', 'type_display_name');
        return view('backend.cfpSchedule.edit', compact('data', 'cfp_schedule_types'));
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
            /*switch ($input['schedule_type']) {
                case 'meet_up':
                    $duration = 60;
                    break;
                default: //video_call, call
                    $duration = 30;
                    break;
            }// dd($input);
            //dd(Carbon::parse(Input::get('schedule_available_time_slot'))->format('Y-m-d H:i:s'));
            $schedule_type = $this->cfpScheduleType->findByTypeName($input['schedule_type']); 
            $input['schedule_type_id'] = $schedule_type->id;
            $input['schedule_start_date'] = Carbon::parse($input['schedule_available_time_slot'])->format('Y-m-d H:i:s');
            $input['schedule_end_date'] = Carbon::parse($input['schedule_available_time_slot'])->addMinutes($duration)->format('Y-m-d H:i:s');;
            $client_raw = explode('__', $input['client_id']);
            $cfp_raw = explode('__', $input['cfp_id']);
            $input['client_id'] = $client_raw[0];
            $input['cfp_id'] = $cfp_raw[0];*/


            //dd(Input::all());
            $this->cfpSchedule->update($id, $input);
            Notification::success(trans('app.cfp_schedule_updated'));
            return langRedirectRoute('admin.cfp.schedule.index');
        } catch (ValidationException $e) {
            return langRedirectRoute('admin.cfp.schedule.edit', [ 'id' => $id ] )->withInput()->withErrors($e->getErrors());
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
		/*$attr = [ 
                'title' => trans('app.cfp_schedule')
            ];*/
        /*if(!Entrust::can(['delete_testimonial'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }*/
        /*$this->cfpSchedule->delete($id);
        Notification::success(trans('app.cfp_schedule_deleted'));
        return langRedirectRoute('admin.schedule.cfp.index');*/

        $attr = [ 
                'title' => trans('app.cfp_schedule')
            ];
        if(!Entrust::can(['delete_cfp_schedule'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        if($this->cfpSchedule->delete($id)){
            Notification::success(trans('app.cfp_schedule_deleted'));
        }else{
            Notification::error(trans('app.delete_failed'));
        }
        
        return langRedirectRoute('admin.cfp.schedule.index');
	}

	public function confirmDestroyxx($id) {
        $attr = [ 
                'title' => trans('app.cfp_schedule')
            ];
        /*if(!Entrust::can(['delete_testimonial'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }*/
        $cfpSchedule = $this->cfpSchedule->find($id);
        if(!$cfpSchedule['id'])
        {
            $user = $this->user->find($cfpSchedule->customer_id);
        }
        //dd($user);
        return view('backend.cfpSchedule.confirm-destroy', compact('cfpSchedule','user'));
    }

    public function confirmDestroy($id) {
        $attr = [ 
                'title' => trans('app.cfp_schedule')
            ];
        if(!Entrust::can(['delete_cfp_schedule'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $cfpSchedule = $this->cfpSchedule->find($id);
        //$user = $this->user->find($CfpClient->client_id);
        //dd($user);
        return view('backend.cfpSchedule.confirm-destroy', compact('cfpSchedule'));
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
            'schedule_start_date' => $schedule_start_date,
            'record_flag_is_not' => 'D'
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
            $notAvailableTimeSlots = $this->cfpSchedule->availableTimeSlot($filter);//jika datetime saat ini menyentuh wakru sparetime berarti includein aja schedule time nya ( karena sudah tidak boleh edit schedule )
            

            $notAvailableTimeSlot_time = [];
            $debug_notAvailableTimeSlot_time = [];
            if(count($notAvailableTimeSlots) > 0){
                foreach ($notAvailableTimeSlots as $notAvailableTimeSlot) {
                    $notAvailableTimeSlot_time[] = [ Carbon::parse($notAvailableTimeSlot->schedule_start_date_plus_spare)->format('H:i'), Carbon::parse($notAvailableTimeSlot->schedule_end_date)->format('H:i') ];
                }
            }

            //$now_datetime_formated = '2018-03-29 17:31:00';//untuk debugging dengan mamanipulasi datetime saat ini ( sehingga akan terblock sampai jam ini )
            $now_datetime_formated = Carbon::now()->format('Y-m-d H:i:s');//'2017-01-03 14:29';
            $notAvailableTimeSlots_now = $this->cfpSchedule->findBy($filter);//ambil hanya schedule id bersangkutan
            //dd($now_datetime_formated.' > '.$notAvailableTimeSlots_now->schedule_start_date_plus_spare);
            if(!is_null($notAvailableTimeSlots_now) && strtotime($now_datetime_formated) > strtotime($notAvailableTimeSlots_now->schedule_start_date_plus_spare)){//jika waktunya lebih besar dari tanggal sekarang maka masukkan ke daftar not available timeslot
                $notAvailableTimeSlot_time[] = [ Carbon::parse($notAvailableTimeSlots_now->schedule_start_date_plus_spare)->format('H:i'), Carbon::parse($notAvailableTimeSlots_now->schedule_end_date)->format('H:i') ];
            }

            //dd($notAvailableTimeSlot_time);
            //echo $schedule_start_date_formated.' = '.$today_date;
            //tambahkan jika tanggal nya adalah hari ini
            if(strtotime($schedule_start_date_formated) == strtotime($today_date)){ 
                $today_time_start = $cfp_working_hour_start; //dd($today_time_start);
                $today_time_end = '';
                //$now_datetime_formated = '2017-03-29 17:31';//untuk debugging dengan mamanipulasi datetime saat ini ( sehingga akan terblock sampai jam ini )
                //$now_datetime_formated = Carbon::now()->format('Y-m-d H:i:s');//'2017-01-03 14:29';
                if(Carbon::parse($now_datetime_formated)->format('i') > 0 && Carbon::parse($now_datetime_formated)->format('i') <= $duration ){
                    $today_time_end = Carbon::parse(Carbon::parse($now_datetime_formated)->format('H:00'))->addMinutes($duration)->format('H:i');
                }/*else if(Carbon::parse($now_datetime_formated)->format('i') > $duration ){
                    $today_time_end = Carbon::parse(Carbon::parse($now_datetime_formated)->format('H:'.$duration))->addMinutes($duration)->format('H:i');
                }*/else{
                    $today_time_end = Carbon::parse(Carbon::parse($now_datetime_formated)->format('H:'.$duration))->addMinutes($duration)->format('H:i');
                    
                }
                $notAvailableTimeSlot_time[] = [ Carbon::parse($today_time_start)->format('H:i'), $today_time_end ];

                //$notAvailableTimeSlot_time += getDatesFromRange_raw($today_time_start, Carbon::parse(Carbon::parse($today_time_end)->subMinutes(30)->format('H:i')), $duration);
                /*$today_start = Carbon::parse($today_time_start);
                $today_end = Carbon::parse(Carbon::parse($today_time_end)->subMinutes(30)->format('H:i'));
                $today_diff = $today_end->diffInMinutes($today_start);
                $today_tmp = Carbon::parse($today_start);
                for($i = 0; $i < ($today_diff/$duration); $i++){
                    $tmp_next = $today_tmp->addMinutes($duration);
                    $Hi_format = $tmp_next->format('H:i');
                    $notAvailableTimeSlot_time[] = $Hi_format;
                    //$tmp_raw[$Hi_format] = in_array($Hi_format, $blocked_slots)?'not_available':'available';
                }*/
                //var_dump($notAvailableTimeSlot_time); exit;
                //$notAvailableTimeSlot_time[] = [ $today_time_start, Carbon::parse($today_time_end)->subMinutes(30)->format('H:i') ];
                //$debug_notAvailableTimeSlot_time[] = [ $today_time_start, Carbon::parse($today_time_end)->subMinutes(30)->format('H:i') ];
                //dd($debug_notAvailableTimeSlot_time);
                //var_dump($debug_notAvailableTimeSlot_time); exit;
            }
            
        }    

        //var_dump(Input::has('schedule_id'));// exit;
        //$notAvailableTimeSlots = $this->cfpSchedule->availableTimeSlot($filter);
        //dd($notAvailableTimeSlots);
        //dd($notAvailableTimeSlots);
        //dd($schedule_type);
        

/*
        $notAvailableTimeSlot_time = [];
        if(count($notAvailableTimeSlots) > 0){
            foreach ($notAvailableTimeSlots as $notAvailableTimeSlot) {
                $notAvailableTimeSlot_time[] = [ Carbon::parse($notAvailableTimeSlot->schedule_start_date)->format('H:i'), Carbon::parse($notAvailableTimeSlot->schedule_end_date)->format('H:i') ];
            }
        }*/
        //dd($notAvailableTimeSlot_time);
        //$notAvailableTimeSlot_time = ['09:30', '13:00'];//example

        //dd($schedule_start_date.' '.$cfp_working_hour_start.', '.$schedule_start_date.' '.$cfp_working_hour_start.', '.$duration);
        $availableTimeSlot_time = getDatesFromRange($schedule_start_date.' '.$cfp_working_hour_start, $schedule_start_date.' '.$cfp_working_hour_end, $duration, $notAvailableTimeSlot_time);
        //dd($availableTimeSlot_time);

        /*foreach ($variable as $key => $value) {
            # code...
        }
        $timeSlot_times = array_map(function ($item) {
            return in_array($item, $notAvailableTimeSlot_time)?
        }, $availableTimeSlot_time);*/
        
        return [
            'duration' => $duration,
            'cfp_working_hour_start' => $cfp_working_hour_start,//'09:00',
            'cfp_working_hour_end' => $cfp_working_hour_end,//'17:00',
            'time_slots' => $availableTimeSlot_time,
            'notAvailableTimeSlot_time' => $notAvailableTimeSlot_time
        ];
        




        /*$default_role_cfp = config_db_cached('settings::default_role_cfp');
        $clients_raw = $this->user->findByName2($search_name, $default_role_cfp);
        $clients_arr = [];
        foreach($clients_raw as $client_raw){
            $clients_arr[] = [ 
                'id' => $client_raw->id.'__'.$client_raw->name,
                'name' => $client_raw->name
            ];
        }
        return $clients_arr;*/
    }

    public function cfpScheduleDayOff()
    {
        $attr = [ 
            'title' => trans('app.cfp_schedule_dayoff')
        ];
        $filter = Input::all(); 
        unset($filter['_token']);
        $page = Input::get('page', 1);
        $perPage = config_db_cached('settings::backend_per_page');
        $filter['cfp_id'] = Auth::user()->id;
        $pagiData = $this->cfpScheduleDayOff->paginate($page, $perPage, $filter);

        $cfp_schedule_day_off = new LengthAwarePaginator($pagiData->items, $pagiData->totalItems, $perPage, $page, [
            'path' => Paginator::resolveCurrentPath()
        ]);
        $cfp_schedule_day_off->setPath("");
        $cfp_schedule_day_off->appends($filter);

        return view('backend.cfpSchedule.dayoff', compact('cfp_schedule_day_off'));
    }

    public function cfpScheduleDayOffShow($id)
    {
        $data = $this->cfpScheduleDayOff->find($id);
        $data['cfp_id'] = $data->cfp_id.'__'.$data->name;
        $data['cfp_schedule_cut_off_start_date'] = \Carbon\Carbon::parse($data->cfp_schedule_cut_off_start_date)->format('d M Y');
        $data['cfp_schedule_cut_off_end_date'] = \Carbon\Carbon::parse($data->cfp_schedule_cut_off_end_date)->format('d M Y');
        $data['is_approval'] = $data->is_approval;
        $data['description'] = $data->description;

        return view('backend.cfpSchedule.dayoffshow', compact('data'));
    }

    public function cfpScheduleDayOffCreate()
    {
        if(!Entrust::can(['add_schedule_cfp_dayoff'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        return view('backend.cfpSchedule.dayoffcreate');
    }

    public function cfpScheduleDayOffStore()
    {
        try {
			$input = Input::all();
            $this->cfpScheduleDayOff->create($input);
            Notification::success( trans('app.cfp_schedule_dayoff_added') );
            return redirect('en/admin/cfp/schedule_dayoff');
        } catch (ValidationException $e) { //dd($e->getErrors());
            return view('backend.cfpSchedule.dayoffcreate')->withErrors($e->getErrors());
        }
    }

    public function cfpScheduleDayOffEdit($id)
    {
        $attr = [ 
            'title' => trans('app.cfp_schedule')
        ];
        if(!Entrust::can(['edit_schedule_cfp_dayoff'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $data = $this->cfpScheduleDayOff->find($id);
        $data['cfp_id'] = $data->cfp_id.'__'.$data->name;
        $data['cfp_schedule_day_off_start_date'] = \Carbon\Carbon::parse($data->cfp_schedule_day_off_start_date)->format('d M Y');
        $data['cfp_schedule_day_off_end_date'] = \Carbon\Carbon::parse($data->cfp_schedule_day_off_end_date)->format('d M Y');
        $data['is_approval'] = $data->is_approval;
        $data['description'] = $data->description;
        return view('backend.cfpSchedule.dayoffedit', compact('data'));
    }

    public function cfpScheduleDayOffUpdate($id)
    {
        try {   
            $input = Input::all();
            $this->cfpScheduleDayOff->update($id, $input);
            Notification::success( trans('app.cfp_schedule_dayoff_updated') );
            return redirect('en/admin/cfp/schedule_dayoff');
        } catch (ValidationException $e) {
            return view('backend.cfpSchedule.dayoffedit', [ 'id' => $id ])->withErrors($e->getErrors());
        }
    }

    public function cfpScheduleDayOffConfirmDelete($id)
    {
        $attr = [ 
            'title' => trans('app.cfp_schedule_dayoff')
        ];
        if(!Entrust::can(['delete_cfp_schedule_day_off'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $cfpScheduleDayOff = $this->cfpScheduleDayOff->find($id);
        return view('backend.cfpSchedule.dayoff-confirm-destroy', compact('cfpScheduleDayOff'));
    }

    public function cfpScheduleDayOffDelete($id)
    {
        $attr = [ 
            'title' => trans('app.cfp_schedule_dayoff')
        ];
        if(!Entrust::can(['delete_cfp_schedule_day_off'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        if($this->cfpScheduleDayOff->delete($id)){
            Notification::success(trans('app.cfp_schedule_dayoff_deleted'));
        }else{
            Notification::error(trans('app.delete_failed'));
        }
        
        return redirect('en/admin/cfp/schedule_dayoff');
    }

}
