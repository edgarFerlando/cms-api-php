@extends('backend.layout.layout')
@section('content')
<section class="content-header">
	<h1>{!! trans('app.finance_settings') !!}</h1>
	<ol class="breadcrumb">
		<li><a href="{!! url(getLang() . '/admin/finance/settings') !!}"><i class="fa fa-bookmark"></i> {!! trans('app.settings') !!}</a></li>
		<li>{!! trans('app.finance') !!}</li>
		<li class="active">{!! trans('app.investment') !!}</li>
	</ol>
</section>
<div class="content">
	<div class="box">  
		{!! Form::open(array('action' => '\App\Http\Controllers\Backend\SettingController@investmentStore')) !!}
		<div class="box-header with-border">
            <h3 class="box-title">{!! trans('app.investment') !!}</h3>
        </div>
		<div class="box-body">
			<div class="form-group {!! $errors->has('rate_deposit') ? 'has-error' : '' !!}">
				{!! Form::label('deposito', trans('app.deposit').' *') !!}
				{!! Form::text('rate_deposit', config_db_cached('settings::rate_deposit'), [ 'class' => 'number form-control', 'autocomplete' => 'off' ]) !!}
				@if ($errors->first('rate_deposit'))
					<span class="help-block">{!! $errors->first('rate_deposit') !!}</span>
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
