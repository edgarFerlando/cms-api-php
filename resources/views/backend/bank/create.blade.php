@extends('backend.layout.layout')
@section('content')
<section class="content-header">
	<h1>{!! trans('app.bank') !!}</h1>
	<ol class="breadcrumb">
		<li><a href="{!! url(getLang() . '/admin/bank') !!}"><i class="fa fa-bookmark"></i> {!! trans('app.bank') !!}</a></li>
		<li class="active">{!! trans('app.add_new') !!}</li>
	</ol>
</section>

<div class="content">
	<div class="box">  
		{!! Form::open(array('action' => '\App\Http\Controllers\Backend\BankController@store')) !!}
		<div class="box-header with-border">
            <h3 class="box-title">{!! trans('app.add_new') !!}</h3>
        </div>
		<div class="box-body">
			<div class="form-group {!! $errors->has('title') ? 'has-error' : '' !!}">
				{!! Form::label('title', trans('app.title').' *') !!}
				{!! 
					Form::text('title', Input::old('title'), [
						'class'			=> 'form-control', 
						'autocomplete' 	=> 'off', 
					]) 
				!!}
				@if ($errors->first('title'))
					<span class="help-block">{!! $errors->first('title') !!}</span>
				@endif
			</div>
			<div class="form-group {!! $errors->has('slug') ? 'has-error' : '' !!}">
				{!! Form::label('slug', trans('app.slug').' *') !!}
				{!! 
					Form::text('slug', Input::old('slug'), [
							'class'=>'form-control slug', 
							'autocomplete' => 'off',
						]) 
				!!}
				@if ($errors->first('slug'))
					<span class="help-block">{!! $errors->first('slug') !!}</span>
				@endif
            </div>
            <div class="form-group {!! $errors->has('featured_image') ? 'has-error' : '' !!}">
				{!! Form::label('featured_image', trans('app.featured_image')) !!}
				{!! 
					Form::cke_image('featured_image', Input::old('featured_image'), [ 'class'=>'form-control', 'height' => 200 ])
				!!}
				@if ($errors->first('featured_image'))
					<span class="help-block">{!! $errors->first('featured_image') !!}</span>
				@endif
			</div>
			<div class="form-group {!! $errors->has('color') ? 'has-error' : '' !!}">
				{!! Form::label('color', trans('app.color').' *') !!}
				{!! Form::input('color','color[0]',null, array('class' => 'form-control color','placeholder' => 'Enter Color','id' => 'color')) !!}
				@if ($errors->first('color'))
					<span class="help-block">{!! $errors->first('color') !!}</span>
				@endif
			</div>
            <div class="form-group {!! $errors->has('is_status') ? 'has-error' : '' !!}">
                {!! Form::label('is_status', trans('app.is_status').' *') !!}
                {!! Form::select('is_status', ['Belum Ditampilkan', 'Tampilkan'], 0, ['class' => 'selectize_clients']) !!}
                @if ($errors->first('is_status'))
                    <span class="help-block">{!! $errors->first('is_status') !!}</span>
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