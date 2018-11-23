<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Repositories\Page\PageInterface;
use App\Repositories\Page\PageRepository as Page;
use Response;

class PageController extends Controller {

	protected $page;

    public function __construct(PageInterface $page) {

        $this->page = $page;
    }

    /**
     * Display page
     * @param $slug
     * @return \Illuminate\View\View
     */
    public function show($slug) {
        
        $page = $this->page->getBySlug($slug, true);
        if($page === null)
            return Response::view('errors.'.getLang().'_404', array(), 404);

        $transRoute = [
            'route' => 'page_slug',
            'attrs' => [ 'slug' => trans_get_only('slug', $page->translations) ]
        ];

        $url_attributes_localize = $this->page->getSlugsByID($page->id);
        

        return view('frontend.page.show', compact('transRoute', 'page', 'url_attributes_localize'));
        
        
    }

}
