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
								<h2>{{ trans('app.account_activation') }}</h2>
								<p>
									{!! Notification::container('frontend')->showAll() !!}
									{!! Form::open() !!}
									{!! Form::hidden('activation_code', Request::segment(3)) !!}
									<div class="row {!! $errors->has('password') ? 'error' : '' !!}">
										<div class="large-12 columns">
											<label>{!! trans('app.password') !!} *
												{!! Form::password('password', '', [ 'class' => 'form-control' ]) !!}
												@if ($errors->first('password'))
													<span class="help-block error">{!! $errors->first('password') !!}</span>
												@endif
											</label>
										</div>
									</div>
									<div class="row {!! $errors->has('password_confirmation') ? 'error' : '' !!}">
										<div class="large-12 columns">
											<label>{!! trans('app.confirm_password') !!} *
												{!! Form::password('password_confirmation', '', [ 'class' => 'form-control' ]) !!}
												@if ($errors->first('password_confirmation'))
													<span class="help-block error">{!! $errors->first('password_confirmation') !!}</span>
												@endif
											</label>
										</div>
									</div>
									<div class="row action-panel">
										<div class="small-12 columns">
											{!! Form::submit( trans('app.activate'), [ 'class' => 'button thirdary tiny radius expand' ] ) !!}
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