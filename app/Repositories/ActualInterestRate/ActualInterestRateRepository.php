<?php namespace App\Repositories\ActualInterestRate;

use App\Models\ActualInterestRate;
use Config;
use Response;
use App\Category;
use Str;
use App\Repositories\RepositoryAbstract;
use App\Repositories\CrudableInterface as CrudableInterface;
use App\Repositories\RepositoryInterface as RepositoryInterface;
use App\Exceptions\Validation\ValidationException;
use App\Repositories\AbstractValidator as Validator;
use LaravelLocalization;
use Notification;
use Auth;
use Carbon\Carbon;
use Request;
use Route;
use Input;


class ActualInterestRateRepository extends RepositoryAbstract implements ActualInterestRateInterface, CrudableInterface {


    protected $perPage;
    protected $ActualInterestRate;
    /**
     * Rules
     *
     * @var array
     */
    protected static $rules;
    protected static $attributeNames;

    /**
     * @param ActualInterestRate $ActualInterestRate
     */
    public function __construct(ActualInterestRate $ActualInterestRate) {

        $config = Config::get('holiday');
        $this->perPage = $config['per_page'];
        $this->ActualInterestRate = $ActualInterestRate; 

        $rules_n_attributeNames = $this->rules();
        self::$rules = $rules_n_attributeNames['rules'];
        self::$attributeNames = $rules_n_attributeNames['attributeNames'];
    }

    public function rules(){
        $_rules = array();
        $setAttributeNames = array();
        $_rules['interest_rate_id'] = 'required';
        $_rules['rate'] = 'required|numeric';
        //$_rules['period'] = 'required|date_format:"M Y"';
        $method = Request::method();
        //Input::merge(array('period' => Carbon::parse(Input::get('top_up_amount'))->format('Y-m-01')));
        //$input = Input::all();
        
        switch($method)
        {
            case 'POST'://create 
                if(Input::has('interest_rate_id'))
                    $_rules['period'] = 'required|date_format:"M Y"|unique_actual_interest_rate:'.Input::get('interest_rate_id');
                //$_rules['rate'] = 'required|numeric|unique:actual_interest_rates,rate,NULL,id,deleted_by,0';
            break;
            case 'PATCH'://update
                $actual_interest_rate_id = Route::current()->getParameter('actual_interest_rate');//interest_rate adalah id nya
                //dd($actual_interest_rate_id);
                $old_data = $this->find($actual_interest_rate_id); //dd(unformat_money_raw(Input::get('rate')));
                //dd($old_data);
                //dd(unformat_money_raw(Input::get('rate')));
                $interest_rate_id = Input::get('interest_rate_id');
                $period = Carbon::parse(Input::get('period'))->format('Y-m-d');
                if(!is_null($old_data) && $old_data->interest_rate_id == $interest_rate_id && $old_data->period == $period){
                    $_rules['period'] = 'required|date_format:"M Y"';
                }else{
                    $_rules['period'] = 'required|date_format:"M Y"|unique_actual_interest_rate:'.Input::get('interest_rate_id');
                }
            break;
            default:
                //$_rules['rate'] = 'required|numeric|unique:actual_interest_rates,rate';
            break;
        }
        
        //$_rules['bgcolor'] = 'required';
        //$_rules['bgcolor2'] = 'required';
        /*foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
            $_rules['subject.'.$locale] = 'required';
            $_rules['body.'.$locale] = 'required';

            $setAttributeNames['subject.' . $locale] = trans('app.subject').' [ ' . $properties['native'].' ]';
            $setAttributeNames['body.' . $locale] = trans('app.content').' [ ' . $properties['native'].' ]';

            $setAttributeNames['email_template'] = trans('app.email_template');
        }*/
        //foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
            //$_rules['subject'] = 'required';
            //$_rules['imap_body(imap_stream, msg_number)'] = 'required';

            $setAttributeNames['interest_rate_id'] = trans('app.product');
            $setAttributeNames['rate'] = trans('app.rate');
        //}
        return [
            'rules' => $_rules,
            'attributeNames' => $setAttributeNames
        ];
    }

    /**
     * @return mixed
     */
    public function all() {
        return $this->ActualInterestRate->orderBy('rate', 'DESC')->where('deleted_by', 0)->get();//ambil yang belum dihapus
    }

    /**
     * @param $limit
     * @return mixed
     */
    public function getLastActualInterestRate($limit) {

        return $this->ActualInterestRate->orderBy('created_at', 'desc')->take($limit)->offset(0)->get();
    }

    /**
     * @return mixed
     */
    public function lists() {

        //return $this->ActualInterestRate->get()->lists('title', 'id');
        return $this->ActualInterestRate->all()->lists('title', 'id');
    }

    /**
     * Get paginated actualInterestRates
     *
     * @param int $page Number of actualInterestRates per page
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

        //$query = $this->ActualInterestRate->with('tags')->orderBy('created_at', 'DESC');
        $query = $this->ActualInterestRate
        ->select('actual_interest_rates.*', 'uc.name as created_by_name', 'uu.name as updated_by_name')
        ->with('interest_rate')
        ->where('actual_interest_rates.record_flag', '!=', 'D')
        ->orderBy('rate', 'DESC');

        $query->join('users as uc', 'uc.id', '=', 'actual_interest_rates.created_by', 'left');
        $query->join('users as uu', 'uu.id', '=', 'actual_interest_rates.updated_by', 'left');

        $actualInterestRates = $query->skip($limit * ($page - 1))->take($limit)->get();

        $result->totalItems = $this->totalActualInterestRates($all);
        $result->items = $actualInterestRates->all();
        return $result;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function find($id) {
        return $this->ActualInterestRate->where('record_flag', '!=', 'D')->find($id);
        //return $this->ActualInterestRate->with(['tags', 'category'])->findOrFail($id);
    }

    /**
     * @param $slug
     * @return mixed
     */
    /*public function getBySlug($slug) {
        //return $this->ActualInterestRate->with(['tags', 'category'])->where('slug', $slug)->first();
        return $this->ActualInterestRate->with(['category'])->where('slug', $slug)->first();
    }*/
     public function getBySlug($slug, $isPublished = false) {
        if($isPublished === true)
           return $this->ActualInterestRate->select('actualInterestRates.id', 'actualInterestRate_translations.slug')
            ->join('actualInterestRate_translations', 'actualInterestRates.id', '=', 'actualInterestRate_translations.actualInterestRate_id')
            ->where('slug', $slug)->where('is_published', true)->firstOrFail();

        return $this->ActualInterestRate->select('actualInterestRates.id', 'actualInterestRate_translations.slug')
            ->join('actualInterestRate_translations', 'actualInterestRates.id', '=', 'actualInterestRate_translations.actualInterestRate_id')
            ->where('slug', $slug)->firstOrFail();
    }

    /**
     * @param $attributes
     * @return bool|mixed
     * @throws \App\Exceptions\Validation\ValidationException
     */
    public function create($attributes) {
        $user_id = Auth::user()->id;
        $t_attributes = array();
        //$t_attributes['email_template_module_id'] = $attributes['email_template_module'];
        //dd($t_attributes);
        //dd($attributes['rate']);
        if($this->isValid($attributes)) {
            //foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
                $t_attributes += [ //[$locale] = [
                    'interest_rate_id' => $attributes['interest_rate_id'],
                    'rate' => $attributes['rate'],
                    'period' => Carbon::parse($attributes['period'])->format('Y-m-d'),
                    'created_by' => $user_id, 
                    'created_at' => Carbon::now(), 
                    'updated_by' => $user_id, 
                    'updated_at' => Carbon::now(),
                    'record_flag' => 'N'
                ];
            //}
            $this->ActualInterestRate->fill($t_attributes)->save();

            return true;
        }
        throw new ValidationException('Actual Interest rate validation failed', $this->getErrors());
    }

    /**
     * @param $id
     * @param $attributes
     * @return bool|mixed
     * @throws \App\Exceptions\Validation\ValidationException
     */
    public function update($id, $attributes) {
        $user_id = Auth::user()->id;
        $t_attributes = array();
        //$t_attributes['email_template_module_id'] = $attributes['email_template_module'];
        if($this->isValid($attributes)) {
            $this->ActualInterestRate = $this->find($id);
            //foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
                $t_attributes=[//[$locale] = [
                    'interest_rate_id' => $attributes['interest_rate_id'],
                    'rate' => $attributes['rate'],
                    'period' => Carbon::parse($attributes['period'])->format('Y-m-d'),
                    'updated_by' => $user_id, 
                    'updated_at' => Carbon::now(),
                    'record_flag' => 'U'
                ];
            // /}
            $this->ActualInterestRate->fill($t_attributes)->save();
            return true;
        }


        throw new ValidationException('Actual Interest Rate validation failed', $this->getErrors());
    }

    /**
     * @param $id
     * @return mixed|void
     */
    public function delete($id) {
        $user_id = Auth::user()->id;
        $ActualInterestRate = $this->ActualInterestRate->find($id);
        //$ActualInterestRate->tags()->detach();
        if($ActualInterestRate){
            //$ActualInterestRate->delete();
            $ActualInterestRate->record_flag = 'D';
            $ActualInterestRate->deleted_by = $user_id; 
            $ActualInterestRate->deleted_at = Carbon::now();
            $ActualInterestRate->save();
            return true;
        }
        throw new ValidationException('Actual Interest Rate delete failed', $this->getErrors());
    }

    /**
     * @param $id
     * @return mixed
     */
    public function togglePublish($id) {

        $ActualInterestRate = $this->ActualInterestRate->find($id);

        $ActualInterestRate->is_published = ($ActualInterestRate->is_published) ? false : true;
        $ActualInterestRate->save();

        return Response::json(array('result' => 'success', 'changed' => ($ActualInterestRate->is_published) ? 1 : 0));
    }

    /**
     * @param $id
     * @return string
     */
    function getUrl($id) {

        $ActualInterestRate = $this->ActualInterestRate->findOrFail($id);
        return url('ActualInterestRate/' . $id . '/' . $ActualInterestRate->slug, $parameters = array(), $secure = null);
    }

    /**
     * Get total ActualInterestRate count
     * @param bool $all
     * @return mixed
     */
    protected function totalActualInterestRates($all = false) {

        if(!$all) {
            return $this->ActualInterestRate->where('is_published', 1)->count();
        }

        return $this->ActualInterestRate->where('record_flag', '!=', 'D')->count();
    }
}
