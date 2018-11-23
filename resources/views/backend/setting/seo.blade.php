@extends('backend.layout.layout')
@section('content')
<section class="content-header">
	<h1>{!! trans('app.seo_settings') !!}</h1>
	<ol class="breadcrumb">
		<li><a href="{!! url(getLang() . '/admin/settings') !!}"><i class="fa fa-bookmark"></i> {!! trans('app.settings') !!}</a></li>
		<li class="active">{!! trans('app.seo') !!}</li>
	</ol>
</section>
<div class="content">
	<div class="box">  
		{!! Form::open(array('action' => '\App\Http\Controllers\Backend\SettingController@seoStore')) !!}
		<div class="box-header with-border">
            <h3 class="box-title">{!! trans('app.edit') !!}</h3>
        </div>
		<div class="box-body">
			<div class="form-group {!! lang_errors_has($errors, 'default_meta_keywords') ? 'has-error' : '' !!}">
                {!! Form::label('default_meta_keywords', trans('app.default_meta_keywords').' *') !!}
                {!! 
                    Form::lang_text('default_meta_keywords', lang_val_config_db('settings', 'default_meta_keywords'), [ 'class'=>'form-control'], $errors) 
                !!}
            </div>
            <div class="form-group {!! lang_errors_has($errors, 'default_meta_description') ? 'has-error' : '' !!}">
                {!! Form::label('default_meta_description', trans('app.default_meta_description').' *') !!}
                {!! 
                    Form::lang_textarea('default_meta_description', lang_val_config_db('settings', 'default_meta_description'), [ 'class'=>'form-control', 'rows' => 5 ], $errors) 
                !!}
            </div>
		</div>
		<div class="box-footer">
			{!! Form::submit(trans('app.save'), array('class' => 'btn btn-success')) !!}
		</div>
		{!! Form::close() !!}
		</div>
</div>
@stop
