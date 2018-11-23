@extends('backend.layout.layout')
@section('content')
<section class="content-header">
	<h1>{!! trans('app.reading_settings') !!}</h1>
	<ol class="breadcrumb">
		<li><a href="{!! url(getLang() . '/admin/settings') !!}"><i class="fa fa-bookmark"></i> {!! trans('app.settings') !!}</a></li>
		<li class="active">{!! trans('app.reading') !!}</li>
	</ol>
</section>
<div class="content">
	<div class="box">  
		{!! Form::open(array('action' => '\App\Http\Controllers\Backend\SettingController@readingStore')) !!}
		<div class="box-header with-border">
            <h3 class="box-title">{!! trans('app.reading') !!}</h3>
        </div>
		<div class="box-body">
			<div class="form-group {!! $errors->has('per_page') ? 'has-error' : '' !!}">
				{!! Form::label('per_page', trans('app.per_page').' *') !!}
				{!! Form::text('per_page', config_db_cached('settings::per_page'), [ 'class' => 'form-control', 'autocomplete' => 'off' ]) !!}
				@if ($errors->first('per_page'))
					<span class="help-block">{!! $errors->first('per_page') !!}</span>
				@endif
			</div>
			<div class="form-group {!! $errors->has('backend_per_page') ? 'has-error' : '' !!}">
				{!! Form::label('backend_per_page', trans('app.backend_per_page').' *') !!}
				{!! Form::text('backend_per_page', config_db_cached('settings::backend_per_page'), [ 'class' => 'form-control', 'autocomplete' => 'off']) !!}
				@if ($errors->first('backend_per_page'))
					<span class="help-block">{!! $errors->first('backend_per_page') !!}</span>
				@endif
			</div>
			<!-- <br />
			<h4>{!! trans('app.product') !!}</h4>
			<div class="form-group {!! $errors->has('scroller_newest_products') ? 'has-error' : '' !!}">
				{!! Form::label('scroller_newest_products', trans('app.scroller_newest_products').' *') !!}
				{!! Form::text('scroller_newest_products', config_db_cached('settings::scroller_newest_products'), [ 'class' => 'form-control', 'autocomplete' => 'off']) !!}
				@if ($errors->first('scroller_newest_products'))
					<span class="help-block">{!! $errors->first('scroller_newest_products') !!}</span>
				@endif
			</div>
			<div class="form-group {!! $errors->has('scroller_last_shipping_products') ? 'has-error' : '' !!}">
				{!! Form::label('scroller_last_shipping_products', trans('app.scroller_last_shipping_products').' *') !!}
				{!! Form::text('scroller_last_shipping_products', config_db_cached('settings::scroller_last_shipping_products'), [ 'class' => 'form-control', 'autocomplete' => 'off']) !!}
				@if ($errors->first('scroller_last_shipping_products'))
					<span class="help-block">{!! $errors->first('scroller_last_shipping_products') !!}</span>
				@endif
			</div>
			<div class="row">
				<div class="col-lg-6">
					<div class="form-group {!! $errors->has('scroller_best_seller_products') ? 'has-error' : '' !!}">
						{!! Form::label('scroller_best_seller_products', trans('app.scroller_best_seller_products').' *') !!}
						{!! Form::text('scroller_best_seller_products', config_db_cached('settings::scroller_best_seller_products'), [ 'class' => 'form-control', 'autocomplete' => 'off']) !!}
						@if ($errors->first('scroller_best_seller_products'))
							<span class="help-block">{!! $errors->first('scroller_best_seller_products') !!}</span>
						@endif
					</div>
				</div>
				<div class="col-lg-6">
					<div class="form-group {!! $errors->has('scroller_best_seller_products_period') ? 'has-error' : '' !!}">
						{!! Form::label('scroller_best_seller_products_period', trans('app.scroller_best_seller_products_period').' * [ '.trans('app.n_days_ago').' ]') !!}
						{!! Form::text('scroller_best_seller_products_period', config_db_cached('settings::scroller_best_seller_products_period'), [ 'class' => 'form-control', 'autocomplete' => 'off']) !!}
						@if ($errors->first('scroller_best_seller_products_period'))
							<span class="help-block">{!! $errors->first('scroller_best_seller_products_period') !!}</span>
						@endif
					</div>
				</div>
			</div>-->
		</div>
		<div class="box-footer">
			{!! Form::submit(trans('app.save'), array('class' => 'btn btn-success')) !!}
		</div>
		{!! Form::close() !!}
		</div>
</div>
@stop
