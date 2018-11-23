@extends('backend/layout/layout')
@section('content')
<section class="content-header">
	<h1>{!! trans('app.interest_rate') !!}</h1>
	<ol class="breadcrumb">
		<li><a href="{!! url(getLang() . '/admin/settings/interest-rate') !!}"><i class="fa fa-bookmark"></i> {!! trans('app.interest_rate') !!}</a></li>
		<li class="active">{!! trans('app.add_new') !!}</li>
	</ol>
</section>
<div class="content">
	<div class="box">  
		{!! Form::open(array('action' => '\App\Http\Controllers\Backend\InterestRateController@store')) !!}
		<div class="box-header with-border">
            <h3 class="box-title">{!! trans('app.add_new') !!}</h3>
        </div>
		<div class="box-body">
			<div class="form-group {!! $errors->has('taxo_wallet_asset_id') ? 'has-error' : '' !!}">
				{!! Form::label('taxo_wallet_asset_id', trans('app.product').' *') !!}
				{!!  Form::select('taxo_wallet_asset_id', $taxo_wallet_asset_options, '', [ 'class' => 'form-control' ]) !!}
				@if ($errors->first('taxo_wallet_asset_id'))
					<span class="help-block">{!! $errors->first('taxo_wallet_asset_id') !!}</span>
				@endif
			</div>
			<div class="form-group {!! $errors->has('rate') ? 'has-error' : '' !!}">
				{!! Form::label('rate', '% '.trans('app.rate').' *') !!}
				{!! Form::text('rate', Input::old('rate'), [ 'class' => 'form-control number', 'autocomplete' => 'off' ]) !!}
				@if ($errors->first('rate'))
					<span class="help-block">{!! $errors->first('rate') !!}</span>
				@endif
			</div>
			<div class="form-group {!! $errors->has('bgcolor') ? 'has-error' : '' !!}">
				{!! Form::label('bgcolor', trans('app.background_color').' *') !!}
				{!! Form::text('bgcolor', Input::old('bgcolor'), [ 'class' => 'form-control', 'autocomplete' => 'off' ]) !!}
				@if ($errors->first('bgcolor'))
					<span class="help-block">{!! $errors->first('bgcolor') !!}</span>
				@endif
			</div>
			<div class="form-group {!! $errors->has('bgcolor2') ? 'has-error' : '' !!}">
				{!! Form::label('bgcolor2', trans('app.background_color').' 2 *') !!}
				{!! Form::text('bgcolor2', Input::old('bgcolor2'), [ 'class' => 'form-control', 'autocomplete' => 'off' ]) !!}
				@if ($errors->first('bgcolor2'))
					<span class="help-block">{!! $errors->first('bgcolor2') !!}</span>
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