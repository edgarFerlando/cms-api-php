@extends('backend.layout.layout')
@section('content')
<section class="content-header">
	<h1>{!! trans('app.email_template_mapping') !!}</h1>
	<ol class="breadcrumb">
		<li><a href="{!! url(getLang() . '/admin/contact-us') !!}"><i class="fa fa-bookmark"></i> {!! trans('app.email_template_mapping') !!}</a></li>
		<li class="active">{!! trans('app.edit') !!}</li>
	</ol>
</section>
<div class="content">
	{!! Notification::showAll() !!}
	<div class="box">  
		{!! Form::open(array('action' => '\App\Http\Controllers\Backend\EmailTemplateMappingController@store')) !!}
		<div class="box-body">
			@foreach($modules as $module)
				<h4>{!! $module->name  !!}</h4>
					<hr />
				<div class="form-group {!! lang_errors_has($errors, 'cc') ? 'has-error' : '' !!}">
					{!! Form::label('cc', 'bcc') !!}
					{!! 
						Form::text('cc['.$module->id.']', isset($module_template[$module->id]['cc'])?$module_template[$module->id]['cc']:'', [
							'class'			=> 'form-control', 
							'autocomplete' 	=> 'off', 
						], $errors) 
					!!}
					<span class="help-block">{!! trans('app.separate_with_commas') !!}</span>
				</div>
				<div class="form-group {!! $errors->has('email_template') ? 'has-error' : '' !!}">
					{!! Form::label('email_template', trans('app.email_template')) !!}
					{!!  Form::select('email_template['.$module->id.']', buildEmailTemplateLists($module->emailTemplates), isset($module_template[$module->id]['email_template_id'])?$module_template[$module->id]['email_template_id']:'', [ 'class' => 'form-control' ]) !!}

					@if ($errors->first('email_template'))
						<span class="help-block">{!! $errors->first('email_template') !!}</span>
					@endif
				</div>
				<br />
			@endforeach
		</div>
		<div class="box-footer">
			{!! Form::submit(trans('app.save'), array('class' => 'btn btn-success')) !!}
		</div>
		{!! Form::close() !!}
		</div>
</div>
@stop
