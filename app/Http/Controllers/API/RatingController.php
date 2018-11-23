<?php namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use Auth;
use App\Repositories\CfpRating\CfpRatingInterface;
use Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use App\Exceptions\Validation\ValidationException;

class RatingController extends Controller {
	protected $cfpRating;

    public function __construct(CfpRatingInterface $cfpRating) {
        $this->cfpRating = $cfpRating;
    }
	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index(Request $request) {

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
	public function store(Request $request)
	{	
        try {
			$data = $this->cfpRating->create($request->all());
            return response()->json([
				'result' => 'success',
				'data' => $data
			]);
        } catch (ValidationException $e) {
            return response()->json([
					'result' => 'error',
					'alerts' => $e->getErrors()
			]);
        }
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show(Request $request) {
		$id = $request->input('id');
		//$cfpRating = $this->cfpRating->find($id);
		$cfpRating = $this->cfpRating->findAverage($id);
        return response()->json([
			'result' => 'success',
			'data' => $cfpRating
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
