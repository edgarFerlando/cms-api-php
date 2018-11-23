<?php namespace App\Http\Controllers;

use App\Repositories\Article\ArticleInterface;
use App\Repositories\Taxonomy\TaxonomyInterface;
//use App\Repositories\Tag\TagInterface;
//use App\Repositories\Category\CategoryInterface;
//use App\Repositories\Category\CategoryRepository as Category;
//use App\Repositories\Tag\TagRepository as Tag;
use Input;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use View;

class ArticleController extends Controller {

    protected $article;
    protected $tag;
    protected $category;

    public function __construct(ArticleInterface $article, TaxonomyInterface $category) {

        $this->article = $article;
        //$this->tag = $tag;
        $this->category = $category;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index() {
        //dd('test');
        $page = Input::get('page', 1);
        $perPage = 5;
        $pagiData = $this->article->paginate($page, $perPage, false);


        $articles = new LengthAwarePaginator($pagiData->items, $pagiData->totalItems, $perPage, $page, [
            'path' => Paginator::resolveCurrentPath()
        ]);

        $articles->setPath("");

        //$tags = $this->tag->all();
        $categories = $this->category->all();
        return view('frontend.article.index', compact('articles', 'categories'));
    }

    /**
     * @param $id
     * @return \Illuminate\View\View
     */
    public function show($slug) {

        $article = $this->article->getBySlug($slug);
        if($article === null)
            return Response::view('errors.missing', array(), 404);

        View::composer('frontend.layout', function ($view) use ($article) {

            $view->with('meta_keywords', $article->meta_keywords);
            $view->with('meta_description', $article->meta_description);
        });

        $categories = $this->category->all();
        //$tags = $this->tag->all();
        return view('frontend.article.show', compact('article', 'categories'));
    }
}
