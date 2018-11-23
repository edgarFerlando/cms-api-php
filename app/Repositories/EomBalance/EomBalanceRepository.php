<?php namespace App\Repositories\EomBalance;

use App\Models\EomBalance;

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

use Validator as Valid;
use Carbon\Carbon;


class EomBalanceRepository extends RepositoryAbstract implements EomBalanceInterface, CrudableInterface {

    protected $perPage;
    protected $eomBalance;
    /**
     * Rules
     *
     * @var array
     */
    protected static $rules;
    protected static $attributeNames;

    /**
     * @param ProductAttribute $productAttribute
     */
    public function __construct(EomBalance $eomBalance) {

        $config = Config::get('holiday');
        $this->perPage = $config['per_page'];
        $this->eomBalance = $eomBalance;

        $rules_n_attributeNames = $this->rules();
        self::$rules = $rules_n_attributeNames['rules'];
        self::$attributeNames = $rules_n_attributeNames['attributeNames'];
    }

    public function rules(){
        $_rules = array();
        $setAttributeNames = array();

        $_rules['client_id'] = 'required';
        $_rules['period'] = 'required|date_format:"Y-m"|end_of_month:client_id';
        $_rules['balance'] = 'required';

        $setAttributeNames['client_id'] = trans('app.client_id');
        $setAttributeNames['period'] = trans('app.period');
        $setAttributeNames['balance'] = trans('app.balance');

        return [
            'rules' => $_rules,
            'attributeNames' => $setAttributeNames
        ]; 

    }

    public function all() {
        return $this->eomBalance->orderBy('created_by', 'DESC')->get();
    }

    public function paginate($page = 1, $limit = 10, $all = false) {

        $result = new \StdClass;
        $result->page = $page;
        $result->limit = $limit;
        $result->totalItems = 0;
        $result->items = array();

        $query = $this->eomBalance->with('createdBy', 'updatedBy')->orderBy('created_by', 'DESC');
        //dd($query);
        if(!$all) {
            $query->where('is_published', 1);
        }

        //\DB::enableQueryLog();

        $eomBalances = $query->skip($limit * ($page - 1))->take($limit)->get();

        //dd(\DB::getQueryLog());
        //dd($categoryCodes);

        $result->totalItems = $this->totalEomBalances($all);
        $result->items = $eomBalances->all();

        /*foreach ($result->items as $key => $item) {
            $result->items[$key]->grades = $item;

            $userCreate = User::find($item->created_by);

            $result->items[$key]->userCreate = $userCreate->name;

            if(!is_null($item->updated_by))
            {
                $userUpdate = User::find($item->updated_by);
                $result->items[$key]->userUpdate = $userUpdate->name;
            }
        };*/
        
        //dd($result);

        return $result;
    }

    public function find($id) {

        $rules['id'] = 'required|exists:eomBalances,id';

        $data['id'] = $id;

        $validator = Valid::make($data, $rules);

        if ($validator->fails())
        { 
            //dd($validator->errors()->first('id'));
            $data['id_data'] = $validator->errors()->first('id');
            return $data;
        }

        return $this->eomBalance->findOrFail($id);
    }

    public function create($attributes) {

        if($this->isValid($attributes)) {

            $client_id = $attributes['client_id'];
            $input_type = $attributes['input_type'];
            $period = $attributes['period'];
            $period_last_day_of_month = Carbon::parse($period.'-01')->endOfMonth()->format('Y-m-d');
            $balance = $attributes['balance'];
            $notes = $attributes['notes'];

            $eomBalance = $this->eomBalance->create([
                'client_id' => $client_id,
                'input_type' => $input_type,
                'period' => $period_last_day_of_month,
                'balance' => $balance,
                'notes' => $notes,
                'created_by' => $client_id, 
                'created_at' => Carbon::now(), 
                'updated_by' => $client_id, 
                'updated_at' => Carbon::now(),
                'record_flag' => 'N'
            ]);
            //dd($schedule);
            return $eomBalance;
        }
        throw new ValidationException('EomBalance attribute validation failed', $this->getErrors());
    }

    public function update($id, $attributes) {

        $attributes['id'] = $id;

        $rules['id'] = 'required|exists:eomBalances,id';

        $validator = Valid::make($attributes, $rules);

        if ($validator->fails())
        { 
            throw new ValidationException('eomBalance attribute validation failed', $validator->errors());
        }

        if($this->isValid($attributes)) {
            $t_attributes = array();

            $user_id = Auth::user()->id;

            $this->eomBalance = $this->find($id);

            $t_attributes = [
                'eomBalance_name' => $attributes['eomBalance_name'],
                'thumb_path' => getImagePath(str_replace(url('/'), '', $attributes['eomBalance_thumb'])),
                'icon_path' => getImagePath(str_replace(url('/'), '', $attributes['eomBalance_icon'])),
                'position_under_grade_id' => $attributes['eomBalance_position_under_grade'],
                'updated_by' => $user_id, 
                'updated_at' => Carbon::now(),
                'record_flag' => 'U'
            ];
            //dd($attributes);
            /*$t_attributes['eomBalance_name'] = $attributes['eomBalance_name'];
            $t_attributes['updated_by'] = $user;
            $t_attributes['updated_on'] = date("Y-m-d H:i:s");
            $t_attributes['record_flag'] = 'U';*/
            /*
            $t_attributes['testimonial'] = Str::slug($attributes['name'][getLang()], '_');
            foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
                $t_attributes[$locale] = [
                    'name' => $attributes['name'][$locale]
                ];
            }
            */
            $this->eomBalance->fill($t_attributes)->save();
            return true;
        }
        throw new ValidationException('EomBalance attribute validation failed', $this->getErrors());
    }

    public function delete($id) {

        $rules['id'] = 'required|exists:eomBalances,id';

        $attributes['id'] = $id;

        $validator = Valid::make($attributes, $rules);

        if ($validator->fails())
        { 
            throw new ValidationException('EomBalance attribute validation failed', $validator->errors());
        }

        $eomBalance = $this->eomBalance->findOrFail($id);
        $eomBalance->delete();
    }

    protected function totalEomBalances($all = false) {
        return $this->eomBalance->count();
    }
}
