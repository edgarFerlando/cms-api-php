<?php namespace App\Http\Controllers\Backend;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Repositories\Article\ArticleInterface;
use App\Repositories\Taxonomy\TaxonomyInterface;
use Redirect;
use View;
use Input;
use Validator;
use Response;
use Notification;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use App\Repositories\Article\ArticleRepository as Article;
use App\Repositories\Taxonomy\TaxonomyRepository as Category;
use App\Exceptions\Validation\ValidationException;
use Config;
use Entrust;

class ArticleController extends Controller {

	protected $article;
    protected $category;
    protected $taxonomy;

    public function __construct(ArticleInterface $article, TaxonomyInterface $category) {

        View::share('active', 'blog');
        $this->article = $article;
        $this->category = $category;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index() {
        $attr = [ 
                'title' => trans('app.article')
            ];
        if(!Entrust::can(['read_article'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $page = Input::get('page', 1);
        $perPage = config_db_cached('settings::backend_per_page');

        $filter = Input::all(); 
        unset($filter['_token']);

        $pagiData = $this->article->paginate($page, $perPage, $filter);
        $totalItems = $pagiData->totalItems;
        $articles = new LengthAwarePaginator($pagiData->items, $totalItems, $perPage, $page, [
            'path' => Paginator::resolveCurrentPath()
        ]);

        $articles->setPath("");
        $articles->appends($filter);

        $cats_raw = $this->category->getTermsByPostType('article')->toHierarchy();
        $cat_options[' '] = '-';
        $cat_options += renderLists($cats_raw); 

        return view('backend.article.index', compact('articles', 'cat_options', 'totalItems'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create() {
        $attr = [ 
                'title' => trans('app.article')
            ];
        if(!Entrust::can(['create_article'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $category_options[''] = '-';
        $category_options += renderLists($this->category->getTermsByPostType('article'));
        return view('backend.article.create', compact('category_options'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store() {
        try {
            $this->article->create(Input::all());
            Notification::success( trans('app.article_added') );
            return langRedirectRoute('admin.article.index');
        } catch (ValidationException $e) {
            return langRedirectRoute('admin.article.create')->withInput()->withErrors($e->getErrors());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function show($id) {
        $attr = [ 
                'title' => trans('app.article')
            ];
        if(!Entrust::can(['read_article'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $article = $this->article->find($id);
        return view('backend.article.show', compact('article'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function edit($id) {
        $attr = [ 
                'title' => trans('app.article')
            ];
        if(!Entrust::can(['update_article'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $article = $this->article->find($id);
        /*$tags = null;

        foreach ($article->tags as $tag) {
            $tags .= ',' . $tag->name;
        }
        $tags = substr($tags, 1);*/
        $category_options[''] = '-';
        $category_options += renderLists($this->category->getTermsByPostType('article'));
        return view('backend.article.edit', compact('article','category_options'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int $id
     * @return Response
     */
    public function update($id) {
        try {
            $this->article->update($id, Input::all());
            Notification::success( trans('app.article_updated') );
            return langRedirectRoute('admin.article.index');
        } catch (ValidationException $e) {
            return langRedirectRoute('admin.article.edit', [ 'id' => $id ] )->withInput()->withErrors($e->getErrors());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return Response
     */
    public function destroy($id) {

        $this->article->delete($id);
        Notification::success( trans('app.article_deleted') );
        return langRedirectRoute('admin.article.index');
    }

    public function confirmDestroy($id) {
        $attr = [ 
                'title' => trans('app.article')
            ];
        if(!Entrust::can(['delete_article'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $article = $this->article->find($id);
        return view('backend.article.confirm-destroy', compact('article'));
    }

    public function togglePublish($id) {

        return $this->article->togglePublish($id);
    }

}