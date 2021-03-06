@extends('backend.layout.layout')
@section('content')
<section class="content-header">
    <h1> {!! trans('app.action_plan_category') !!}</h1>
    <ol class="breadcrumb">
        <li><a href="{!! langRoute('admin.settings.plan-analysis.action-plan-category.index') !!}"><i class="fa fa-bookmark"></i> {!! trans('app.action_plan_category') !!}</a></li>
        <li class="active">{!! trans('app.delete') !!}</li>
    </ol>
</section>
<div class="content">

    <div class="box">  
        {!! Form::open( array(  'route' => array(getLang(). '.admin.settings.plan-analysis.action-plan-category.destroy', $triangle->id ) ) ) !!}
        {!! Form::hidden('_method', 'DELETE') !!}
        <div class="box-header with-border">
            <h3 class="box-title">{!! HTML::decode(trans('app.delete_confirm')) !!} <b>{!! $triangle->title !!} </b> ?</h3>
        </div>
        <div class="box-footer">
            {!! Form::submit(trans('app.yes'), array('class' => 'btn btn-danger')) !!}
            {!! link_to( langRoute('admin.settings.plan-analysis.action-plan-category.index'), trans('app.no'), array( 'class' => 'btn btn-primary' ) ) !!}
        </div>
            
        {!! Form::close() !!}
    </div>
</div>
@stop
