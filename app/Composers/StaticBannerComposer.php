<?php namespace App\Composers;

use App\Repositories\Banner\BannerInterface;

class StaticBannerComposer {

    /**
     * @var \App\Repositories\Banner\BannerInterface
     */
    protected $menu;

    /**
     * @param BannerInterface $menu
     */
    public function __construct(BannerInterface $banner){
        $this->banner = $banner;
    }

    /**
     * @param $view
     */
    public function compose($view) {
        $staticBanner = $this->banner->staticBanner();
        $view->with('staticBanner', $staticBanner);
    }
}

