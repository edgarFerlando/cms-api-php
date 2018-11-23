<?php namespace App\Repositories\Article;

use App\Article;
use Config;
use Response;
//use App\Tag;
use App\Category;
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
use Auth;


class ArticleRepository extends RepositoryAbstract implements ArticleInterface, CrudableInterface {

    /*protected $width;
    protected $height;
    protected $thumbWidth;
    protected $thumbHeight;
    protected $imgDir;*/
    protected $perPage;
    protected $article;
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
     * @param Article $article
     */
    public function __construct(Article $article) {

        $config = Config::get('holiday');
        $this->perPage = $config['per_page'];
        /*$this->width = $config['modules']['article']['image_size']['width'];
        $this->height = $config['modules']['article']['image_size']['height'];
        $this->thumbWidth = $config['modules']['article']['thumb_size']['width'];
        $this->thumbHeight = $config['modules']['article']['thumb_size']['height'];
        $this->imgDir = $config['modules']['article']['image_dir'];*/
        $this->article = $article;

        $rules_n_attributeNames = $this->rules();
        self::$rules = $rules_n_attributeNames['rules'];
        self::$attributeNames = $rules_n_attributeNames['attributeNames'];
    }

    public function rules(){
        $_rules = array();
        $setAttributeNames = array();
        $_rules['category'] = 'required';
        $_rules['title'] = 'required|max:255';
        $_rules['slug'] = 'required|alpha_dash|max:255';
        $_rules['body'] = 'required';
        $_rules['source_name'] = 'required';
        $_rules['source_url'] = 'required';

        $setAttributeNames['title'] = trans('app.title');
        $setAttributeNames['slug'] = trans('app.slug');
        $setAttributeNames['body'] = trans('app.content');
        $setAttributeNames['source_name'] = trans('app.source');
        $setAttributeNames['source_url'] = trans('app.source_url');

        $setAttributeNames['category'] = trans('app.category');
        return [
            'rules' => $_rules,
            'attributeNames' => $setAttributeNames
        ];
    }

    /**
     * @return mixed
     */
    public function all() {
        return $this->article->orderBy('created_at', 'DESC')->where('is_published', 1)->get();
    }

    /**
     * @param $limit
     * @return mixed
     */
    public function getLastArticle($limit) {

        return $this->article->orderBy('created_at', 'desc')->take($limit)->offset(0)->get();
    }

    /**
     * @return mixed
     */
    public function lists() {

        //return $this->article->get()->lists('title', 'id');
        return $this->article->all()->lists('title', 'id');
    }

    /**
     * Get paginated articles
     *
     * @param int $page Number of articles per page
     * @param int $limit Results per page
     * @param boolean $all Show published or all
     * @return StdClass Object with $items and $totalItems for pagination
     */
    public function paginate($page = 1, $limit = 10, $filter = array()) {

        $result = new \StdClass;
        $result->page = $page;
        $result->limit = $limit;
        $result->totalItems = 0;
        $result->items = array();

        //$query = $this->article->with('tags')->orderBy('created_at', 'DESC');
        $query = $this->article->select('articles.*', 'uc.name as created_by_name', 'uu.name as updated_by_name')//, 't.title as category_name')
        ->with(['category'])
        ->orderBy('created_at', 'DESC');
        $query->join('users as uc', 'uc.id', '=', 'articles.created_by', 'left');
        $query->join('users as uu', 'uu.id', '=', 'articles.updated_by', 'left');
        //$query->join('taxonomies as t', 't.id', '=', 'articles.category_id', 'left');

        if(is_array($filter)){
            foreach($filter as $ff => $term){
                if(trim($term) != ''){
                    switch ($ff) {
                        case 'title':
                            $query->whereRaw('LOWER(title) like ?', [ '%'.strtolower($term).'%' ]);
                        break;
                        case 'category':
                            $query->where('category_id', $term);
                        break;
                        case 'is_published':
                            if($term  !== '')//tidak kosong, tapi harus 0 atau 1 saja
                                $query->where('is_published', $term);
                        break;
                    }
                }
            }
        }

        $articles = $query->skip($limit * ($page - 1))->take($limit)->get();

        $result->totalItems = $this->totalArticles($filter);
        $result->items = $articles->all();
        return $result;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function find($id) {
        return $this->article->with(['category'])->find($id);
        //return $this->article->with(['tags', 'category'])->findOrFail($id);
    }

    /**
     * @param $slug
     * @return mixed
     */
    /*public function getBySlug($slug) {
        //return $this->article->with(['tags', 'category'])->where('slug', $slug)->first();
        return $this->article->with(['category'])->where('slug', $slug)->first();
    }*/
     public function getBySlug($slug, $isPublished = false) {
        if($isPublished === true)
           return $this->article->select('articles.id', 'article_translations.slug')
            ->join('article_translations', 'articles.id', '=', 'article_translations.article_id')
            ->where('slug', $slug)->where('is_published', true)->firstOrFail();

        return $this->article->select('articles.id', 'article_translations.slug')
            ->join('article_translations', 'articles.id', '=', 'article_translations.article_id')
            ->where('slug', $slug)->firstOrFail();
    }

    /**
     * @param $attributes
     * @return bool|mixed
     * @throws \App\Exceptions\Validation\ValidationException
     */
    public function create($attributes) {
        $t_attributes = array();
        $user_id = Auth::user()->id;
        $t_attributes['category_id'] = $attributes['category'];
        $t_attributes['source_name'] = $attributes['source_name'];
        $t_attributes['source_url'] = $attributes['source_url'];
        $t_attributes['is_published'] = isset($attributes['is_published']) ? true : false;
        $t_attributes['featured_image'] = isset($attributes['featured_image'])?getImagePath($attributes['featured_image']):'';

        if($this->isValid($attributes)) {
            $t_attributes += [
                'title' => $attributes['title'],
                'slug' => $attributes['slug'],
                'body' => $attributes['body'],
                'created_by' => $user_id,
                'updated_by' => $user_id,
                'record_flag' => 'N'
            ];
            $this->article->fill($t_attributes)->save();

            return true;
        }
        throw new ValidationException('Article validation failed', $this->getErrors());
    }

    /**
     * @param $id
     * @param $attributes
     * @return bool|mixed
     * @throws \App\Exceptions\Validation\ValidationException
     */
    public function update($id, $attributes) {
        $t_attributes = array();
        $user_id = Auth::user()->id;
        $t_attributes['category_id'] = $attributes['category'];
        $t_attributes['source_name'] = $attributes['source_name'];
        $t_attributes['source_url'] = $attributes['source_url'];
        $t_attributes['is_published'] = isset($attributes['is_published']) ? true : false;
        $t_attributes['featured_image'] = isset($attributes['featured_image'])?getImagePath($attributes['featured_image']):'';
        if($this->isValid($attributes)) {
            $this->article = $this->find($id);
            $t_attributes += [
                'title' => $attributes['title'],
                'slug' => $attributes['slug'],
                'body' => $attributes['body'],
                'created_by' => $user_id,
                'updated_by' => $user_id,
                'record_flag' => 'U'
            ];
            $this->article->fill($t_attributes)->save();

            return true;
        }
        throw new ValidationException('Article validation failed', $this->getErrors());
    }

    /**
     * @param $id
     * @return mixed|void
     */
    public function delete($id) {

        $article = $this->article->findOrFail($id);
        //$article->tags()->detach();
        $article->delete();
    }

    /**
     * @param $id
     * @return mixed
     */
    public function togglePublish($id) {

        $article = $this->article->find($id);

        $article->is_published = ($article->is_published) ? false : true;
        $article->save();

        return Response::json(array('result' => 'success', 'changed' => ($article->is_published) ? 1 : 0));
    }

    /**
     * @param $id
     * @return string
     */
    function getUrl($id) {

        $article = $this->article->findOrFail($id);
        return url('article/' . $id . '/' . $article->slug, $parameters = array(), $secure = null);
    }

    /**
     * Get total article count
     * @param bool $all
     * @return mixed
     */
    protected function totalArticles($filter = array()) {
        $query = $this->article->select('articles.id');
        if(is_array($filter)){ 
            foreach($filter as $ff => $term){
                if(trim($term) != ''){
                    switch ($ff) {
                        case 'title':
                            $query->whereRaw('LOWER(title) like ?', [ '%'.strtolower($term).'%' ]);
                        break;
                        case 'category':
                            $query->where('category_id', $term);
                        break;
                        case 'is_published':
                            if($term  !== '')//tidak kosong, tapi harus 0 atau 1 saja
                                $query->where('is_published', $term);
                        break;
                    }
                }
            }
        }
       
        return $query->count();
    }

    public function allByJUNK($page = 1, $limit = 10, $filter = array()) {

        $result = new \StdClass;
        $result->page = $page;
        $result->limit = $limit;
        $result->totalItems = 0;
        $result->items = array();

        $query = $this->article->select('articles.*', 'uc.name as created_by_name', 'uu.name as updated_by_name')
        ->orderBy('created_at', 'DESC');
        $query->join('users as uc', 'uc.id', '=', 'articles.created_by', 'left');
        $query->join('users as uu', 'uu.id', '=', 'articles.updated_by', 'left');

        if(is_array($filter)){
            foreach($filter as $ff => $term){
                if(trim($term) != ''){
                    switch ($ff) {
                        case 'title':
                            $query->whereRaw('LOWER(title) like ?', [ '%'.strtolower($term).'%' ]);
                        break;
                        case 'category':
                            $query->where('category_id', $term);
                        break;
                    }
                }
            }
        }

        $articles = $query->skip($limit * ($page - 1))->take($limit)->get();

        $result->totalItems = $this->totalArticles($filter);
        $result->items = $articles->all();
        return $result;
    }
}
