<!-- <nav class="top-bar main-menu" data-topbar role="navigation" data-options="is_hover: false"> -->
<nav class="top-bar main-menu" data-topbar data-options="is_hover: false">
	<ul class="mobile-menu">
			    {{--<!-- <li class="name">
			      <h1><a href="#"></a></h1>
			    </li>
			    <li class="toggle-offCanvas menu-icon"><a href="idOfLeftMenu" role="button" class="left-off-canvas-toggle"><span>{!! trans('app.category') !!}</span></a></li>--> --}}
			    <li class="toggle-topbar menu-icon"><a href="#"><span></span></a></li>
			</ul>
		<div class="cs right">
			<div class="cs-label">{!! trans('app.customer_service') !!}</div>
			<div class="cs-body">{!! config_db_cached('contact_us::cs_phone') !!}</div>
		</div>
	<section class="top-bar-section">
		<!--<div class="cs right">
			<div class="cs-label">{!! trans('app.customer_service') !!}</div>
			<div class="cs-body">+62 21 666 78 517</div>
		</div>-->
		
		<ul class="left force-right mobile-submenu">
			<!--
			<li class="has-dropdown no-caret {{ setActive('hotel*') }}">
				<a href="#">{{ trans_uc('app.hotel') }}</a>
				<ul class="dropdown dropdown-form hotel">
					<li class="has-form">
						{!! Form::open([ 'route' => getLang().'.dosearch' ]) !!}
						<div class="row">
							<div class="small-12 columns">
								<h2>{!! trans('app.find_hotel') !!}</h2>
							</div>
						</div>
						<div class="row">
							<div class="small-12 columns">
								<label>{!! trans('app.destination') !!}
								{!! Form::text( 'destination', '', [ 'class' => 'radius autocomplete', 'placeholder' => trans('app.destination_or_hotel_name') ] ) !!}
								{!! Form::hidden('search_by') !!}
								{!! Form::hidden('search_slug') !!} 
								<input type="hidden" name="search_post_type" value="hotel" />
								</label>
							</div>
						</div>
						<div class="row">
							<div class="small-4 columns">
								<label>{!! trans('app.check_in') !!}
								{!! Form::text( 'checkin', '', [ 'class' => 'radius checkin', 'autocomplete' => 'off' ] ) !!}
								</label>
							</div>
							<div class="small-4 columns">
								<label>{!! trans('app.nights') !!}
									{!! Form::select('nights', [ 0 => ''] + range(0,Config::get('holiday.max_nights')), 0, [ 'class' => 'radius nights' ]) !!}
								</label>
							</div>
							<div class="small-4 columns">
								<label>{!! trans('app.check_out') !!}
								{!! Form::text( 'checkout', '', [ 'class' => 'radius checkout', 'autocomplete' => 'off' ] ) !!}</label>
							</div>
						</div>
						<div class="row action-panel">
							<div class="small-5 columns">
								{!! HTML::decode(HTML::link( route(getLang().'.dashboard.hotel.countries'), '<i class="fa fa-caret-right"></i> '.trans('app.view_all'), [ 'class' => 'button expand link tiny' ] ) ) !!}
							</div>
							<div class="small-7 columns">
								{!! Form::submit( trans('app.search'), [ 'class' => 'button thirdary tiny radius expand' ] ) !!}
							</div>
						</div>
						{!! Form::close() !!}
					</li>
				</ul>
			</li>

			<li class="has-dropdown no-caret {{ setActive('playground*') }}">
				<a href="#">{{ trans_uc('app.playground') }}</a>
				<ul class="dropdown dropdown-form playground" >
					<li class="has-form">
						{!! Form::open([ 'route' => getLang().'.dosearch' ]) !!}
						<div class="row">
							<div class="small-12 columns">
								<h2>{!! trans('app.find_playground') !!}</h2>
							</div>
						</div>
						<div class="row">
							<div class="small-12 large-8 columns">
								<label>{!! trans('app.destination') !!}
								{!! Form::text( 'playground_destination', '', [ 'class' => 'radius autocomplete', 'placeholder' => trans('app.destination_or_playground_name') ] ) !!}
								{!! Form::hidden('search_by') !!}
								{!! Form::hidden('search_slug') !!}
								<input type="hidden" name="search_post_type" value="playground" />
								</label>
							</div>
							<div class="small-12 large-4 columns">
								<label>{!! trans('app.date') !!}
								{!! Form::text( 'playground_visit_date', '', [ 'class' => 'radius fdatepicker', 'autocomplete' => 'off' ] ) !!}
								</label>
							</div>
						</div>
						<div class="row action-panel">
							<div class="small-5 columns">
								{!! HTML::decode(HTML::link( route(getLang().'.dashboard.playground.countries'), '<i class="fa fa-caret-right"></i> '.trans('app.view_all'), [ 'class' => 'button expand link tiny' ] ) ) !!}
							</div>
							<div class="small-7 columns">
								{!! Form::submit( trans('app.search'), [ 'class' => 'button thirdary tiny radius expand' ] ) !!}
							</div>
						</div>
						{!! Form::close() !!}
					</li>
				</ul>
			</li>
			-->
			<li class="has-dropdown no-caret {{ setActive('trip*') }}">
				<a href="#">{{ trans_uc('app.trip') }}</a>
				<ul class="dropdown dropdown-form trip" >
					<li class="has-form">
						{!! Form::open([ 'route' => getLang().'.dosearch' ]) !!}
						<div class="row">
							<div class="small-12 columns">
								<h2>{!! trans('app.find_trip') !!}</h2>
							</div>
						</div>
						<div class="row">
							<div class="small-12 large-8 columns">
								<label>{!! trans('app.destination') !!}
								{!! Form::text( 'trip_destination', '', [ 'class' => 'radius autocomplete', 'placeholder' => trans('app.destination_or_trip_name') ] ) !!}
								{!! Form::hidden('search_by') !!}
								{!! Form::hidden('search_slug') !!}
								<!-- {!! Form::hidden('search_post_type', 'playground') !!}-->
								<input type="hidden" name="search_post_type" value="trip" />
								</label>
							</div>
							<div class="small-12 large-4 columns date">
								<label>{!! trans('app.date') !!}
								{!! Form::text( 'trip_visit_date', '', [ 'class' => 'radius fdatepickertrip', 'autocomplete' => 'off' ] ) !!}
								</label>
							</div>
						</div>
						<div class="row action-panel">
							<div class="small-5 columns">
								{!! HTML::decode(HTML::link( route(getLang().'.dashboard.trip.countries'), '<i class="fa fa-caret-right"></i> '.trans('app.view_all'), [ 'class' => 'button expand link tiny' ] ) ) !!}
							</div>
							<div class="small-7 columns">
								{!! Form::submit( trans('app.search'), [ 'class' => 'button thirdary tiny radius expand' ] ) !!}
							</div>
						</div>
						{!! Form::close() !!}
					</li>
				</ul>
			</li>

			<li class="has-dropdown no-caret {{ setActive('tourguide*') }}">
				<a href="#">{{ trans_uc('app.tour_guide') }}</a>
				<ul class="dropdown dropdown-form tourguide" >
					<li class="has-form">
						{!! Form::open([ 'route' => getLang().'.dosearch' ]) !!}
						<div class="row">
							<div class="small-12 columns">
								<h2>{!! trans('app.find_tourguide') !!}</h2>
							</div>
						</div>
						<div class="row">
							<div class="small-12 large-12 columns">
								<label>{!! trans('app.tourguide_name') !!}
								{!! Form::text( 'tourguide_name', '', [ 'class' => 'radius autocomplete', 'placeholder' => trans('app.tourguide_name') ] ) !!}
								{!! Form::hidden('search_by') !!}
								{!! Form::hidden('search_slug') !!}
								<!-- {!! Form::hidden('search_post_type', 'playground') !!}-->
								<input type="hidden" name="search_post_type" value="tourguide" />
								</label>
							</div>
						</div>
						<div class="row action-panel">
							<div class="small-5 columns">
								{!! HTML::decode(HTML::link( getLang().'/tourguide', '<i class="fa fa-caret-right"></i> '.trans('app.view_all'), [ 'class' => 'button expand link tiny' ] ) ) !!}
							</div>
							<div class="small-7 columns">
								{!! Form::submit( trans('app.search'), [ 'class' => 'button thirdary tiny radius expand' ] ) !!}
							</div>
						</div>
						{!! Form::close() !!}
					</li>
				</ul>
			</li>

			<li class="has-dropdown no-caret {{ setActive('merchant*') }}">
				<a href="#">{{ trans_uc('app.merchant') }}</a>
				<ul class="dropdown dropdown-form merchant" >
					<li class="has-form">
						{!! Form::open([ 'route' => getLang().'.dosearch' ]) !!}
						<div class="row">
							<div class="small-12 columns">
								<h2>{!! trans('app.find_merchant') !!}</h2>
							</div>
						</div>
						<div class="row">
							<div class="small-12 large-12 columns">
								<label>{!! trans('app.merchant_name') !!}
								{!! Form::text( 'merchant_destination', '', [ 'class' => 'radius autocomplete', 'placeholder' => trans('app.merchant_name') ] ) !!}
								{!! Form::hidden('search_by') !!}
								{!! Form::hidden('search_slug') !!}
								<!-- {!! Form::hidden('search_post_type', 'playground') !!}-->
								<input type="hidden" name="search_post_type" value="merchant" />
								</label>
							</div>
						</div>
						<div class="row action-panel">
							<div class="small-5 columns">
								{!! HTML::decode(HTML::link( route(getLang().'.dashboard.merchant.categories'), '<i class="fa fa-caret-right"></i> '.trans('app.view_all'), [ 'class' => 'button expand link tiny' ] ) ) !!}
							</div>
							<div class="small-7 columns">
								{!! Form::submit( trans('app.search'), [ 'class' => 'button thirdary tiny radius expand' ] ) !!}
							</div>
						</div>
						{!! Form::close() !!}
					</li>
				</ul>
			</li>
			
			<li class="has-dropdown no-caret {{ setActive('reservation*') }}">
				<a href="#">{{ trans_uc('app.reservation') }}</a>
				<ul class="dropdown dropdown-form reservation">
					<li class="has-form">
						{!! Form::open([ 'route' => getLang().'.docheckreservation' ]) !!}
						<div class="row">
							<div class="small-12 columns">
								<h2>{!! trans('app.check_reservation') !!}</h2>
							</div>
						</div>
						<div class="row">
							<div class="small-12 columns">
								<label>{!! trans('app.booking_no') !!}
								{!! Form::text( 'booking_no', '', [ 'class' => 'radius'] ) !!}
								</label>
							</div>
						</div>
						<div class="row">
							<div class="small-12 columns">
								<label>{!! trans('app.email') !!}
								{!! Form::text( 'email', '', [ 'class' => 'radius'] ) !!}
								</label>
							</div>
						</div>
						<div class="row action-panel">
							<div class="small-12 columns">
								{!! Form::submit( trans('app.check'), [ 'class' => 'button thirdary tiny radius expand' ] ) !!}
							</div>
						</div>
						{!! Form::close() !!}
					</li>
				</ul>
			</li>

			<li class="no-caret">
				<a href="{!! URL::route(getLang().'.testimonial') !!}">{{ trans_uc('app.testimonial') }}</a>
			</li>
			{{--
			<li class="no-caret">
				<a href="{!! URL::route(getLang().'.flow') !!}">{{ trans_uc('app.flow') }}</a>
			</li>
			--}}
			{!! $mainMenu_menus !!}
		</ul>
	</section>
</nav>