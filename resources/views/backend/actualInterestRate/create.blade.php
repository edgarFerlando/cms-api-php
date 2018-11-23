@extends('backend/layout/layout')
@section('content')
<section class="content-header">
	<h1>{!! trans('app.actual_interest_rate') !!}</h1>
	<ol class="breadcrumb">
		<li><a href="{!! url(getLang() . '/admin/settings/actual-interest-rate') !!}"><i class="fa fa-bookmark"></i> {!! trans('app.actual_interest_rate') !!}</a></li>
		<li class="active">{!! trans('app.add_new') !!}</li>
	</ol>
</section>
<div class="content">
	<div class="box">  
		{!! Form::open(array('action' => '\App\Http\Controllers\Backend\ActualInterestRateController@store')) !!}
		<div class="box-header with-border">
            <h3 class="box-title">{!! trans('app.add_new') !!}</h3>
        </div>
		<div class="box-body">
			<div class="form-group {!! $errors->has('interest_rate_id') ? 'has-error' : '' !!}">
				{!! Form::label('interest_rate_id', trans('app.product').' *') !!}
				{!!  Form::select('interest_rate_id', $interestRate_options, '', [ 'class' => 'form-control' ]) !!}
				@if ($errors->first('interest_rate_id'))
					<span class="help-block">{!! $errors->first('interest_rate_id') !!}</span>
				@endif
			</div>
			<div class="form-group {!! $errors->has('rate') ? 'has-error' : '' !!}">
				{!! Form::label('rate', '% '.trans('app.rate').' *') !!}
				{!! Form::text('rate', Input::old('rate'), [ 'class' => 'form-control number', 'autocomplete' => 'off' ]) !!}
				@if ($errors->first('rate'))
					<span class="help-block">{!! $errors->first('rate') !!}</span>
				@endif
			</div>
			<div class="form-group {!! $errors->has('period') ? 'has-error' : '' !!}">
				{!! Form::label('period', trans('app.period').' *') !!}
				{!! Form::text('period', Input::old('period'), [ 'class' => 'form-control bdatepicker-month-short', 'autocomplete' => 'off' ]) !!}
				@if ($errors->first('period'))
					<span class="help-block">{!! $errors->first('period') !!}</span>
				@endif
			</div>
		</div>
		<div class="box-footer">
			{!! Form::submit(trans('app.save'), array('class' => 'btn btn-success')) !!}
			{!! link_to( langRoute('admin.settings.finance.actual-interest-rate.index'), trans('app.cancel'), array( 'class' => 'btn btn-primary  form-controll' ) ) !!}
		</div>
		{!! Form::close() !!}
		</div>
</div>
@stop