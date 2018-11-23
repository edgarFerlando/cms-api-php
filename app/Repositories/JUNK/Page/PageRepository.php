<?php namespace App\Repositories\Page;

use App\Page;
//use App\PageTranslation;
use Config;
use Response;
use App\Repositories\RepositoryAbstract;
use App\Repositories\CrudableInterface as CrudableInterface;
use App\Repositories\RepositoryInterface as RepositoryInterface;
use App\Exceptions\Validation\ValidationException;
use App\Repositories\AbstractValidator as Validator;
use LaravelLocalization;
use Notification;
use App\PageTranslation;

class PageRepository extends RepositoryAbstract implements PageInterface, CrudableInterface {

    /**
     * @var
     */
    protected $perPage;
    /**
     * @var \Page
     */
    protected $page;
    /**
     * Rules
     *
     * @var array
     */
    protected static $rules; /*[
        'title'   => 'required|min:3',
        'content' => 'required|min:5'];*/

    protected static $attributeNames;

    /**
     * @param Page $page
     */
    public function __construct(Page $page) {
        $this->perPage = Config::get('holiday.per_page');
        $this->page = $page;
        $rules_n_attributeNames = $this->rules();
        self::$rules = $rules_n_attributeNames['rules'];
        self::$attributeNames = $rules_n_attributeNames['attributeNames'];
    }

    public function rules(){
        $lang_rules = array();
        $setAttributeNames = array();
        foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
            $lang_rules['title.'.$locale] = 'required|max:255';
            $lang_rules['slug.'.$locale] = 'required|alpha_dash|max:255';
            $lang_rules['body.'.$locale] = 'required';

            $setAttributeNames['title.' . $locale] = trans('app.title').' [ ' . $properties['native'].' ]';
            $setAttributeNames['slug.' . $locale] = trans('app.slug').' [ ' . $properties['native'].' ]';
            $setAttributeNames['body.' . $locale] = trans('app.content').' [ ' . $properties['native'].' ]';
        }
        return [
            'rules' => $lang_rules,
            'attributeNames' => $setAttributeNames
        ];
    }

    /**
     * @return mixed
     */
    public function all() {

        return $this->page->orderBy('created_at', 'DESC')->where('is_published', 1)->get();
    }

   /* public function findByTitle(){
        /*\DB::enableQueryLog();
$this->page->pageTranslation()->where('page_translations.title', 'Halaman dua')->first();
dd(\DB::getQueryLog());*//*
    return $this->page->whereHas('pageTranslation', function($q){
        $q->where('title', '=', 'Page Four x');
    })->first()->id;

        //return $this->page->pageTranslation()->where('page_translations.title', 'Halaman dua')->first();
    }*/

    /**
     * @param $slug
     * @return mixed
     */
    /*public function getBySlug($slug, $isPublished = false) {
        if($isPublished === true)
           return $this->page->select('pages.id', 'page_translations.slug')
            ->join('page_translations', 'pages.id', '=', 'page_translations.page_id')
            ->where('slug', $slug)->where('is_published', true)->first();

        return $this->page->select('pages.id', 'page_translations.slug')
            ->join('page_translations', 'pages.id', '=', 'page_translations.page_id')
            ->where('slug', $slug)->first();
    }*/

    public function getBySlug($slug, $isPublished = false) {

        $q = $this->page->whereHas('pageTranslation', function($q) use ($slug){
            $q->where('slug', $slug);
        });

        if($isPublished === true)
            $q->where('is_published', true);
            
        return $q->first();
    }

    /**
     * @param $slug
     * @return mixed
     */
    public function getSlugsByID($id) {
        $page_with_attrs =  $this->page
        ->select('locale', 'slug')
        ->join('page_translations', 'pages.id', '=', 'page_translations.page_id')
        ->where( 'pages.id', $id)->get()->toArray();
        //dd($page_with_attrs);
        if($page_with_attrs){
            $attrs = [];
            foreach ($page_with_attrs as $attr) { 
                $attrs[$attr['locale']]['url'] = $attr['url'];
            } //dd($attrs);
            return $attrs;
        }
    }

    /**
     * @return mixed
     */
    public function lists() {

        //return $this->page->lists('title', 'id');
        return $this->page->all()->lists('title', 'id');
    }

    /**
     * Get paginated pages
     *
     * @param int $page Number of pages per page
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

        $query = $this->page->with(['translations'])->orderBy('created_at', 'DESC');

        if(!$all) {
            $query->where('is_published', 1);
        }

        $pages = $query->skip($limit * ($page - 1))->take($limit)->get();
        $result->totalItems = $this->totalPages($all);
        $result->items = $pages->all();

        return $result;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function find($id) {

        return $this->page->find($id);
    }

    /**
     * @param $attributes
     * @return bool|mixed
     * @throws \App\Exceptions\Validation\ValidationException
     */
    public function create($attributes) {
        if($this->isValid($attributes)) {
            $t_attributes['is_published'] = isset($attributes['is_published']) ? true : false;
            $langs = LaravelLocalization::getSupportedLocales();
            foreach ($langs as $localeCode => $properties) {
                $t_attributes[$localeCode] = [
                    'title' => $attributes['title'][$localeCode],
                    'slug' => $attributes['slug'][$localeCode],
                    'body' => $attributes['body'][$localeCode]
                ];
            }
            //dd($t_attributes);
            $this->page->fill($t_attributes)->save();

            return true;
        }
        //dd($this->getErrors());
        /*Notification::error([
            'en.slug' => 'The en.slug field is required.',
            'en.title' => 'The en.title field is required.' ]);*/
        throw new ValidationException('Page validation failed', $this->getErrors());
    }

    /**
     * @param $id
     * @param $attributes
     * @return bool|mixed
     * @throws \App\Exceptions\Validation\ValidationException
     */
    public function update($id, $attributes) {
        if($this->isValid($attributes)) {
            $t_attributes['is_published'] = isset($attributes['is_published']) ? true : false;

            $this->page = $this->find($id);
            $langs = LaravelLocalization::getSupportedLocales();
            foreach ($langs as $localeCode => $properties) {
                $t_attributes[$localeCode] = [
                    'title' => $attributes['title'][$localeCode],
                    'slug' => $attributes['slug'][$localeCode],
                    'body' => $attributes['body'][$localeCode]
                ];
            }
            $this->page->fill($t_attributes)->save();

            return true;
        }

        throw new ValidationException('Category validation failed', $this->getErrors());
    }

    /**
     * @param $id
     * @return mixed|void
     */
    public function delete($id) {

        $this->page->findOrFail($id)->delete();
    }

    /**
     * @param $id
     * @return mixed
     */
    public function togglePublish($id) {

        $page = $this->page->find($id);
        $page->is_published = ($page->is_published) ? false : true;
        $page->save();

        return Response::json(array('result' => 'success', 'changed' => ($page->is_published) ? 1 : 0));
    }

    /**
     * Get total page count
     * @param bool $all
     * @return mixed
     */
    protected function totalPages($all = false) {

        if(!$all) {
            return $this->page->where('is_published', 1)->count();
        }

        return $this->page->count();
    }
}
