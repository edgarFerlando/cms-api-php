<?php namespace App\Http\Middleware;

use Closure;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;

class VerifyCsrfToken extends BaseVerifier {

	protected $except = [
        'api/*',
    ];
	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		//add this condition 
	    foreach($this->except as $route) {

	      if ($request->is($route)) {
	        return $next($request);
	      }
	    }

		return parent::handle($request, $next);
	}

}
