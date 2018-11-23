@extends('backend.layout.layout')
@section('content')
<section class="content-header">
    <h1> {{{ trans('app.triangle') }}}</h1>
    <ol class="breadcrumb">
        <li><a href="{!! url(getLang() . '/admin/settings/plan-analysis/triangle') !!}"><i class="fa fa-bookmark"></i> {{{ trans('app.triangle') }}}</a></li>
        <li class="active">{{{ trans('app.edit') }}}</li>
    </ol>
</section>
<div class="content">
    <div class="box">
        {!! Form::open( array( 'route' => array( getLang() . '.admin.settings.plan-analysis.triangle.update', $data->id), 'method' => 'PATCH', 'files'=>true)) !!}
        {!! Form::hidden('id', $data->id) !!}
        <div class="box-header with-border">
            <h3 class="box-title">{!! trans('app.edit') !!}</h3>
        </div>
        <div class="box-body">
            <div class="form-group {!! $errors->has('triangle_layer_id') ? 'has-error' : '' !!}">
				{!! Form::label('triangle_layer_id', trans('app.triangle_layer').' *') !!}
				{!!  Form::select('triangle_layer_id', $layer_opts, val($data, 'triangle_layer_id'), [ 'class' => 'selectize' ]) !!}
				@if ($errors->first('triangle_layer_id'))
					<span class="help-block">{!! $errors->first('triangle_layer_id') !!}</span>
				@endif
			</div>
			<div class="form-group {!! $errors->has('step_1') ? 'has-error' : '' !!}">
				{!! Form::label('step_1', trans('app.step_1').' *') !!}
				{!!  Form::select('step_1', $step_1_opts, Input::get('step_1'), [ 'class' => 'selectize_triangle_step_1', 'old' => val($data, 'step_1') ]) !!}
				@if ($errors->first('step_1'))
					<span class="help-block">{!! $errors->first('step_1') !!}</span>
				@endif
			</div>
			<div class="form-group {!! $errors->has('step_2') ? 'has-error' : '' !!}">
				{!! Form::label('step_2', trans('app.step_2').' *') !!}
				{!! Form::select('step_2', [], '', [ 'class' => 'selectize_triangle_step_2', 'autocomplete' => 'off', 'old' => val($data, 'step_2') ]) !!}
				@if ($errors->first('step_2'))
					<span class="help-block">{!! $errors->first('step_2') !!}</span>
				@endif
			</div>
			<div class="form-group {!! $errors->has('step_3') ? 'has-error' : '' !!}">
				{!! Form::label('step_3', trans('app.step_3')) !!}
				{!! Form::select('step_3', [], '', [ 'class' => 'selectize_triangle_step_3', 'autocomplete' => 'off', 'old' => val($data, 'step_3') ]) !!}
				@if ($errors->first('step_3'))
					<span class="help-block">{!! $errors->first('step_3') !!}</span>
				@endif
			</div>
        </div>
        <div class="box-footer">
            {!! Form::submit(trans('app.save'), array('class' => 'btn btn-success')) !!}&nbsp;
            {!! link_to( langRoute('admin.settings.plan-analysis.triangle.index'), trans('app.cancel'), array( 'class' => 'btn btn-primary' ) ) !!}
        </div>
        {!! Form::close() !!}
    </div>
</div>
@stop
