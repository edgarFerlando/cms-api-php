@extends('frontend.plain-layout')
@section('body')
<div class="content-wrap">
	<div class="row">
		<div class="large-12 columns auth-notif-header">
            	<img src="{{ asset('img/logo-fundtastic.png') }}">
		</div>
       	<div class="large-12 columns auth-notif-body">
            	<img src="{{ asset('img/user-failed.png') }}">
			<dl>
				<dt>Account error</dt>
				<dd>Aktivasi akun Anda gagal atau sudah pernah diaktivasi.</dd>
			</dl>
		</div>
	</div>
</div>
@stop