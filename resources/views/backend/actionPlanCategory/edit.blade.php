@extends('backend.layout.layout')
@section('content')
<section class="content-header">
    <h1> {{{ trans('app.action_plan_category') }}}</h1>
    <ol class="breadcrumb">
        <li><a href="{!! url(getLang() . '/admin/settings/plan-analysis/action-plan-category') !!}"><i class="fa fa-bookmark"></i> {{{ trans('app.action_plan_category') }}}</a></li>
        <li class="active">{{{ trans('app.edit') }}}</li>
    </ol>
</section>
<div class="content">
    <div class="box">
        {!! Form::open( array( 'route' => array( getLang() . '.admin.settings.plan-analysis.action-plan-category.update', $data->id), 'method' => 'PATCH', 'files'=>true)) !!}
        {!! Form::hidden('id', $data->id) !!}
        <div class="box-header with-border">
            <h3 class="box-title">{!! trans('app.edit') !!}</h3>
        </div>
        <div class="box-body">
			<div class="form-group {!! $errors->has('title') ? 'has-error' : '' !!}">
				{!! Form::label('title', trans('app.title').' *') !!}
				{!! Form::text('title', val($data, 'title'), [ 'class' => 'form-control slug-source', 'autocomplete' => 'off' ]) !!}
				@if ($errors->first('title'))
					<span class="help-block">{!! $errors->first('title') !!}</span>
				@endif
			</div>
        </div>
        <div class="box-footer">
            {!! Form::submit(trans('app.save'), array('class' => 'btn btn-success')) !!}&nbsp;
            {!! link_to( langRoute('admin.settings.plan-analysis.action-plan-category.index'), trans('app.cancel'), array( 'class' => 'btn btn-primary' ) ) !!}
        </div>
        {!! Form::close() !!}
    </div>
</div>
@stop
