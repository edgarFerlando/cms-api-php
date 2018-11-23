<?php namespace App\Composers;

use Config;
use App\Repositories\Taxonomy\TaxonomyInterface;

class OffCanvasComposer {

    /**
     * @var \App\Repositories\Menu\MenuInterface
     */
    protected $offCanvas;

    /**
     * @param MenuInterface $menu
     */
    public function __construct(TaxonomyInterface $taxonomy){
        $this->offCanvas = $taxonomy;
    }

    /**
     * @param $view
     */
    public function compose($view) {
        $items = $this->offCanvas->getTermsByPostType('hotel')->toHierarchy();
        $categories = $this->offCanvas->htmlHieOffCanvas($items);
        $view->with('offCanvasMenu_menus', $categories);
    }
}

