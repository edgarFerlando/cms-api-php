<?php namespace App\Http\Controllers;

use App\Repositories\Product\ProductInterface;
//use App\Repositories\Tag\TagInterface;
use App\Repositories\Taxonomy\TaxonomyInterface;
use App\Repositories\Taxonomy\TaxonomyRepository as Category;
use App\Repositories\User\UserInterface;
//use App\Repositories\Tag\TagRepository as Tag;
use App\Models\BookingDetail;
use App\ProductVariation;

use Input;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use View;
use Config;
use Response;
use Request;

use Validator;
use Redirect;
use Notification;

class ProductController extends Controller {

    protected $product;
    //protected $tag;
    protected $productCategory;

    protected $user;

    protected $bookingDetail;

    public function __construct(ProductInterface $product, TaxonomyInterface $productCategory, UserInterface $user) {

        $this->product = $product;
        //$this->tag = $tag;
        $this->productCategory = $productCategory; 

        $this->user = $user;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index() {
        $page = Input::get('page', 1);
        $perPage = 5;
        $pagiData = $this->product->paginateAll($page, $perPage, false);


        $products = new LengthAwarePaginator($pagiData->items, $pagiData->totalItems, $perPage, $page, [
            'path' => Paginator::resolveCurrentPath()
        ]);

        $products->setPath("");

        //$tags = $this->tag->all();
        $productCategories = $this->productCategory->hotel(); 
        return view('frontend.product.index', compact('products', 'productCategories'));
    }

    public function trip_index()
    {
        $transRoute = [
            'route' => 'trip',
            'attrs' => []
        ];
        return view('frontend.trip.index');
    }

    public function country($country = null){
        $transRoute = [
            'route' => 'hotel_country',
            'attrs' => []
        ];
        
        if(is_null($country)){
            $locations = $this->productCategory->getTermsByPostType('hotel')->toHierarchy();
            return view('frontend.hotel.countries', compact('locations', 'transRoute'));
        }else{ 
            $locations = $this->productCategory->findBySlug($country, 'hotel')->getDescendants()->toHierarchy();//getTermsByPostType('hotel')->toHierarchy();
            return view('frontend.hotel.country', compact('locations', 'country', 'transRoute'));
        }
    }

    public function playground_country($country = null){
        $transRoute = [
            'route' => 'playground_country',
            'attrs' => []
        ];

        
        if(is_null($country)){
            $locations = $this->productCategory->getTermsByPostType('playground')->toHierarchy();
            return view('frontend.playground.countries', compact('locations', 'transRoute'));
        }else{
            $locations = $this->productCategory->findBySlug($country, 'playground')->getDescendants()->toHierarchy();//getTermsByPostType('hotel')->toHierarchy();
            return view('frontend.playground.country', compact('locations', 'country', 'transRoute'));
        }
    }

    public function trip_country($country = null){
        $transRoute = [
            'route' => 'trip_country',
            'attrs' => []
        ];
        
        if(is_null($country)){
            $locations = $this->productCategory->getTermsByPostType('trip')->toHierarchy();
            //dd($locations);
            
            return view('frontend.trip.countries', compact('transRoute', 'locations'));
        }else{
            $locations = $this->productCategory->findBySlug($country, 'trip')->getDescendants()->toHierarchy();//getTermsByPostType('hotel')->toHierarchy();
            return view('frontend.trip.country', compact('locations', 'country', 'transRoute'));
        }
    }

    public function merchant_category($category = null){
        $transRoute = [
            'route' => 'merchant_category',
            'attrs' => []
        ];

        
        if(is_null($category)){
            //$categories = $this->productCategory->getTermsByPostType('merchant')->toHierarchy();
            $page = Input::get('page', 1);
            $perPage = config_db_cached('settings::per_page');
            //dd($test);
            $filter['post_type'] = 'merchant';

            if(!is_null(Input::get('category')))
            {
                $filter['country'] = Input::get('category');
                $pagiData = $this->product->allByCity($filter['country'], true, $page, $perPage, $filter);
            }
            else
            {
                $filter['country'] = $category;
                $pagiData = $this->product->allByCityPost($filter['country'], true, $page, $perPage, $filter);
            }
            
            //$pagiData = $this->product->allByCityPost($filter['country'], true, $page, $perPage, $filter);

            //dd($pagiData);

            $merchants = new LengthAwarePaginator($pagiData->items, $pagiData->totalItems, $perPage, $page, [
                'path' => Paginator::resolveCurrentPath()
            ]);

            $merchants->setPath("");

            $sidebar_filters = $this->product->getFilterByPost($filter);
            //dd($products);
            //return view('frontend.merchant.categories', compact('categories', 'transRoute'));
            return view('frontend.merchant.all', compact('transRoute', 'merchants', 'sidebar_filters'));//'categories'));
        }else{
            $categories = $this->productCategory->findBySlug($category, 'merchant')->getDescendants()->toHierarchy();//getTermsByPostType('hotel')->toHierarchy();
            return view('frontend.merchant.category', compact('categories', 'category', 'transRoute'));
        }
    }

    public function city_JUNK($country){ 
        $country = $this->productCategory->findBySlug($country, 'hotel'); 
        $locations = $this->productCategory->find($country->id)->getDescendants()->toHierarchy();
        return view('frontend.hotel.city', compact('locations', 'country'));
    }

    public function city($country){ 
        //sample path : http://xxx.local/id/hotel/kota/jakarta
        $transRoute = [
            'route' => 'hotel_city_slug',
            'attrs' => [ 'country' => $country ]
        ];

        //jika ada parameter dibelakangnya
        $qs_string = Request::getQueryString();
        parse_str($qs_string, $qs);
        $transRoute['attrs'] = array_merge($transRoute['attrs'], $qs);

        //$regions = $this->productCategory->findBySlug($country, 'hotel')->getDescendants(); 

        $page = Input::get('page', 1);
        $perPage = config_db_cached('settings::per_page');

        $filter = Input::all(); 
        $filter_params = $filter;
        $filter['post_type'] = 'hotel';
        $filter['country'] = $country;
        //unset($filter['sortBy']);
        unset($filter['_token']); //dd($filter);
        //\DB::enableQueryLog();
        //dd($filter);
        $sortBy = Input::get('sortBy');

        $pagiData = $this->product->allByCity($country, true, $page, $perPage, $filter, $sortBy);
    //dd(\DB::getQueryLog());
        $hotels = new LengthAwarePaginator($pagiData->items, $pagiData->totalItems, $perPage, $page, [
            'path' => Paginator::resolveCurrentPath()
        ]);
        $hotels->setPath('');//dd($hotels->render());
       
        $hotels->appends(array_merge($filter_params, [ 'sortBy' => $sortBy]));
        $sidebar_filters = $this->product->getFilterByCountry($filter);
        return view('frontend.hotel.city', compact('transRoute', 'hotels', 'sidebar_filters'));
    }

    public function playground_city($country){ 
        $page = Input::get('page', 1);
        $perPage = Config::get('holiday.per_page');

        $filter = Input::all(); 
        $filter['post_type'] = 'playground';
        $filter['country'] = $country;
        unset($filter['_token']); //dd($filter);
        $pagiData = $this->product->allByCity($country, true, $page, $perPage, $filter);

        $hotels = new LengthAwarePaginator($pagiData->items, $pagiData->totalItems, $perPage, $page, [
            'path' => Paginator::resolveCurrentPath()
        ]);
        $hotels->setPath('');

        $sidebar_filters = $this->product->getFilterByCountry($filter);//$country);
        return view('frontend.playground.city', compact('hotels', 'sidebar_filters'));
    }

    public function trip_city($country){ 
        
        $page = Input::get('page', 1);
        $perPage = Config::get('holiday.per_page');

        $filter = Input::all(); 
        $filter['post_type'] = 'trip';
        $filter['country'] = $country;
        $filter['start_date'] = date('Y-m-d');
        //$sortBy = 'startDate';
        //
        //dd($filter);
        unset($filter['_token']); //dd($filter);
        $pagiData = $this->product->allByCityDate($country, true, $page, $perPage, $filter);

        //dd($pagiData);

        $hotels = new LengthAwarePaginator($pagiData->items, $pagiData->totalItems, $perPage, $page, [
            'path' => Paginator::resolveCurrentPath()
        ]);

        $hotels->setPath('');
        //dd($hotels);
        $sidebar_filters = $this->product->getFilterByCountryDate($filter);//$country);
        //dd($sidebar_filters);
        return view('frontend.trip.city', compact('hotels', 'sidebar_filters'));
    }

    public function merchant_sub($country){ 
        $page = Input::get('page', 1);
        $perPage = Config::get('holiday.per_page');

        $filter = Input::all(); 
        $filter['post_type'] = 'merchant';
        $filter['country'] = $country;
        unset($filter['_token']); //dd($filter);
        $pagiData = $this->product->allByCity($country, true, $page, $perPage, $filter);

        $merchants = new LengthAwarePaginator($pagiData->items, $pagiData->totalItems, $perPage, $page, [
            'path' => Paginator::resolveCurrentPath()
        ]);
        $merchants->setPath('');

        $sidebar_filters = $this->product->getFilterByCountry($filter);//$country);
        return view('frontend.merchant.sub', compact('merchants', 'sidebar_filters'));
    }

    public function region($region){ 
        $region_id = $this->productCategory->findBySlug($region, 'hotel')->id; 
        //$hotels = $this->product->AllByRegion($region_id);
        //return view('frontend.product.region', compact('locations'));




        $page = Input::get('page', 1);
        $perPage = Config::get('holiday.per_page');
        $filter = Input::all(); 
        unset($filter['_token']); //dd($filter);
        $pagiData = $this->product->allByRegion($region_id, true, $page, $perPage, $filter);

        $hotels = new LengthAwarePaginator($pagiData->items, $pagiData->totalItems, $perPage, $page, [
            'path' => Paginator::resolveCurrentPath()
        ]);
        $hotels->setPath('');//dd($hotels->render());

        $sidebar_filters = $this->product->getFilterByRegion($region);
        return view('frontend.hotel.region', compact('hotels', 'sidebar_filters'));
    }
    
    public function productCategory($category){
        $page = Input::get('page', 1);
        $perPage = config_db_cached('settings::per_page');

        $filter = Input::all(); 
        $filter['is_published'] = 1;
        unset($filter['_token']); //dd($filter);
        $pagiData = $this->product->allByCategory($category, true, $page, $perPage, $filter);

        $items = new LengthAwarePaginator($pagiData->items, $pagiData->totalItems, $perPage, $page, [
            'path' => Paginator::resolveCurrentPath()
        ]);
        $items->setPath('');//dd($hotels->render());

        $trans_category = $this->productCategory->findBySlugLocale($category, 'clothing');//get all translations by local translated item
        $category_title = $trans_category->title;
        
        $transRoute = [ 
            'route' => 'product-category/{category}',
            'attrs' => [ 'category' => trans_get_only('slug', $trans_category->taxonomyTranslations) ]
        ];

        return view('frontend.product.items', compact('items', 'transRoute', 'category_title'));
    }

    /**
     * @param $id
     * @return \Illuminate\View\View
     */
    public function show($slug) {
        //sample path : http://xxx.local/en/hotel/jw-marriot?checkin=08/18/2016&checkout=08/20/2016
        $transRoute = [
            'route' => 'hotel_slug',
            'attrs' => [ 'slug' => $slug ]
        ];

        //jika ada parameter dibelakangnya
        $qs_string = Request::getQueryString();
        parse_str($qs_string, $qs);
        $transRoute['attrs'] = array_merge($transRoute['attrs'], $qs);

        $product = $this->product->getBySlugWithVariations($slug);
        //dd($product);
        $url_attributes_localize = $this->product->getSlugsByID($product->id);
        if($product === null)
            return Response::view('errors.missing', array(), 404);

        /*View::composer('frontend.layout', function ($view) use ($product) {
            $view->with('meta_keywords', $product->meta_keywords);
            $view->with('meta_description', $product->meta_description);
        });*/
        //dd($product);
        $productCategories = $this->productCategory->getTermsByPostType('hotel');
        $gmap = [
            'lat' => $product->productMeta->hotel_latitude,
            'lng' => $product->productMeta->hotel_longitude
        ];
        //$tags = $this->tag->all();
        return view('frontend.hotel.detail', compact('transRoute', 'gmap', 'product', 'productCategories', 'url_attributes_localize'));
    }

    public function playground_show($slug) {
        //sample path : http://xxx.local/en/hotel/jw-marriot?checkin=08/18/2016&checkout=08/20/2016
        $transRoute = [
            'route' => 'playground_slug',
            'attrs' => [ 'slug' => $slug ]
        ];

        //jika ada parameter dibelakangnya
        $qs_string = Request::getQueryString();
        parse_str($qs_string, $qs);
        $transRoute['attrs'] = array_merge($transRoute['attrs'], $qs);

        $product = $this->product->getBySlugWithVariations($slug);
        //dd($product);
        $url_attributes_localize = $this->product->getSlugsByID($product->id);
        if($product === null)
            return Response::view('errors.missing', array(), 404);

        /*View::composer('frontend.layout', function ($view) use ($product) {
            $view->with('meta_keywords', $product->meta_keywords);
            $view->with('meta_description', $product->meta_description);
        });*/
        //dd($product);
        $productCategories = $this->productCategory->getTermsByPostType('playground');
        $gmap = [
            'lat' => $product->productMeta->playground_latitude,
            'lng' => $product->productMeta->playground_longitude
        ];
        //$tags = $this->tag->all();
        return view('frontend.playground.detail', compact('transRoute', 'gmap', 'product', 'productCategories', 'url_attributes_localize'));
    }

    public function trip_show($slug) {
        //sample path : http://xxx.local/en/hotel/jw-marriot?checkin=08/18/2016&checkout=08/20/2016
        $transRoute = [
            'route' => 'trip_slug',
            'attrs' => [ 'slug' => $slug ]
        ];

        //jika ada parameter dibelakangnya
        $qs_string = Request::getQueryString();
        parse_str($qs_string, $qs);
        $transRoute['attrs'] = array_merge($transRoute['attrs'], $qs);

        $product = $this->product->getBySlugWithVariations($slug);
        //dd($product);
        $url_attributes_localize = $this->product->getSlugsByID($product->id);
        if($product === null)
            return Response::view('errors.missing', array(), 404);

        /*View::composer('frontend.layout', function ($view) use ($product) {
            $view->with('meta_keywords', $product->meta_keywords);
            $view->with('meta_description', $product->meta_description);
        });*/
        //dd($product);
        $productCategories = $this->productCategory->getTermsByPostType('trip');

        $location = $this->productCategory->find($product->product_category_id)->title;
        /*$gmap = [
            'lat' => $product->productMeta->trip_latitude,
            'lng' => $product->productMeta->trip_longitude
        ];*/

        //$tags = $this->tag->all();
        $date = [
            'start_date' => $product->productMeta->start_date,
            'end_date' => $product->productMeta->end_date
        ];
        
        //dd($location);

        return view('frontend.trip.detail', compact('transRoute','location', 'date','product', 'productCategories', 'url_attributes_localize'));
    }

    public function my_trip_show($id, $slug) {
        //sample path : http://xxx.local/en/hotel/jw-marriot?checkin=08/18/2016&checkout=08/20/2016
        $transRoute = [
            'route' => 'my_trip_slug',
            'attrs' => [ 'slug' => $slug ]
        ];

        //jika ada parameter dibelakangnya
        $qs_string = Request::getQueryString();
        parse_str($qs_string, $qs);
        $transRoute['attrs'] = array_merge($transRoute['attrs'], $qs);

        $product = $this->product->getBySlugWithVariations($slug);
        //dd($product);
        $url_attributes_localize = $this->product->getSlugsByID($product->id);
        if($product === null)
            return Response::view('errors.missing', array(), 404);

        /*View::composer('frontend.layout', function ($view) use ($product) {
            $view->with('meta_keywords', $product->meta_keywords);
            $view->with('meta_description', $product->meta_description);
        });*/
        //dd($product);
        $productCategories = $this->productCategory->getTermsByPostType('trip');

        $location = $this->productCategory->find($product->product_category_id)->title;
        /*$gmap = [
            'lat' => $product->productMeta->trip_latitude,
            'lng' => $product->productMeta->trip_longitude
        ];*/

        //$tags = $this->tag->all();
        $date = [
            'start_date' => $product->productMeta->start_date,
            'end_date' => $product->productMeta->end_date
        ];
        
        $data['tripBookingDetail'] = BookingDetail::findOrFail($id);
        $data['variation'] = ProductVariation::with(['productAttributeOption.productAttributeOptionTranslation'])->findOrFail($data['tripBookingDetail']->variation_id);

        //dd($location);
        //dd($data);

        return view('frontend.trip.my-detail', compact('transRoute','location', 'date', 'data','product', 'productCategories', 'url_attributes_localize'));
    }

    public function merchant_show($slug) {
        //sample path : http://xxx.local/en/hotel/jw-marriot?checkin=08/18/2016&checkout=08/20/2016
        $transRoute = [
            'route' => 'merchant_slug',
            'attrs' => [ 'slug' => $slug ]
        ];

        //jika ada parameter dibelakangnya
        $qs_string = Request::getQueryString();
        parse_str($qs_string, $qs);
        $transRoute['attrs'] = array_merge($transRoute['attrs'], $qs);

        $product = $this->product->getBySlugWithVariations($slug);
        //dd($product);
        $url_attributes_localize = $this->product->getSlugsByID($product->id);
        if($product === null)
            return Response::view('errors.missing', array(), 404);

        /*View::composer('frontend.layout', function ($view) use ($product) {
            $view->with('meta_keywords', $product->meta_keywords);
            $view->with('meta_description', $product->meta_description);
        });*/
        //dd($product);
        $productCategories = $this->productCategory->getTermsByPostType('merchant');
        /*$gmap = [
            'lat' => $product->productMeta->merchant_latitude,
            'lng' => $product->productMeta->merchant_longitude
        ];*/
        //$tags = $this->tag->all();
        return view('frontend.merchant.detail', compact('transRoute', 'gmap', 'product', 'productCategories', 'url_attributes_localize'));
    }

    function allDestinations($params){//$search_key, $params){
        $search_key = $params['query'];
        $post_type = $params['post_type'];
        //$location = Input::get('query');
        $locations_raw = $this->productCategory->findAllByTitleLike($search_key, $post_type);
        $locations_arr = [];
        foreach($locations_raw as $location_raw){
            $locations_arr[] = [ 
                'title' => $location_raw->title,
                'slug' => $location_raw->slug,
                'depth_as' => get_depth_as($location_raw->depth)
            ];
        }
        $locations = array_map(function ($location) { 
            return ['value' => $location['title'], 
                'data' => [ 'category' => trans('app.location').' : '.trans($location['depth_as']), 'search_by' => 'taxonomy_'.$location['depth_as'], 'search_slug' =>  $location['slug'] ]
            ]; 
        }, $locations_arr);

        $items_raw = $this->product->findAllByTitleLike($search_key, $post_type);
        $items_arr = [];
        foreach($items_raw as $hotel_raw){
            $items_arr[] = [ 
                'title' => $hotel_raw->title,
                'slug' => $hotel_raw->slug
            ];//$hotel_raw->title;
        }
        $items = array_map(function ($item) use ($post_type){
            return ['value' => $item['title'], 
                'data' => [ 'category' => trans('app.'.$post_type), 'search_by' => 'product', 'search_slug' => $item['slug'] ]
            ]; 
        }, $items_arr);


        return array_merge($locations, $items);
    }

    public function destination(){
        /*$nhlTeams = ['Anaheim Ducks', 'Atlanta Thrashers', 'Boston Bruins', 'Buffalo Sabres', 'Calgary Flames', 'Carolina Hurricanes', 'Chicago Blackhawks', 'Colorado Avalanche', 'Columbus Blue Jackets', 'Dallas Stars', 'Detroit Red Wings', 'Edmonton OIlers', 'Florida Panthers', 'Los Angeles Kings', 'Minnesota Wild', 'Montreal Canadiens', 'Nashville Predators', 'New Jersey Devils', 'New Rork Islanders', 'New York Rangers', 'Ottawa Senators', 'Philadelphia Flyers', 'Phoenix Coyotes', 'Pittsburgh Penguins', 'Saint Louis Blues', 'San Jose Sharks', 'Tampa Bay Lightning', 'Toronto Maple Leafs', 'Vancouver Canucks', 'Washington Capitals'];
        $nbaTeams = ['Atlanta Hawks', 'Boston Celtics', 'Charlotte Bobcats', 'Chicago Bulls', 'Cleveland Cavaliers', 'Dallas Mavericks', 'Denver Nuggets', 'Detroit Pistons', 'Golden State Warriors', 'Houston Rockets', 'Indiana Pacers', 'LA Clippers', 'LA Lakers', 'Memphis Grizzlies', 'Miami Heat', 'Milwaukee Bucks', 'Minnesota Timberwolves', 'New Jersey Nets', 'New Orleans Hornets', 'New York Knicks', 'Oklahoma City Thunder', 'Orlando Magic', 'Philadelphia Sixers', 'Phoenix Suns', 'Portland Trail Blazers', 'Sacramento Kings', 'San Antonio Spurs', 'Toronto Raptors', 'Utah Jazz', 'Washington Wizards'];
        $nhl = array_map(function ($team) { 
            return ['value' => $team, 
                    'data' => [ 'category' => 'NHL' ]
            ]; 
        }, $nhlTeams);
        $nba = array_map(function ($team) { 
            return [ 'value' => $team, 
            'data'=> [ 'category' => 'NBA' ]
            ]; 
        }, $nbaTeams);
        $teams = array_merge($nhl, $nba);
        $teams = $teams?$teams:[];*/
       /* $location = Input::get('query');
        $locations_raw = $this->productCategory->findAllByTitleLike($location, 'hotel');
        $locations_arr = [];
        foreach($locations_raw as $location_raw){
            $locations_arr[] = [ 
                'title' => $location_raw->title,
                'slug' => $location_raw->slug
            ];
        }
        $locations = array_map(function ($location) { 
            return ['value' => $location['title'], 
                'data' => [ 'category' => trans('app.location'), 'search_by' => 'taxonomy', 'search_slug' =>  $location['slug'] ]
            ]; 
        }, $locations_arr);

        $hotels_raw = $this->product->findAllByTitleLike($location, 'hotel');
        $hotels_arr = [];
        foreach($hotels_raw as $hotel_raw){
            $hotels_arr[] = [ 
                'title' => $hotel_raw->title,
                'slug' => $hotel_raw->slug
            ];//$hotel_raw->title;
        }
        $hotels = array_map(function ($hotel) {
            return ['value' => $hotel['title'], 
                'data' => [ 'category' => trans('app.hotel'), 'search_by' => 'product', 'search_slug' => $hotel['slug'] ]
            ]; 
        }, $hotels_arr);*/


        $destinations = $this->allDestinations(Input::all());//Input::get('query'));//array_merge($locations, $hotels );
        return Response::json([ 
            'query' => 'Unit',
            'suggestions' => $destinations
            ]);
    }

    function destination_tourguide()
    {
        $destinations = $this->allDestinations_tourguide(Input::all());//$this->allDestinations_tourguide(Input::all());//Input::get('query'));//array_merge($locations, $hotels );
        return Response::json([ 
            'query' => 'Unit',
            'suggestions' => $destinations
            ]);
    }

    function allDestinations_tourguide($params){//$search_key, $params){
        $search_key = $params['query'];
        $post_type = 9;
        //$location = Input::get('query');

        $items_raw = $this->user->findByName($search_key, $post_type);
        $items_arr = [];
        foreach($items_raw as $guide_raw){
            $items_arr[] = [ 
                'title' => $guide_raw->name,
                'id' => $guide_raw->id
            ];//$hotel_raw->title;
        }
        $items = array_map(function ($item) {
            return ['value' => $item['title'], 
                'data' => [ 'category' => 'Tourguide', 'search_by' => 'product', 'search_slug' => $item['title']]
            ]; 
        }, $items_arr);


        return array_merge($items);
    }

    public function search(){
        $transRoute = [
            'route' => 'search',
            'attrs' => []
        ];

        $search_post_type = Input::old('search_post_type');
        switch ($search_post_type) {
            case 'hotel':
                return view('frontend.hotel.search', compact('transRoute'));
                break;
            case 'playground':
                return view('frontend.playground.search', compact('transRoute'));
                break;
            case 'trip':
                return view('frontend.trip.search', compact('transRoute'));
                break;
            case 'merchant':
                return view('frontend.merchant.search', compact('transRoute'));
                break;
            case 'tourguide':
                return view('frontend.auth.tourguide.search', compact('transRoute'));
                break;
            default:
                return view('frontend.trip.search', compact('transRoute'));
                break;
        }
        
    }

    public function doSearch(){
        $get = Input::all();

        switch($get['search_post_type']){
            case 'hotel' :
                return $this->doSearch_hotel($get);
            break;
            case 'playground' :
                return $this->doSearch_playground($get);
            break;
            case 'trip' :
                return $this->doSearch_trip($get);
            break;
            case 'merchant' :
                return $this->doSearch_merchant($get);
            break;
            case 'tourguide' :
                return $this->doSearch_tourguide($get);
            break;
        }
    }

    function doSearch_hotel($get){
        $v = Validator::make($get, [
            'destination' => 'required',
            'checkin' => 'required',
            'checkout' => 'required'
        ]);

        if ($v->fails())
        { 
            return Redirect::Route(getLang().'.search')->withInput()->withErrors($v->errors());
        }

        $destination = $get['destination'];
        $params = [ 'query' => $destination, 'post_type' => $get['search_post_type']];
        $checkin = Input::get('checkin');
        $checkout = Input::get('checkout');
        $search_by = Input::get('search_by');
        $search_slug = Input::get('search_slug');

        if($search_by == 'taxonomy_country'){
            return Redirect::route(getLang().'.dashboard.hotel.country', [ $search_slug, 'checkin='.$checkin, 'checkout='.$checkout ])->withInput();
        }if($search_by == 'taxonomy_city'){
            return Redirect::route(getLang().'.dashboard.hotel.city', [ $search_slug, 'checkin='.$checkin, 'checkout='.$checkout ])->withInput();
        }elseif($search_by == 'taxonomy_region'){
            return Redirect::route(getLang().'.dashboard.hotel.region', [ $search_slug, 'checkin='.$checkin, 'checkout='.$checkout ])->withInput();
        }elseif($search_by == 'product'){
            return Redirect::route(getLang().'.dashboard.hotel.show', [ $search_slug, 'checkin='.$checkin, 'checkout='.$checkout ])->withInput();
        }else{
            $destinations = $this->allDestinations($params);
            if(count($destinations)){
                $search_result_html = '<dl>';
                $search_result = [];
                foreach ($destinations as $result_item) {
                    if($result_item['data']['search_by'] == 'taxonomy_country'){
                        $url = route(getLang().'.dashboard.hotel.country', [ $result_item['data']['search_slug'] ]);
                    }if($result_item['data']['search_by'] == 'taxonomy_city'){
                        $url = route(getLang().'.dashboard.hotel.city', [ $result_item['data']['search_slug'], 'checkin='.$checkin, 'checkout='.$checkout ]);
                    }if($result_item['data']['search_by'] == 'taxonomy_region'){
                        $url = route(getLang().'.dashboard.hotel.region', [ $result_item['data']['search_slug'], 'checkin='.$checkin, 'checkout='.$checkout ]);
                    }elseif($result_item['data']['search_by'] == 'product'){
                        $url = route(getLang().'.dashboard.hotel.show', [ $result_item['data']['search_slug'], 'checkin='.$checkin, 'checkout='.$checkout ]);
                    }
                    $search_result[$result_item['data']['category']][] = '<a href="'.$url.'">'.$result_item['value'].'</a>';
                }
                foreach ($search_result as $category => $result_items) { 
                    $search_result_html .= '<dt>'.$category.'</dt>';
                    $categories_item = array_map(function ($result_item) {
                        return '<dd>'.$result_item.'</dd>'; 
                    }, $result_items);
                    $search_result_html .= implode('', $categories_item);
                }
                $search_result_html .= '</dl>';
            }else{
                Notification::container('frontend')->error( trans('app.no_results_found') );
            }
            return Redirect::Route(getLang().'.search')->with(compact('search_result_html'))->withInput()->withErrors($v->errors());
        }
    }

    function doSearch_playground($get){
        $v = Validator::make($get, [
            'playground_destination' => 'required',
            'playground_visit_date' => 'required'
        ]);

        if ($v->fails()){   
            //dd($get);
            return Redirect::Route(getLang().'.search')->withInput()->withErrors($v->errors());
        }

        $destination = $get['playground_destination'];
        $params = [ 'query' => $destination, 'post_type' => $get['search_post_type']];
        $playground_visit_date = Input::get('playground_visit_date');
        $search_by = Input::get('search_by');
        $search_slug = Input::get('search_slug');

        if($search_by == 'taxonomy_country'){
            return Redirect::route(getLang().'.dashboard.playground.country', [ $search_slug, 'playground_visit_date='.$playground_visit_date ])->withInput();
        }if($search_by == 'taxonomy_city'){
            return Redirect::route(getLang().'.dashboard.playground.city', [ $search_slug, 'playground_visit_date='.$playground_visit_date ])->withInput();
        }elseif($search_by == 'taxonomy_region'){
            return Redirect::route(getLang().'.dashboard.playground.region', [ $search_slug, 'playground_visit_date='.$playground_visit_date ])->withInput();
        }elseif($search_by == 'product'){
            return Redirect::route(getLang().'.dashboard.playground.show', [ $search_slug, 'playground_visit_date='.$playground_visit_date ])->withInput();
        }else{
            $destinations = $this->allDestinations($params);
            if(count($destinations)){
                $search_result_html = '<dl>';
                $search_result = [];
                foreach ($destinations as $result_item) {
                    if($result_item['data']['search_by'] == 'taxonomy_country'){
                        $url = route(getLang().'.dashboard.playground.country', [ $result_item['data']['search_slug'] ]);
                    }if($result_item['data']['search_by'] == 'taxonomy_city'){
                        $url = route(getLang().'.dashboard.playground.city', [ $result_item['data']['search_slug'], 'playground_visit_date='.$playground_visit_date ]);
                    }if($result_item['data']['search_by'] == 'taxonomy_region'){
                        $url = route(getLang().'.dashboard.playground.region', [ $result_item['data']['search_slug'], 'playground_visit_date='.$playground_visit_date ]);
                    }elseif($result_item['data']['search_by'] == 'product'){
                        $url = route(getLang().'.dashboard.playground.show', [ $result_item['data']['search_slug'], 'playground_visit_date='.$playground_visit_date ]);
                    }
                    $search_result[$result_item['data']['category']][] = '<a href="'.$url.'">'.$result_item['value'].'</a>';
                }
                foreach ($search_result as $category => $result_items) { 
                    $search_result_html .= '<dt>'.$category.'</dt>';
                    $categories_item = array_map(function ($result_item) {
                        return '<dd>'.$result_item.'</dd>'; 
                    }, $result_items);
                    $search_result_html .= implode('', $categories_item);
                }
                $search_result_html .= '</dl>';
            }else{
                Notification::container('frontend')->error( trans('app.no_results_found') );
            }
            return Redirect::Route(getLang().'.search')->with(compact('search_result_html'))->withInput()->withErrors($v->errors());
        }
    }

    function doSearch_trip($get){
        $v = Validator::make($get, [
            'trip_destination' => 'required',
            'trip_visit_date' => 'required'
        ]);

        if ($v->fails()){   
            //dd($get);
            return Redirect::Route(getLang().'.search')->withInput()->withErrors($v->errors());
        }

        $destination = $get['trip_destination'];
        $params = [ 'query' => $destination, 'post_type' => $get['search_post_type']];
        $trip_visit_date = Input::get('trip_visit_date');
        $search_by = Input::get('search_by');
        $search_slug = Input::get('search_slug');

        if($search_by == 'taxonomy_country'){
            return Redirect::route(getLang().'.dashboard.trip.country', [ $search_slug, 'trip_visit_date='.$trip_visit_date ])->withInput();
        }if($search_by == 'taxonomy_city'){
            //dd($get);
            return Redirect::route(getLang().'.dashboard.trip.city', [ $search_slug, 'trip_visit_date='.$trip_visit_date ])->withInput();
        }elseif($search_by == 'taxonomy_region'){
            //dd($get);
            return Redirect::route(getLang().'.dashboard.trip.region', [ $search_slug, 'trip_visit_date='.$trip_visit_date ])->withInput();
        }elseif($search_by == 'product'){
            return Redirect::route(getLang().'.dashboard.trip.show', [ $search_slug, 'trip_visit_date='.$trip_visit_date ])->withInput();
        }else{
            //dd($get);
            $destinations = $this->allDestinations($params);
            if(count($destinations)){
                $search_result_html = '<dl>';
                $search_result = [];
                foreach ($destinations as $result_item) {
                    if($result_item['data']['search_by'] == 'taxonomy_country'){
                        $url = route(getLang().'.dashboard.tirp.country', [ $result_item['data']['search_slug'] ]);
                    }if($result_item['data']['search_by'] == 'taxonomy_city'){
                        $url = route(getLang().'.dashboard.trip.city', [ $result_item['data']['search_slug'], 'trip_visit_date='.$trip_visit_date ]);
                    }if($result_item['data']['search_by'] == 'taxonomy_region'){
                        $url = route(getLang().'.dashboard.trip.region', [ $result_item['data']['search_slug'], 'trip_visit_date='.$trip_visit_date ]);
                    }elseif($result_item['data']['search_by'] == 'product'){
                        $url = route(getLang().'.dashboard.trip.show', [ $result_item['data']['search_slug'], 'trip_visit_date='.$trip_visit_date ]);
                    }
                    $search_result[$result_item['data']['category']][] = '<a href="'.$url.'">'.$result_item['value'].'</a>';
                }
                foreach ($search_result as $category => $result_items) { 
                    $search_result_html .= '<dt>'.$category.'</dt>';
                    $categories_item = array_map(function ($result_item) {
                        return '<dd>'.$result_item.'</dd>'; 
                    }, $result_items);
                    $search_result_html .= implode('', $categories_item);
                }
                $search_result_html .= '</dl>';
            }else{
                Notification::container('frontend')->error( trans('app.no_results_found') );
            }
            return Redirect::Route(getLang().'.search')->with(compact('search_result_html'))->withInput()->withErrors($v->errors());
        }
    }

    function doSearch_merchant($get){
        $v = Validator::make($get, [
            'merchant_destination' => 'required'
        ]);

        if ($v->fails()){   
            //dd($v);
            return Redirect::Route(getLang().'.search')->withInput()->withErrors($v->errors());
        }

        $destination = $get['merchant_destination'];
        $params = [ 'query' => $destination, 'post_type' => $get['search_post_type']];
        $search_by = Input::get('search_by');
        $search_slug = Input::get('search_slug');

        if($search_by == 'taxonomy_country'){
            return Redirect::route(getLang().'.dashboard.merchant.categories', [ $search_slug ])->withInput();
        }if($search_by == 'taxonomy_city'){
            return Redirect::route(getLang().'.dashboard.merchant.sub', [ $search_slug ])->withInput();
        }elseif($search_by == 'taxonomy_region'){
            return Redirect::route(getLang().'.dashboard.merchant.region', [ $search_slug ])->withInput();
        }elseif($search_by == 'product'){
            return Redirect::route(getLang().'.dashboard.merchant.show', [ $search_slug ])->withInput();
        }else{
            $destinations = $this->allDestinations($params);
            if(count($destinations)){
                $search_result_html = '<dl>';
                $search_result = [];
                foreach ($destinations as $result_item) {
                    if($result_item['data']['search_by'] == 'taxonomy_country'){
                        $url = route(getLang().'.dashboard.merchant.categories', [ $result_item['data']['search_slug'] ]);
                    }if($result_item['data']['search_by'] == 'taxonomy_city'){
                        $url = route(getLang().'.dashboard.merchant.sub', [ $result_item['data']['search_slug'] ]);
                    }if($result_item['data']['search_by'] == 'taxonomy_region'){
                        $url = route(getLang().'.dashboard.merchant.region', [ $result_item['data']['search_slug'] ]);
                    }elseif($result_item['data']['search_by'] == 'product'){
                        $url = route(getLang().'.dashboard.merchant.show', [ $result_item['data']['search_slug'] ]);
                    }
                    $search_result[$result_item['data']['category']][] = '<a href="'.$url.'">'.$result_item['value'].'</a>';
                }
                foreach ($search_result as $category => $result_items) { 
                    $search_result_html .= '<dt>'.trans('app.merchant').'</dt>';
                    $categories_item = array_map(function ($result_item) {
                        return '<dd>'.$result_item.'</dd>'; 
                    }, $result_items);
                    $search_result_html .= implode('', $categories_item);
                }
                $search_result_html .= '</dl>';
            }else{
                Notification::container('frontend')->error( trans('app.no_results_found') );
            }
            return Redirect::Route(getLang().'.search')->with(compact('search_result_html'))->withInput()->withErrors($v->errors());
        }
    }

    function doSearch_tourguide($get){
        $get = Input::all();
            $v = Validator::make($get, [
                'tourguide_name' => 'required',
            ]);

            if ($v->fails())
            { 
                //dd($v);
                return Redirect::Route(getLang().'.search')->withInput()->withErrors($v->errors());
            }

        $users = $this->user->findByName($get['tourguide_name'], 9);
        //dd($users);
        //foreach ($users as $key => $value) {
            //$filter['user_id'][$key] = $value->id;
        //}

        //dd($filter);
        //$userProduct = $this->product->allByUser($filter);
        
        //dd($userProduct);

        return view('frontend.auth.tourguide.search', compact('users'));

    }

    public function doChangeNights(){
        $get = Input::all();
        $setAttributeNames = [];
        switch ($get['post_type']) {
            case 'hotel':
                $rules = [
                    'checkin' => 'required|date|date_format:m/d/Y',
                    'checkout' => 'required|date|date_format:m/d/Y'
                ];  

                $redirect_url_fail = $get['hotel_url'];
                $redirect_url = $get['hotel_url'].build_get_param(['checkin' => $get['checkin'], 'checkout' => $get['checkout']]);
            break;
            case 'playground' :
                $rules = [
                    'playground_visit_date' => 'required|date|date_format:m/d/Y'
                ];
                $redirect_url_fail = $get['playground_url'];
                $redirect_url = $get['playground_url'].build_get_param(['playground_visit_date' => $get['playground_visit_date']]);

                $setAttributeNames['playground_visit_date'] = trans('app.date');
            break;
            case 'trip' :
                $rules = [
                    'trip_visit_date' => 'required|date|date_format:m/d/Y'
                ];
                $redirect_url_fail = $get['trip_url'];
                $redirect_url = $get['trip_url'].build_get_param(['trip_visit_date' => $get['trip_visit_date']]);

                $setAttributeNames['trip_visit_date'] = trans('app.date');
            break;
            case 'merchant' :
                $rules = [
                    'merchant_visit_date' => 'required|date|date_format:m/d/Y'
                ];
                $redirect_url_fail = $get['merchant_url'];
                $redirect_url = $get['merchant_url'].build_get_param(['merchant_visit_date' => $get['merchant_visit_date']]);

                $setAttributeNames['merchant_visit_date'] = trans('app.date');
            break;
        }


        
        $v = Validator::make($get, $rules);
        $v->setAttributeNames($setAttributeNames);
        if ($v->fails())
        { 
            return Redirect($redirect_url_fail)->withInput()->withErrors($v->errors());
        }

        /*$checkin = Input::get('checkin');
        $checkout = Input::get('checkout');

        $attrs = [];
        $param_attrs = '';
        $params = [
            'checkin' => $checkin,
            'checkout' => $checkout
        ];*/
        /*if($params){
            $param_attr = [];
            foreach ($params as $key => $value) {
                $param_attr[] = $key.'='.$value;
            }
            $param_attrs = '?'.implode('&', $param_attr);
        }*/
        //$param_attrs = build_get_param($params);

        return Redirect($redirect_url)->withInput();

    }
}
