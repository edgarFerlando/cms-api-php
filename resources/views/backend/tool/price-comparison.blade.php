@extends('backend.layout.layout')
@section('content')
<section class="content-header">
	<h1>{!! trans('app.price_comparison') !!}</h1>
	<ol class="breadcrumb">
		<li><a href="{!! url(getLang() . '/admin/tools') !!}"><i class="fa fa-bookmark"></i> {!! trans('app.tools') !!}</a></li>
		<li class="active">{!! trans('app.price_comparison') !!}</li>
	</ol>
</section>
<div class="content">
	<div class="box">  
		<div class="box-body">
			<div class="row filter-form">
				{!! Form::open([ 'url' => '#', 'class' => 'do-price-comparison' ]) !!}
		        <div class="col-xs-10">
		          <div class="form-group">
		            {!! Form::label('hotel_name', trans('app.hotel_name')) !!}
		            {!! Form::text('hotel_name', Input::get('hotel_name'), [ 'class'  => 'form-control'], $errors) !!}
		          </div>
		        </div>
		        <div class="col-xs-2">
		          <div class="form-group action-tool">
		            {!! Form::submit(trans('app.check'), array('class' => 'btn btn-success')) !!}
		          </div>
		        </div>
		        {!! Form::close() !!}
	      	</div>
	      	<div class="row">
	      		<div class="col-xs-12">
		        	<ul id="agoda-hotel-lists"></ul>
		        </div>
	      		<!--<div class="col-xs-12" id="siteloader">
		        	
		        </div>
		        <div class="col-xs-12">
		        	<iframe id="agoda" src="https://www.agoda.com" width="100%" height="300"></iframe>
		        </div>
		        <div class="col-xs-12" id="misteraladin">
		        	<iframe id="load" src="https://www.misteraladin.com/hotel" width="100%" height="300"></iframe> 
		        </div>-->
	      	</div>
		</div>
	</div>
</div>
@stop
