<?php namespace App\Http\Controllers\Backend;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Repositories\Affiliate\AffiliateInterface;
use App\Repositories\Referral\ReferralInterface;
use App\Repositories\Permission\PermissionInterface;
use Redirect;
use View;
use Input;
use Validator;
use Response;
use Notification;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use App\Repositories\Affiliate\AffiliateRepository as Affiliate;
use App\Repositories\Permission\PermissionRepository as Permission;
use App\Exceptions\Validation\ValidationException;
use Config;
use Entrust;
use HTML;
use App\Models\BookingStatus;


class AffiliateController extends Controller {

	protected $affiliate;
    protected $permission;
    protected $referral;

    public function __construct(ReferralInterface $referral, AffiliateInterface $affiliate, PermissionInterface $permission) {
        $this->affiliate = $affiliate;
        $this->permission = $permission;
        $this->referral = $referral;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index() {
        $attr = [
                'title' => trans('app.affiliate')
            ];

        if(!Entrust::can(['read_affiliate'])){
            $attr += [
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }


        $page = Input::get('page', 1);
        $perPage = config_db_cached('settings::backend_per_page'); 
        $filter = Input::all();  
        unset($filter['_token']);
        $pagiData = $this->affiliate->paginate($page, $perPage, $filter);

        $affiliates = new LengthAwarePaginator($pagiData->items, $pagiData->totalItems, $perPage, $page, [
            'path' => Paginator::resolveCurrentPath()
        ]);
        $affiliates->setPath("");
        $affiliates->appends($filter); 
        return view('backend.affiliate.index', compact('affiliates'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create() {
        $attr = [
                'title' => trans('app.affiliate'),
                'box_title' => trans('app.add_new')
            ];

        if(!Entrust::can(['create_affiliate'])){
            $attr += [
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }


        $permissions = $this->permission->all();
        return view('backend.affiliate.create', compact('permissions'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store() {
        try {
            $this->affiliate->create(Input::all());
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

        $affiliate = $this->affiliate->find($id);

        $attr = [
                'title' => trans('app.affiliate'),
                'box_title' => fulldate_trans($affiliate->created_at).' [ '.$affiliate->user->name.' ]'
            ];
        if(!Entrust::can(['read_affiliate'])){
            $attr += [
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }


        return view('backend.affiliate.show', compact('attr', 'affiliate'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function edit($id) {
        $affiliate = $this->affiliate->find($id);

        $attr = [ 
                'title' => trans('app.affiliate'),
                'box_title' => trans('app.edit')
            ];
        if(!Entrust::can(['update_affiliate'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }

        return view('backend.affiliate.edit', compact('affiliate'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int $id
     * @return Response
     */
    public function update($id) {
        try {
            $this->affiliate->update($id, Input::all());
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

        $this->affiliate->delete($id);
        Notification::success( trans('app.data_deleted') );
        return langRedirectRoute('admin.payment-confirmation.index');
    }

    public function confirmDestroy($id) {

        $affiliate = $this->affiliate->find($id);

        $attr = [
                'title' => trans('app.affiliate'),
                'box_title' => HTML::decode(trans('app.delete_confirm')).' <b> '.trans('app.affiliate').' '.fulldate_trans($affiliate->created_at).' [ Booking no : '.$affiliate->booking->booking_no.' ] </b> ?'
            ];
        if(!Entrust::can(['delete_affiliate'])){
            $attr += [
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }


        return view('backend.affiliate.confirm-destroy', compact('attr', 'affiliate'));
    }

    public function togglePublishJUNK($id) {

        return $this->affiliate->togglePublish($id);
    }

    public function referrals() {
        $attr = [
                'title' => trans('app.referral')
            ];

        if(!Entrust::can(['read_referral'])){
            $attr += [
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }

        $page = Input::get('page', 1);
        $perPage = config_db_cached('settings::backend_per_page'); 
        $filter = Input::all();  
        unset($filter['_token']);
        $pagiData = $this->referral->paginate($page, $perPage, $filter);

        $referrals = new LengthAwarePaginator($pagiData->items, $pagiData->totalItems, $perPage, $page, [
            'path' => Paginator::resolveCurrentPath()
        ]);
        $referrals->setPath("");
        $referrals->appends($filter); 
        $affiliate_name_options[''] = '';
        $affiliate_name_options += $this->affiliate->lists('name', 'id');

        $status_options[''] = '';
        $status_options += BookingStatus::all()->lists('name', 'id');
        return view('backend.affiliate.referrals', compact('referrals', 'affiliate_name_options', 'status_options'));
    }

}