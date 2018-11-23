<?php namespace App\Repositories\Testimonial;

use App\Models\Testimonial;

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


class TestimonialRepository extends RepositoryAbstract implements TestimonialInterface, CrudableInterface {

    protected $perPage;
    protected $testimonial;
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
    public function __construct(Testimonial $testimonial) {

        $config = Config::get('holiday');
        $this->perPage = $config['per_page'];
        $this->testimonial = $testimonial;

        $rules_n_attributeNames = $this->rules();
        self::$rules = $rules_n_attributeNames['rules'];
        self::$attributeNames = $rules_n_attributeNames['attributeNames'];
    }

    public function rules(){
        $_rules = array();
        $setAttributeNames = array();
        
        $_rules['name'] = 'required|max:255';
        $setAttributeNames['name'] = trans('app.testimonial');
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
        return $this->testimonial->with(['translations'])->orderBy('created_at', 'DESC')->get();
    }

    public function paginate($page = 1, $limit = 10, $all = false) {

        $result = new \StdClass;
        $result->page = $page;
        $result->limit = $limit;
        $result->totalItems = 0;
        $result->items = array();

        $query = $this->testimonial->with(['translations','testimonialTranslation'])->orderBy('created_at', 'DESC');

        if(!$all) {
            $query->where('is_published', 1);
        }

        $testimonials = $query->skip($limit * ($page - 1))->take($limit)->get();
        //dd($testimonials);
        $result->totalItems = $this->totalTestimonials($all);
        $result->items = $testimonials->all();

        foreach ($result->items as $key => $item) {
            $user = User::with(['userMetas'])->find($item->user_id);
            $result->items[$key]->user = $user;
        };
        
        //dd($result->items);

        return $result;
    }

    public function find($id) {
        return $this->testimonial->with(['translations'])->findOrFail($id);
    }

    public function create($attributes) {
        if($this->isValid($attributes)) {
            $t_attributes = array();
            $t_attributes['user_id'] = Auth::user()->id;
            $t_attributes['testimonial'] = $attributes['name'];
            foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
                $t_attributes[$locale] = [
                    'name' => $attributes['name']
                ];
            }
            /*
            $t_attributes['testimonial'] = Str::slug($attributes['name'][getLang()], '_');
            foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
                $t_attributes[$locale] = [
                    'name' => $attributes['name'][$locale]
                ];
            }
            //dd($t_attributes);
            */
            $this->testimonial->fill($t_attributes)->save();

            return true;
        }
        throw new ValidationException('Product attribute validation failed', $this->getErrors());
    }

    public function update($id, $attributes) {
        if($this->isValid($attributes)) {
            $t_attributes = array();
            $this->testimonial = $this->find($id);
            $t_attributes['testimonial'] = $attributes['name'];
            foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
                $t_attributes[$locale] = [
                    'name' => $attributes['name']
                ];
            }
            /*
            $t_attributes['testimonial'] = Str::slug($attributes['name'][getLang()], '_');
            foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
                $t_attributes[$locale] = [
                    'name' => $attributes['name'][$locale]
                ];
            }
            */
            $this->testimonial->fill($t_attributes)->save();
            return true;
        }
        throw new ValidationException('Product attribute validation failed', $this->getErrors());
    }

    public function delete($id) {
        $testimonial = $this->testimonial->findOrFail($id);
        $testimonial->delete();
    }

    protected function totalTestimonials($all = false) {
        return $this->testimonial->count();
    }
}
