<?php namespace App\Http\Controllers\backend;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Redirect;
use View;
use Input;
use Validator;
use Response;
use Request;
use Notification;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use App\Repositories\Taxonomy\TaxonomyInterface;
use App\Repositories\Taxonomy\TaxonomyRepository as Taxonomy;
use App\Exceptions\Validation\ValidationException;


class TaxonomyController extends Controller {

	protected $taxonomy;
    protected $productAttribute;

    public function __construct(TaxonomyInterface $taxonomy) {

        $this->taxonomy = $taxonomy;
    }

    public function index($post_type) {

        //dd($this->taxonomy->testingcolumn());

        /*foreach($this->taxonomy->roots($post_type) as $prod_type){
            dd($prod_type->id);
        }*/
        $items = $this->taxonomy->getTermsByPostType($post_type);
        $taxonomies = $this->taxonomy->getMenuHTML($post_type, $items);
        return view('backend.taxonomy.index', compact('post_type', 'taxonomies'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create($post_type) { 
        $parent_options[''] = '-';
        $parent_options += renderLists($this->taxonomy->getTermsByPostType($post_type)->toHierarchy());
        
        switch ($post_type) {
            case 'action_plan':
                $view = 'backend.taxonomy.actionplan.create';
                break;
            case 'branch':
                $view = 'backend.taxonomy.branch.create';
                break;
            case 'financial_health_structure':
                $view = 'backend.taxonomy.financialHealth.create';
                break;
            default:
                $view = 'backend.taxonomy.create';
                break;
        }

        return view($view, compact('parent_options', 'post_type'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store() {
        $post_type = Input::get('post_type'); 
        try {
            $this->taxonomy->create(Input::all());
            Notification::success(trans('app.data_added'));
            return Redirect::route('admin.taxonomy.index', [ 'post_type' =>  $post_type ]);
        } catch (ValidationException $e) {
            return Redirect::route('admin.taxonomy.create', [ 'post_type' =>  $post_type ])->withInput()->withErrors($e->getErrors());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return Response
     */
    public function show($post_type, $id) {

        $term = $this->taxonomy->find($id);
        
        /*if($term){
            if($term->productAttributeTaxonomy){
                $productAttributes = [];
                foreach($term->productAttributeTaxonomy as $product_attribute){
                    $productAttributes[] = $product_attribute->productAttribute->productAttributeTranslation->name;
                }
            }
            $term->productAttributes = $productAttributes;
        }*/
        //dd($term);
        return view('backend.taxonomy.show', compact('term'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return Response
     */
    public function edit($post_type, $id) {
        $parent_options[''] = '-';
        $parent_options += renderLists($this->taxonomy->getTermsByPostType($post_type));
        $term = $this->taxonomy->findWithMetas($id);
        $taxoMeta = userMeta($term->taxoMetas);

        switch ($post_type) {
            case 'action_plan':
                $view = 'backend.taxonomy.actionplan.edit';
                break;
            case 'branch':
                $view = 'backend.taxonomy.branch.edit';
                break;
            case 'financial_health_structure':
                $view = 'backend.taxonomy.financialHealth.edit';
                break;
            default:
                $view = 'backend.taxonomy.edit';
                break;
        }

        return view($view, compact('post_type', 'term', 'parent_options', 'taxoMeta'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param int $id
     * @return Response
     */
    public function update($id) {
        $post_type = Input::get('post_type');     
        try {
            $this->taxonomy->update($id, Input::all());
            Notification::success(trans('app.data_updated'));
            return Redirect::route('admin.taxonomy.index', [ $post_type ]);
        } catch (ValidationException $e) {
            return Redirect::route('admin.taxonomy.edit', [ $post_type, $id ])->withInput()->withErrors($e->getErrors());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return Response
     */
    public function destroy($post_type, $id) {

        $this->taxonomy->delete($id);
        Notification::success( trans('app.data_deleted') );
        return Redirect::route('admin.taxonomy.index', [ $post_type ]);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function confirmDestroy($post_type, $id) {
        $term = $this->taxonomy->find($id);
        return view('backend.taxonomy.confirm-destroy', compact('term'));
    }

    public function save() {
        $this->taxonomy->changeParentById($this->taxonomy->parseJsonArray(json_decode(Request::get('json'), true)));
        return Response::json(array('result' => 'success'));
    }

    public function getListAssetRepaymentCategories(){
		$asset_repayment_categories_raw = $this->taxonomy->getTermsByPostType_n_parent('wallet', 'asset')->toHierarchy();
        $item_options = renderLists($asset_repayment_categories_raw);
        //return $parent_options;

        $item_arr = []; //dd($item_options);
        foreach($item_options as $item_id => $item_val){
            $item_arr[] = [ 
                'id' => $item_id,
                'name' => $item_val
            ];
        }

        return Response::json($item_arr);
        //return Response::json([
        //    'suggestions' => $clients_arr
        //]);
	}

    /*public function getProductAttributes(){
        $id = Input::get('id');
        $taxo = $this->taxonomy->find($id);
        foreach()
        return Response::json($taxo);
    }*/

}

