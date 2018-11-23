@extends('backend.layout.layout')
@section('content')
<section class="content-header">
	<h1>{!! trans('app.schedule_cfp') !!}</h1>
	<ol class="breadcrumb">
		<li><a href="{!! url(getLang() . '/admin/cfp/schedule') !!}"><i class="fa fa-bookmark"></i> {!! trans('app.schedule_cfp') !!}</a></li>
		<li class="active">{!! trans('app.add_new') !!}</li>
	</ol>
</section>
<div class="content">
	<div class="box">  
		{!! Form::open(array('action' => '\App\Http\Controllers\Backend\CfpScheduleController@store')) !!}
		<div class="box-header with-border">
            <h3 class="box-title">{!! trans('app.add_new') !!}</h3>
        </div>
		<div class="box-body">
			<div class="form-group {!! $errors->has('schedule_type') ? 'has-error' : '' !!}">
				{!! Form::label('schedule_type', trans('app.cfp_schedule_type').' *') !!}
				{!!  Form::select('schedule_type', $cfp_schedule_types, '', [ 'class' => 'form-control' ]) !!}
				@if ($errors->first('schedule_type'))
					<span class="help-block">{!! $errors->first('schedule_type') !!}</span>
				@endif
			</div>
			<div class="form-group {!! $errors->has('title') ? 'has-error' : '' !!}">
				{!! Form::label('title', trans('app.your_concern').' *') !!}
				{!! Form::text('title', Input::old('title'), [ 'class' => 'form-control', 'autocomplete' => 'off' ]) !!}
				@if ($errors->first('title'))
					<span class="help-block">{!! $errors->first('title') !!}</span>
				@endif
			</div>
			<div class="form-group {!! $errors->has('cfp_id') ? 'has-error' : '' !!}">
				{!! Form::label('cfp_id', trans('app.cfp_name').' *') !!}
				{!! Form::select('cfp_id', [], '', [ 'class' => 'selectize_cfps', 'autocomplete' => 'off', 'selectize_url' => 'admin/autocomplete/cfpclient-cfps', 'old' => Input::old('cfp_id') ]) !!}
				@if ($errors->first('cfp_id'))
					<span class="help-block">{!! $errors->first('cfp_id') !!}</span>
				@endif
			</div>
			<div class="form-group {!! $errors->has('client_id') ? 'has-error' : '' !!}">
				{!! Form::label('client_id', trans('app.client_name').' *') !!}
				{!! Form::select('client_id', [], '', [ 'class' => 'selectize_clients', 'selectize_url' => 'admin/autocomplete/cfpclient-clients', 'autocomplete' => 'off', 'old' => Input::old('client_id') ]) !!}
				@if ($errors->first('client_id'))
					<span class="help-block">{!! $errors->first('client_id') !!}</span>
				@endif
			</div>
			<!--<div class="form-group">
			<div class="row">
				<div class="col-lg-6">-->
					<div class="form-group {!! $errors->has('schedule_start_date') ? 'has-error' : '' !!}">
		                {!! Form::label('schedule_start_date', trans('app.schedule_start_date').' *') !!}
		                {!! 
		                    Form::text('schedule_start_date', Input::old('schedule_start_date'), [ 'class'=>'form-control bdatepicker_schedule_start_date'])
		                !!}
				        @if ($errors->first('schedule_start_date'))
							<span class="help-block">{!! $errors->first('schedule_start_date') !!}</span>
						@endif
		            </div>
				<!--</div>
				<div class="col-lg-6">
					<div class="form-group {!! $errors->has('schedule_available_time_slot') ? 'has-error' : '' !!}">
		                {!! Form::label('schedule_available_time_slot', trans('app.available_time_slot').' *') !!}
		                {!!  Form::select('schedule_available_time_slot', [], '', [ 'class' => 'form-control', 'old' => Input::old('schedule_available_time_slot') ]) !!}
				        @if ($errors->first('schedule_available_time_slot'))
							<span class="help-block">{!! $errors->first('schedule_available_time_slot') !!}</span>
						@endif
		            </div>
				</div>
			</div>-->
			<div class="form-group {!! $errors->has('schedule_available_time_slot') ? 'has-error' : '' !!}">
                {!! Form::label('schedule_available_time_slot', trans('app.available_time_slot').' *') !!}
                {!!  Form::hidden('schedule_available_time_slot', Input::old('schedule_available_time_slot')) !!}
                <!--<div id="day-schedule" data-disabled="%5B%5B%5B%2209%3A30%22%2C%2211%3A00%22%5D%2C%5B%2213%3A00%22%2C%2216%3A30%22%5D%5D%5D" data-start="09:00" data-end="17:00"></div>-->
                <div id="day-schedule">&nbsp;</div>
		        @if ($errors->first('schedule_available_time_slot'))
					<span class="help-block">{!! $errors->first('schedule_available_time_slot') !!}</span>
				@endif
            </div>
			<!--</div>
			<div class="row">
				<div class="col-lg-6">
					<div class="form-group {!! $errors->has('schedule_start_date') ? 'has-error' : '' !!}">
						{!! Form::label('schedule_start_date', trans('app.schedule_start_date').' *') !!}
						{!! Form::text('schedule_start_date', Input::old('schedule_start_date'), [ 'class' => 'form-control schedule-date', 'autocomplete' => 'off', 'readonly' => 'readonly']) !!}
						@if ($errors->first('schedule_start_date'))
							<span class="help-block">{!! $errors->first('schedule_start_date') !!}</span>
						@endif
					</div>
				</div>
				<div class="col-lg-6">
					<div class="form-group {!! $errors->has('schedule_end_date') ? 'has-error' : '' !!}">
						{!! Form::label('schedule_end_date', trans('app.schedule_end_date').' *') !!}
						{!! Form::text('schedule_end_date', Input::old('schedule_end_date'), [ 'class' => 'form-control schedule-date', 'autocomplete' => 'off' ,'readonly' => 'readonly']) !!}
						@if ($errors->first('schedule_end_date'))
							<span class="help-block">{!! $errors->first('schedule_end_date') !!}</span>
						@endif
					</div>
				</div>
			</div>
			<p></p>
			<div class="form-group {!! $errors->has('location') ? 'has-error' : '' !!}">
				{!! Form::label('location', trans('app.location')) !!}
				{!! Form::text('location', Input::old('location'), [ 'class' => 'form-control', 'autocomplete' => 'off' ]) !!}
				@if ($errors->first('location'))
					<span class="help-block">{!! $errors->first('location') !!}</span>
				@endif
			</div>-->
			<div class="form-group {!! $errors->has('notes') ? 'has-error' : '' !!}">
				{!! Form::label('notes', trans('app.notes')) !!}
				{!! Form::textarea('notes', Input::old('notes'), [ 'class' => 'form-control', 'autocomplete' => 'off', 'rows' => 4 ]) !!}
				@if ($errors->first('notes'))
					<span class="help-block">{!! $errors->first('notes') !!}</span>
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
