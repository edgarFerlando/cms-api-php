<?php namespace App\Http\Controllers\Backend;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Repositories\ActualInterestRate\ActualInterestRateInterface;
use Redirect;
use View;
use Input;
use Validator;
use Response;
use Notification;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use App\Repositories\ActualInterestRate\ActualInterestRateRepository as ActualInterestRate;
use App\Exceptions\Validation\ValidationException;
use Config;
use App\Repositories\Taxonomy\TaxonomyInterface;

use App\Models\ActualInterestRateModule;
use App\Models\InterestRate;
use Carbon\Carbon;

class ActualInterestRateController extends Controller {

	protected $actualInterestRate;

    public function __construct(ActualInterestRateInterface $actualInterestRate, TaxonomyInterface $taxonomy) {
        $this->actualInterestRate = $actualInterestRate;
        $this->taxonomy = $taxonomy;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index() {
        $page = Input::get('page', 1);
        $perPage = config_db_cached('settings::backend_per_page');
        $pagiData = $this->actualInterestRate->paginate($page, $perPage, true);

        $actualInterestRates = new LengthAwarePaginator($pagiData->items, $pagiData->totalItems, $perPage, $page, [
            'path' => Paginator::resolveCurrentPath()
        ]);

        $actualInterestRates->setPath("");

        return view('backend.actualInterestRate.index', compact('actualInterestRates'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create() {
        $interestRate_options[''] = '-';
        $interestRate_options += collect(\DB::select(
            \DB::raw('select ir.id, concat(t.title,\' \',ir.rate, \'%\') as product_name from interest_rates as ir
            left join taxonomies as t on t.id=ir.taxo_wallet_asset_id 
            where ir.record_flag <> \'D\'
            order by ir.rate asc')
        ))->lists('product_name', 'id');
        return view('backend.actualInterestRate.create', compact('interestRate_options'));
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
            $this->actualInterestRate->create($input);
            Notification::success( trans('app.data_added') );
            return langRedirectRoute('admin.settings.finance.actual-interest-rate.index');
        } catch (ValidationException $e) {
            return langRedirectRoute('admin.settings.finance.actual-interest-rate.create')->withInput()->withErrors($e->getErrors());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function show($id) {

        $actualInterestRate = $this->actualInterestRate->find($id);
        return view('backend.actualInterestRate.show', compact('actualInterestRate'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function edit($id) {

        $actualInterestRate = $this->actualInterestRate->find($id);
        $actualInterestRate['period'] = Carbon::parse($actualInterestRate->period)->format('M Y');
        $interestRate_options[''] = '-';
        $interestRate_options += collect(\DB::select(
            \DB::raw('select ir.id, concat(t.title,\' \',ir.rate, \'%\') as product_name from interest_rates as ir
            left join taxonomies as t on t.id=ir.taxo_wallet_asset_id 
            where ir.record_flag <> \'D\'
            order by ir.rate asc')
        ))->lists('product_name', 'id');
        return view('backend.actualInterestRate.edit', compact('actualInterestRate','interestRate_options'));
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
            $this->actualInterestRate->update($id, $input);
            Notification::success( trans('app.data_updated') );
            return langRedirectRoute('admin.settings.finance.actual-interest-rate.index');
        } catch (ValidationException $e) {
            return langRedirectRoute('admin.settings.finance.actual-interest-rate.edit', [ 'id' => $id ] )->withInput()->withErrors($e->getErrors());
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
            $this->actualInterestRate->delete($id);
            Notification::success( trans('app.data_deleted') );
            return langRedirectRoute('admin.settings.finance.actual-interest-rate.index');
        } catch (ValidationException $e) {
            return langRedirectRoute('admin.settings.finance.actual-interest-rate.delete', [ 'id' => $id ] )->withInput()->withErrors($e->getErrors());
        }
    }

    public function confirmDestroy($id) {
        $actualInterestRate = $this->actualInterestRate->find($id);
        return view('backend.actualInterestRate.confirm-destroy', compact('actualInterestRate'));
    }

    public function togglePublish($id) {
        return $this->actualInterestRate->togglePublish($id);
    }

}