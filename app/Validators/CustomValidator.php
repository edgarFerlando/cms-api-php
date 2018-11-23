<?php namespace App\Validators;

use Illuminate\Validation\Validator;
use App\Repositories\CfpSchedule\CfpScheduleRepository;
use App\Models\CfpSchedule;
use Input;
use Carbon\Carbon;
use App\Taxonomy;
use App\Models\EomBalance;
use DB;
use App\Models\ActualInterestRate;
use App\User;
use App\BankAccount;
use Log;

class CustomValidator extends Validator {

    protected $cfpSchedule;
    
    public function validateIsScheduleExpired($attribute, $value, $parameters){
        $schedule_id = $parameters[0];
        $schedule = CfpSchedule::find($schedule_id);
        if(!is_null($schedule)){
            $schedule_start_date_plus_spare = $schedule->schedule_start_date_plus_spare;
            if(strtotime($schedule_start_date_plus_spare) >= strtotime(Carbon::now())){
                return true;
            }else{
                return false;
            }
        }
        return false;
    }

    public function validateWorkingHours($attribute, $value, $parameters)
    { 	
        $duration = 30;
        $cfp_working_hour_start = config_db_cached('settings::cfp_working_hour_start');
        $cfp_working_hour_end = config_db_cached('settings::cfp_working_hour_end');
    	$schedule_type = Input::get($parameters[0]);
    	switch ($schedule_type) {
            case 'meet_up':
                $spare_time = 120;//in minutes, 2 hours
                $schedule_type_duration = 60;
                break;
            default: //video_call, call
                $spare_time = 30;//in minutes, 30 minutes
                $schedule_type_duration = 30;
                break;
        }
        $start_Hi = Carbon::parse($value)->format('H:i');
        $start = strtotime($start_Hi) <= strtotime($cfp_working_hour_start)?Carbon::parse($start_Hi):Carbon::parse($start_Hi)->subMinutes($spare_time);

        $cfp_working_hour_end_adjust = Carbon::parse($cfp_working_hour_end)->subMinutes($schedule_type_duration)->format('H:i');//sudah disesuaikan dengan jenis schedule nya supaya bisa ditentukan batas akhir jam yang boleh dibook
        // codingan mas yudi "strtotime($start->format('H:i')) >= strtotime($cfp_working_hour_start) && strtotime($start_Hi) <= strtotime($cfp_working_hour_end_adjust)"
        
        if($start_Hi >= $cfp_working_hour_start && $start_Hi <= $cfp_working_hour_end){
            return true;//masih jam kerja
        }else{
            return false;//diluar jam kerja
        }
    }

    public function validateEndOfMonth($attribute, $value, $parameters)
    { 	
        $client_id = Input::get($parameters[0]);

        $client_cutoff_date = User::where('id', $client_id)->pluck('cutoff_date');
        if(is_null($client_cutoff_date)){
            return false;//client cutoff date not found
        }

        $now_Ym = Carbon::now()->format('Y-m');
        $now_Ymd = Carbon::now()->format('Y-m-d');
        $now_Ym31 = Carbon::now()->endOfMonth()->format('Y-m-'.$client_cutoff_date); 
        $period_Ym = $value;
        $period_Ym31 = Carbon::parse($period_Ym.'-01')->endOfMonth()->format('Y-m-d');
        $period_Ym31_byCutoff = Carbon::parse($period_Ym.'-01')->endOfMonth()->format('Y-m-'.$client_cutoff_date);//sesuai cutoff date
        //dd('period_Ym31_byCutoff '.$period_Ym31_byCutoff.' > period_Ym31 '.$period_Ym31);
        $period_Ym31 = $period_Ym31_byCutoff > $period_Ym31 ? $period_Ym31 : $period_Ym31_byCutoff;
        //dd('now_Ym31 '.$now_Ym31.' ---- period_Ym31 '.$period_Ym31);
        //dd($period_Ym.' == '.$now_Ym);

        if(strtotime($period_Ym) == strtotime($now_Ym)){
            Log::debug('periode sudah sama');
            //jika inputan periode adalah 2018-04 dan datetime now adalah tanggal 2018-04-06.
            //berarti periode nya sama yaitu apr 2018 atau 2018-04
            if(strtotime($now_Ymd) != strtotime($now_Ym31)){
                Log::debug('belum saatnya mengisi oem');
                //jika memenuhi kondisi ini
                //jika cut off date tanggal 31, saat store tanggal 6
                return false; //belum masa nya input eom balance
            }else{
                //jika cut off date tanggal 31, saat store tanggal 31 juga
                //jika cut off date tanggal 28, saat store tanggal 31 juga
                $eom_exist = EomBalance::where('period', $now_Ym31)
                    ->where('client_id', $client_id)
                    ->first();//check to database;
                if(is_null($eom_exist)){
                    Log::debug('oem periode '.$period_Ym.' belum ada di database');
                    //jika belum pernah input eom pada periode tersebut
                    return true;
                }else{
                    Log::debug('oem periode '.$period_Ym.' sudah ada di database');
                    return false;
                }
            }
        }elseif(strtotime($period_Ym) < strtotime($now_Ym)){
            Log::debug('periode yang diinput lebih kecil dari periode saat ini yaitu '.$now_Ym);
            //jika inputan periode adalah 2018-04 dan datetime now adalah tanggal 2018-05-10.
            //check to database
            $eom_exist = EomBalance::where('period', $period_Ym31)
            ->where('client_id', $client_id)
            ->first();//check to database;

            if(is_null($eom_exist)){
                Log::debug('oem periode '.$period_Ym.' belum ada di database');
                return true;
            }else{
                Log::debug('oem periode '.$period_Ym.' sudah ada di database');
                return false;//sudah ada di DB
            }
        }elseif(strtotime($period_Ym) > strtotime($now_Ym)){
            Log::debug('periode yang diinput lebih besar dari periode saat ini yaitu '.$now_Ym);
            return false;//belum masa nya input eom balance
        }
    }

    public function validateCannotBookingSameDay($attribute, $value, $parameters)
    {
        return false;
    }

    public function validateCannotBookingSaturday($attribute, $value, $parameters)
    {
        return false;
    }

    public function validateCannotBookingSunday($attribute, $value, $parameters)
    {
        return false;
    }

    public function validateTimeslotAvailable($attribute, $value, $parameters)
    { 	
        $duration = 30;
        $cfp_working_hour_start = config_db_cached('settings::cfp_working_hour_start');//'09:00:00';
        $cfp_working_hour_end = config_db_cached('settings::cfp_working_hour_end');//'17:00:00';
    	$cfp_id_raw = Input::get($parameters[0]);
        $cfp_raw = explode('__', $cfp_id_raw);
        $cfp_id = $cfp_raw[0];
    	$schedule_type = Input::get($parameters[1]); //dd($parameters);
        //$schedule_id = Input::get($parameters[2]);
        //if(is_null($schedule_id))//jika melalui API
        $schedule_id = $parameters[2]; //dd($schedule_id);
    	$this->cfpSchedule = new CfpScheduleRepository(new CfpSchedule);
    	$schedule_start_date = Carbon::parse($value)->format('y-m-d');

    	switch ($schedule_type) {
            case 'meet_up':
                $spare_time = 120;//in minutes, 2 hours
                $schedule_type_duration = 60;
                break;
            default: //video_call, call
                $spare_time = 30;//in minutes, 30 minutes
                $schedule_type_duration = 30;
                break;
        }

        $tmp_raw = [];
        $start_Hi = Carbon::parse($value)->format('H:i');
        $start = strtotime($start_Hi) <= strtotime($cfp_working_hour_start)?Carbon::parse($start_Hi):Carbon::parse($start_Hi)->subMinutes($spare_time);
      
	    $diff = strtotime($start_Hi) == strtotime($cfp_working_hour_start)?$schedule_type_duration:$spare_time+$schedule_type_duration;
	    $tmp = Carbon::parse($start);
	    //init
	    $Hi_format_init = $start->format('H:i');
        $schedule_start_date_formated = Carbon::parse($schedule_start_date)->format('d F Y');
    	$filter = [
            'schedule_type' => $schedule_type,
            'cfp_id' => $cfp_id,
            'schedule_start_date' => $schedule_start_date_formated,
            'record_flag_is_not' => 'D'
        ];
        //dd($schedule_id);
        if($schedule_id){
            $filter['schedule_id'] = $schedule_id;
        }

        $notAvailableTimeSlots = $this->cfpSchedule->availableTimeSlot($filter);
		$notAvailableTimeSlot_time = notAvailableTimeSlot_time([
			'duration' => $duration,
			'schedule_start_date_formated' => $schedule_start_date_formated,
			'notAvailableTimeSlots' => $notAvailableTimeSlots,
			'cfp_working_hour_start' => $cfp_working_hour_start,
			'cfp_working_hour_end' => $cfp_working_hour_end
		]);
        //dd($notAvailableTimeSlot_time);
        $blocked_slots_list = getDatesFromRange_notAvailable($schedule_start_date.' '.$cfp_working_hour_start, $schedule_start_date.' '.$cfp_working_hour_end, $duration, $notAvailableTimeSlot_time);
       //dd($blocked_slots_list);

        //$blocked_slots_list, harus kita buat exclude time milik cfp ini & schedule id ini
        //karena kondisinya edit
        $Hi_format_init = $start->format('H:i'); //dd($Hi_format_init);
        //$tmp_raw[$Hi_format_init] = in_array($Hi_format_init, $blocked_slots_list)?'not_available':'available';
        if(in_array($Hi_format_init, $blocked_slots_list))
	        	$tmp_raw[] = $Hi_format_init;
	    for($i = 0; $i < ($diff/$duration)-1; $i++){
	        $tmp_next = $tmp->addMinutes($duration);
	        $Hi_format = $tmp_next->format('H:i');
	        //$tmp_raw[] = $tmp_next->format('H:i');
	        if(in_array($Hi_format, $blocked_slots_list))
	        	$tmp_raw[] = $Hi_format;
        }
	    //dd($tmp_raw);
        return count($tmp_raw) > 0?false:true;
    }

    public function validateTaxoExist($attribute, $value, $parameters)
    {   
        $id = $value;
        $post_type = isset($parameters[0])?$parameters[0]:''; 	
        $depth = isset($parameters[1])?$parameters[1]:'';
        if($post_type != '' && $depth != ''){

            if (!is_numeric($id)) {
                return false;
            }

            $branch_is_exist = Taxonomy::where('post_type', $post_type)->where('depth', $depth)->where('id',$id)->first();
			if(!is_null($branch_is_exist)){
				return true;
            }
            return false;
        }else{
            return false;
        }
    }

    protected function replaceBefore($message, $attribute, $rule, $parameters)
    {
        //All custom placeholders that live in the message for
        //this rule should live in the first parameter of str_replace
        //dd($parameters);
        $param_date = $parameters[0];// dd($param_date);
        $age = Carbon::parse($param_date)->diffInYears(Carbon::now());// dd($age);
        if($attribute == 'date_of_birth')
            return str_replace([':age'], $age, $message);
    }

    public function validateCfpAvailable($attribute, $value, $parameters)
    {   
        $branch_id = $value;
        $default_role_id_cfp = config_db_cached('settings::default_role_id_cfp');

        $cfp_is_available = collect(DB::select('
            select users.id as cfp_id from users 
            left join user_metas on user_metas.user_id = users.id
            left join role_user on role_user.user_id = users.id
            where user_metas.meta_key = \'branch\' and 
            user_metas.meta_value = \''.$branch_id.'\' and 
            users.is_active = \'1\' and
            role_user.role_id = '.$default_role_id_cfp.'
            order by users.id asc limit 1'))->first();
        if(!is_null($cfp_is_available)){
            return true;
        }
        return false;
    }

    /**
     * Validate if a user's email is exist as non-deleted or not.
     *
     * @param  string|array  $attribute
     * @param  string  $value
     * @param  string|array  $parameters
     * @return bool
     */
    public function validateUserUniqueSoftDelete($attribute, $value, $parameters){
        $user_is_exist = User::where('email', $value)->where(function($q){
            $q->where('record_flag','')
                ->orWhere('record_flag', 'N');
        })->first();

        return is_null($user_is_exist); //Continue insert new user on FALSE
    }

    /**
     * Validate if a user's internet banking id is exist as non-deleted or not.
     *
     * @param  string|array  $attribute
     * @param  string  $value
     * @param  string|array  $parameters
     * @return bool
     */    
    public function validateInternetBankingIDUniqueForUser($attribute, $value, $parameters){
        $user_id = $parameters[0];
        //select bank_accounts where user_id=? and account_no=?        
        $account_is_exist = BankAccount::where('user_id',$user_id)->where('ibank_uid', $value)->first();

        return is_null($account_is_exist); //Continue insert new user on FALSE
    }

    public function validateUniqueActualInterestRate($attribute, $value, $parameters){
        $interest_rate_id = $parameters[0];
        $period = Carbon::parse($value)->format('Y-m-d');
        $items = ActualInterestRate::where('period', $period)
        ->where('interest_rate_id', $interest_rate_id)
        ->get(); //dd($items);
        return count($items) > 0?false:true;
    }

    public function validateCannotBookingNationalDayIndonesia($attribute, $value, $parameters)
    {
        $path = storage_path() . "/json/national_holiday.json";
        $json_holiday = json_decode(file_get_contents($path), true);
        $data_holiday = array();
        foreach($json_holiday['items'] as $holidays){
            $nested_data['summary'] = $holidays['summary'];
            $nested_data['start_holiday'] = $holidays['start']['date'];
            $data_holiday[] = $nested_data;
        }
        $date_holiday = array_column($data_holiday, 'start_holiday');
        $holiday_date = in_array(date('Y-m-d', strtotime($value)), $date_holiday);

        if($holiday_date != true){
            return true;
        }else{
            return false;
        }
    }
}