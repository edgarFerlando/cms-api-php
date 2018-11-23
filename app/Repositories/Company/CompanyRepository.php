<?php namespace App\Repositories\Company;

use App\Models\Code;
use App\Models\Company;

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


class CompanyRepository extends RepositoryAbstract implements CompanyInterface, CrudableInterface {

    protected $perPage;
    protected $company;
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
    public function __construct(Company $company) {

        $config = Config::get('holiday');
        $this->perPage = $config['per_page'];
        $this->company = $company;

        $rules_n_attributeNames = $this->rules();
        self::$rules = $rules_n_attributeNames['rules'];
        self::$attributeNames = $rules_n_attributeNames['attributeNames'];
    }

    public function rules(){
        $_rules = array();
        $setAttributeNames = array();
        
        $_rules['company_name'] = 'required|max:100';
        $_rules['company_type'] = 'required|numeric|exists:mylife_mst_code,code';

        $setAttributeNames['company_name'] = trans('app.company_name');
        $setAttributeNames['company_type'] = trans('app.company_type');
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
        return $this->company->orderBy('created_by', 'DESC')->get();
    }

    public function paginate($page = 1, $limit = 10, $all = false) {

        $result = new \StdClass;
        $result->page = $page;
        $result->limit = $limit;
        $result->totalItems = 0;
        $result->items = array();

        $query = $this->company->orderBy('created_by', 'DESC');
        //dd($query);
        if(!$all) {
            $query->where('is_published', 1);
        }

        //\DB::enableQueryLog();

        $companies = $query->skip($limit * ($page - 1))->take($limit)->get();

        //dd(\DB::getQueryLog());
        //dd($categoryCodes);

        $result->totalItems = $this->totalCompanies($all);
        $result->items = $companies->all();

        foreach ($result->items as $key => $item) {
            $result->items[$key]->companies = $item;

            $code = Code::find($item->company_type);
            $userCreate = User::find($item->created_by);

            $result->items[$key]->codeName = $code->code_name;
            $result->items[$key]->userCreate = $userCreate->name;

            if(!is_null($item->updated_by))
            {
                $userUpdate = User::find($item->updated_by);
                $result->items[$key]->userUpdate = $userUpdate->name;
            }
        };
        
        //dd($result);

        return $result;
    }

    public function find($id) {
        return $this->company->findOrFail($id);
    }

    public function create($attributes) {
        if($this->isValid($attributes)) {
            $t_attributes = array();
            $t_attributes['company_name'] = $attributes['company_name'];
            $t_attributes['company_type'] = $attributes['company_type'];
            $t_attributes['keterangan'] = $attributes['keterangan'];
            $t_attributes['created_by'] = Auth::user()->id;
            $t_attributes['created_on'] = date("Y-m-d H:i:s");
            $t_attributes['record_flag'] = 'N';
            /*
            $t_attributes['testimonial'] = Str::slug($attributes['name'][getLang()], '_');
            foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
                $t_attributes[$locale] = [
                    'name' => $attributes['name'][$locale]
                ];
            }
            //dd($t_attributes);
            */
            $this->company->fill($t_attributes)->save();

            return true;
        }
        throw new ValidationException('Company attribute validation failed', $this->getErrors());
    }

    public function update($id, $attributes) {
        if($this->isValid($attributes)) {
            $t_attributes = array();
            $this->company = $this->find($id);
            //dd($attributes);
            $t_attributes['company_name'] = $attributes['company_name'];
            $t_attributes['company_type'] = $attributes['company_type'];
            $t_attributes['keterangan'] = $attributes['keterangan'];
            $t_attributes['updated_by'] = Auth::user()->id;
            $t_attributes['updated_on'] = date("Y-m-d H:i:s");
            $t_attributes['record_flag'] = 'U';
            /*
            $t_attributes['testimonial'] = Str::slug($attributes['name'][getLang()], '_');
            foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
                $t_attributes[$locale] = [
                    'name' => $attributes['name'][$locale]
                ];
            }
            */
            $this->company->fill($t_attributes)->save();
            return true;
        }
        throw new ValidationException('Company attribute validation failed', $this->getErrors());
    }

    public function delete($id) {
        $company = $this->company->findOrFail($id);
        $company->delete();
    }

    protected function totalCompanies($all = false) {
        return $this->company->count();
    }
}
