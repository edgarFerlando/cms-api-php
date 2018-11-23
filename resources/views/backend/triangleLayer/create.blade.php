@extends('backend.layout.layout')
@section('content')
<section class="content-header">
	<h1>{!! trans('app.triangle_layer') !!}</h1>
	<ol class="breadcrumb">
		<li><a href="{!! url(getLang() . '/admin/settings/plan-analysis/triangle_layer') !!}"><i class="fa fa-bookmark"></i> {!! trans('app.triangle_layer') !!}</a></li>
		<li class="active">{!! trans('app.add_layer') !!}</li>
	</ol>
</section>
<div class="content">
	<div class="box">  
		{!! Form::open(array('action' => '\App\Http\Controllers\Backend\TriangleLayerController@store')) !!}
		<div class="box-header with-border">
            <h3 class="box-title">{!! trans('app.add_layer') !!}</h3>
        </div>
		<div class="box-body">
			<div class="form-group {!! $errors->has('title') ? 'has-error' : '' !!}">
				{!! Form::label('title', trans('app.title').' *') !!}
				{!! Form::text('title', Input::old('title'), [ 'class' => 'form-control slug-source', 'autocomplete' => 'off' ]) !!}
				@if ($errors->first('title'))
					<span class="help-block">{!! $errors->first('title') !!}</span>
				@endif
			</div>
			<div class="form-group {!! $errors->has('description') ? 'has-error' : '' !!}">
				{!! Form::label('description', trans('app.description').' *') !!}
				{!! Form::textarea('description', Input::old('description'), [ 'class' => 'form-control', 'autocomplete' => 'off', 'rows' => 4 ]) !!}
				@if ($errors->first('description'))
					<span class="help-block">{!! $errors->first('description') !!}</span>
				@endif
			</div>
			<div class="form-group {!! $errors->has('stack_number') ? 'has-error' : '' !!}">
				{!! Form::label('stack_number', trans('app.stack_number')) !!}
				{!! Form::selectRange('stack_number', 1,10, '', [ 'class' => 'selectize', 'autocomplete' => 'off' ]) !!}
				@if ($errors->first('stack_number'))
					<span class="help-block">{!! $errors->first('stack_number') !!}</span>
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
