@extends('backend.layout.layout')
@section('content')
<section class="content-header">
	<h1>{!! trans('app.finance_settings') !!}</h1>
	<ol class="breadcrumb">
		<li><a href="{!! url(getLang() . '/admin/settings') !!}"><i class="fa fa-bookmark"></i> {!! trans('app.settings') !!}</a></li>
		<li >{!! trans('app.finance') !!}</li>
		<li class="active">{!! trans('app.inflation') !!}</li>
	</ol>
</section>
<div class="content">
	<div class="box">  
		{!! Form::open(array('action' => '\App\Http\Controllers\Backend\SettingController@inflationStore')) !!}
		<div class="box-header with-border">
            <h3 class="box-title">{!! trans('app.inflation') !!}</h3>
        </div>
		<div class="box-body">
			<div class="form-group {!! $errors->has('rate_inflation') ? 'has-error' : '' !!}">
				{!! Form::label('inflasi', trans('app.inflation').' *') !!}
				{!! Form::text('rate_inflation', config_db_cached('settings::rate_inflation'), [ 'class' => 'number form-control', 'autocomplete' => 'off' ]) !!}
				@if ($errors->first('rate_inflation'))
					<span class="help-block">{!! $errors->first('rate_inflation') !!}</span>
				@endif
			</div>
			<div class="form-group {!! $errors->has('rate_property_inflation') ? 'has-error' : '' !!}">
				{!! Form::label('rate_property_inflation', trans('app.property_inflation').' *') !!}
				{!! Form::text('rate_property_inflation', config_db_cached('settings::rate_property_inflation'), [ 'class' => 'number form-control', 'autocomplete' => 'off' ]) !!}
				@if ($errors->first('rate_property_inflation'))
					<span class="help-block">{!! $errors->first('rate_property_inflation') !!}</span>
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
