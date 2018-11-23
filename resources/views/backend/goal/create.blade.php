@extends('backend.layout.layout')
@section('content')
<section class="content-header">
	<h1>{!! trans('app.goal') !!}</h1>
	<ol class="breadcrumb">
		<li><a href="{!! url(getLang() . '/admin/goal') !!}"><i class="fa fa-bookmark"></i> {!! trans('app.goal') !!}</a></li>
		<li class="active">{!! trans('app.add_new') !!}</li>
	</ol>
</section>
<div class="content">
	<div class="box">  
		{!! Form::open(array('action' => '\App\Http\Controllers\Backend\GoalController@store')) !!}
		<div class="box-header with-border">
            <h3 class="box-title">{!! trans('app.add_new') !!}</h3>
        </div>
		<div class="box-body">
			<div class="form-group {!! $errors->has('goal_name') ? 'has-error' : '' !!}">
				{!! Form::label('goal_name', trans('app.goal_name').' *') !!}
				{!! Form::text('goal_name', Input::old('goal_name'), [ 'class' => 'form-control', 'autocomplete' => 'off' ]) !!}
				@if ($errors->first('goal_name'))
					<span class="help-block">{!! $errors->first('goal_name') !!}</span>
				@endif
			</div>
			<div class="form-group {!! $errors->has('goal_icon') ? 'has-error' : '' !!}">
				{!! Form::label('goal_icon', trans('app.goal_icon').' *') !!}
				{!! Form::cke_image('goal_icon', old_input_img_singlelang(Input::old('goal_icon')) , [ 'class'=>'is_cke']) !!}
				@if ($errors->first('goal_icon'))
					<span class="help-block">{!! $errors->first('goal_icon') !!}</span>
				@endif
			</div>
			<div class="form-group {!! $errors->has('goal_thumb') ? 'has-error' : '' !!}">
				{!! Form::label('goal_thumb', trans('app.goal_thumb').' *') !!}
				{!! Form::cke_image('goal_thumb', old_input_img_singlelang(Input::old('goal_thumb')) , [ 'class'=>'is_cke']) !!}
				@if ($errors->first('goal_thumb'))
					<span class="help-block">{!! $errors->first('goal_thumb') !!}</span>
				@endif
			</div>
			<div class="form-group {!! $errors->has('goal_position_under_grade') ? 'has-error' : '' !!}">
				{!! Form::label('goal_position_under_grade', trans('app.goal_position_under_grade').' *') !!}
				{!!  Form::select('goal_position_under_grade', $grade_options, '', [ 'class' => 'form-control' ]) !!}
				@if ($errors->first('goal_position_under_grade'))
					<span class="help-block">{!! $errors->first('goal_position_under_grade') !!}</span>
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
