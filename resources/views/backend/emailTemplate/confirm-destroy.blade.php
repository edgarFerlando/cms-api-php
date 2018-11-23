@extends('backend.layout.layout')
@section('content')
<section class="content-header">
    <h1> {!! trans('app.email_template') !!}</h1>
    <ol class="breadcrumb">
        <li><a href="{!! langRoute('admin.settings.email-template.index') !!}"><i class="fa fa-bookmark"></i> {!! trans('app.dashboard') !!}</a></li>
        <li class="active">{!! trans('app.delete') !!}</li>
    </ol>
</section>
<div class="content">
    <div class="box">  
        {!! Form::open( array(  'route' => array(getLang(). '.admin.settings.email-template.destroy', $emailTemplate->id ) ) ) !!}
        {!! Form::hidden('_method', 'DELETE') !!}
        <div class="box-header with-border">
            <h3 class="box-title">{!! HTML::decode(trans('app.delete_confirm')) !!} <b>{!! $emailTemplate->title !!} </b> ?</h3>
        </div>
        <div class="box-footer">
            {!! Form::submit(trans('app.yes'), array('class' => 'btn btn-danger')) !!}
            {!! link_to( langRoute('admin.settings.email-template.index'), trans('app.no'), array( 'class' => 'btn btn-primary' ) ) !!}
        </div>
        {!! Form::close() !!}
    </div>
</div>
@stop
