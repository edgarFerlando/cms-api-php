<?php namespace App\Http\Controllers\Backend;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Repositories\Customer\CustomerInterface;
use Redirect;
use View;
use Input;
use Validator;
use Response;
use Notification;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use App\Repositories\Customer\CustomerRepository as Customer;
use App\Exceptions\Validation\ValidationException;
use Config;

//use Illuminate\Contracts\Auth\Guard;
//use Illuminate\Contracts\Auth\Registrar;
//use Illuminate\Foundation\Auth\AuthenticatesAndRegistersCustomers;


class CustomerController extends Controller {

    protected $customer;

    public function __construct(CustomerInterface $customer){//, Guard $auth, Registrar $registrar) {
        $this->customer = $customer;
        //$this->auth = $auth;
		//$this->registrar = $registrar;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index() {

        $page = Input::get('page', 1);
        $perPage = Config::get('holiday.per_page');
        $pagiData = $this->customer->paginate($page, $perPage, true);
        $customers = new LengthAwarePaginator($pagiData->items, $pagiData->totalItems, $perPage, $page, [
            'path' => Paginator::resolveCurrentPath()
        ]);
        $customers->setPath('');
        return view('backend.customer.index', compact('customers'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create() {

        return view('backend.customer.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request) {
        try {
            $this->customer->create(Input::all());
            Notification::success( trans('app.customer_added') );
            return langRedirectRoute('admin.customer.index');
        } catch (ValidationException $e) {
            return langRedirectRoute('admin.customer.create')->withInput()->withErrors($e->getErrors());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function show($id) {

        $customer = $this->customer->find($id);
        return view('backend.customer.show', compact('customer'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function edit($id) { 
        $customer = $this->customer->find($id);
        return view('backend.customer.edit', compact('customer'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int $id
     * @return Response
     */
    public function update($id) {
        try {   
            $this->customer->update($id, Input::all());
            Notification::success(trans('app.customer_updated'));
            return langRedirectRoute('admin.customer.index');
        } catch (ValidationException $e) {
            return langRedirectRoute('admin.customer.edit', [ 'id' => $id ] )->withInput()->withErrors($e->getErrors());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return Response
     */
    public function destroy($id) {

        $this->customer->delete($id);
        Notification::success(trans('app.customer_deleted'));
        return langRedirectRoute('admin.customer.index');
    }

    public function confirmDestroy($id) {

        $customer = $this->customer->find($id);
        return view('backend.customer.confirm-destroy', compact('customer'));
    }
}
