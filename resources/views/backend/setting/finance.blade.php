@extends('backend.layout.layout')
@section('content')
<section class="content-header">
	<h1>{!! trans('app.finance_settings') !!}</h1>
	<ol class="breadcrumb">
		<li><a href="{!! url(getLang() . '/admin/settings') !!}"><i class="fa fa-bookmark"></i> {!! trans('app.settings') !!}</a></li>
		<li class="active">{!! trans('app.finance') !!}</li>
	</ol>
</section>
<div class="content">
	<div class="box">  
		{!! Form::open(array('action' => '\App\Http\Controllers\Backend\SettingController@financeStore')) !!}
		<div class="box-header with-border">
            <h3 class="box-title">{!! trans('app.finance') !!}</h3>
        </div>
		<div class="box-body">
			<h4>Inflasi</h4>
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
			<h4>Investasi</h4>
			<div class="form-group {!! $errors->has('rate_deposit') ? 'has-error' : '' !!}">
				{!! Form::label('deposito', trans('app.deposit').' *') !!}
				{!! Form::text('rate_deposit', config_db_cached('settings::rate_deposit'), [ 'class' => 'number form-control', 'autocomplete' => 'off' ]) !!}
				@if ($errors->first('rate_deposit'))
					<span class="help-block">{!! $errors->first('rate_deposit') !!}</span>
				@endif
			</div>
			<h4>Asuransi</h4>
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
