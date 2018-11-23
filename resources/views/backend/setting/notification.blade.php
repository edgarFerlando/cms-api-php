@extends('backend.layout.layout')
@section('content')
<section class="content-header">
	<h1>{!! trans('app.notification_settings') !!}</h1>
	<ol class="breadcrumb">
		<li><a href="{!! url(getLang() . '/admin/settings') !!}"><i class="fa fa-bookmark"></i> {!! trans('app.settings') !!}</a></li>
		<li class="active">{!! trans('app.notification') !!}</li>
	</ol>
</section>
<div class="content">
	<div class="box">  
		{!! Form::open(array('action' => '\App\Http\Controllers\Backend\SettingController@notificationStore')) !!}
		<div class="box-header with-border">
            <h3 class="box-title">{!! trans('app.notification') !!}</h3>
        </div>
		<div class="box-body">
			<div class="form-group {!! $errors->has('client_max_number_notifications') ? 'has-error' : '' !!}">
				{!! Form::label('client_max_number_notifications', trans('app.client_max_number_notifications').' *') !!}
				{!! Form::text('client_max_number_notifications', config_db_cached('settings::client_max_number_notifications'), [ 'class' => 'form-control', 'autocomplete' => 'off' ]) !!}
				<span class="help-block info">Jumlah max notifikasi yang akan ditampilkan pada client app</span>
				@if ($errors->first('client_max_number_notifications'))
					<span class="help-block">{!! $errors->first('client_max_number_notifications') !!}</span>
				@endif
			</div>
			<div class="form-group {!! $errors->has('cfp_max_number_notifications') ? 'has-error' : '' !!}">
				{!! Form::label('cfp_max_number_notifications', trans('app.cfp_max_number_notifications').' *') !!}
				{!! Form::text('cfp_max_number_notifications', config_db_cached('settings::cfp_max_number_notifications'), [ 'class' => 'form-control', 'autocomplete' => 'off']) !!}
				<span class="help-block info">Jumlah max notifikasi yang akan ditampilkan pada CFP app</span>
				@if ($errors->first('cfp_max_number_notifications'))
					<span class="help-block">{!! $errors->first('cfp_max_number_notifications') !!}</span>
				@endif
			</div>
		</div>
		<div class="box-footer">
			{!! Form::submit(trans('app.save'), array('class' => 'btn btn-success')) !!}
		</div>
		{!! Form::close() !!}
		</div>
</div>
@stop
