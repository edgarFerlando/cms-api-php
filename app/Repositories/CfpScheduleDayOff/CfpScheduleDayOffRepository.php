<?php namespace App\Repositories\CfpScheduleDayOff;
use App\Models\CfpScheduleDayOff;
use Auth;
use App\Repositories\RepositoryAbstract;
use App\Repositories\CrudableInterface as CrudableInterface;
use App\Repositories\RepositoryInterface as RepositoryInterface;
use Config;
use App\Exceptions\Validation\ValidationException;
use App\Repositories\AbstractValidator as Validator;
use Notification;
use App\User;
use Carbon\Carbon;

class CfpScheduleDayOffRepository extends RepositoryAbstract implements CfpScheduleDayOffInterface, CrudableInterface {

    protected $perPage;
    protected $cfpScheduleDayOff;

    protected static $rules;
    protected static $attributeNames;

    public function __construct(CfpScheduleDayOff $cfpScheduleDayOff)
    {
        $config = Config::get('holiday');
        $this->perPage = $config['per_page'];
        $this->cfpScheduleDayOff = $cfpScheduleDayOff;

        $rules_n_attributeNames = $this->rules();
        self::$rules = $rules_n_attributeNames['rules'];
        self::$attributeNames = $rules_n_attributeNames['attributeNames'];
    }

    public function rules()
    {
        $_rules = array();
        $setAttributeNames = array();
        
        //$_rules['cfp_id'] = 'required';
        $_rules['cfp_schedule_day_off_start_date'] = 'required';
        $_rules['cfp_schedule_day_off_end_date'] = 'required';
        $_rules['description'] = 'required';

        //$setAttributeNames['cfp_id'] = trans('app.cfp_id');
        $setAttributeNames['cfp_schedule_day_off_start_date'] = trans('app.cfp_schedule_day_off_start_date');
        $setAttributeNames['cfp_schedule_day_off_end_date'] = trans('app.cfp_schedule_day_off_end_date');
        $setAttributeNames['description'] = trans('app.description');

        return [
            'rules' => $_rules,
            'attributeNames' => $setAttributeNames
        ];
    }

    public function all() {
        return $this->cfpScheduleDayOff->orderBy('created_by', 'DESC')->get();
    }

    public function lists($id_key, $value_key) {
        return $this->cfpScheduleDayOff->all()->lists($value_key, $id_key);
    }

    public function find($id) {
        return $this->cfpScheduleDayOff
        ->select('cfp_schedule_day_offs.*', 'users.name')
        ->join('users', 'users.id', '=', 'cfp_schedule_day_offs.cfp_id')
        ->find($id);
    }

    public function paginate($page = 1, $limit = 10, $filter = array()) {

        $result = new \StdClass;
        $result->page = $page;
        $result->limit = $limit;
        $result->totalItems = 0;
        $result->items = array();

        $query = $this->cfpScheduleDayOff->select('cfp_schedule_day_offs.*','users.name');

        if(is_array($filter)){
            foreach($filter as $ff => $term){
                if(trim($term) != ''){
                    switch ($ff) {
                        case 'cfp_id':
                            if($term  !== 3)//tidak kosong, tapi harus 0 atau 1 saja
                                $query->where('cfp_id', $term);
                        break;
                    }
                }
            }
        }
        
        $query->orderBy('cfp_schedule_day_offs.created_by', 'DESC');
        $query->join('users', 'cfp_schedule_day_offs.cfp_id', '=', 'users.id', 'left');

        $cfpScheduleDayOff = $query->skip($limit * ($page - 1))->take($limit)->get();


        $result->totalItems = $this->totalcfpScheduleDayOff($filter);
        $result->items = $cfpScheduleDayOff->all();

        foreach ($result->items as $key => $item) {
            $result->items[$key]->codes = $item;
        };
        
        //dd($result);

        return $result;
    }

    public function create($attributes)
    {
        $cfp_raw = isset($attributes['cfp_id']) ? explode('__', $attributes['cfp_id']) : Auth::user()->id;
        if($this->isValid($attributes)) {
            $t_attributes = array();
            $t_attributes['cfp_id'] = !empty($cfp_raw[0]) ? $cfp_raw[0] : $cfp_raw;
            $t_attributes['cfp_schedule_day_off_start_date'] = Carbon::parse($attributes['cfp_schedule_day_off_start_date'])->format('Y-m-d H:i:s');
            $t_attributes['cfp_schedule_day_off_end_date'] = Carbon::parse($attributes['cfp_schedule_day_off_end_date'])->format('Y-m-d H:i:s');
            $t_attributes['is_approval'] = isset($attributes['is_approval']) ? $attributes['is_approval'] : 0;
            $t_attributes['description'] = $attributes['description'];
            $t_attributes['created_by'] = Auth::user()->id;
            $t_attributes['created_at'] = date("Y-m-d H:i:s");
            $t_attributes['record_flag'] = 'N';

            $this->cfpScheduleDayOff->fill($t_attributes)->save();

            return true;
        }
        throw new ValidationException('CFP Schedule Day Off attribute validation failed', $this->getErrors());
    }

    public function update($id, $attributes) {
        $cfp_raw = explode('__', $attributes['cfp_id']);
        if($this->isValid($attributes)) {
            $t_attributes = array();
            $this->cfpScheduleDayOff = $this->find($id);

            $t_attributes['cfp_id'] = $cfp_raw[0];
            $t_attributes['cfp_schedule_day_off_start_date'] = Carbon::parse($attributes['cfp_schedule_day_off_start_date'])->format('Y-m-d H:i:s');
            $t_attributes['cfp_schedule_day_off_end_date'] = Carbon::parse($attributes['cfp_schedule_day_off_end_date'])->format('Y-m-d H:i:s');
            $t_attributes['is_approval'] = $attributes['is_approval'];
            $t_attributes['description'] = $attributes['description'];
            $t_attributes['updated_by'] = Auth::user()->id;
            $t_attributes['updated_on'] = date("Y-m-d H:i:s");
            $t_attributes['record_flag'] = 'U';

            $this->cfpScheduleDayOff->fill($t_attributes)->save();
            return true;
        }
        throw new ValidationException('CFP Schedule Day Off attribute validation failed', $this->getErrors());
    }

    public function delete($id) {
        $cfpScheduleDayOff = $this->cfpScheduleDayOff->findOrFail($id);
        if($cfpScheduleDayOff)
            $cfpScheduleDayOff->delete();
            return true;

        return false;
    }

    protected function totalcfpScheduleDayOff($all = false) {
        return $this->cfpScheduleDayOff->count();
    }

    public function whereDayOff($filter = array())
    {
        $query = CfpScheduleDayOff::query();

        if(is_array($filter)){
            foreach($filter as $ff => $term){
                if(is_array($term) || trim($term) != ''){
                    switch ($ff) {
                        case 'cfp_id':
                            $query->where('cfp_id', $term);
                        break;
                        case 'is_approval':
                            $query->where('is_approval', $term);
                        break;
                    }
                }
            }
        }

        return $query->get();
    }

}