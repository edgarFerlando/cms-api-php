@extends('backend.layout.layout')
@section('content')
<section class="content-header">
	<h1>{!! trans('app.permission') !!}</h1>
	<ol class="breadcrumb">
		<li><a href="{!! url(getLang() . '/admin/user') !!}"><i class="fa fa-bookmark"></i> {!! trans('app.permission') !!}</a></li>
		<li class="active">{!! trans('app.add_new') !!}</li>
	</ol>
</section>
<div class="content">
	<div class="box">  
		{!! Form::open(array('action' => '\App\Http\Controllers\Backend\PermissionController@store')) !!}
		<div class="box-header with-border">
            <h3 class="box-title">{!! trans('app.add_new') !!}</h3>
        </div>
		<div class="box-body">
			<div class="form-group {!! $errors->has('module') ? 'has-error' : '' !!}">
				{!! Form::label('module', trans('app.module').' *') !!}
				{!! Form::text('module', Input::old('module'), [ 'class' => 'form-control', 'autocomplete' => 'off' ]) !!}
				<span class="help-block info">e.g. Wallet</span>
				@if ($errors->first('module'))
					<span class="help-block">{!! $errors->first('module') !!}</span>
				@endif
			</div>
			<div class="form-group {!! $errors->has('name') ? 'has-error' : '' !!}">
				{!! Form::label('name', trans('app.name').' *') !!}
				{!! Form::text('name', Input::old('name'), [ 'class' => 'form-control', 'autocomplete' => 'off' ]) !!}
				<span class="help-block info">e.g. delete_wallet_transaction</span>
				@if ($errors->first('name'))
					<span class="help-block">{!! $errors->first('name') !!}</span>
				@endif
			</div>
			<div class="form-group {!! $errors->has('display_name') ? 'has-error' : '' !!}">
				{!! Form::label('display_name', trans('app.display_name').' *') !!}
				{!! Form::text('display_name', Input::old('display_name'), [ 'class' => 'form-control', 'autocomplete' => 'off' ]) !!}
				<span class="help-block info">e.g. Delete Transaction</span>
				@if ($errors->first('display_name'))
					<span class="help-block">{!! $errors->first('display_name') !!}</span>
				@endif
			</div>
			<div class="form-group {!! $errors->has('description') ? 'has-error' : '' !!}">
				{!! Form::label('description', trans('app.description')) !!}
				{!! Form::text('description', Input::old('description'), [ 'class' => 'form-control', 'autocomplete' => 'off' ]) !!}
				@if ($errors->first('desciption'))
					<span class="help-block">{!! $errors->first('desciption') !!}</span>
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
