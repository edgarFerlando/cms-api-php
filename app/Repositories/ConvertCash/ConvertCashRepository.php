<?php namespace App\Repositories\ConvertCash;

use App\Models\ConvertCash;

use Auth;
use Config;
use Response;
use Illuminate\Support\Str;
use App\Repositories\RepositoryAbstract;
use App\Repositories\CrudableInterface as CrudableInterface;
use App\Repositories\RepositoryInterface as RepositoryInterface;
use App\Exceptions\Validation\ValidationException;
use App\Repositories\AbstractValidator as Validator;
use LaravelLocalization;
use Notification;
use App\User;
use Carbon\Carbon;
use Validator as Valid;
use Illuminate\Support\MessageBag;
use Input;
use DB;
use Request;
use App\Models\ActiveVersionPlanDetail;
use App\Models\ActiveVersionDetail;
use App\Models\AssetRepaymentPaid;

class ConvertCashRepository extends RepositoryAbstract implements ConvertCashInterface, CrudableInterface {

    protected $perPage;
    protected $convertCash;
    /**
     * Rules
     *
     * @var array
     */
    protected static $rules;
    protected static $attributeNames;

    public function __construct(ConvertCash $convertCash) {
        $this->convertCash = $convertCash;
        $rules_n_attributeNames = $this->rules();
        self::$rules = $rules_n_attributeNames['rules'];
        self::$attributeNames = $rules_n_attributeNames['attributeNames'];
    }

    public function rules(){
        $_rules = array();
        $setAttributeNames = array();
        
        $_rules['created_by_id'] = 'required';
        $_rules['client_id'] = 'required';
        $_rules['asset_repayment_id'] = 'required';
        //$_rules['active_version_plan_detail_id'] = 'required';

        $setAttributeNames['created_by_id'] = "created by";
        $setAttributeNames['client_id'] = "client";
        $setAttributeNames['asset_repayment_id'] = "asset repayment";
        //$setAttributeNames['active_version_plan_detail_id'] = "Plan detail";
        return [
            'rules' => $_rules,
            'attributeNames' => $setAttributeNames
        ]; 

    }

    public function all() {
    }

    public function paginate($page = 1, $limit = 10, $filter = array()) {
    }

    public function find($id) {
    }

    public function create($attributes) {
        if($this->isValid($attributes)) {
            DB::beginTransaction();
            $created_by_id = $attributes['created_by_id'];
            $client_id = $attributes['client_id'];
            $asset_repayment_id = $attributes['asset_repayment_id'];
            $catatan = $attributes['catatan'];
            
            //cek plan benar-benar berstatus F
            // $active_version_plan_detail_id = $attributes['active_version_plan_detail_id'];
            // $activeVersionPlanDetail = ActiveVersionPlanDetail::where('id', $active_version_plan_detail_id)
            // ->first();
            // if($activeVersionPlanDetail){
            //     if( isset($activeVersionPlanDetail) && $activeVersionPlanDetail->status == 'finished' ){
                    //\DB::enableQueryLog();
                    $start_date = Carbon::now()->startOfMonth()->format('Y-m-d 00:00:00');
                    $end_date_check_version = Carbon::now()->format('Y-m-d H:i:s');
                    $maxApprovedActiveVersion_portfolioAnalysis = ActiveVersionDetail::where('created_at', '>=', $start_date)
                    ->where('created_at', '<=', $end_date_check_version)
                    ->where('active_version_key', 'financialCheckup_portfolioAnalysis')
                    ->where('user_id', $client_id)
                    ->where('status', 'approved')->max('version');
                    //dd(\DB::getQueryLog());
                    
                    //get cicilan terbayar
                    $assetRepayPaid_cicilan = AssetRepaymentPaid::select('cicilan_terbayar', 'is_cash_converted')
                    ->where('asset_repayment_id', $asset_repayment_id)
                    ->where('version', $maxApprovedActiveVersion_portfolioAnalysis)->first();


                    if(!is_null($assetRepayPaid_cicilan)){
                        if(isset($assetRepayPaid_cicilan->cicilan_terbayar) && $assetRepayPaid_cicilan->cicilan_terbayar > 0){
                            if(isset($assetRepayPaid_cicilan->is_cash_converted) && $assetRepayPaid_cicilan->is_cash_converted === 1){
                                throw new ValidationException('Data already converted', [ 'data' => 'already_converted' ]);
                            }
                            $new_data = [
                                'client_id' => $client_id,
                                'portfolio_analysis_version' => $maxApprovedActiveVersion_portfolioAnalysis,
                                'asset_repayment_id' => $asset_repayment_id,
                                'jumlah' => $assetRepayPaid_cicilan,
                                'catatan' => $catatan,
                                'created_by' => $created_by_id,
                                'created_at' => Carbon::now(),
                                'updated_by' => $created_by_id,
                                'updated_at' => Carbon::now(),
                                'record_flag' => 'N'
                            ];

                            if($this->convertCash->insert($new_data)){
                                //update is cash converted
                                AssetRepaymentPaid::where('asset_repayment_id', $asset_repayment_id)
                                ->where('version', $maxApprovedActiveVersion_portfolioAnalysis)
                                ->update([
                                    'is_cash_converted' => 1
                                ]);
                            }
                        }
                    }else{
                        throw new ValidationException('Data empty', [ 'data' => 'value_empty' ]);
                    }
                // }else{
                //     throw new ValidationException('Data not found', [ 'data' => 'data_not_found' ]);
                // }
            }
            throw new ValidationException('Convert cash validation failed', $this->getErrors());
            
        //}
       // throw new ValidationException('Convert Cash validation failed', $this->getErrors());
    }

    public function update($id, $attributes) {
    }

    public function delete($id) {
    }

    protected function totalConvertCashs($filter = array()) {
    }
}
