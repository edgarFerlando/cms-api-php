@extends('backend.layout.layout')
@section('content')
<section class="content-header">
	<h1>{!! trans('app.category_code') !!}</h1>
	<ol class="breadcrumb">
		<li><a href="{!! url(getLang() . '/admin/category/code') !!}"><i class="fa fa-bookmark"></i> {!! trans('app.category_code') !!}</a></li>
		<li class="active">{!! trans('app.add_new') !!}</li>
	</ol>
</section>
<div class="content">
	<div class="box">  
		{!! Form::open(array('action' => '\App\Http\Controllers\Backend\CategoryCodeController@store')) !!}
		<div class="box-header with-border">
            <h3 class="box-title">{!! trans('app.add_new') !!}</h3>
        </div>
		<div class="box-body">
			<div class="form-group {!! $errors->has('category_name') ? 'has-error' : '' !!}">
				{!! Form::label('category_name', trans('app.category_name').' *') !!}
				{!! Form::text('category_name', Input::old('category_name'), [ 'class' => 'form-control', 'autocomplete' => 'off' ]) !!}
				@if ($errors->first('category_name'))
					<span class="help-block">{!! $errors->first('category_name') !!}</span>
				@endif
			</div>
			<div class="form-group {!! $errors->has('keterangan') ? 'has-error' : '' !!}">
				{!! Form::label('keterangan', trans('app.keterangan')) !!}
				{!! Form::text('keterangan', Input::old('keterangan'), [ 'class' => 'form-control', 'autocomplete' => 'off' ]) !!}
				@if ($errors->first('keterangan'))
					<span class="help-block">{!! $errors->first('keterangan') !!}</span>
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
