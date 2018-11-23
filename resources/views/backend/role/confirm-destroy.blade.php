@extends('backend.layout.layout')
@section('content')
<section class="content-header">
    <h1> {!! trans('app.role') !!}</h1>
    <ol class="breadcrumb">
        <li><a href="{!! langRoute('admin.user.role.index') !!}"><i class="fa fa-bookmark"></i> {!! trans('app.dashboard') !!}</a></li>
        <li class="active">{!! trans('app.delete') !!}</li>
    </ol>
</section>
<div class="content">
    <div class="box">  
        {!! Form::open( array(  'route' => array(getLang(). '.admin.user.role.destroy', $role->id ) ) ) !!}
        {!! Form::hidden('_method', 'DELETE') !!}
        <div class="box-header">
            <h3 class="box-title">{!! HTML::decode(trans('app.delete_confirm')) !!} <b>{!! $role->display_name !!} </b> ?</h3>
        </div>
        <div class="box-footer">
            {!! Form::submit(trans('app.yes'), array('class' => 'btn btn-danger')) !!}
            {!! link_to( langRoute('admin.user.role.index'), trans('app.no'), array( 'class' => 'btn btn-primary' ) ) !!}
        </div>
        {!! Form::close() !!}
    </div>
</div>
@stop
