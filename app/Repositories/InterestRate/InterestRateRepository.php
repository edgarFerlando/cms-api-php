<?php namespace App\Repositories\InterestRate;

use App\Models\InterestRate;
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


class InterestRateRepository extends RepositoryAbstract implements InterestRateInterface, CrudableInterface {


    protected $perPage;
    protected $interestRate;
    /**
     * Rules
     *
     * @var array
     */
    protected static $rules;
    protected static $attributeNames;

    /**
     * @param InterestRate $interestRate
     */
    public function __construct(InterestRate $interestRate) {

        $config = Config::get('holiday');
        $this->perPage = $config['per_page'];
        $this->interestRate = $interestRate; 

        $rules_n_attributeNames = $this->rules();
        self::$rules = $rules_n_attributeNames['rules'];
        self::$attributeNames = $rules_n_attributeNames['attributeNames'];
    }

    public function rules(){
        $_rules = array();
        $setAttributeNames = array();
        $_rules['taxo_wallet_asset_id'] = 'required';
        $method = Request::method();
        switch($method)
        {
            case 'POST'://create 
            
                $_rules['rate'] = 'required|numeric|unique:interest_rates,rate,NULL,id,deleted_by,0';
            break;
            case 'PATCH'://update
                $interest_rate_id = Route::current()->getParameter('interest_rate');//interest_rate adalah id nya
                //dd($interest_rate_id);
                $old_data = $this->find($interest_rate_id); //dd(unformat_money_raw(Input::get('rate')));
                //dd($old_data);
                if(!is_null($old_data) && $old_data->rate == unformat_money_raw(Input::get('rate'))){
                    $_rules['rate'] = 'required|numeric';
                }else{
                    $_rules['rate'] = 'required|numeric|unique:interest_rates,rate';
                }
            break;
            default:
                $_rules['rate'] = 'required|numeric|unique:interest_rates,rate';
            break;
        }
        
        $_rules['bgcolor'] = 'required';
        $_rules['bgcolor2'] = 'required';
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

            $setAttributeNames['taxo_wallet_asset_id'] = trans('app.product');
            $setAttributeNames['bgcolor'] = trans('app.background_color');
            $setAttributeNames['bgcolor2'] = trans('app.background_color').' 2';
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
        return $this->interestRate->orderBy('rate', 'DESC')->where('deleted_by', 0)->get();//ambil yang belum dihapus
    }

    /**
     * @param $limit
     * @return mixed
     */
    public function getLastInterestRate($limit) {

        return $this->interestRate->orderBy('created_at', 'desc')->take($limit)->offset(0)->get();
    }

    /**
     * @return mixed
     */
    public function lists() {

        //return $this->interestRate->get()->lists('title', 'id');
        return $this->interestRate->all()->lists('title', 'id');
    }

    /**
     * Get paginated interestRates
     *
     * @param int $page Number of interestRates per page
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

        //$query = $this->interestRate->with('tags')->orderBy('created_at', 'DESC');
        $query = $this->interestRate
        ->select('interest_rates.*', 'uc.name as created_by_name', 'uu.name as updated_by_name')
        ->with('product')
        ->where('interest_rates.record_flag', '!=', 'D')
        ->orderBy('rate', 'DESC');

        $query->join('users as uc', 'uc.id', '=', 'interest_rates.created_by', 'left');
        $query->join('users as uu', 'uu.id', '=', 'interest_rates.updated_by', 'left');

        if(!$all) {
            $query->where('is_published', 1);
        }

        $interestRates = $query->skip($limit * ($page - 1))->take($limit)->get();

        $result->totalItems = $this->totalInterestRates($all);
        $result->items = $interestRates->all();
        return $result;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function find($id) {
        return $this->interestRate->where('record_flag', '!=', 'D')->find($id);
        //return $this->interestRate->with(['tags', 'category'])->findOrFail($id);
    }

    /**
     * @param $slug
     * @return mixed
     */
    /*public function getBySlug($slug) {
        //return $this->interestRate->with(['tags', 'category'])->where('slug', $slug)->first();
        return $this->interestRate->with(['category'])->where('slug', $slug)->first();
    }*/
     public function getBySlug($slug, $isPublished = false) {
        if($isPublished === true)
           return $this->interestRate->select('interestRates.id', 'interestRate_translations.slug')
            ->join('interestRate_translations', 'interestRates.id', '=', 'interestRate_translations.interestRate_id')
            ->where('slug', $slug)->where('is_published', true)->firstOrFail();

        return $this->interestRate->select('interestRates.id', 'interestRate_translations.slug')
            ->join('interestRate_translations', 'interestRates.id', '=', 'interestRate_translations.interestRate_id')
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
                    'taxo_wallet_asset_id' => $attributes['taxo_wallet_asset_id'],
                    'rate' => $attributes['rate'],
                    'bgcolor' => $attributes['bgcolor'],
                    'bgcolor2' => $attributes['bgcolor2'],
                    'created_by' => $user_id, 
                    'created_at' => Carbon::now(), 
                    'updated_by' => $user_id, 
                    'updated_at' => Carbon::now(),
                    'record_flag' => 'N'
                ];
            //}
            $this->interestRate->fill($t_attributes)->save();

            return true;
        }
        throw new ValidationException('Interest rate validation failed', $this->getErrors());
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
            $this->interestRate = $this->find($id);
            //foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
                $t_attributes=[//[$locale] = [
                    'taxo_wallet_asset_id' => $attributes['taxo_wallet_asset_id'],
                    'rate' => $attributes['rate'],
                    'bgcolor' => $attributes['bgcolor'],
                    'bgcolor2' => $attributes['bgcolor2'],
                    'updated_by' => $user_id, 
                    'updated_at' => Carbon::now(),
                    'record_flag' => 'U'
                ];
            // /}
            $this->interestRate->fill($t_attributes)->save();

            return true;
        }


        throw new ValidationException('InterestRate validation failed', $this->getErrors());
    }

    /**
     * @param $id
     * @return mixed|void
     */
    public function delete($id) {
        $user_id = Auth::user()->id;
        $interestRate = $this->interestRate->find($id);
        //$interestRate->tags()->detach();
        if($interestRate){
            //$interestRate->delete();
            $interestRate->record_flag = 'D';
            $interestRate->deleted_by = $user_id; 
            $interestRate->deleted_at = Carbon::now();
            $interestRate->save();
            return true;
        }
        throw new ValidationException('Interest Rate delete failed', $this->getErrors());
    }

    /**
     * @param $id
     * @return mixed
     */
    public function togglePublish($id) {

        $interestRate = $this->interestRate->find($id);

        $interestRate->is_published = ($interestRate->is_published) ? false : true;
        $interestRate->save();

        return Response::json(array('result' => 'success', 'changed' => ($interestRate->is_published) ? 1 : 0));
    }

    /**
     * @param $id
     * @return string
     */
    function getUrl($id) {

        $interestRate = $this->interestRate->findOrFail($id);
        return url('interestRate/' . $id . '/' . $interestRate->slug, $parameters = array(), $secure = null);
    }

    /**
     * Get total interestRate count
     * @param bool $all
     * @return mixed
     */
    protected function totalInterestRates($all = false) {

        if(!$all) {
            return $this->interestRate->where('is_published', 1)->count();
        }

        return $this->interestRate->where('record_flag', '!=', 'D')->count();
    }
}
