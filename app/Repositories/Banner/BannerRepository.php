<?php namespace App\Repositories\Banner;

use App\Models\Banner;
use Config;
use Response;
//use App\Tag;
use App\Models\BannerGroup;
use Str;
//use Event;
//use Image;
//use File;
use App\Repositories\RepositoryAbstract;
use App\Repositories\CrudableInterface as CrudableInterface;
use App\Repositories\RepositoryInterface as RepositoryInterface;
use App\Exceptions\Validation\ValidationException;
use App\Repositories\AbstractValidator as Validator;
use LaravelLocalization;
use Notification;

use App\Menu;
use App\Repositories\Menu\MenuRepository;


class BannerRepository extends RepositoryAbstract implements BannerInterface, CrudableInterface {

    /*protected $width;
    protected $height;
    protected $thumbWidth;
    protected $thumbHeight;
    protected $imgDir;*/
    protected $perPage;
    protected $banner;
    protected $menu;
    /**
     * Rules
     *
     * @var array
     */
    protected static $rules;/* = [
        'title'   => 'required',
        'content' => 'required'
    ];*/
    protected static $attributeNames;

    /**
     * @param Banner $banner
     */
    public function __construct(Banner $banner) {

        $config = Config::get('holiday');
        $this->perPage = $config['per_page'];
        $this->banner = $banner;
        $this->menu = new MenuRepository(new Menu);
        $rules_n_attributeNames = $this->rules();
        self::$rules = $rules_n_attributeNames['rules'];
        self::$attributeNames = $rules_n_attributeNames['attributeNames'];
    }

    public function rules(){
        $_rules = array();
        $setAttributeNames = array();
        $_rules['group'] = 'required';
        foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
            $_rules['title.'.$locale] = 'required|max:255';
            $_rules['image.'.$locale] = 'required';

            $setAttributeNames['title.' . $locale] = trans('app.title').' [ ' . $properties['native'].' ]';
            $setAttributeNames['image.' . $locale] = trans('app.image').' [ ' . $properties['native'].' ]';

            $setAttributeNames['group'] = trans('app.group');
        }
        return [
            'rules' => $_rules,
            'attributeNames' => $setAttributeNames
        ];
    }

    /**
     * @return mixed
     */
    public function all() {
        return $this->banner->orderBy('created_at', 'DESC')->where('is_published', 1)->get();
    }

    /**
     * @param $limit
     * @return mixed
     */
    public function getLastBanner($limit) {

        return $this->banner->orderBy('created_at', 'desc')->take($limit)->offset(0)->get();
    }

    /**
     * @return mixed
     */
    public function lists() {

        //return $this->banner->get()->lists('title', 'id');
        return $this->banner->all()->lists('title', 'id');
    }

    /**
     * Get paginated banners
     *
     * @param int $page Number of banners per page
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

        //$query = $this->banner->with('tags')->orderBy('created_at', 'DESC');
        $query = $this->banner->orderBy('created_at', 'DESC');

        if(!$all) {
            $query->where('is_published', 1);
        }

        $banners = $query->skip($limit * ($page - 1))->take($limit)->get();

        $result->totalItems = $this->totalBanners($all);
        $result->items = $banners->all();

        return $result;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function find($id) {
        return $this->banner->with(['bannerGroup'])->findOrFail($id);
    }

    /**
     * @param $slug
     * @return mixed
     */
     public function getBySlug($slug, $isPublished = false) {
        if($isPublished === true)
           return $this->banner->select('banners.id', 'banner_translations.slug')
            ->join('banner_translations', 'banners.id', '=', 'banner_translations.banner_id')
            ->where('slug', $slug)->where('is_published', true)->firstOrFail();

        return $this->banner->select('banners.id', 'banner_translations.slug')
            ->join('banner_translations', 'banners.id', '=', 'banner_translations.banner_id')
            ->where('slug', $slug)->firstOrFail();
    }

    /**
     * @param $attributes
     * @return bool|mixed
     * @throws \App\Exceptions\Validation\ValidationException
     */
    public function create($attributes) {
        $t_attributes = array();
        if ($attributes['type'] == 'module') {
            $option = $attributes['option'];
            $url = $this->menu->getUrl($option);
            $attributes['url'] = $url;
        }

        $t_attributes['banner_group_id'] = $attributes['group'];
        $t_attributes['type'] = $attributes['type'];
        $t_attributes['option'] = $attributes['option'];
        $t_attributes['is_published'] = isset($attributes['is_published']) ? true : false;

        if($this->isValid($attributes)) { 
            foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
                $t_attributes[$locale] = [
                    'title' => $attributes['title'][$locale],
                    'image' => getImagePath($attributes['image'][$locale]),
                    'url' => $attributes['url'][$locale]
                ];
            }
            $this->banner->fill($t_attributes)->save();

            return true;
        }
        throw new ValidationException('Banner validation failed', $this->getErrors());
    }


    /**
     * @param $id
     * @param $attributes
     * @return bool|mixed
     * @throws \App\Exceptions\Validation\ValidationException
     */
    public function update($id, $attributes) {
        if($this->isValid($attributes)) {
            $this->banner = $this->find($id);
            if ($attributes['type'] == 'module') {
                $option = $attributes['option'];
                $url = $this->menu->getUrl($option);
                $attributes['url'] = $url;
            }

            $t_attributes['banner_group_id'] = $attributes['group'];
            $t_attributes['type'] = $attributes['type'];
            $t_attributes['option'] = $attributes['option'];
            $t_attributes['is_published'] = isset($attributes['is_published']) ? true : false;
        
            
            foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
                $t_attributes[$locale] = [
                    'title' => $attributes['title'][$locale],
                    'image' => getImagePath($attributes['image'][$locale]),
                    'url' => $attributes['url'][$locale]
                ];
            }
            $this->banner->fill($t_attributes)->save();

            return true;
        }

        throw new ValidationException('Banner validation failed', $this->getErrors());
    }

    /**
     * @param $id
     * @return mixed|void
     */
    public function delete($id) {

        $banner = $this->banner->findOrFail($id);
        //$banner->tags()->detach();
        $banner->delete();
    }

    /**
     * @param $id
     * @return mixed
     */
    public function togglePublish($id) {

        $banner = $this->banner->find($id);

        $banner->is_published = ($banner->is_published) ? false : true;
        $banner->save();

        return Response::json(array('result' => 'success', 'changed' => ($banner->is_published) ? 1 : 0));
    }

    /**
     * @param $id
     * @return string
     */
    function getUrl($id) {

        $banner = $this->banner->findOrFail($id);
        return url('banner/' . $id . '/' . $banner->slug, $parameters = array(), $secure = null);
    }

    /**
     * Get total banner count
     * @param bool $all
     * @return mixed
     */
    protected function totalBanners($all = false) {

        if(!$all) {
            return $this->banner->where('is_published', 1)->count();
        }

        return $this->banner->count();
    }

    public function mainBanners() {
        return $this->banner->with(['translations'])->where('banner_group_id', 1)->where('is_published', 1)->get();
    }

    public function staticBanner() {
        return $this->banner->where('banner_group_id', 2)->where('is_published', 1)->orderBy('created_at', 'desc')->first();
    }
}
