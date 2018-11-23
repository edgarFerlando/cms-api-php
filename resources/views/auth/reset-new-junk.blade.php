@extends('frontend.plain-layout')
@section('body')
<div class="content-wrap">
	<div class="row">
		<div class="large-12 columns auth-notif-header">
            	<img src="{{ asset('img/logo-fundtastic.png') }}">
		</div>
       	<div class="large-12 columns auth-notif-body">
            <img src="{{ asset('img/reset.png') }}">
			<dl>
				<dt>Reset your password</dt>
				<dd>Masukkan password baru Anda.</dd>
			</dl>
			{!! Form::open( [ 'route' => 'do-reset-password' ] ) !!}
				{{-- {!! Form::hidden('token', $token) !!} --}}
				<div class="row">
					<div class="small-12 columns {!! $errors->has('email') ? 'error' : '' !!}">
						<label>{!! trans('app.email') !!}
							{!! Form::text( 'email', old('email'), [ 'class' => 'radius autocomplete'] ) !!}
							@if ($errors->first('email'))
								<span class="help-block error">{!! $errors->first('email') !!}</span>
							@endif
						</label>
					</div>
					<div class="small-12 columns {!! $errors->has('password') ? 'error' : '' !!}">
						<label>{!! trans('app.password') !!}
							{!! Form::password( 'password', [ 'class' => 'radius autocomplete'] ) !!}
							@if ($errors->first('password'))
								<span class="help-block error">{!! $errors->first('password') !!}</span>
							@endif
						</label>
					</div>
					<div class="small-12 columns {!! $errors->has('password_confirmation') ? 'error' : '' !!}">
						<label>{!! trans('app.confirm_password') !!}
							{!! Form::password( 'password_confirmation', [ 'class' => 'radius autocomplete'] ) !!}
							@if ($errors->first('password_confirmation'))
								<span class="help-block error">{!! $errors->first('password_confirmation') !!}</span>
							@endif
						</label>
					</div>
				</div>
				<div class="row action-panel">
					<div class="small-12 columns">
						{!! Form::submit( trans('app.save'), [ 'class' => 'button thirdary tiny radius expand' ] ) !!}
					</div>
				</div>
				{!! Form::close() !!}
		</div>
	</div>
</div>
@stop