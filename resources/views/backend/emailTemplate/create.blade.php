@extends('backend/layout/layout')
@section('content')
<section class="content-header">
	<h1>{!! trans('app.email_template') !!}</h1>
	<ol class="breadcrumb">
		<li><a href="{!! url(getLang() . '/admin/settings/email-template') !!}"><i class="fa fa-bookmark"></i> {!! trans('app.email_template') !!}</a></li>
		<li class="active">{!! trans('app.add_new') !!}</li>
	</ol>
</section>
<div class="content">
	<div class="box">  
		{!! Form::open(array('action' => '\App\Http\Controllers\Backend\EmailTemplateController@store')) !!}
		{!! Form::hidden('emailTemplateModules', $emailTemplateModules) !!}
		<div class="box-header with-border">
            <h3 class="box-title">{!! trans('app.add_new') !!}</h3>
        </div>
		<div class="box-body">
			<div class="form-group {!! $errors->has('email_template_module') ? 'has-error' : '' !!}">
				{!! Form::label('email_template_module', trans('app.module').' *') !!}
				{!!  Form::select('email_template_module', $emailTemplateModule_options, '', [ 'class' => 'form-control' ]) !!}
				@if ($errors->first('email_template_module'))
					<span class="help-block">{!! $errors->first('email_template_module') !!}</span>
				@endif
			</div>
			<div class="form-group {!! $errors->has('subject') ? 'has-error' : '' !!}">
				{!! Form::label('subject', trans('app.subject').' *') !!}
				{!! 
					Form::text('subject', Input::old('subject'), [
						'class'			=> 'form-control', 
						'autocomplete' 	=> 'off', 
					], $errors) 
				!!}
			</div>
			<div class="form-group {!! $errors->has('body') ? 'has-error' : '' !!}">
				{!! Form::label('body', trans('app.content').' *') !!}
				{!! 
					Form::textarea('body', Input::old('body'), [
							'class'=>'form-control', 
						], $errors) 
				!!}
				<ul class="list-unstyled">
		            <li><strong>Available variables :</strong></li>
		            <div class="available_variables"></div>
		        </ul>
			</div>
		</div>
		<div class="box-footer">
			{!! Form::submit(trans('app.save'), array('class' => 'btn btn-success')) !!}
		</div>
		{!! Form::close() !!}
		</div>
</div>
@stop