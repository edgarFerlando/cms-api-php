@extends('backend.layout.layout')
@section('content')
<section class="content-header">
	<h1>{!! trans('app.action_plan_category') !!}</h1>
	<ol class="breadcrumb">
		<li><a href="{!! url(getLang() . '/admin/settings/plan-analysis/action_plan_category') !!}"><i class="fa fa-bookmark"></i> {!! trans('app.action_plan_category') !!}</a></li>
		<li class="active">{!! trans('app.add_layer') !!}</li>
	</ol>
</section>
<div class="content">
	<div class="box">  
		{!! Form::open(array('action' => '\App\Http\Controllers\Backend\ActionPlanCategoryController@store')) !!}
		<div class="box-header with-border">
            <h3 class="box-title">{!! trans('app.add_layer') !!}</h3>
        </div>
		<div class="box-body">
			<div class="form-group {!! $errors->has('title') ? 'has-error' : '' !!}">
				{!! Form::label('title', trans('app.title').' *') !!}
				{!! Form::text('title', Input::old('title'), [ 'class' => 'form-control slug-source', 'autocomplete' => 'off' ]) !!}
				@if ($errors->first('title'))
					<span class="help-block">{!! $errors->first('title') !!}</span>
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
