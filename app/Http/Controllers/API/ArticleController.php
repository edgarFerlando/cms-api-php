<?php namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use Auth;
use App\Repositories\Article\ArticleInterface;
use Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class ArticleController extends Controller {
	protected $article;

    public function __construct(ArticleInterface $article) {
        $this->article = $article;
    }
	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index(Request $request) {
		$page = $request->input('page');
		$perPage = $request->input('perpage');

        //$page = Input::get('page', 1);
        //$perPage = config_db_cached('settings::backend_per_page');

        $filter = $request->input('filter');
        //unset($filter['_token']);

        $pagiData = $this->article->paginate($page, $perPage, $filter);
        $totalItems = $pagiData->totalItems;
        $articles = new LengthAwarePaginator($pagiData->items, $totalItems, $perPage, $page, [
            'path' => Paginator::resolveCurrentPath()
        ]);
		
        $articles->setPath("");
        $articles->appends($filter); return $articles;
        return response()->json([
			'result' => 'success',
			'data' => $articles,
			'totalItems' => $totalItems
		]);
    }

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		//
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{	
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show(Request $request) {
		$id = $request->input('id');
        $article = $this->article->find($id);
        return response()->json([
			'result' => 'success',
			'data' => $article
		]);
    }

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update()
	{
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy()
	{
		
	}

}
