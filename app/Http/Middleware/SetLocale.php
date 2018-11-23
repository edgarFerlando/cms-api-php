<?php namespace App\Http\Middleware;

use Closure;
use LaravelLocalization as Localization;

class SetLocale {

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		$locale = $request->route('locale');
	    Localization::setLocale($locale);
	    return $next($request);
	}

}
