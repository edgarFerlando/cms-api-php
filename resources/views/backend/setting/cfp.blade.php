@extends('backend.layout.layout')
@section('content')
<section class="content-header">
	<h1>{!! trans('app.cfp_settings') !!}</h1>
	<ol class="breadcrumb">
		<li><a href="{!! url(getLang() . '/admin/settings') !!}"><i class="fa fa-bookmark"></i> {!! trans('app.settings') !!}</a></li>
		<li class="active">{!! trans('app.cfp') !!}</li>
	</ol>
</section>
<div class="content">
	<div class="box">  
		{!! Form::open(array('action' => '\App\Http\Controllers\Backend\SettingController@cfpStore')) !!}
		<div class="box-header with-border">
            <h3 class="box-title">{!! trans('app.cfp') !!}</h3>
        </div>
		<div class="box-body">
			<h4>Working hours</h4>
			<div class="form-group {!! $errors->has('cfp_working_hour_start') ? 'has-error' : '' !!}">
				{!! Form::label('cfp_working_hour_start', trans('app.start').' *') !!}
				{!! Form::text('cfp_working_hour_start', config_db_cached('settings::cfp_working_hour_start'), [ 'class' => 'form-control', 'autocomplete' => 'off' ]) !!}
				@if ($errors->first('cfp_working_hour_start'))
					<span class="help-block">{!! $errors->first('cfp_working_hour_start') !!}</span>
				@endif
			</div>
			<div class="form-group {!! $errors->has('cfp_working_hour_end') ? 'has-error' : '' !!}">
				{!! Form::label('cfp_working_hour_end', trans('app.end').' *') !!}
				{!! Form::text('cfp_working_hour_end', config_db_cached('settings::cfp_working_hour_end'), [ 'class' => 'form-control', 'autocomplete' => 'off' ]) !!}
				@if ($errors->first('cfp_working_hour_end'))
					<span class="help-block">{!! $errors->first('cfp_working_hour_end') !!}</span>
				@endif
			</div>
			<h4>CFP Reminder</h4>
			<div class="form-group {!! $errors->has('cfp_remind_x_minutes_before_schedule') ? 'has-error' : '' !!}">
				{!! Form::label('cfp_remind_x_minutes_before_schedule', 'Set X minutes before schedule datetime *') !!}
				{!! Form::text('cfp_remind_x_minutes_before_schedule', config_db_cached('settings::cfp_remind_x_minutes_before_schedule'), [ 'class' => 'form-control', 'autocomplete' => 'off' ]) !!}
				@if ($errors->first('cfp_remind_x_minutes_before_schedule'))
					<span class="help-block">{!! $errors->first('cfp_remind_x_minutes_before_schedule') !!}</span>
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
