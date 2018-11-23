@extends('backend.layout.layout')
@section('content')
<section class="content-header">
	<h1>{!! trans('app.cfp_client') !!}</h1>
	<ol class="breadcrumb">
		<li><a href="{!! url(getLang() . '/admin/cfp/client') !!}"><i class="fa fa-bookmark"></i> {!! trans('app.cfp_client') !!}</a></li>
		<li class="active">{!! trans('app.add_new') !!}</li>
	</ol>
</section>
<div class="content">
	<div class="box">  
		{!! Form::open(array('action' => '\App\Http\Controllers\Backend\CfpClientController@store')) !!}
		<div class="box-header with-border">
            <h3 class="box-title">{!! trans('app.add_new') !!}</h3>
        </div>
		<div class="box-body">
			<div class="form-group {!! $errors->has('client_id') ? 'has-error' : '' !!}">
				{!! Form::label('client_id', trans('app.client_name').' *') !!}
				{!! Form::select('client_id', [], '', [ 'class' => 'selectize_clients', 'autocomplete' => 'off', 'old' => Input::old('client_id') ]) !!}
				@if ($errors->first('client_id'))
					<span class="help-block">{!! $errors->first('client_id') !!}</span>
				@endif
			</div>
			<div class="form-group {!! $errors->has('cfp_id') ? 'has-error' : '' !!}">
				{!! Form::label('cfp_id', trans('app.cfp_name').' *') !!}
				{!! Form::select('cfp_id', [], '', [ 'class' => 'selectize_cfps', 'autocomplete' => 'off', 'old' => Input::old('cfp_id') ]) !!}
				@if ($errors->first('cfp_id'))
					<span class="help-block">{!! $errors->first('cfp_id') !!}</span>
				@endif
			</div>
			<div class="form-group {!! $errors->has('notes') ? 'has-error' : '' !!}">
				{!! Form::label('notes', trans('app.notes')) !!}
				{!! Form::textarea('notes', Input::old('notes'), [ 'class' => 'form-control', 'autocomplete' => 'off', 'rows' => 4 ]) !!}
				@if ($errors->first('notes'))
					<span class="help-block">{!! $errors->first('notes') !!}</span>
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
