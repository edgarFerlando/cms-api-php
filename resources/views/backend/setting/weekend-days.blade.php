@extends('backend.layout.layout')
@section('content')
<section class="content-header">
	<h1>{!! trans('app.weekend_days_settings') !!}</h1>
	<ol class="breadcrumb">
		<li><a href="{!! url(getLang() . '/admin/settings') !!}"><i class="fa fa-bookmark"></i> {!! trans('app.settings') !!}</a></li>
		<li class="active">{!! trans('app.weekend_days') !!}</li>
	</ol>
</section>
<div class="content">
	<div class="box">  
		
		<div class="box-header with-border">
            <h3 class="box-title">{!! trans('app.edit') !!}</h3>
        </div>
		<div class="box-body">
			<div class="row filter-form">
				{!! Form::open(array('action' => '\App\Http\Controllers\Backend\SettingController@weekendDaysStore')) !!}
				<div class="col-xs-12">
					<div class="form-group {!! $errors->has('weekend_date') ? 'has-error' : '' !!}">
		                {!! Form::label('weekend_date', trans('app.weekend_date').' *') !!}
		                {!! 
		                    Form::text('weekend_date', Input::old('weekend_date'), [ 'class'=>'form-control bdatepicker'])
		                !!}
				        @if ($errors->first('weekend_date'))
							<span class="help-block">{!! $errors->first('weekend_date') !!}</span>
						@endif
		            </div>
		        </div>
		        <div class="col-xs-12">
		            <div class="form-group {!! $errors->has('description') ? 'has-error' : '' !!}">
		                {!! Form::label('description', trans('app.description').' *') !!}
		                {!! 
		                    Form::text('description', Input::old('description'), [ 'class'=>'form-control' ]) 
		                !!}
		                @if ($errors->first('description'))
							<span class="help-block">{!! $errors->first('description') !!}</span>
						@endif
		            </div>
	            </div>
	            <div class="col-xs-12">
		            {!! Form::submit(trans('app.save'), array('class' => 'btn btn-success')) !!}
	            </div>
	            <div class="col-xs-12">&nbsp;</div>
	            {!! Form::close() !!}
	        </div>
	        <br />
	        <div class="row">
	        	<?php
	        	//Y-m-d
	        		//$weekend_days_data = [ '2016-01-01', '2016-01-02', '2016-01-03', '2016-02-04', '2016-02-05', '2016-02-06' ];
	        	$html_data = [];
	        	$html_data_tooltip = [];
	        	foreach($weekend_days as $days_data){
	        		$day = explode('-', $days_data->weekend_date);
	        		$html_data[$day[0]][$day[1]][] = $days_data->weekend_date;//$day[1].'/'.$day[2].'/'.$day[0];
	        		$html_data_tooltip[$day[0]][$day[1]][] = $days_data->description;
	        	}
	        	//dd($html_data);
	        	?>
	        	
        		@foreach ($html_data as $year => $month_data)
        			@foreach ($month_data as $month => $days_data)
        				<div class="col-xs-3">
        					<div class="bdatepicker-allmonths" datepicker-id="{!! $year.'-'.$month !!}" data-date="{!! implode(',', $days_data) !!}" data-date-tooltip="{!! implode(',', $html_data_tooltip[$year][$month]) !!}"></div>	
        				</div>
        			@endforeach
        		@endforeach
		  		<!-- <div class="bdatepicker-allmonths" data-date="06/01/2016,06/03/2016,06/05/2016,06/07/2016"></div>
		  		<div class="bdatepicker-allmonths" data-date="01/02/2016,01/03/2016,02/03/2016"></div> -->
			  	
			</div>
		</div>
	</div>
	
</div>
@stop
