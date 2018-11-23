<?php namespace App\Repositories\Menu;

use App\Menu;
//use App\MenuTranslations;
use Config;
use Response;
//use App\Models\Tag;
//use App\Category;
use Str;
//use Event;
//use Image;
//use File;
use App\Page;
use App\Repositories\Page\PageRepository as PageRepository;
use App\Repositories\RepositoryAbstract;
use App\Repositories\CrudableInterface as CrudableInterface;
use App\Repositories\RepositoryInterface as RepositoryInterface;
use App\Exceptions\Validation\ValidationException;
use App\Repositories\AbstractValidator as Validator;
use LaravelLocalization;
use Notification;
use URL;
use Input;
use Lang;
use ReflectionClass;
use App\Taxonomy;
use App\Repositories\Taxonomy\TaxonomyRepository;

class MenuRepository extends RepositoryAbstract implements MenuInterface, CrudableInterface {

    /**
     * @var \Menu
     */
    protected $menu;
    protected $taxonomy;

    /**
     * Rules
     *
     * @var array
     */
    protected static $rules; 

    protected static $attributeNames;

    /**
     * @param Menu $menu
     */
    public function __construct(Menu $menu) {
        $this->menu = $menu;
        $this->taxonomy = new TaxonomyRepository(new Taxonomy);
        $rules_n_attributeNames = $this->rules();
        self::$rules = $rules_n_attributeNames['rules'];
        self::$attributeNames = $rules_n_attributeNames['attributeNames'];
    }

    /**
     * harus dimodifikasi, agar rules bisa pakai input
     */
    public function rules(){
        $lang_rules = array();
        $setAttributeNames = array();
        //$lang_rules['menu_group'] = 'required';
        //$setAttributeNames['menu_group'] = trans('app.menu_group');
        foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
            $lang_rules['title.'.$locale] = 'required|max:255';
            //$lang_rules['url.'.$locale] = 'required';

            $setAttributeNames['title.' . $locale] = trans('app.title').' [ ' . $properties['native'].' ]';
            //$setAttributeNames['url.' . $locale] = trans('app.url').' [ ' . $properties['native'].' ]';
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
        return $this->menu->orderBy('order', 'asc')->get();
    }

    /**
     * @param $id
     * @return mixed
     */
    public function find($id) {

        return $this->menu->find($id);
    }

    public function findByMenuGroupID($menu_group_id = null){
        return $this->menu->where('menu_group_id', $menu_group_id)->orderBy('order', 'ASC')->get();
    }

    /* diambil dari App/Menu sebelumnya */
    public function getMaxOrder() {

        $menu = $this->menu->orderBy('order', 'desc')->first();
        if(isset($menu))
            return $menu->order;

        return 0;
    }

    public function generateMenu($menu, $parentId = 0) {

        $result = null;

        foreach($menu as $item) {

            if($item->parent_id == $parentId) {
                //$imageName = ($item->is_published) ? "publish_16x16.png" : "not_publish_16x16.png";
                $iconClass = ($item->is_published) ?'fa fa-eye' : 'fa fa-eye-slash';

                $result .= "<li class='dd-item' data-id='{$item->id}'>
                <button type='button' data-action='collapse'><i class='fa fa-minus-square-o'></i></button>
                <button type='button' data-action='expand' style='display: none;'><i class='fa fa-plus-square-o'></i></button>
                <div class='dd-handle'></div>
                <div class='dd-content'><a href='" . langRoute('admin.menu.show', $item->id) . "'>{$item->title}</a>
                <div class='ns-actions'>
                <a title='Edit Menu' class='edit-menu' href='" . langRoute('admin.menu.edit', $item->id) . "'><i class='fa fa-pencil'></i></a>
                <a class='delete-menu' href='" . URL::route('admin.menu.delete', $item->id) . "'><i class='fa fa-trash-o'></i></a>
                <a title='Publish Menu' id='{$item->id}' class='publish' href='#'><i id='publish-image-" . $item->id . "' class='" . $iconClass . "'></i></a>
                <input type='hidden' value='1' name='menu_id'>
                </div>
                </div>" . $this->generateMenu($menu, $item->id) . "
                </li>";
            }
        }

        return $result ? "\n<ol class=\"dd-list\">\n$result</ol>\n" : null;
    }

    public function getMenuHTML($items) {

        return $this->generateMenu($items);
    }

    public function parseJsonArray($jsonArray, $parentID = 0) {

        $return = array();

        foreach($jsonArray as $subArray) {

            $returnSubArray = array();

            if(isset($subArray['children'])) {
                $returnSubArray = $this->parseJsonArray($subArray['children'], $subArray['id']);
            }

            $return[] = array('id' => $subArray['id'], 'parentID' => $parentID);
            $return = array_merge($return, $returnSubArray);
        }

        return $return;
    }

    public function changeParentById($data) {
        foreach($data as $k => $v) {
            $item = $this->menu->find($v['id']);
            $item->parent_id = $v['parentID'] == 0?null:$v['parentID'];
            $item->order = $k + 1;
            $item->save();
        }
    }

    public function hasChildItems($id) {

        $count = $this->menu->where('parent_id', $id)->where('is_published', 1)->get()->count();
        if($count === 0)
            return false;

        return true;
    }

    public function getMenuOptions() {

        $opts = array();
        $page = new PageRepository(new Page);
        $pageOpts = $page->lists();

        foreach($pageOpts as $k => $v) {
            $opts['Page']['page-' . $k] = $v;
        }

        $opts['Article Category'] = renderListsForMenuOption($this->taxonomy->getTermsByPostType('article')->toHierarchy(), 'taxonomy');

        /*$photoGallery = new PhotoGalleryRepository(new PhotoGallery);
        $photoGalleryOpts = $photoGallery->lists();

        foreach($photoGalleryOpts as $k => $v) {
            $opts['PhotoGallery']['photoGallery-' . $k] = $v;
        }*/

        $menuOptions = array(
            'General'       => [
                'home'    => trans('app.home'),
                //'article_categories' => trans('app.article_taxonomies'),
                //'article' => trans('app.article'),
                //'news'    => 'News',
                //'blog'    => 'Blog',
                //'project' => 'Project',
                //'faq'     => 'Faq',
                'confirmation' => trans('app.booking_confirmation'),
                'contact' => trans('app.contact')
                ],
                'Page'          => (isset($opts['Page']) ? $opts['Page'] : array()),
            //'Photo Gallery' => (isset($opts['PhotoGallery']) ? $opts['PhotoGallery'] : array()),
            );

        return $menuOptions;
    }

    /**
     * @return mixed
     */
    public function lists() {

        //return $this->page->lists('title', 'id');
        return $this->menu->all()->lists('title', 'id');
    }

    public function getUrl($option) {

        $url = "";

        switch($option) {
            case 'home':
            case 'article':
            /*case 'news':
                $url = "/news";
            break;
            case 'blog':
                $url = "/article";
            break;
            case 'project':
                $url = "/project";
            break;
            case 'faq':
                $url = "/faq";
            break;*/
            case 'confirmation':
            case 'contact':
                foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
                    $url[$locale] = Lang::get('module_url.'.$option, [], $locale);
                }
            break;
            default:
            foreach (LaravelLocalization::getSupportedLocales() as $locale => $properties) {
                    $url[$locale] = $this->getModuleUrl($option, $locale);
                }
                //$url = $this->getModuleUrl($option);
            break;
        }

        //$url = "/" . getLang() . "/" . ltrim($url, '/');

        return $url;
    }

    public function getModuleUrlJUNK($option, $locale = null) {
        if(is_null($locale))
            $locale = getLang();
        $pieces = explode('-', $option);
        $reflection = new ReflectionClass('App\\'.ucfirst($pieces[0]));
        $module = $reflection->newInstance();
        $module =  $module::with([$pieces[0].'Translations' => function($query) use ($locale)
            {
                $query->where('locale', $locale);

            }])->whereHas($pieces[0].'Translations', function($q) use ($locale) {
                $q->where('locale', '=', $locale);
            })->first();

        switch ($pieces[0]) {
            case 'page':
                $slug = $module->pageTranslations[0]->slug;
                break;
        }
        //if($locale == 'id') dd($module->pageTranslations[0]->slug);
        //$module = $module::with(['pageTranslation'])->find($pieces[1]);
        return Lang::get('routes.'.$pieces[0], [], $locale).'/'.$slug;
    }

    public function getModuleUrl($option, $locale = null) {
        if(is_null($locale))
            $locale = getLang();
        $segment = [];
        $pieces = explode('-', $option);
        $reflection = new ReflectionClass('App\\'.ucfirst($pieces[0]).'Translation'); 
        $module = $reflection->newInstance();
        switch ($pieces[0]) {
            case 'page':
                $module =  $module::where('locale', '=', $locale)->where('page_id', $pieces[1])->first();
                $segment[] = $module->slug;//slug
                break;
            case 'taxonomy':
                $module =  $module::with('taxonomy')->where('locale', '=', $locale)->where('taxonomy_id', $pieces[1])->first();
                $segment[] = Lang::get('module_url.'.$module->taxonomy->post_type, [], $locale).'/'.$module->slug;//slug
                break;
        }
        return implode('/', $segment);
    }

    public function generateFrontMenu($menu, $parentId = 0, $starter = false) {

        $result = null;

        foreach($menu as $item) {

            if($item->parent_id == $parentId) {

                $childItem = $this->hasChildItems($item->id);
                $result .= "<li class='".setActive($item->url)." menu-item " . (($childItem) ? 'dropdown' : null) . (($childItem && $item->parent_id != 0) ? ' dropdown-submenu' : null) . "'>
                <a href='" . langUrl($item->url) . "' " . (($childItem) ? 'class="dropdown-toggle" data-toggle="dropdown"' : null) . ">{$item->title}" . (($childItem && $item->parent_id == 0) ? '<b class="caret"></b>' : null) . "</a>" . $this->generateFrontMenu($menu, $item->id) . "
                </li>";
            }
        }

        //return $result ? "\n<ul class='" . (($starter) ? ' center main-menu ' : null) . "'>\n$result</ul>\n" : null;
        return $result ? $result : null;
    }

    public function getFrontMenuHTML($items) {

        return $this->generateFrontMenu($items, 0, true);
    }
    /* end App/Menu */
    /**
     * @param $attributes
     * @return bool|mixed
     * @throws \App\Exceptions\Validation\ValidationException
     */
    public function create($attributes) {
        if($this->isValid($attributes)) {
            if ($attributes['type'] == 'module') {
                $option = $attributes['option'];
                $url = $this->getUrl($option);
                $attributes['url'] = $url;
            }
            /*$host = $_SERVER['SERVER_NAME'];
            $urlInfo = parse_url($formData['url']);
            if (isset($urlInfo['host']))
                $url = ($host == $urlInfo['host']) ? $urlInfo['path'] : $formData['url'];
            else
                $url = ($formData['type'] == 'module') ? $formData['url'] : "http://" . $formData['url'];*/
            $t_attributes['menu_group_id'] = $attributes['menu_group'];
            $t_attributes['type'] = $attributes['type'];
            $t_attributes['option'] = $attributes['option'];
            $t_attributes['is_published'] = isset($attributes['is_published']) ? true : false;
            $t_attributes['parent_id'] = $attributes['parent']?$attributes['parent']:null;
            $t_attributes['order'] = $this->getMaxOrder() + 1;
            $langs = LaravelLocalization::getSupportedLocales();
            foreach ($langs as $locale => $properties) {
                $t_attributes[$locale] = [
                    'title' => $attributes['title'][$locale],
                    'url' => $attributes['url'][$locale],
                ];
            }
            $this->menu->fill($t_attributes)->save();
            return true;
        }
        throw new ValidationException( 'Menu validation failed', $this->getErrors());


        /*

        $formData = Request::all();
        if ($formData['type'] == 'module') {
            $option = $formData['option'];
            $url = $this->menu->getUrl($option);
            $formData['url'] = $url;
        }

        $host = $_SERVER['SERVER_NAME'];
        $urlInfo = parse_url($formData['url']);

        $rules = array(
            'title' => 'required',
            'url'   => 'required'
        );

        $validation = Validator::make($formData, $rules);

        if ($validation->fails()) {
            return langRedirectRoute('admin.menu.create')->withErrors($validation)->withInput();
        }

        $this->menu->fill($formData);
        $this->menu->order = $this->menu->getMaxOrder() + 1;

        if (isset($urlInfo['host']))
            $url = ($host == $urlInfo['host']) ? $urlInfo['path'] : $formData['url'];
        else
            $url = ($formData['type'] == 'module') ? $formData['url'] : "http://" . $formData['url'];

        $this->menu->lang = getLang();
        $this->menu->url = $url;
        $this->menu->save();

        Notification::success('Menu was successfully added');
        return langRedirectRoute('admin.menu.index');

        */
    }


    /**
     * @param $id
     * @param $attributes
     * @return bool|mixed
     * @throws \App\Exceptions\Validation\ValidationException
     */
    public function update($id, $attributes) {
        if($this->isValid($attributes)) {
            $this->menu = $this->find($id);
            if ($attributes['type'] == 'module') {
                $option = $attributes['option'];
                $url = $this->getUrl($option);
                $attributes['url'] = $url;
            }
            //$t_attributes['menu_group_id'] = $attributes['menu_group'];
            $t_attributes['type'] = $attributes['type'];
            $t_attributes['option'] = $attributes['option'];
            $t_attributes['is_published'] = isset($attributes['is_published']) ? true : false;
            $t_attributes['parent_id'] = $attributes['parent']?$attributes['parent']:null;
            $langs = LaravelLocalization::getSupportedLocales();
            foreach ($langs as $locale => $properties) {
                $t_attributes[$locale] = [
                    'title' => $attributes['title'][$locale],
                    'url' => $attributes['url'][$locale],
                ];
            }
            $this->menu->fill($t_attributes)->save();

            return true;
        }

        throw new ValidationException('Menu validation failed', $this->getErrors());
    }

    /**
     * @param $id
     * @return mixed|void
     */
    public function delete($id) {

        $this->page->findOrFail($id)->delete();
    }
}
