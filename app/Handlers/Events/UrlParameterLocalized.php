<?php namespace App\Handlers\Events;

use App\Events\ParameterLocalize;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldBeQueued;

class UrlParameterLocalized {

	/**
	 * Create the event handler.
	 *
	 * @return void
	 */
	public function __construct()
	{
		//
	}

	/**
	 * Handle the event.
	 *
	 * @param  ParameterLocalize  $event
	 * @return void
	 */
	public function handle(ParameterLocalize $event)
	{
		dd('ini ada di UrlParameterLocalized'.$event);
	}

}
