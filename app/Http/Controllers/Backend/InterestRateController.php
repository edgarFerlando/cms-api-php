<?php namespace App\Http\Controllers\Backend;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Repositories\InterestRate\InterestRateInterface;
//use App\Repositories\InterestRateModule\InterestRateModuleInterface;
use Redirect;
use View;
use Input;
use Validator;
use Response;
use Notification;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use App\Repositories\InterestRate\InterestRateRepository as InterestRate;
//use App\Repositories\InterestRateModule\InterestRateModuleRepository as Category;
use App\Exceptions\Validation\ValidationException;
use Config;
use App\Repositories\Taxonomy\TaxonomyInterface;

use App\Models\InterestRateModule;

class InterestRateController extends Controller {

	protected $interestRate;
    //protected $interestRateModule;

    public function __construct(InterestRateInterface $interestRate, TaxonomyInterface $taxonomy) {

        //View::share('active', 'blog');
        $this->interestRate = $interestRate;
        $this->taxonomy = $taxonomy;
        //$this->interestRateModule = new InterestRateModule;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index() {
        $page = Input::get('page', 1);
        $perPage = config_db_cached('settings::backend_per_page');
        $pagiData = $this->interestRate->paginate($page, $perPage, true);

        $interestRates = new LengthAwarePaginator($pagiData->items, $pagiData->totalItems, $perPage, $page, [
            'path' => Paginator::resolveCurrentPath()
        ]);

        $interestRates->setPath("");

        return view('backend.interestRate.index', compact('interestRates'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create() {
        $taxo_wallet_asset_options[''] = '-';
        $taxo_wallet_asset_options += renderLists($this->taxonomy->getTermsByPostType_n_parent('wallet', 'asset')->toHierarchy());
        return view('backend.interestRate.create', compact('taxo_wallet_asset_options'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store() {
        try {
            $input = Input::all();
            $input['rate'] = unformat_money_raw($input['rate']); //dd($input);
            $this->interestRate->create($input);
            Notification::success( trans('app.data_added') );
            return langRedirectRoute('admin.settings.finance.interest-rate.index');
        } catch (ValidationException $e) {
            return langRedirectRoute('admin.settings.finance.interest-rate.create')->withInput()->withErrors($e->getErrors());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function show($id) {

        $interestRate = $this->interestRate->find($id);
        return view('backend.interestRate.show', compact('interestRate'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function edit($id) {

        $interestRate = $this->interestRate->find($id);
        $taxo_wallet_asset_options[''] = '-';
        $taxo_wallet_asset_options += renderLists($this->taxonomy->getTermsByPostType_n_parent('wallet', 'asset')->toHierarchy());
        return view('backend.interestRate.edit', compact('interestRate','taxo_wallet_asset_options'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int $id
     * @return Response
     */
    public function update($id) {
        try {
            $input = Input::all();
            $input['rate'] = unformat_money_raw($input['rate']);
            $this->interestRate->update($id, $input);
            Notification::success( trans('app.data_updated') );
            return langRedirectRoute('admin.settings.finance.interest-rate.index');
        } catch (ValidationException $e) {
            return langRedirectRoute('admin.settings.finance.interest-rate.edit', [ 'id' => $id ] )->withInput()->withErrors($e->getErrors());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return Response
     */
    public function destroy($id) {
        try {
            $this->interestRate->delete($id);
            Notification::success( trans('app.data_deleted') );
            return langRedirectRoute('admin.settings.finance.interest-rate.index');
        } catch (ValidationException $e) {
            return langRedirectRoute('admin.settings.finance.interest-rate.delete', [ 'id' => $id ] )->withInput()->withErrors($e->getErrors());
        }
    }

    public function confirmDestroy($id) {

        $interestRate = $this->interestRate->find($id);
        return view('backend.interestRate.confirm-destroy', compact('interestRate'));
    }

    public function togglePublish($id) {

        return $this->interestRate->togglePublish($id);
    }

}