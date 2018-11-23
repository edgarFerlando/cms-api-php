<?php namespace App\Repositories\Grade;

use App\Models\Grade;

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


class GradeRepository extends RepositoryAbstract implements GradeInterface, CrudableInterface {

    protected $perPage;
    protected $grade;
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
    public function __construct(Grade $grade) {

        $config = Config::get('holiday');
        $this->perPage = $config['per_page'];
        $this->grade = $grade;

        $rules_n_attributeNames = $this->rules();
        self::$rules = $rules_n_attributeNames['rules'];
        self::$attributeNames = $rules_n_attributeNames['attributeNames'];
    }

    public function rules(){
        $_rules = array();
        $setAttributeNames = array();
        
        $_rules['grade_name'] = 'required';
        $_rules['grade_ages'] = 'required';
        $_rules['grade_thumb'] = 'required';
        $_rules['grade_button_label'] = 'required';

        $setAttributeNames['grade_name'] = trans('app.grade_name');
        $setAttributeNames['grade_ages'] = trans('app.grade_ages');
        $setAttributeNames['grade_thumb'] = trans('app.grade_thumb');
        $setAttributeNames['grade_button_label'] = trans('app.grade_button_label');
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
        return $this->grade->orderBy('created_by', 'DESC')->get();
    }

    public function lists($value,$key) {
        return $this->grade->orderBy('created_by', 'DESC')->lists($value, $key);
    }

    public function paginate($page = 1, $limit = 10, $all = false) {

        $result = new \StdClass;
        $result->page = $page;
        $result->limit = $limit;
        $result->totalItems = 0;
        $result->items = array();

        $query = $this->grade->with('createdBy', 'updatedBy')->orderBy('created_by', 'DESC');
        //dd($query);
        if(!$all) {
            $query->where('is_published', 1);
        }

        //\DB::enableQueryLog();

        $grades = $query->skip($limit * ($page - 1))->take($limit)->get();

        //dd(\DB::getQueryLog());
        //dd($categoryCodes);

        $result->totalItems = $this->totalGrades($all);
        $result->items = $grades->all();

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

        $rules['id'] = 'required|exists:grades,id';

        $data['id'] = $id;

        $validator = Valid::make($data, $rules);

        if ($validator->fails())
        { 
            //dd($validator->errors()->first('id'));
            $data['id_data'] = $validator->errors()->first('id');
            return $data;
        }

        return $this->grade->findOrFail($id);
    }

    public function create($attributes) {

        if($this->isValid($attributes)) {

            $user_id = Auth::user()->id;

            $grade = $this->grade->create([
                'grade_name' => $attributes['grade_name'],
                'ages' => $attributes['grade_ages'],
                'thumb_path' => getImagePath($attributes['grade_thumb']),
                'button_label' => $attributes['grade_button_label'],
                'created_by' => $user_id, 
                'created_at' => Carbon::now(), 
                'updated_by' => $user_id, 
                'updated_at' => Carbon::now(), 
                'record_flag' => 'N'
            ]);
            //dd($schedule);
            return $grade->id;
        }
        throw new ValidationException('Grade attribute validation failed', $this->getErrors());
    }

    public function update($id, $attributes) {

        $attributes['id'] = $id;

        $rules['id'] = 'required|exists:grades,id';

        $validator = Valid::make($attributes, $rules);

        if ($validator->fails())
        { 
            throw new ValidationException('Grade attribute validation failed', $validator->errors());
        }

        if($this->isValid($attributes)) {
            $t_attributes = array();

            $user_id = Auth::user()->id;

            $this->grade = $this->find($id);
            $t_attributes = [
                'grade_name' => $attributes['grade_name'],
                'ages' => $attributes['grade_ages'],
                'thumb_path' => getImagePath(str_replace(url('/'), '', $attributes['grade_thumb'])),
                'button_label' => $attributes['grade_button_label'],
                'updated_by' => $user_id, 
                'updated_at' => Carbon::now(), 
                'record_flag' => 'U'
            ];

            //dd($attributes);
            /*$t_attributes['grade_name'] = $attributes['grade_name'];
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
            $this->grade->fill($t_attributes)->save();
            return true;
        }
        throw new ValidationException('Grade attribute validation failed', $this->getErrors());
    }

    public function delete($id) {

        $rules['id'] = 'required|exists:grades,id';

        $attributes['id'] = $id;

        $validator = Valid::make($attributes, $rules);

        if ($validator->fails())
        { 
            throw new ValidationException('Grade attribute validation failed', $validator->errors());
        }

        $grade = $this->grade->findOrFail($id);
        $grade->delete();
    }

    protected function totalGrades($all = false) {
        return $this->grade->count();
    }
}
