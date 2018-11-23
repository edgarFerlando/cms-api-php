@extends('backend.layout.layout')
@section('content')
<section class="content-header">
	<h1>{!! trans('app.finance_settings') !!}</h1>
	<ol class="breadcrumb">
		<li><a href="{!! url(getLang() . '/admin/settings') !!}"><i class="fa fa-bookmark"></i> {!! trans('app.settings') !!}</a></li>
		<li>{!! trans('app.finance') !!}</li>
		<li class="active">{!! trans('app.insurance') !!}</li>
	</ol>
</section>
<div class="content">
	<div class="box">  
		{!! Form::open(array('action' => '\App\Http\Controllers\Backend\SettingController@insuranceStore')) !!}
		<div class="box-header with-border">
            <h3 class="box-title">{!! trans('app.finance') !!}</h3>
        </div>
		<div class="box-body">
			<div class="form-group {!! $errors->has('price_life_insurance') ? 'has-error' : '' !!}">
				{!! Form::label('price_life_insurance', trans('app.price_life_insurance').' *') !!}
				{!! Form::text('price_life_insurance', config_db_cached('settings::price_life_insurance'), [ 'class' => 'number form-control', 'autocomplete' => 'off' ]) !!}
				<span class="help-block info">per 1 M, annualy</span>
				@if ($errors->first('price_life_insurance'))
					<span class="help-block">{!! $errors->first('price_life_insurance') !!}</span>
				@endif
			</div>
			<div class="form-group {!! $errors->has('price_critical_insurance') ? 'has-error' : '' !!}">
				{!! Form::label('price_critical_insurance', trans('app.price_critical_insurance').' *') !!}
				{!! Form::text('price_critical_insurance', config_db_cached('settings::price_critical_insurance'), [ 'class' => 'number form-control', 'autocomplete' => 'off' ]) !!}
				<span class="help-block info">per 1 M, monthly</span>
				@if ($errors->first('price_critical_insurance'))
					<span class="help-block">{!! $errors->first('price_critical_insurance') !!}</span>
				@endif
			</div>
			<div class="form-group {!! $errors->has('tenor_plan_protection') ? 'has-error' : '' !!}">
				{!! Form::label('tenor_plan_protection', trans('app.tenor_plan_protection').' *') !!}
				{!! Form::text('tenor_plan_protection', config_db_cached('settings::tenor_plan_protection'), [ 'class' => 'number form-control', 'autocomplete' => 'off' ]) !!}
				<span class="help-block info">in year</span>
				@if ($errors->first('tenor_plan_protection'))
					<span class="help-block">{!! $errors->first('tenor_plan_protection') !!}</span>
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
