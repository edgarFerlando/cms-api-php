@extends('frontend.plain-layout')
@section('body')
<style>
	.auth-notif-body .btn-border-ijo input.button {
		cursor: pointer;
		border: 2px solid #54c7bd;
	}
</style>
<div class="content-wrap">
	<div class="row">
		<div class="large-12 columns auth-notif-header">
            	<img src="{{ asset('img/logo-fundtastic.png') }}">
		</div>
       	<div class="large-12 columns auth-notif-body">
            	<img src="{{ asset('img/user-success.png') }}">
			<dl>
				<dt>Account activated</dt>
				<dd>Selamat aktivasi akun Anda telah sukses.</dd>
			</dl>

			<div class="row action-panel" style="width:200px;margin:auto;margin-top:30px;margin-bottom:30px;">
				<div class="small-12 columns btn-border-ijo">

					<a href='https://fundtastic.co.id'>
					{!! Form::submit( 'Mulai Sekarang', [ 'class' => 'button thirdary tiny radius expand' ] ) !!}
					</a>

				</div>
			</div>

		</div>
	</div>
</div>
@stop