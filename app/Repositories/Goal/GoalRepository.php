<?php namespace App\Repositories\Goal;

use App\Models\Goal;

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


class GoalRepository extends RepositoryAbstract implements GoalInterface, CrudableInterface {

    protected $perPage;
    protected $goal;
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
    public function __construct(Goal $goal) {

        $config = Config::get('holiday');
        $this->perPage = $config['per_page'];
        $this->goal = $goal;

        $rules_n_attributeNames = $this->rules();
        self::$rules = $rules_n_attributeNames['rules'];
        self::$attributeNames = $rules_n_attributeNames['attributeNames'];
    }

    public function rules(){
        $_rules = array();
        $setAttributeNames = array();

        $_rules['goal_name'] = 'required';
        $_rules['goal_icon'] = 'required';
        $_rules['goal_thumb'] = 'required';

        $setAttributeNames['goal_name'] = trans('app.goal_name');
        $setAttributeNames['goal_icon'] = trans('app.goal_icon');
        $setAttributeNames['goal_thumb'] = trans('app.goal_thumb');

        /*
        foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
            $_rules['name.'.$locale] = 'required|max:255';

            $setAttributeNames['name.' . $locale] = trans('app.name').' [ ' . $properties['native'].' ]';
        }
        */

        return [
            'rules' => $_rules,
            'attributeNames' => $setAttributeNames
        ]; 

    }

    public function all() {
        return $this->goal->orderBy('created_by', 'DESC')->get();
    }

    public function paginate($page = 1, $limit = 10, $all = false) {

        $result = new \StdClass;
        $result->page = $page;
        $result->limit = $limit;
        $result->totalItems = 0;
        $result->items = array();

        $query = $this->goal->with('createdBy', 'updatedBy')->orderBy('created_by', 'DESC');
        //dd($query);
        if(!$all) {
            $query->where('is_published', 1);
        }

        //\DB::enableQueryLog();

        $goals = $query->skip($limit * ($page - 1))->take($limit)->get();

        //dd(\DB::getQueryLog());
        //dd($categoryCodes);

        $result->totalItems = $this->totalGoals($all);
        $result->items = $goals->all();

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

        $rules['id'] = 'required|exists:goals,id';

        $data['id'] = $id;

        $validator = Valid::make($data, $rules);

        if ($validator->fails())
        { 
            //dd($validator->errors()->first('id'));
            $data['id_data'] = $validator->errors()->first('id');
            return $data;
        }

        return $this->goal->findOrFail($id);
    }

    public function create($attributes) {

        if($this->isValid($attributes)) {

            $user_id = Auth::user()->id;

            $goal = $this->goal->create([
                        'goal_name' => $attributes['goal_name'],
                        'thumb_path' => getImagePath($attributes['goal_thumb']),
                        'icon_path' => getImagePath($attributes['goal_icon']),
                        'position_under_grade_id' => $attributes['goal_position_under_grade'],
                        'created_by' => $user_id, 
                        'created_at' => Carbon::now(), 
                        'updated_by' => $user_id, 
                        'updated_at' => Carbon::now(),
                        'record_flag' => 'N'
                    ]);
            //dd($schedule);
            return $goal->id;
        }
        throw new ValidationException('Goal attribute validation failed', $this->getErrors());
    }

    public function update($id, $attributes) {

        $attributes['id'] = $id;

        $rules['id'] = 'required|exists:goals,id';

        $validator = Valid::make($attributes, $rules);

        if ($validator->fails())
        { 
            throw new ValidationException('goal attribute validation failed', $validator->errors());
        }

        if($this->isValid($attributes)) {
            $t_attributes = array();

            $user_id = Auth::user()->id;

            $this->goal = $this->find($id);

            $t_attributes = [
                'goal_name' => $attributes['goal_name'],
                'thumb_path' => getImagePath(str_replace(url('/'), '', $attributes['goal_thumb'])),
                'icon_path' => getImagePath(str_replace(url('/'), '', $attributes['goal_icon'])),
                'position_under_grade_id' => $attributes['goal_position_under_grade'],
                'updated_by' => $user_id, 
                'updated_at' => Carbon::now(),
                'record_flag' => 'U'
            ];
            //dd($attributes);
            /*$t_attributes['goal_name'] = $attributes['goal_name'];
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
            $this->goal->fill($t_attributes)->save();
            return true;
        }
        throw new ValidationException('Goal attribute validation failed', $this->getErrors());
    }

    public function delete($id) {

        $rules['id'] = 'required|exists:goals,id';

        $attributes['id'] = $id;

        $validator = Valid::make($attributes, $rules);

        if ($validator->fails())
        { 
            throw new ValidationException('Goal attribute validation failed', $validator->errors());
        }

        $goal = $this->goal->findOrFail($id);
        $goal->delete();
    }

    protected function totalGoals($all = false) {
        return $this->goal->count();
    }
}
