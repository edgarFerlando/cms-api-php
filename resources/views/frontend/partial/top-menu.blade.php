<?php
//$page_terms_and_conditions = config_db_cached('settings::page_terms_and_conditions['.getLang().']');
?>
<nav class="top-bar top-menu" data-topbar data-options="is_hover: false">
	<section class="top-bar-section">
		<ul class="right">
			@if(Auth::check())
				<li class="has-dropdown">
					<a href="#">{!! Auth::user()->name?Auth::user()->name:Auth::user()->email !!}</a>
					<ul class="dropdown">
						<li><a href="{!! URL::route(getLang().'.mybooking') !!}">{!! trans('app.my_booking') !!}</a></li>
						<li><a href="{!! URL::route(getLang().'.myprofile') !!}">{!! trans('app.my_profile') !!}</a></li>
						<li class="divider"></li>
						<li><a href="{!! URL::route(getLang().'.logout') !!} ">{{{ trans('app.logout') }}}</a></li>
					</ul>
				</li>
			@else
				<li class="has-dropdown no-caret {{ setActive('hotel*') }}">
					<a href="#">{!! trans('app.login') !!}</a>
					<ul class="dropdown dropdown-form login">
						<li class="has-form">
							{!! Form::open( [ 'route' => getLang().'.login' ] ) !!}
							<div class="row">
								<div class="small-12 columns">
									<label>{!! trans('app.email') !!}
									{!! Form::text( 'email', '', [ 'class' => 'radius autocomplete'] ) !!}
									</label>
								</div>
							</div>
							<div class="row">
								<div class="small-12 columns">
									<label>{!! trans('app.password') !!}
									{!! Form::password( 'password', '', [ 'class' => 'radius autocomplete'] ) !!}
									</label>
								</div>
							</div>
							<div class="row action-panel">
								<div class="small-12 columns">
									{!! Form::submit( trans('app.login'), [ 'class' => 'button thirdary tiny radius expand' ] ) !!}
								</div>
							</div>
							{!! Form::close() !!}
						</li>
					</ul>
				</li>
				<li class="has-dropdown no-caret {{ setActive('hotel*') }}">
					<a href="#">{!! trans('app.register') !!}</a>
					<ul class="dropdown dropdown-form register">
						<li class="has-form">
							{!! Form::open([ 'route' => getLang().'.register' ]) !!}
							<div class="row">
								<div class="small-12 columns">
									<label>{!! trans('app.email') !!}
									{!! Form::text( 'email', '', [ 'class' => 'radius'] ) !!}
									</label>
									{{-- <div class="help-text">{{{ trans('app.register_agree') }}} <a href="{!! langUrl(config_db_cached('settings::page_terms_and_conditions['.getLang().']')) !!}" target="_blank">{{{ trans('app.terms_and_conditions') }}}</a> {{{ trans('app.and') }}} <a href="{!! langUrl(config_db_cached('settings::page_policy_privacy['.getLang().']')) !!}" target="_blank">{{{ trans('app.privacy_policy') }}}</a>.</div> --}}
								</div>
							</div>
							<div class="row action-panel">
								<div class="small-12 columns">
									{!! Form::submit( trans('app.join') , [ 'class' => 'button thirdary tiny radius expand' ] ) !!}
								</div>
							</div>
							{!! Form::close() !!}
						</li>
					</ul>
				</li>
			@endif
			<li><a href="{{ URL::route(getLang().'.booking') }}">{!! trans('app.my_cart') !!}</a></li>
			<!--<li class="has-dropdown">
		        <a href="#">IDR</a>
		        <ul class="dropdown">
		          <li><a href="#">IDR</a></li>
		          <li><a href="#">USD</a></li>
		        </ul>
		    </li>-->
			<li class="has-dropdown">
		        <a href="#">{{ LaravelLocalization::getCurrentLocaleName() }}</a>
		        <ul class="dropdown">
					<?php
						
						if(!isset($transRoute)){
							$transRoute = [ 
					            'route' => '',
					            'attrs' => []
					        ];
						}

					?>
					@foreach(LaravelLocalization::getSupportedLocales() as $locale => $properties)
						@if($locale != LaravelLocalization::getCurrentLocale())
						<li>
							<a rel="alternate" hreflang="{{$locale}}" href="{{ trans_route($locale, $transRoute['route'], $transRoute['attrs']) }}">
								{!! $properties['native'] !!}
							</a>
						</li>
						@endif
					@endforeach
				</ul>
				<!-- 
		        <ul class="dropdown">
		        	@foreach(LaravelLocalization::getSupportedLocales() as $locale => $properties)
				        <li>
				        	<?php 
				        	if(isset($url_attributes_localize)){ 
				        		$request_url = langURL($url_attributes_localize[$locale]['url']);
				        	}else{
				        		$request_url = Request::url();
				        	}
				        	?>
				            <a rel="alternate" hreflang="{{$locale}}" href="{{ LaravelLocalization::getLocalizedURL($locale, $request_url) }}">
				                {{{ $properties['native'] }}}
				            </a>
				        </li>
				    @endforeach
		        </ul>-->
		      </li>
		</ul>
	</section>
</nav>