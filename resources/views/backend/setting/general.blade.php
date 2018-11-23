@extends('backend.layout.layout')
@section('content')
<section class="content-header">
	<h1>{!! trans('app.general_settings') !!}</h1>
	<ol class="breadcrumb">
		<li><a href="{!! url(getLang() . '/admin/settings') !!}"><i class="fa fa-bookmark"></i> {!! trans('app.settings') !!}</a></li>
		<li class="active">{!! trans('app.general') !!}</li>
	</ol>
</section>
<div class="content">
	<div class="box">  
		{!! Form::open(array('action' => '\App\Http\Controllers\Backend\SettingController@generalStore')) !!}
		<div class="box-header with-border">
            <h3 class="box-title">{!! trans('app.edit') !!}</h3>
        </div>
		<div class="box-body">
			<div class="form-group {!! $errors->has('site_title') ? 'has-error' : '' !!}">
				{!! Form::label('site_title', trans('app.site_title').' *') !!}
				{!! Form::cke_min('site_title', config_db_cached('settings::site_title'), [ 'class' => 'form-control', 'autocomplete' => 'off', 'height' => 50 ]) !!}
				@if ($errors->first('site_title'))
					<span class="help-block">{!! $errors->first('site_title') !!}</span>
				@endif
			</div>
			<h4>Authentication</h4>

			<div class="form-group {!! $errors->has('default_role_id_client') ? 'has-error' : '' !!}">
				{!! Form::label('default_role_id_client', trans('app.default_role_client').' *') !!}
				{!!  Form::select('default_role_id_client', $role_options, config_db_cached('settings::default_role_id_client'), [ 'class' => 'selectize' ]) !!}
				@if ($errors->first('default_role_id_client'))
					<span class="help-block">{!! $errors->first('default_role_id_client') !!}</span>
				@endif
			</div>
			<div class="form-group {!! $errors->has('default_role_id_cfp') ? 'has-error' : '' !!}">
				{!! Form::label('default_role_id_cfp', trans('app.default_role_cfp').' *') !!}
				{!!  Form::select('default_role_id_cfp', $role_options, config_db_cached('settings::default_role_id_cfp'), [ 'class' => 'selectize' ]) !!}
				@if ($errors->first('default_role_id_cfp'))
					<span class="help-block">{!! $errors->first('default_role_id_cfp') !!}</span>
				@endif
			</div>
			<!-- 
			<div class="form-group {!! $errors->has('new_user_default_role') ? 'has-error' : '' !!}">
				{!! Form::label('new_user_default_role', trans('app.new_user_default_role')) !!}
				{!! Form::select('new_user_default_role', $role_options, config_db_cached('settings::new_user_default_role'), [ 'class' => 'selectize', 'autocomplete' => 'off' ]) !!}
				@if ($errors->first('new_user_default_role'))
					<span class="help-block">{!! $errors->first('new_user_default_role') !!}</span>
				@endif
			</div> -->
		</div>
		<div class="box-footer">
			{!! Form::submit(trans('app.save'), array('class' => 'btn btn-success')) !!}
		</div>
		{!! Form::close() !!}
		</div>
</div>
@stop
