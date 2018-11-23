@extends('backend.layout.layout')
@section('content')
<section class="content-header">
	<h1>{!! trans('app.reference') !!}</h1>
	<ol class="breadcrumb">
		<li><a href="{!! url(getLang() . '/admin/reference') !!}"><i class="fa fa-bookmark"></i> {!! trans('app.reference') !!}</a></li>
		<li class="active">{!! trans('app.add_new') !!}</li>
	</ol>
</section>
<div class="content">
	<div class="box">  
		{!! Form::open(array('action' => '\App\Http\Controllers\Backend\ReferenceController@store')) !!}
		<div class="box-header with-border">
            <h3 class="box-title">{!! trans('app.add_new') !!}</h3>
        </div>
		<div class="box-body">
			<div class="form-group {!! $errors->has('code') ? 'has-error' : '' !!}">
				{!! Form::label('code', trans('app.code').' *') !!}
				{!! Form::text('code', Input::old('code'), [ 'class' => 'form-control', 'autocomplete' => 'off' ]) !!}
				@if ($errors->first('code'))
					<span class="help-block">{!! $errors->first('code') !!}</span>
				@endif
			</div>
			<div class="form-group {!! $errors->has('name') ? 'has-error' : '' !!}">
				{!! Form::label('name', trans('app.name').' *') !!}
				{!! Form::text('name', Input::old('name'), [ 'class' => 'form-control', 'autocomplete' => 'off' ]) !!}
				@if ($errors->first('name'))
					<span class="help-block">{!! $errors->first('name') !!}</span>
				@endif
			</div>
			<div class="form-group {!! $errors->has('company') ? 'has-error' : '' !!}">
				{!! Form::label('company', trans('app.company')) !!}
				{!! Form::text('company', Input::old('company'), [ 'class' => 'form-control', 'autocomplete' => 'off' ]) !!}
				@if ($errors->first('company'))
					<span class="help-block">{!! $errors->first('company') !!}</span>
				@endif
			</div>
			<div class="form-group {!! $errors->has('email') ? 'has-error' : '' !!}">
				{!! Form::label('email', trans('app.email').' *') !!}
				{!! Form::text('email', Input::old('email'), [ 'class' => 'form-control', 'autocomplete' => 'off' ]) !!}
				@if ($errors->first('email'))
					<span class="help-block">{!! $errors->first('email') !!}</span>
				@endif
			</div>
			<div class="form-group {!! $errors->has('phone') ? 'has-error' : '' !!}">
				{!! Form::label('phone', trans('app.phone')) !!}
			{!! Form::text('phone', Input::old('phone'), [ 'class' => 'form-control', 'autocomplete' => 'off' ]) !!}
				@if ($errors->first('phone'))
					<span class="help-block">{!! $errors->first('phone') !!}</span>
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
