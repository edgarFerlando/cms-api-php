<?php namespace App\Repositories\Cycle;

use App\Models\Cycle;
use Config;
use Response;
use Str;
use App\Repositories\RepositoryAbstract;
use App\Repositories\CrudableInterface as CrudableInterface;
use App\Repositories\RepositoryInterface as RepositoryInterface;
use App\Exceptions\Validation\ValidationException;
use App\Repositories\AbstractValidator as Validator;
use LaravelLocalization;
use Notification;
use App\Repositories\Cycle\CycleInterface;
use Carbon\Carbon;
use DB;


class CycleRepository extends RepositoryAbstract implements CycleInterface, CrudableInterface {

    protected $perPage;
    protected $cycles;
    /**
     * Rules
     *
     * @var array
     */
    protected static $rules;
    protected static $attributeNames;

    /**
     * @param Cycles $cycles
     */
    public function __construct(Cycle $cycle) {
        $this->cycle = $cycle;

        $rules_n_attributeNames = $this->rules();
        self::$rules = $rules_n_attributeNames['rules'];
        self::$attributeNames = $rules_n_attributeNames['attributeNames'];
    }

    public function rules(){
        $_rules = array();
        $setAttributeNames = array();
        return [
            'rules' => $_rules,
            'attributeNames' => $setAttributeNames
        ];
    }

    /**
     * @return mixed
     */
    public function all() {
        return $this->cycles->with('cyclesOption')->orderBy('created_at', 'DESC')->get();
    }

    public function is_full_cycle($client_id, $create_if_needed = false) {
        $free_consultation_limit = intval(config_db_cached('settings::free_consultation_limit'));
        $count_full_cycles = $this->cycle->where('client_id', $client_id)
        ->whereNotNull('cashflow_analysis_version_approved')
        ->whereNotNull('portfolio_analysis_version_approved')
        ->whereNotNull('plan_analysis_version_approved')
        ->whereNotNull('completed_at')
        ->count('id');
        if($count_full_cycles >= $free_consultation_limit){
            throw new ValidationException('Consultation validation failed', [ 'count_full_cycles' => $count_full_cycles, 'consultation' => 'Free consultation is over limit' ]);
        }
        
        $cycle = $this->cycle->where('client_id', $client_id)->whereNull('completed_at')->first();
        DB::beginTransaction();
        if(is_null($cycle)){
            if($create_if_needed === true){
                //create new cycle
                $cycle_saved = $this->cycle->create([
                    'client_id' => $client_id,
                    'started_at' => Carbon::now()
                ]);
                
                $cycle_id = $cycle_saved->id;
                $next_step = 'financialCheckup_cashflowAnalysis';
                $details = [
                    'cashflow_analysis_version_approved' => null,
                    'portfolio_analysis_version_approved' => null,
                    'plan_analysis_version_approved' => null
                ];
            }else{
                //get last completed cycle if exist
                $full_cycle = $this->cycle->where('client_id', $client_id)->whereNotNull('completed_at')->orderBy('id', 'desc')->first();
                
                
                $next_step = 'client_must_create_new_schedule';
                if(is_null($full_cycle)){
                    $cycle_id = null;
                    $details = null;
                }else{
                    $cycle_id = $full_cycle->id;
                    $details = [
                        'cashflow_analysis_version_approved' => $full_cycle->cashflow_analysis_version_approved,
                        'portfolio_analysis_version_approved' => $full_cycle->portfolio_analysis_version_approved,
                        'plan_analysis_version_approved' => $full_cycle->plan_analysis_version_approved,
                    ];
                }
            }
            
        }else{ //ada isi
            $cycle_id = $cycle->id;
            $details = [
                'cashflow_analysis_version_approved' => $cycle->cashflow_analysis_version_approved,
                'portfolio_analysis_version_approved' => $cycle->portfolio_analysis_version_approved,
                'plan_analysis_version_approved' => $cycle->plan_analysis_version_approved,
            ];
            //check latest step
            if(is_null($cycle->cashflow_analysis_version_approved) || $cycle->cashflow_analysis_version_approved === ''){
                $next_step = 'financialCheckup_cashflowAnalysis';
            }elseif(is_null($cycle->portfolio_analysis_version_approved) || $cycle->portfolio_analysis_version_approved === ''){
                $next_step = 'financialCheckup_portfolioAnalysis';
            }elseif(is_null($cycle->plan_analysis_version_approved) || $cycle->plan_analysis_version_approved === ''){
                $next_step = 'planAnalysis';
            }else{
                $next_step = 'error__completed_at_is_empty';
            }
        }
        
        DB::commit();
        return [
            'count_full_cycles' => $count_full_cycles,
            'cycle_id' => $cycle_id,
            'next_step' => $next_step,
            'details' => $details
        ];
    }

    /**
     * @param $limit
     * @return mixed
     */
    public function getLastCycles($limit) {

        return $this->cycles->orderBy('created_at', 'desc')->take($limit)->offset(0)->get();
    }

    /**
     * @return mixed
     */
    public function lists() {
        return $this->cycles->all()->lists('name', 'id');
    }

    /**
     * Get paginated cycless
     *
     * @param int $page Number of cycless per page
     * @param int $limit Results per page
     * @param boolean $all Show published or all
     * @return StdClass Object with $items and $totalItems for pagination
     */
    public function paginate($page = 1, $limit = 10, $all = false) {

        $result = new \StdClass;
        $result->page = $page;
        $result->limit = $limit;
        $result->totalItems = 0;
        $result->items = array();

        $query = $this->cycles->orderBy('created_at', 'DESC');

        if(!$all) {
            $query->where('is_published', 1);
        }

        $cycless = $query->skip($limit * ($page - 1))->take($limit)->get();

        $result->totalItems = $this->totalCycless($all);
        $result->items = $cycless->all();

        return $result;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function find($id) {
        return $this->cycles->findOrFail($id);
    }

    /**
     * @param $attributes
     * @return bool|mixed
     * @throws \App\Exceptions\Validation\ValidationException
     */
    public function create($attributes) {
        if($this->isValid($attributes)) {
            $t_attributes = array();
            foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
                $t_attributes[$locale] = [
                    'name' => $attributes['name'][$locale]
                ];
            }
            $this->cycles->fill($t_attributes)->save();

            return true;
        }
        throw new ValidationException('Product attribute validation failed', $this->getErrors());
    }


    /**
     * @param $id
     * @param $attributes
     * @return bool|mixed
     * @throws \App\Exceptions\Validation\ValidationException
     */
    public function update($id, $attributes) {
        if($this->isValid($attributes)) {
            $t_attributes = array();
            $this->cycles = $this->find($id);
            foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
                $t_attributes[$locale] = [
                    'name' => $attributes['name'][$locale]
                ];
            }
            $this->cycles->fill($t_attributes)->save();
            return true;
        }
        throw new ValidationException('Product attribute validation failed', $this->getErrors());
    }

    /**
     * @param $id
     * @return mixed|void
     */
    public function delete($id) {
        $cycles = $this->cycles->findOrFail($id);
        $cycles->delete();
    }

    /**
     * Get total cycles count
     * @param bool $all
     * @return mixed
     */
    protected function totalCycless($all = false) {
        return $this->cycles->count();
    }
}
