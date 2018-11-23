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

use App\User;
use App\Repositories\Wallet\WalletRepository as Wallet;
use Carbon\Carbon;
use App\Repositories\PlanA\PlanARepository;
use App\Repositories\PlanB\PlanBRepository;
use App\Repositories\PlanAnalysis\PlanAnalysisRepository;
use App\Repositories\InterestRate\InterestRateRepository;
//use App\Repositories\Triangle\TriangleRepository;
use App\Models\TriangleLayer;
use App\Taxonomy;
use App\Repositories\ActionPlanCategory\ActionPlanCategoryRepository;
use App\Repositories\Taxonomy\TaxonomyInterface;
use App\Models\ActualInterestRate;

use App\Repositories\CashflowAnalysis\CashflowAnalysisRepository;
use App\Repositories\PortfolioAnalysis\PortfolioAnalysisRepository;

use App\Models\Goal;
use App\Models\ActiveVersionDetail;

class PlanController extends Controller {

	protected $cfpSchedule;
	protected $user;
	protected $planA;
	protected $planB;
	protected $planAnalysis;
	protected $interestRate;
	protected $actionPlanCategory;
	protected $cashflowAnalysis;
	protected $portfolioAnalysis;
	//protected $triangle;

    public function __construct(Wallet $wallet, UserInterface $user, PlanARepository $planA, PlanBRepository $planB, PlanAnalysisRepository $planAnalysis, InterestRateRepository $interestRate, ActionPlanCategoryRepository $actionPlanCategory, TaxonomyInterface $taxonomy, CashflowAnalysisRepository $cashflowAnalysis, PortfolioAnalysisRepository $portfolioAnalysis) {
        
        $this->user = $user;
        $this->wallet = $wallet;
        $this->planA = $planA;
        $this->planB = $planB;
        $this->planAnalysis = $planAnalysis;
		$this->InterestRate = $interestRate;
		$this->actionPlanCategory = $actionPlanCategory;
		$this->taxonomy = $taxonomy;
		$this->cashflowAnalysis = $cashflowAnalysis;
		$this->portfolioAnalysis = $portfolioAnalysis;
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
	{ 
		$transaction_type_id = $request->input('transaction_type_id');
		//$amount = unformat_money($request->input('amount'));
        $category_id = $request->input('category_id');
        
        $notes = $request->input('notes');
        //$transaction_date = Carbon::parse($request->input('transaction_date'))->format('Y-m-d');

        $transaction_date = trim($request->input('transaction_date')) == ''?'':Carbon::parse($request->input('transaction_date'))->format('Y-m-d');
        $category_type_id = $request->input('category_type_id'); //dd($request->input('amount'));
        $amount = trim($request->input('amount')) == '' || $request->input['amount'] === 0?'':unformat_money($request->input('amount'));
       //dd($amount);
		$user_id = $request->input('user_id');
		try {
			$data = $this->wallet->create([
				'user_id' => $user_id,
				'transaction_type' => $transaction_type_id,
	            'amount' => $amount,
	            'wallet_category' => $category_id,
	            'category_type' => $category_type_id,
	            'notes' => $notes,
	            'transaction_date' => $transaction_date,
	            'created_by' => $user_id, 
	            'created_at' => Carbon::now(), 
	            'updated_by' => $user_id, 
	            'updated_at' => Carbon::now(),
	            'record_flag' => 'N'
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
		$transaction_type_id = $request->input('transaction_type_id');
		//$amount = unformat_money($request->input('amount'));
        $category_id = $request->input('category_id');
        
        $notes = $request->input('notes');
        //$transaction_date = Carbon::parse($request->input('transaction_date'))->format('Y-m-d');

        $transaction_date = trim($request->input('transaction_date')) == ''?'':Carbon::parse($request->input('transaction_date'))->format('Y-m-d');
        $category_type_id = $request->input('category_type_id'); //dd($request->input('amount'));
        $amount = trim($request->input('amount')) == '' || $request->input['amount'] === 0?'':unformat_money($request->input('amount'));
       //dd($amount);
		$user_id = $request->input('user_id');
		try {
			$data = $this->wallet->update($request->input('id'), [
				'user_id' => $user_id,
				'transaction_type' => $transaction_type_id,
	            'amount' => $amount,
	            'wallet_category' => $category_id,
	            'category_type' => $category_type_id,
	            'notes' => $notes,
	            'transaction_date' => $transaction_date,
	            'updated_by' => $user_id, 
	            'updated_at' => Carbon::now(),
	            'record_flag' => 'U'
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
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy() //delete belom selesai , terakhir ngerjain tanggal 10 oct 2017
	{
		try {
			$wallet = $this->wallet->find(Input::get('id'));
	        if($wallet['id']){
	        	$wallet->delete();
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

	public function planSimulation(Request $request){

        $usia = $request->input('usia');
        $usia_pensiun = $request->input('usia_pensiun');
        $lama_tahun_investasi = $usia_pensiun-$usia;
        $lama_bulan_investasi = $lama_tahun_investasi*12;
        

        $inflasi = config_db_cached('settings::rate_inflation');
        $pv = $request->input('pendapatan_pensiun_perbulan');
        $pv_raw = $pv;

        //dd(calc_inf_fv('ori', $pv, $inflasi, $lama_tahun_investasi, true));

        $deposito_rate = config_db_cached('settings::rate_deposit');
        $suffix_data_name = 'needinv';
        $need_inv_pv = $pv_raw/(($deposito_rate/100)/12);
        
        $res['lama_tahun_investasi'] = $lama_tahun_investasi;
        $res['lama_bulan_investasi'] = $lama_bulan_investasi;
        //echo $suffix_data_name.', '.$need_inv_pv.', '.$inflasi.', '.$lama_tahun_investasi.', '.false.'<br />';exit;
        $res += calc_inf_fv($suffix_data_name, $need_inv_pv, $inflasi, $lama_tahun_investasi, false);//inflasi seharusnya dihitung dalam tahun
        $res[$suffix_data_name]['rate_inv_'.$suffix_data_name] = $deposito_rate;
        //dd($res);
        $res += calc_ins('planprotection', $res);
        $res += add_ins_html('planprotection', $res);
        //dd($res);

        $inv_rate_options = [];
        $inv_rate_options = $this->InterestRate->all();
        //dd($inv_rate_options->toArray());
        /*if(!is_null($inv_rate_options_raw)){
        	foreach ($inv_rate_options_raw as $key => $inv_rate_option_raw) {
        		$inv_rate_options
        	}
        }*/

        //pilih investasi ---> seharusnya pakai helper aja. sudah ada saat ini, cek aja
        $inv_rate_optionsx = [
            0 => [
                'bgcolor' => '#FF0091',
                'bgcolor2' => '#9C005C',
                'product' => 'Deposito',
                'rates' => [
                    5, 6,7,8
                ]
            ],
            1 => [
                'bgcolor' => '#00BEFF',
                'bgcolor2' => '#007193',
                'product' => 'Government Bond',
                'rates' => [
                    9
                ]
            ],
            2 => [
                'bgcolor' => '#00FC12',
                'bgcolor2' => '#019300',
                'product' => 'Corporate Bond',
                'rates' => [
                    10,11,12
                ]
            ],
            3 => [
                'bgcolor' => '#FFEA00',
                'bgcolor2' => '#B8B700',
                'product' => 'Money Market',
                'rates' => [
                    13
                ]
            ],
            4 => [
                'bgcolor' => '#FF7800',
                'bgcolor2' => '#B84800',
                'product' => 'Mutual Fund Mix',
                'rates' => [
                    14
                ]
            ],
            5 => [
                'bgcolor' => '#AE00FF',
                'bgcolor2' => '#610093',
                'product' => 'Mutual Fund Equity',
                'rates' => [
                    15,16,17,18
                ]
            ]
        ];
        /*$arr_rate_options = [];
        $arr_rate_idx = 0;
        foreach ($inv_rate_options as $inv_rate_option) {
            foreach ($inv_rate_option['rates'] as $idx_rate => $rate) {
                $arr_rate_options[$arr_rate_idx]['product'] = $inv_rate_option['product'];
                $arr_rate_options[$arr_rate_idx]['bgcolor'] = $inv_rate_option['bgcolor'];
                $arr_rate_options[$arr_rate_idx]['rate'] = $rate;
                $arr_rate_options[$arr_rate_idx]['details'] = calc_inv_pv_getPayment(slugify($inv_rate_option['product'], '_').'_'.$rate, $rate, $res);
                $arr_rate_idx++;
            }
        }

        $res['income_simulation'] = $arr_rate_options;*/
        $res['income_simulation'] = is_null($inv_rate_options)?[]:income_simulation_rate_options($inv_rate_options, $res);
        return response()->json([
			'result' => 'success',
			'data' => $res
		]);
    }

    public function storePlanA(Request $request){
		try {
			$data = $this->planA->create($request->all());
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

	public function destroyPlanA(Request $request){
		try {
			$data = $this->planA->delete($request->all());
            return response()->json([
				'result' => 'success'
			]);
        } catch (ValidationException $e) {
            return response()->json([
					'result' => 'error',
					'alerts' => $e->getErrors()
			]);
        }
	}

	public function showPlanA(Request $request){
		$data = $this->planA->showByVersion($request);
		if(isset($request['modules']) && in_array('actual_growth', $request['modules']) && isset($data['plan_a']) && count($data['plan_a'])){

			$plan_perbulan = $data['plan_a'][0]['plan_perbulan'];
			$plan_rate = $data['plan_a'][0]['income_simulation']['bunga_investasi_pertahun'];
			$actual_interest_rates = $data['plan_a'][0]['income_simulation']['actual_interest_rates'];

			$safe_actual_interest_rates = [];
			foreach ($actual_interest_rates as $actual_interest_rate) {
				$safe_actual_interest_rates[Carbon::parse($actual_interest_rate->period)->format('Y-m')] = $actual_interest_rate->rate;
			}
			//dd($safe_actual_interest_rates);
			$activated_at = $data['plan_a'][0]['detail_activation_latest']['created_at'];
			//$month_activated_at = $activated_at->startOfMonth(); //dd($month_activated_at);
			$lama_bulan_investasi = 12; //dd($actual_interest_rates);
			//$actualInterestRates = ActualInterestRate::where('interest_rate_id', $interest_rate_id)
			//->where('record_flag', '<>', 'D')->lists('rate', 'period'); dd($actualInterestRates);
			$curr_date = Carbon::now()->startOfYear()->format('Y-m-d'); //dd($curr_date);
			for($i=0;$i<$lama_bulan_investasi;$i++){
				$key_rate_month = Carbon::parse($curr_date)->addMonths($i)->format('Y-m');
				//dd($key_rate_month);
				$plan_rates[$key_rate_month] = $plan_rate;
				$actual_rates[$key_rate_month] = (isset($safe_actual_interest_rates[$key_rate_month]))?$safe_actual_interest_rates[$key_rate_month]:0;
			} //dd($actual_rates);
			$plan_growth_simulation = calc_inv_dynamicRate($plan_perbulan, $plan_rates, ['lama_bulan_investasi' => $lama_bulan_investasi]);
			$actual_growth_simulation = calc_inv_dynamicRate($plan_perbulan, $actual_rates, ['lama_bulan_investasi' => $lama_bulan_investasi]);
			$data['actual_growth'] = [
				'plan' => $plan_growth_simulation,
				'actual' => $actual_growth_simulation
			];
		}
        return response()->json([
			'result' => 'success',
			'data' => $data
		]);
	}

	public function storePlanB(Request $request){
		try {
			$data = $this->planB->create($request->all());
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

	public function destroyPlanB(Request $request){
		try {
			$data = $this->planB->delete($request->all());
            return response()->json([
				'result' => 'success'
			]);
        } catch (ValidationException $e) {
            return response()->json([
					'result' => 'error',
					'alerts' => $e->getErrors()
			]);
        }
	}

	public function showPlanB(Request $request){
		$data = $this->planB->showByVersion($request);
        return response()->json([
			'result' => 'success',
			'data' => $data
		]);
	}

	public function storePlanAnalysis(Request $request){
		try {
			$input = $request->all();
			$user_id = $input['user_id'];
			$data = $this->planAnalysis->create($input);
			return response()->json([
				'result' => 'success'
			]);
        } catch (ValidationException $e) {
            return response()->json([
				'result' => 'error',
				'alerts' => $e->getErrors()
			]);
        }
	}

	public function showPlanAnalysis(Request $request){
		$data = $this->planAnalysis->showByVersion($request);
        return response()->json([
			'result' => 'success',
			'data' => $data
		]);
	}

	public function plans(Request $request){
		$data = $this->planAnalysis->showAllDraft($request);
        return response()->json([
			'result' => 'success',
			'data' => $data
		]);
	}

	public function plansJUNK(Request $request){
		$user_id = $request->get('user_id'); //dd($user_id);
		$a_plans_raw = $this->planA->getAllBy($request); //dd($a_plans_raw->toArray());
		$a_plans = [];// dd($a_plans_raw);
		foreach ($a_plans_raw as $a_plan_raw) {// dd($a_plan->id);
			$a_plans[] = $this->planA->showByVersion([
				'user_id' => $user_id,
				'plan_id' => $a_plan_raw->id,
				'version' => 'last',
				'modules' => [ 'plan_a', 'income_simulations', 'plan_protections', 'insurance_coverages' ]
			]);
		}

		//dd($a_plans_raw);
		//$data = array_merge($a_plans_raw);
        return response()->json([
			'result' => 'success',
			'data' => $a_plans
		]);
	}

	public function triangleLayers(Request $request){
		//\DB::enableQueryLog();
		/*$layers = TriangleLayer::select('triangle_layers.id as id', 'triangle_layers.title', 'triangle_layers.description', 'triangle_layers.layers.*')->with(['layerDetails' => function($q){
			$q->select('step_1','step_2','step_3');
		}])->orderBy('stack_number', 'DESC')
		->get();*/
		//dd(\DB::getQueryLog());
		$layers = TriangleLayer::with('layerDetails')->orderBy('stack_number', 'DESC')
		->get();
		//dd($layers->toArray());
		$all_layers = [];
		foreach($layers as $layer_id => $layer){
			$all_layers[$layer_id]['title'] = $layer->title;
			$all_layers[$layer_id]['description'] = $layer->description;
			$all_layers[$layer_id]['stack_number'] = $layer->stack_number;

			if($layer->layerDetails){
				foreach($layer->layerDetails as $id_dtl => $dtl){
					//$all_layers[$layer_id]['next_step'][$id_dtl]['type'] = $dtl->step_1;
					$all_layers[$layer_id]['type'] = $dtl->step_1;
					if($dtl->step_1 == 'plan_type'){
						$all_layers[$layer_id]['next_step'][$id_dtl]['layer_detail_id'] = $dtl->id;
						$all_layers[$layer_id]['next_step'][$id_dtl]['plan_name'] = $dtl->step_2;
					}else{
						$taxo = Taxonomy::where('id', $dtl->step_2)->first();
						$all_layers[$layer_id]['next_step'][$id_dtl]['layer_detail_id'] = $dtl->id;
						$all_layers[$layer_id]['next_step'][$id_dtl]['goal_name'] = $taxo->title;
						$all_layers[$layer_id]['next_step'][$id_dtl]['next_step']['plan_name'] = $dtl->step_3;
					}
				}
			}
		}
		return response()->json([
			'result' => 'success',
			'data' => $all_layers
		]);
	}

	public function actionPlanCategories(Request $request){
		$data = $this->actionPlanCategory->all($request);
        return response()->json([
			'result' => 'success',
			'data' => $data
		]);
	}

	public function getActionPlans()
	{
		$actionplans_raw = $this->taxonomy->getTermsByPostType('action_plan')->toHierarchy(); 
		$actionplans = [];
		foreach ($actionplans_raw as $actionplan_raw) {
			$actionplans[] = $actionplan_raw;
		}
		return response()->json([
				'result' => 'success',
				'data' => $actionplans
		]);
	}

	public function planAnalysisView($email_web_path){
		$pre_item = ActiveVersionDetail::where('email_web_path', $email_web_path)->first();
		
		if(is_null($pre_item)){
			abort(404);
		}else{
			$p_item = $this->planAnalysis->showByVersion([
				'user_id' => $pre_item->user_id,
				'version' => $pre_item->version
			]);

			$user_id = $pre_item->user_id;
			$user_dtl = $this->user->findWithMetas($user_id);
			$p_created_at = $pre_item->created_at;//plan analyis

			$start_date = Carbon::parse($p_created_at)->startOfMonth()->format('Y-m-d 00:00:00');
			$end_date_check_version = $p_created_at;
			//dd('created_at >= '.$start_date.' , created_at <= '.$end_date_check_version);
			$maxApprovedActiveVersion_ca = ActiveVersionDetail::where('created_at', '>=', $start_date)
			->where('created_at', '<=', $end_date_check_version)
			->where('active_version_key', 'financialCheckup_cashflowAnalysis')
			->where('user_id', $user_id)
			->where('status', 'approved')->max('version');
			$module_version_ca = is_null($maxApprovedActiveVersion_ca)?'':$maxApprovedActiveVersion_ca;
			$ca_item = $this->cashflowAnalysis->showByVersion([
				'user_id' => $user_id,
				'version' => $module_version_ca
			]);
		
			$maxApprovedActiveVersion_pa = ActiveVersionDetail::where('created_at', '>=', $start_date)
			->where('created_at', '<=', $end_date_check_version)
			->where('active_version_key', 'financialCheckup_portfolioAnalysis')
			->where('user_id', $user_id)
			->where('status', 'approved')->max('version');
			$module_version_pa = is_null($maxApprovedActiveVersion_pa)?'':$maxApprovedActiveVersion_pa;
			$pa_item = $this->portfolioAnalysis->showByVersion([
				'user_id' => $user_id,
				'version' => $module_version_pa
			]);
			
			return view('frontend.emailWeb.planAnalysis', compact('p_item', 'ca_item', 'pa_item', 'user_dtl'));
		}
	}
}
