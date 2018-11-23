@extends('frontend.layout')
@section('body')
<div class="content-wrap home collapse">
	<div class="banner">
		<img src="{{ asset('img/banner/spec-banner.jpg') }}">
	</div>
	<div class="row has-carousel">
		<div class="large-3 columns">
			<div class="box shadow">
				<div class="box-header"><h2 class="title">{!! trans('app.find_tourguide') !!}</h2></div>
				<div class="box-content">
					{!! Form::open([ 'route' => getLang().'.dosearch' ]) !!}
					<div class="row">
						<div class="small-12 columns {!! $errors->has('tourguide_name') ? 'error' : '' !!}">
							<label>{!! trans('app.tourguide_name') !!}
								{!! Form::text( 'tourguide_name', Input::get('tourguide_name')?Input::get('tourguide_name'):Input::old('tourguide_name'), [ 'class' => 'radius autocomplete', 'autocomplete_source' => url('lists-city'), 'placeholder' => trans('app.tourguide_name')] ) !!}
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
				</div>
			</div>
		</div>

		<div class="large-9 columns">
			<div class="large-12 columns">
				<h3>{{ trans('app.tour_guide') }}</h3>
			</div>
			<div class="large-12 columns">
				<div class="special-offers-carousel">
					@foreach($users as $user)
					<?php
						$userMeta = userMeta($user->userMetas);
					?>
					<div class="item">
						@if(isset($userMeta->user_image) && $userMeta->user_image != '')
							<img src="{{ asset($userMeta->user_image) }}">
						@else
							<img src="{{ asset('img/products/item-sm-1.jpg') }}">
						@endif
						
						<div class="panel shadow">
							<ul class="no-bullet">
								<li class="title"><h4>{!! HTML::link(getLang().'/tourguide/detail/'.$user->id, $user->name ) !!}</h4></li>
								<!--<li>
									<i class="fa fa-star"></i>
									<i class="fa fa-star"></i>
									<i class="fa fa-star"></i>
									<i class="fa fa-star"></i>
								</li>-->
								<li>{!! $userMeta->language_skill !!}</li>
							</ul>
						</div>
					</div>
					@endforeach
				</div>
				<div class="pagination-centered">
					{!! (new App\Pagination($users))->render() !!}
				</div>
			</div>
		</div>
	</div>
</div>
@stop