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
				<dt>Reset successfully</dt>
				<dd>Silahkan login pada aplikasi Anda.</dd>
			</dl>
		</div>
	</div>
</div>
@stop