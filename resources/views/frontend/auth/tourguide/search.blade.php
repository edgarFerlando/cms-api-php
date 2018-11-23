@extends('frontend.layout')
@section('body')
<div class="content-wrap detail collapse">
	@include('frontend.partial.staticBanner')
	<div class="row">
		<div class="large-12 columns">
			<div class="content-wrapper">
				<div class="row">
					<div class="small-12 columns">
						<div class="panel shadow box">
							<div class="box-content">
								<h2>{{ trans('app.search') }}</h2>
								<p>
									{!! Notification::container('frontend')->showAll() !!}
									{!! Form::open( [ 'route' => getLang().'.dosearch' ] ) !!}
									<div class="row">
										<div class="small-12 large-12 columns {!! $errors->has('tourguide_name') ? 'error' : '' !!}">
											<label>{!! trans('app.tourguide_name') !!}
												{!! Form::text( 'tourguide_name', Input::old('tourguide_name'), [ 'class' => 'radius autocomplete', 'autocomplete_source' => url('lists-city'), 'placeholder' => trans('app.tourguide_name') ] ) !!}
												{!! Form::hidden('search_by', Input::old('search_by')) !!}
												{!! Form::hidden('search_slug', Input::old('search_slug')) !!}
												{!! Form::hidden('search_post_type', 'tourguide') !!}
												@if ($errors->first('tourguide_name'))
													<span class="help-block error">{!! $errors->first('tourguide_name') !!}</span>
												@endif
											</label>
										</div>
									</div>

									<div class="row action-panel">
										<div class="large-12 columns">
											{!! Form::submit( trans('app.search'), [ 'class' => 'button tiny warning radius expand bold' ] ) !!}
										</div>
									</div>
									{!! Form::close() !!}

									@if(isset($users))
										<div class="row">
											<div class="large-12 columns">
											<h4>{!! trans('app.tour_guide') !!}</h4>
											@foreach($users as $tourguide)
												{!! HTML::link(getLang().'/tourguide/detail/'.$tourguide->id, $tourguide->name ) !!}
												<p></p>
											@endforeach
											</div>
										</div>
										<p></p>

										<div class="row">
											<div class="large-12 columns">
											<h4>{!! trans('app.trip') !!}</h4>
											@foreach($users as $tourguide)
												@foreach($tourguide->trips as $trip)
													{!! HTML::link(route(getLang().'.dashboard.trip.show', $trip->slug), $trip->title) !!}
													<p></p>
												@endforeach
											@endforeach
											</div>
										</div>
									@endif

									</div>
									
								</p>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
@stop