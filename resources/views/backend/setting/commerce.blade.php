@extends('backend.layout.layout')
@section('content')
<section class="content-header">
	<h1>{!! trans('app.commerce_settings') !!}</h1>
	<ol class="breadcrumb">
		<li><a href="{!! url(getLang() . '/admin/settings') !!}"><i class="fa fa-bookmark"></i> {!! trans('app.settings') !!}</a></li>
		<li class="active">{!! trans('app.commerce') !!}</li>
	</ol>
</section>
<div class="content">
	<div class="box">
		{!! Form::open(array('action' => '\App\Http\Controllers\Backend\SettingController@commerceStore')) !!}
		<div class="box-header with-border">
            <h3 class="box-title">{!! trans('app.commerce') !!}</h3>
        </div>
		<div class="box-body">
			<div class="form-group {!! $errors->has('payment_deadline') ? 'has-error' : '' !!}">
				{!! Form::label('payment_deadline', trans('app.payment_deadline').' *') !!}
				{!! Form::text('payment_deadline', config_db_cached('settings::payment_deadline'), [ 'class' => 'form-control', 'autocomplete' => 'off' ]) !!}
				@if ($errors->first('payment_deadline'))
					<span class="help-block">{!! $errors->first('payment_deadline') !!}</span>
				@endif
				<span class="help-block info">{!! trans('app.in_minutes') !!}, {!! trans('app.example') !!} 60.<br / >Artinya, 60 menit dihitung sejak waktu pemesanan.</span>
			</div>
		</div>
		<div class="box-footer">
			{!! Form::submit(trans('app.save'), array('class' => 'btn btn-success')) !!}
		</div>
		{!! Form::close() !!}
		</div>
</div>
@stop
