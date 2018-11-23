@extends('backend.layout.layout')
@section('content')
<section class="content-header">
    <h1> {!! trans('app.bank') !!}</h1>
    <ol class="breadcrumb">
        <li><a href="{!! url(getLang() . '/admin/bank') !!}"><i class="fa fa-bookmark"></i> {!! trans('app.bank') !!}</a></li>
        <li class="active">{!! trans('app.delete') !!}</li>
    </ol>
</section>

<div class="content">

    <div class="box">
        {!! Form::model($data, ['method' => 'DELETE', 'action' => ['Backend\BankController@destroy',$data->id]]) !!}
        {!! Form::hidden('_method', 'DELETE') !!}
        <div class="box-header with-border">
            <h3 class="box-title">{!! HTML::decode(trans('app.delete_confirm')) !!}<b> {!! $data->title !!}</b> ?</h3>
        </div>
        <div class="box-footer">
            {!! Form::submit(trans('app.yes'), array('class' => 'btn btn-danger')) !!}
            {!! link_to( url(getLang() . '/admin/bank'), trans('app.back'), array( 'class' => 'btn btn-primary' ) ) !!}
        </div>
            
        {!! Form::close() !!}
    </div>
</div>

@stop