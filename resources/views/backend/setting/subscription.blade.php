@extends('backend.layout.layout')
@section('content')
<section class="content-header">
	<h1>{!! trans('app.subscription_settings') !!}</h1>
	<ol class="breadcrumb">
		<li><a href="{!! url(getLang() . '/admin/settings') !!}"><i class="fa fa-bookmark"></i> {!! trans('app.settings') !!}</a></li>
		<li class="active">{!! trans('app.subscription') !!}</li>
	</ol>
</section>
<div class="content">
{!! Notification::showAll() !!}
	<div class="box">  
		{!! Form::open(array('action' => '\App\Http\Controllers\Backend\SettingController@subscriptionStore')) !!}
		<div class="box-header with-border">
            <h3 class="box-title">{!! trans('app.subscription') !!}</h3>
        </div>
		<div class="box-body">
			<h4>Consultation</h4>
			<div class="form-group {!! $errors->has('free_consultation_limit') ? 'has-error' : '' !!}">
				{!! Form::label('free_consultation_limit', trans('app.free_consultation_limit').' *') !!}
				{!! Form::text('free_consultation_limit', config_db_cached('settings::free_consultation_limit'), [ 'class' => 'form-control', 'autocomplete' => 'off' ]) !!}
				<span class="help-block info">Dibandingkan dengan jumlah cashflow analysis yang pernah disimpan ( Tidak termasuk yang direject ).</span>
				@if ($errors->first('free_consultation_limit'))
					<span class="help-block">{!! $errors->first('free_consultation_limit') !!}</span>
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
