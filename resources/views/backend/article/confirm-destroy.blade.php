@extends('backend.layout.layout')
@section('content')
<section class="content-header">
    <h1> {!! trans('app.article') !!}</h1>
    <ol class="breadcrumb">
        <li><a href="{!! url(getLang() . '/admin/article') !!}"><i class="fa fa-bookmark"></i> {!! trans('app.dashboard') !!}</a></li>
        <li class="active">{!! trans('app.delete') !!}</li>
    </ol>
</section>
<div class="content">
    <div class="box">  
        {!! Form::open( array(  'route' => array(getLang(). '.admin.article.destroy', $article->id ) ) ) !!}
        {!! Form::hidden('_method', 'DELETE') !!}
        <div class="box-header with-border">
            <h3 class="box-title">{!! HTML::decode(trans('app.delete_confirm')) !!} <b>{!! $article->title !!} </b> ?</h3>
        </div>
        <div class="box-body">
            <div class="alert alert-danger">
                {{{ trans('app.maybe_related_to_notification') }}}, <a href="{!! url(getLang() . '/admin/article') !!}">{{{ trans('app.check_it') }}}</a>
            </div>
        </div>
        <div class="box-footer">
            {!! Form::submit(trans('app.yes'), array('class' => 'btn btn-danger')) !!}
            {!! link_to( url(getLang() . '/admin/article'), trans('app.no'), array( 'class' => 'btn btn-primary' ) ) !!}
        </div>
        {!! Form::close() !!}
    </div>
</div>
@stop
