<?php namespace App\Http\Middleware;

use Closure;

use Illuminate\Support\Facades\Log;
use App\Models\Log as LogInput;

class LogAfterRequest {

	protected $log_input;
	public function __construct(LogInput $log_input) {
		$this->log_input = $log_input;
    }

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{

		// Log::info('app.requests', ['request' => $request->all(), 'response' => json_encode($next($request)->getData()) ]);

		/**
		 | --------------------------------------------
		 | Proses memasukan request ke tabel LOG
		 | --------------------------------------------
    	 */
    	 
    	$input = $request->all();

    	$create_log = $this->log_input->create([
			'name' => json_encode($input),
			'description' => '' // json_encode($next($request)->getData())
		]);
		 

		return $next($request);
	}

	public function terminate($request, $response)
	{
		Log::info('app.requests', ['request' => $request->all(), 'response' => $response]);
	}

}