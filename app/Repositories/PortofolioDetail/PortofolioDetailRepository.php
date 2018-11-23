<?php namespace App\Repositories\PortofolioDetail;

use App\Models\PortofolioDetail;
use App\Models\Portofolio;

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


class PortofolioDetailRepository extends RepositoryAbstract implements PortofolioDetailInterface, CrudableInterface {

    protected $perPage;
    protected $portofolioDetail;
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
    public function __construct(PortofolioDetail $portofolioDetail) {

        $config = Config::get('holiday');
        $this->perPage = $config['per_page'];
        $this->portofolioDetail = $portofolioDetail;

        $rules_n_attributeNames = $this->rules();
        self::$rules = $rules_n_attributeNames['rules'];
        self::$attributeNames = $rules_n_attributeNames['attributeNames'];
    }

    public function rules(){
        $_rules = array();
        $setAttributeNames = array();
        
        $_rules['detail_name'] = 'required|max:255';
        $_rules['portofolio_id'] = 'required|numeric';

        $setAttributeNames['detail_name'] = trans('app.detail_name');
        $setAttributeNames['portofolio_id'] = trans('app.portofolio_name');
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
        return $this->portofolioDetail->orderBy('created_by', 'DESC')->get();
    }

    public function paginate($page = 1, $limit = 10, $all = false) {

        $result = new \StdClass;
        $result->page = $page;
        $result->limit = $limit;
        $result->totalItems = 0;
        $result->items = array();

        $query = $this->portofolioDetail->orderBy('created_by', 'DESC');
        //dd($query);
        if(!$all) {
            $query->where('is_published', 1);
        }

        //\DB::enableQueryLog();

        $portofolioDetails = $query->skip($limit * ($page - 1))->take($limit)->get();

        //dd(\DB::getQueryLog());
        //dd($categoryCodes);

        $result->totalItems = $this->totalPortofolioDetails($all);
        $result->items = $portofolioDetails->all();

        foreach ($result->items as $key => $item) {
            //dd($item);
            $result->items[$key]->PortofolioDetail = $item;

            $portofolio = Portofolio::find($item->portofolio_id);
            $userCreate = User::find($item->created_by);

            $result->items[$key]->portofolioName = $portofolio->portofolio_name;
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
        return $this->portofolioDetail->findOrFail($id);
    }

    public function create($attributes) {
        if($this->isValid($attributes)) {
            $t_attributes = array();
            $t_attributes['detail_name'] = $attributes['detail_name'];
            $t_attributes['portofolio_id'] = $attributes['portofolio_id'];
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
            $this->portofolioDetail->fill($t_attributes)->save();

            return true;
        }
        throw new ValidationException('Portofolio detail attribute validation failed', $this->getErrors());
    }

    public function update($id, $attributes) {
        if($this->isValid($attributes)) {
            $t_attributes = array();
            $this->portofolioDetail = $this->find($id);
            //dd($attributes);
            $t_attributes['detail_name'] = $attributes['detail_name'];
            $t_attributes['portofolio_id'] = $attributes['portofolio_id'];
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
            $this->portofolioDetail->fill($t_attributes)->save();
            return true;
        }
        throw new ValidationException('Portofolio detail attribute validation failed', $this->getErrors());
    }

    public function delete($id) {
        $portofolioDetail = $this->portofolioDetail->findOrFail($id);
        $portofolioDetail->delete();
    }

    protected function totalPortofolioDetails($all = false) {
        return $this->portofolioDetail->count();
    }
}
