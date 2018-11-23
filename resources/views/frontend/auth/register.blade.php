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
								<h2>{{ trans('app.register') }}</h2>
								<p>
									{!! Form::open( [ 'route' => getLang().'.register' ] ) !!}
									<div class="row">
										<div class="small-12 columns {!! $errors->has('email') ? 'error' : '' !!}">
											<label>E-Mail
												{!! Form::text( 'email', old('email'), [ 'class' => 'radius autocomplete'] ) !!}
												@if ($errors->first('email'))
													<span class="help-block error">{!! $errors->first('email') !!}</span>
												@endif
											</label>
										</div>
									</div>
									<div class="row action-panel">
										<div class="small-12 columns">
											{!! Form::submit( 'REGISTER', [ 'class' => 'button thirdary tiny radius expand' ] ) !!}
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