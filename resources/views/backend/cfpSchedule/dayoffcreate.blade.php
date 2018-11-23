@extends('backend.layout.layout')
@section('content')
<section class="content-header">
	<h1>{!! trans('app.cfp_schedule_dayoff') !!}</h1>
	<ol class="breadcrumb">
		<li><a href="{!! url(getLang() . '/admin/cfp/schedule_dayoff') !!}"><i class="fa fa-bookmark"></i> {!! trans('app.cfp_schedule_dayoff') !!}</a></li>
		<li class="active">{!! trans('app.add_new') !!}</li>
	</ol>
</section>
<div class="content">
	<div class="box">  
		{!! Form::open(array('action' => '\App\Http\Controllers\Backend\CfpScheduleController@cfpScheduleDayOffStore')) !!}
		<div class="box-header with-border">
            <h3 class="box-title">{!! trans('app.add_new') !!}</h3>
        </div>
		<div class="box-body">
			@if (Auth::user()->id == 3)
			<div class="form-group {!! $errors->has('cfp_id') ? 'has-error' : '' !!}">
				{!! Form::label('cfp_id', trans('app.cfp_name').' *') !!}
				{!! Form::select('cfp_id', [], '', [ 'class' => 'selectize_cfps', 'autocomplete' => 'off', 'selectize_url' => 'admin/autocomplete/cfpclient-cfps', 'old' => Input::old('cfp_id') ]) !!}
				@if ($errors->first('cfp_id'))
					<span class="help-block">{!! $errors->first('cfp_id') !!}</span>
				@endif
			</div>
			@endif
			<!--<div class="form-group">
				<div class="row">
					<div class="col-lg-6">-->
						<div class="form-group {!! $errors->has('cfp_schedule_day_off_start_date') ? 'has-error' : '' !!}">
							{!! Form::label('cfp_schedule_day_off_start_date', trans('app.cfp_schedule_day_off_start_date').' *') !!}
							{!! 
								Form::text('cfp_schedule_day_off_start_date', Input::old('cfp_schedule_day_off_start_date'), [ 'class'=>'form-control bdatepicker_schedule_start_date'])
							!!}
							@if ($errors->first('cfp_schedule_day_off_start_date'))
								<span class="help-block">{!! $errors->first('cfp_schedule_day_off_start_date') !!}</span>
							@endif
						</div>
					<!--</div>
				</div>-->
				
				<!--<div class="form-group">
					<div class="row">
						<div class="col-lg-6">-->
							<div class="form-group {!! $errors->has('cfp_schedule_day_off_end_date') ? 'has-error' : '' !!}">
								{!! Form::label('cfp_schedule_day_off_end_date', trans('app.cfp_schedule_day_off_end_date').' *') !!}
								{!! 
									Form::text('cfp_schedule_day_off_end_date', Input::old('cfp_schedule_day_off_end_date'), [ 'class'=>'form-control bdatepicker_schedule_start_date'])
								!!}
								@if ($errors->first('cfp_schedule_day_off_end_date'))
									<span class="help-block">{!! $errors->first('cfp_schedule_day_off_end_date') !!}</span>
								@endif
							</div>
						<!--</div>
					</div>-->

					@if (Auth::user()->id == 3)
					<div class="form-group {!! $errors->has('is_approval') ? 'has-error' : '' !!}">
						{!! Form::label('is_approval', trans('app.is_approval').' *') !!}
						{!! Form::select('is_approval', ['Belum Disetujui', 'Cuti Disetujui', 'Cuti Ditolak'], 0, ['class' => 'selectize_clients']) !!}
						@if ($errors->first('is_approval'))
							<span class="help-block">{!! $errors->first('is_approval') !!}</span>
						@endif
					</div>
					@endif
					<div class="form-group {!! $errors->has('description') ? 'has-error' : '' !!}">
						{!! Form::label('description', trans('app.description')) !!}
						{!! Form::ckeditor('description', '', [ 'class'=>'form-control', 'height' => '200'], $errors) !!}
						@if ($errors->first('description'))
							<span class="description">{!! $errors->first('description') !!}</span>
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