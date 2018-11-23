<?php namespace App\Providers;

use Illuminate\Support\ServiceProvider;
//use Illuminate\Support\Facades\App;
//use Cart;--> ga bisa pake ini , karena ini facade, musti pake yg mentahnya seperti dibawah ini
use Darryldecode\Cart\Cart;


class TripCartServiceProvider extends ServiceProvider {

	/**
	 * Bootstrap the application services.
	 *
	 * @return void
	 */
	public function boot()
	{
		//
	}

	/**
	 * Register the application services.
	 *
	 * @return void
	 */
	public function register()
	{
		/*App::bind('customercart', function($app)
        { 
            $storage = $app['session']; // laravel session storage
            $events = $app['events']; // laravel event handler
            $instanceName = 'customercart'; // your cart instance name
            $session_key = 'customerdRopShiP95'; // your unique session key to hold cart items

            return new Cart(
                $storage,
                $events,
                $instanceName,
                $session_key
            );
        });*/
        
		$this->app['tripcart'] = $this->app->share(function($app)
        {
            $storage = $app['session']; // laravel session storage
            $events = $app['events']; // laravel event handler
            $instanceName = 'tripcart'; // your cart instance name
            $session_key = 'AsASD67s0ks2';//'customerdRopShiP95'; // your unique session key to hold cart items

            return new Cart(
                $storage,
                $events,
                $instanceName,
                $session_key
            );
        });
	}

}
