@extends('backend.layout.layout')
@section('content')
<section class="content-header">
    <h1> {!! $attr['title'] !!}</h1>
    <ol class="breadcrumb">
        <li><a href="{!! langRoute('admin.'.$attr['name'].'.index') !!}"><i class="fa fa-bookmark"></i> {!! $attr['title'] !!}</a></li>
        <li class="active">{!! trans('app.delete') !!}</li>
    </ol>
</section>
<div class="content">
    <div class="box">  
        {!! Form::open( array(  'route' => array(getLang(). '.admin.'.$attr['name'].'.destroy', $portofolio->id ) ) ) !!}
        {!! Form::hidden('_method', 'DELETE') !!}
        <div class="box-header with-border">
            <h3 class="box-title">{!! HTML::decode(trans('app.delete_confirm')) !!} <b>{!! $portofolio->portofolio_name !!} </b> ?</h3>
        </div>
        <!-- <div class="box-body">
            <div class="alert alert-danger">
                {{{ trans('app.maybe_related_to_notification') }}}, <a href="{!! langRoute('admin.menu.index') !!}">{{{ trans('app.check_it') }}}</a>
            </div>
        </div>
        <div class="box-footer">
            {!! Form::submit(trans('app.yes'), array('class' => 'btn btn-danger')) !!}
            {!! link_to( langRoute('admin.product.attribute.index'), trans('app.no'), array( 'class' => 'btn btn-primary' ) ) !!}
        </div>-->

        <div class="box-footer">
            {!! Form::submit(trans('app.yes'), array('class' => 'btn btn-danger')) !!}
            {!! link_to( langRoute('admin.portofolio.index'), trans('app.no'), array( 'class' => 'btn btn-primary' ) ) !!}
        </div>
            
        {!! Form::close() !!}
    </div>
</div>
@stop
