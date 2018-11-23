<?php namespace App\Http\Controllers\Backend;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Repositories\PaymentConfirmation\PaymentConfirmationInterface;
use App\Repositories\Permission\PermissionInterface;
use Redirect;
use View;
use Input;
use Validator;
use Response;
use Notification;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use App\Repositories\PaymentConfirmation\PaymentConfirmationRepository as PaymentConfirmation;
use App\Repositories\Permission\PermissionRepository as Permission;
use App\Exceptions\Validation\ValidationException;
use Config;
use Entrust;
use HTML;


class PaymentConfirmationController extends Controller {

	protected $paymentConfirmation;
    protected $permission;

    public function __construct(PaymentConfirmationInterface $paymentConfirmation, PermissionInterface $permission) {
        $this->paymentConfirmation = $paymentConfirmation;
        $this->permission = $permission;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index() {
        $attr = [
                'title' => trans('app.payment_confirmation')
            ];
        if(!Entrust::can(['read_payment_confirmation'])){
            $attr += [
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }


        $page = Input::get('page', 1);
        $perPage = config_db_cached('settings::backend_per_page'); 
        $filter = Input::all();  
        unset($filter['_token']);
        $pagiData = $this->paymentConfirmation->paginate($page, $perPage, $filter);

        $paymentConfirmations = new LengthAwarePaginator($pagiData->items, $pagiData->totalItems, $perPage, $page, [
            'path' => Paginator::resolveCurrentPath()
        ]);
        $paymentConfirmations->setPath("");
        $paymentConfirmations->appends($filter); 
        return view('backend.paymentConfirmation.index', compact('paymentConfirmations'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create() {
        $attr = [
                'title' => trans('app.payment_confirmation'),
                'box_title' => trans('app.add_new')
            ];
        if(!Entrust::can(['create_payment_confirmation'])){
            $attr += [
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }


        $permissions = $this->permission->all();
        return view('backend.paymentConfirmation.create', compact('permissions'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store() {
        try {
            $this->paymentConfirmation->create(Input::all());
            Notification::success( trans('app.data_added') );
            return langRedirectRoute('admin.payment-confirmation.index');
        } catch (ValidationException $e) {
            Input::merge(array('top_up_amount' => unformat_money(Input::get('top_up_amount'))));
            return langRedirectRoute('admin.payment-confirmation.create')->withInput()->withErrors($e->getErrors());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function show($id) {

        $paymentConfirmation = $this->paymentConfirmation->find($id);

        $attr = [
                'title' => trans('app.payment_confirmation'),
                'box_title' => fulldate_trans($paymentConfirmation->created_at).' [ '.$paymentConfirmation->user->name.' ]'
            ];
        if(!Entrust::can(['read_payment_confirmation'])){
            $attr += [
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }


        return view('backend.paymentConfirmation.show', compact('attr', 'paymentConfirmation'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function edit($id) {
        $paymentConfirmation = $this->paymentConfirmation->find($id);

        $attr = [ 
                'title' => trans('app.payment_confirmation'),
                'box_title' => trans('app.edit')
            ];
        if(!Entrust::can(['update_payment_confirmation'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }

        return view('backend.paymentConfirmation.edit', compact('paymentConfirmation'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int $id
     * @return Response
     */
    public function update($id) {
        try {
            $this->paymentConfirmation->update($id, Input::all());
            Notification::success( trans('app.data_updated') );
            return langRedirectRoute('admin.payment-confirmation.index');
        } catch (ValidationException $e) {
            return langRedirectRoute('admin.payment-confirmation.edit', [ 'id' => $id ] )->withInput()->withErrors($e->getErrors());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return Response
     */
    public function destroy($id) {

        $this->paymentConfirmation->delete($id);
        Notification::success( trans('app.data_deleted') );
        return langRedirectRoute('admin.payment-confirmation.index');
    }

    public function confirmDestroy($id) {

        $paymentConfirmation = $this->paymentConfirmation->find($id);

        $attr = [
                'title' => trans('app.payment_confirmation'),
                'box_title' => HTML::decode(trans('app.delete_confirm')).' <b> '.trans('app.payment_confirmation').' '.fulldate_trans($paymentConfirmation->created_at).' [ Booking no : '.$paymentConfirmation->booking->booking_no.' ] </b> ?'
            ];
        if(!Entrust::can(['delete_payment_confirmation'])){
            $attr += [
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }


        return view('backend.paymentConfirmation.confirm-destroy', compact('attr', 'paymentConfirmation'));
    }

    public function togglePublishJUNK($id) {

        return $this->paymentConfirmation->togglePublish($id);
    }

}