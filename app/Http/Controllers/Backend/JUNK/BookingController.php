<?php namespace App\Http\Controllers\Backend;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Repositories\Booking\BookingInterface;
use App\Repositories\Taxonomy\TaxonomyInterface;
use Redirect;
use View;
use Input;
use Validator;
use Response;
use Notification;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use App\Repositories\Booking\BookingRepository as Booking;
use App\Repositories\Taxonomy\TaxonomyRepository as Category;
use App\Exceptions\Validation\ValidationException;
use Config;
use App\Models\BookingStatus;

use Entrust;

class BookingController extends Controller {

	protected $booking;
    protected $category;

    public function __construct(BookingInterface $booking, TaxonomyInterface $category) {

        View::share('active', 'blog');
        $this->booking = $booking;
        $this->category = $category;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index() {
        $attr = [ 
                'title' => trans('app.orders')
            ];
        if(!Entrust::can(['read_order'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $page = Input::get('page', 1);
        $perPage = Config::get('holiday.per_page');;
        $pagiData = $this->booking->paginate($page, $perPage, true);

        $bookings = new LengthAwarePaginator($pagiData->items, $pagiData->totalItems, $perPage, $page, [
            'path' => Paginator::resolveCurrentPath()
        ]);

        $bookings->setPath("");

        return view('backend.booking.index', compact('bookings'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create() {

        $category_options[''] = '-';
        $category_options += renderLists($this->category->getTermsByPostType('booking'));
        return view('backend.booking.create', compact('category_options'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store() {
        try {
            $this->booking->create(Input::all());
            Notification::success( trans('app.booking_added') );
            return langRedirectRoute('admin.booking.index');
        } catch (ValidationException $e) {
            return langRedirectRoute('admin.booking.create')->withInput()->withErrors($e->getErrors());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function show($id) {

        $booking = $this->booking->find($id);
        return view('backend.booking.show', compact('booking'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function edit($id) {
        $attr = [ 
                'title' => trans('app.orders')
            ];
        if(!Entrust::can(['update_order'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $booking = $this->booking->find($id);

        //dd($booking);
        //$details_raw = $booking->bookingDetails; dd($details_raw);
        //$details = detailsCartFormated($details_raw); 
        //dd($details);

        /*$tags = null;

        foreach ($booking->tags as $tag) {
            $tags .= ',' . $tag->name;
        }
        $tags = substr($tags, 1);*/
        //$category_options[''] = '-';
        //$category_options += renderLists($this->category->getTermsByPostType('booking'));
        $status_options = BookingStatus::all()->lists('name', 'id');
        //dd($booking);
        return view('backend.booking.edit', compact('booking','category_options', 'status_options'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int $id
     * @return Response
     */
    public function update($id) {
        try {
            $this->booking->updateOrder($id, Input::all());
            Notification::success( trans('app.data_updated') );
            return langRedirectRoute('admin.order.hotel.index');
        } catch (ValidationException $e) {
            return langRedirectRoute('admin.order.hotel.edit', [ 'id' => $id ] )->withInput()->withErrors($e->getErrors());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return Response
     */
    public function destroy($id) {
        $attr = [ 
                'title' => trans('app.orders')
            ];
        if(!Entrust::can(['delete_order'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $this->booking->delete($id);
        Notification::success( trans('app.booking_deleted') );
        return langRedirectRoute('admin.order.hotel.index');
    }

    public function confirmDestroy($id) {
        $attr = [ 
                'title' => trans('app.orders')
            ];
        if(!Entrust::can(['delete_order'])){
            $attr += [ 
                'unauthorized_message' => trans('app.unauthorized_message')
            ];
            return view('backend.auth.unauthorized', compact('attr'));
        }
        $booking = $this->booking->find($id);
        return view('backend.booking.confirm-destroy', compact('booking'));
    }

    public function togglePublish($id) {

        return $this->booking->togglePublish($id);
    }

    public function getPrice_n_total_lbl() {
        $data = Input::all();
        return price_n_total_lbl_form([
            'detail_id' => $data['detail_id'],
            'checkin' => $data['check_in'],
            'checkout' => $data['check_out'],
            'price' => $data['price'],
            'weekend_price' => $data['weekend_price'],
            'quantity' => $data['quantity']
        ]);
    }

    public function getPlayground_price_n_total_lbl() {
        $data = Input::all();
        return playground_price_n_total_lbl_form([
            'detail_id' => $data['detail_id'],
            'playground_visit_date' => $data['playground_visit_date'],
            'price' => $data['price'],
            'weekend_price' => $data['weekend_price'],
            'quantity' => $data['quantity']
        ]);
    }

    public function getTrip_price_n_total_lbl() {
        $data = Input::all();
        return trip_price_n_total_lbl_form([
            'detail_id' => $data['detail_id'],
            'trip_visit_date' => $data['trip_visit_date'],
            'price' => $data['price'],
            'weekend_price' => $data['weekend_price'],
            'quantity' => $data['quantity']
        ]);
    }

    public function getMerchant_price_n_total_lbl() {
        $data = Input::all();
        return merchant_price_n_total_lbl_form([
            'detail_id' => $data['detail_id'],
            'merchant_visit_date' => $data['merchant_visit_date'],
            'price' => $data['price'],
            'weekend_price' => $data['weekend_price'],
            'quantity' => $data['quantity']
        ]);
    }

}