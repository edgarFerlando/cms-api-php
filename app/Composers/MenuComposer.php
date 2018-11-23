<?php namespace App\Composers;

use App\Repositories\Menu\MenuRepository;
use App\Repositories\Menu\MenuInterface;
use Config;

class MenuComposer {

    /**
     * @var \App\Repositories\Menu\MenuInterface
     */
    protected $menu;

    /**
     * @param MenuInterface $menu
     */
    public function __construct(MenuInterface $menu){
        $this->menu = $menu;
    }

    /**
     * @param $view
     */
    public function compose($view) {

        $mainMenu_items = $this->menu->findByMenuGroupID(Config::get('holiday.frontend_mainMenu_group_id'));
        $mainMenu_menus = $this->menu->getFrontMenuHTML($mainMenu_items);
        $view->with('mainMenu_menus', $mainMenu_menus);
    }
}

