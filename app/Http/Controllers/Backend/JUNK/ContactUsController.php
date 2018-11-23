<?php namespace App\Http\Controllers\Backend;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Repositories\ContactUs\ContactUsInterface;
use Redirect;
use View;
use Input;
use Validator;
use Response;
use Notification;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use App\Repositories\ContactUs\ContactUsRepository as ContactUs;
use App\Exceptions\Validation\ValidationException;
use Config;
use Cache;

use Entrust;

class ContactUsController extends Controller {

    protected $contactUs;

    public function __construct(ContactUsInterface $contactUs) {
        $this->contactUs = $contactUs;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index() {
        $attr = [ 
                'title' => trans('app.product_attribute')
            ];
        if(!Entrust::can(['read_contact'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $page = Input::get('page', 1);
        $perPage = Config::get('holiday.per_page');
        $pagiData = $this->contactUs->paginate($page, $perPage, true);
        $contactUss = new LengthAwarePaginator($pagiData->items, $pagiData->totalItems, $perPage, $page, [
            'path' => Paginator::resolveCurrentPath()
        ]);
        $contactUss->setPath('');
        return view('backend.contactUs.index', compact('contactUss'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create() {
        $attr = [ 
                'title' => trans('app.contact_us')
            ];
        if(!Entrust::can(['update_contact'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        return view('backend.contact.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store() {
        try {
            $config_map = [
                'name' => 'company_name',
                'address' => 'address',
                'phone' => 'phone',
                'cs_phone' => 'cs_phone',
                'fax' => 'fax',
                'emails' => 'emails',
                'socmed_facebook' => 'socmed_facebook',
                'socmed_twitter' => 'socmed_twitter',
                'socmed_instagram' => 'socmed_instagram',
                'gmap_lat' => 'gmap_lat',
                'gmap_lng' => 'gmap_lng'
            ];

            foreach($config_map as $ff_name => $config_key){
                config_db()->set('contact_us::'.$config_key, Input::get($ff_name));
                Cache::forever('contact_us::'.$config_key, Input::get($ff_name));//update cache
            }
            
            Notification::success( trans('app.contact_us_updated'));
            return Redirect::route('admin.contact_us');
        } catch (ValidationException $e) {
            return Redirect::route('admin.contact_us')->withInput()->withErrors($e->getErrors());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function show($id) {

        $contactUs = $this->contactUs->find($id);
        return view('backend.contactUs.show', compact('contactUs'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function edit($id) { 
        
        $contactUs = $this->contactUs->find($id);
        return view('backend.contactUs.edit', compact('contactUs'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int $id
     * @return Response
     */
    public function update($id) {
        try {   
            $this->contactUs->update($id, Input::all());
            Notification::success(trans('app.product_attribute_updated'));
            return langRedirectRoute('admin.product.attribute.index');
        } catch (ValidationException $e) {
            return langRedirectRoute('admin.product.attribute.edit', [ 'id' => $id ] )->withInput()->withErrors($e->getErrors());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return Response
     */
    public function destroy($id) {
        $this->contactUs->delete($id);
        Notification::success(trans('app.product_attribute_deleted'));
        return langRedirectRoute('admin.product.attribute.index');
    }

    public function confirmDestroy($id) {

        $contactUs = $this->contactUs->find($id);
        return view('backend.contactUs.confirm-destroy', compact('contactUs'));
    }

    public function togglePublish($id) {
        return $this->contactUs->togglePublish($id);
    }
}