@extends('backend.layout.layout')
@section('content')
<section class="content-header">
	<h1>{!! trans('app.portofolio_detail') !!}</h1>
	<ol class="breadcrumb">
		<li><a href="{!! url(getLang() . '/admin/detail/portofolio') !!}"><i class="fa fa-bookmark"></i> {!! trans('app.portofolio_detail') !!}</a></li>
		<li class="active">{!! trans('app.add_new') !!}</li>
	</ol>
</section>
<div class="content">
	<div class="box">  
		{!! Form::open(array('action' => '\App\Http\Controllers\Backend\PortofolioDetailController@store')) !!}
		<div class="box-header with-border">
            <h3 class="box-title">{!! trans('app.add_new') !!}</h3>
        </div>
		<div class="box-body">
			<div class="form-group {!! $errors->has('detail_name') ? 'has-error' : '' !!}">
				{!! Form::label('detail_name', trans('app.detail_name').' *') !!}
				{!! Form::text('detail_name', Input::old('detail_name'), [ 'class' => 'form-control', 'autocomplete' => 'off' ]) !!}
				@if ($errors->first('detail_name'))
					<span class="help-block">{!! $errors->first('detail_name') !!}</span>
				@endif
			</div>
			<div class="form-group {!! $errors->has('portofolio_id') ? 'has-error' : '' !!}">
				{!! Form::label('portofolio_name', trans('app.portofolio_name').' *') !!}
				{!! Form::select('portofolio_id', $portofolios, Input::old('portofolio_id'), [ 'class' => 'selectize', 'autocomplete' => 'off' ]) !!}
				@if ($errors->first('portofolio_id'))
					<span class="help-block">{!! $errors->first('portofolio_id') !!}</span>
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
