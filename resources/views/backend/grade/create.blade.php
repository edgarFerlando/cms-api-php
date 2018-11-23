@extends('backend.layout.layout')
@section('content')
<section class="content-header">
	<h1>{!! trans('app.grade') !!}</h1>
	<ol class="breadcrumb">
		<li><a href="{!! url(getLang() . '/admin/grade') !!}"><i class="fa fa-bookmark"></i> {!! trans('app.grade') !!}</a></li>
		<li class="active">{!! trans('app.add_new') !!}</li>
	</ol>
</section>
<div class="content">
	<div class="box">  
		{!! Form::open(array('action' => '\App\Http\Controllers\Backend\GradeController@store')) !!}
		<div class="box-header with-border">
            <h3 class="box-title">{!! trans('app.add_new') !!}</h3>
        </div>
		<div class="box-body">
			<div class="form-group {!! $errors->has('grade_name') ? 'has-error' : '' !!}">
				{!! Form::label('grade_name', trans('app.grade_name').' *') !!}
				{!! Form::text('grade_name', Input::old('grade_name'), [ 'class' => 'form-control', 'autocomplete' => 'off' ]) !!}
				@if ($errors->first('grade_name'))
					<span class="help-block">{!! $errors->first('grade_name') !!}</span>
				@endif
			</div>
			<div class="form-group {!! $errors->has('grade_ages') ? 'has-error' : '' !!}">
				{!! Form::label('grade_ages', trans('app.grade_ages').' *') !!}
				{!! Form::text('grade_ages', Input::old('grade_ages'), [ 'class' => 'form-control', 'autocomplete' => 'off' ]) !!}
				@if ($errors->first('grade_ages'))
					<span class="help-block">{!! $errors->first('grade_ages') !!}</span>
				@endif
			</div>
			<div class="form-group {!! $errors->has('grade_thumb') ? 'has-error' : '' !!}">
				{!! Form::label('grade_thumb', trans('app.grade_thumb').' *') !!}
				{!! Form::cke_image('grade_thumb', old_input_img_singlelang(Input::old('grade_thumb')) , [ 'class'=>'is_cke']) !!}
				@if ($errors->first('grade_thumb'))
					<span class="help-block">{!! $errors->first('grade_thumb') !!}</span>
				@endif
			</div>
			<div class="form-group {!! $errors->has('grade_button_label') ? 'has-error' : '' !!}">
				{!! Form::label('grade_button_label', trans('app.grade_button_label').' *') !!}
				{!! Form::text('grade_button_label', Input::old('grade_button_label'), [ 'class' => 'form-control', 'autocomplete' => 'off' ]) !!}
				@if ($errors->first('grade_button_label'))
					<span class="help-block">{!! $errors->first('grade_button_label') !!}</span>
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
