<?php namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel {

	/**
	 * The application's global HTTP middleware stack.
	 *
	 * @var array
	 */
	protected $middleware = [
		'Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode',
		'Illuminate\Cookie\Middleware\EncryptCookies',
		'Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse',
		'Illuminate\Session\Middleware\StartSession',
		'Illuminate\View\Middleware\ShareErrorsFromSession',
		'Krucas\Notification\Middleware\NotificationMiddleware',
		'App\Http\Middleware\VerifyCsrfToken',
	];

	/**
	 * The application's route middleware.
	 *
	 * @var array
	 */
	protected $routeMiddleware = [
		'auth' => 'App\Http\Middleware\Authenticate',
		'auth.basic' => 'Illuminate\Auth\Middleware\AuthenticateWithBasicAuth',
		'guest' => 'App\Http\Middleware\RedirectIfAuthenticated',
        'auth.admin' => 'App\Http\Middleware\AdminAuthenticate',
        'localize' => 'Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRoutes',
        'localizationRedirect' => 'Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRedirectFilter',
        'localeSessionRedirect' => 'Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect',
        'acl' => 'Kodeine\Acl\Middleware\HasPermission',
		'setLocale' => 'App\Http\Middleware\SetLocale',
		//'jwt.auth' => 'Tymon\JWTAuth\Middleware\GetUserFromToken',
		'jwt.auth' => 'App\Http\Middleware\JWTCheck',
		'jwt.refresh' => 'Tymon\JWTAuth\Middleware\RefreshToken',

		'log'	=> 'App\Http\Middleware\LogAfterRequest',
	];

}
