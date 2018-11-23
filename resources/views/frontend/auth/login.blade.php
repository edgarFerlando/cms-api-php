@extends('frontend.layout')
@section('body')
<div class="content-wrap detail collapse">
	<div class="banner">
		<img src="{{ asset('img/banner/spec-banner.jpg') }}">
	</div>
	<div class="row">
		<div class="large-12 columns">
			<div class="content-wrapper">
				<div class="row">
					<div class="small-12 columns">
						<div class="panel shadow box">
							<div class="box-content">
								<h2>{{ trans('app.login') }}</h2>
								<p>
									<?php
									$args = Input::all();
									?>
									{!! Notification::container('frontend')->showAll() !!}
									{!! Form::open( [ 'route' => array_merge([getLang().'.login'], $args) ] ) !!}
									<div class="row">
										<div class="small-12 columns">
											<label>{!! trans('app.email') !!}
												{!! Form::text( 'email', old('email'), [ 'class' => 'radius autocomplete'] ) !!}
											</label>
										</div>
									</div>
									<div class="row">
										<div class="small-12 columns">
											<label>{!! trans('app.password') !!}
												{!! Form::password( 'password', old('password'), [ 'class' => 'radius autocomplete'] ) !!}
											</label>
										</div>
									</div>
									<div class="row">
										<div class="small-12 columns">
											<label>
												{!! Form::checkbox( 'remember', 1, old('remember') ) !!} {!! trans('app.remember_me') !!}
											</label>
										</div>
									</div>
									<div class="row action-panel">
										<div class="small-12 columns">
											{!! Form::submit( trans('app.login'), [ 'class' => 'button thirdary tiny radius expand' ] ) !!}
										</div>
										<div class="small-12 columns">
											<a href="{!! URL::route(getLang().'.forgot-password') !!}">{!! trans('app.forgot_password') !!} ?</a>
										</div>
									</div>
									{!! Form::close() !!}
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