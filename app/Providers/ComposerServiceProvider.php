<?php namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use View;

class ComposerServiceProvider extends ServiceProvider {

	/**
	 * Bootstrap the application services.
	 *
	 * @return void
	 */
	public function boot()
	{
		View::composer('frontend.partial.main-menu', 'App\Composers\MenuComposer');
		//View::composer('frontend.partial.staticBanner', 'App\Composers\StaticBannerComposer');
		//View::composer('frontend.partial.offCanvas', 'App\Composers\OffCanvasComposer');
	}

	/**
	 * Register the application services.
	 *
	 * @return void
	 */
	public function register()
	{
		//
	}

}
