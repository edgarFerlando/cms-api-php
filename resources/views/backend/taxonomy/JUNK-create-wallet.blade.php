@extends('backend.layout.layout')
@section('content')
<section class="content-header">
	<h1>{!! trans('app.'.$post_type.'_taxonomy') !!}</h1>
	<ol class="breadcrumb">
		<li><a href="{!! url(getLang() . '/admin/taxonomy/'.$post_type) !!}"><i class="fa fa-bookmark"></i> {!! trans('app.'.$post_type.'_taxonomy') !!}</a></li>
		<li class="active">{!! trans('app.add_new') !!}</li>
	</ol>
</section>
<div class="content">
	<div class="box">  
		{!! Form::open(array('action' => '\App\Http\Controllers\Backend\TaxonomyController@store')) !!}
		{!! Form::hidden('post_type', $post_type) !!}
		<div class="box-header with-border">
            <h3 class="box-title">{!! trans('app.add_new') !!}</h3>
        </div>
		<div class="box-body">
			<div class="form-group {!! $errors->has('title') ? 'has-error' : '' !!}">
				{!! Form::label('title', trans('app.title').' *') !!}
				{!! Form::text('title', Input::old('title'), [ 'class' => 'form-control slug-source', 'autocomplete' => 'off' ]) !!}
				@if ($errors->first('title'))
					<span class="help-block">{!! $errors->first('title') !!}</span>
				@endif
			</div>
			<div class="form-group {!! $errors->has('slug') ? 'has-error' : '' !!}">
				{!! Form::label('slug', trans('app.slug').' *') !!}
				{!! Form::text('slug', Input::old('slug'), [ 'class' => 'form-control slug', 'autocomplete' => 'off' ]) !!}
				@if ($errors->first('slug'))
					<span class="help-block">{!! $errors->first('slug') !!}</span>
				@endif
			</div>
			<div class="form-group {!! $errors->has('image') ? 'has-error' : '' !!}">
				{!! Form::label('image', trans('app.image').' *') !!}
				{!! 
					Form::cke_image('image', Input::old('image'), [ 'class'=>'form-control', 'height' => 150 ])
				!!}
				@if ($errors->first('image'))
					<span class="help-block">{!! $errors->first('image') !!}</span>
				@endif
			</div>
			<div class="form-group {!! $errors->has('budget') ? 'has-error' : '' !!}">
				{!! Form::label('budget', trans('app.budget').' *') !!}
				{!! Form::text('budget', Input::old('budget'), [ 'class' => 'form-control number', 'autocomplete' => 'off' ]) !!}
				@if ($errors->first('budget'))
					<span class="help-block">{!! $errors->first('budget') !!}</span>
				@endif
			</div>
			<div class="form-group">
				{!! Form::label('parent', trans('app.hierarchy')) !!}
				{!!  Form::select('parent', $parent_options, '', [ 'class' => 'selectize' ]) !!}
			</div>
			
		</div>
		<div class="box-footer">
			{!! Form::submit(trans('app.save'), array('class' => 'btn btn-success')) !!}
		</div>
		{!! Form::close() !!}
		</div>
</div>
@stop
