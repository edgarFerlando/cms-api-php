<?php namespace App\Providers;

use Illuminate\Contracts\Events\Dispatcher as DispatcherContract;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Event;
use App\Providers\RouteTranslator;

class EventServiceProvider extends ServiceProvider {

	/**
	 * The event handler mappings for the application.
	 *
	 * @var array
	 */
	protected $listen = [
		'event.name' => [
			'EventListener',
		]/*,
		'App\Events\ParameterLocalize' => [
			'App\Handlers\Events\UrlParameterLocalized'
		]*/
	];

	/**
	 * Register any other events for your application.
	 *
	 * @param  \Illuminate\Contracts\Events\Dispatcher  $events
	 * @return void
	 */
	public function boot(DispatcherContract $events)
	{
		parent::boot($events);

		Event::listen('routes.translation', function($attributes)
        {
            $locale = 'fr';
            foreach( $attributes as $k => $v ){
                if($k === 'slug'){
                    $attributes[$k] = 'translatedxxx';
                }
            }
            return $attributes;
		});
		

		Event::listen('tymon.jwt.absent', function () {
			return response()->json([
				'result' => 'token not provided',
				//'url' => route('api.v1.client.login')
			], 400);
		});

		Event::listen('tymon.jwt.expired', function () {
			return response()->json([                                   
				'result' => 'token expired',
				//'url' => route('api.v1.client.login')
			], 419);
		});

		Event::listen('tymon.jwt.invalid', function () {
		return response()->json([
			'result' => 'token invalid',
			//'url' => route('api.v1.client.login')
			], 400);
		});

		Event::listen('tymon.jwt.user_not_found', function () {
		return response()->json([
			'result' => 'user not found',
			//'url' => route('api.v1.client.login')
			], 400);
		});
			
	}

}
