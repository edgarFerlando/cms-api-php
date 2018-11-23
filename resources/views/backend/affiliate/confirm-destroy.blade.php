@extends('backend.layout.layout')
@section('content')
<section class="content-header">
    <h1> {!! trans('app.payment_confirmation') !!}</h1>
    <ol class="breadcrumb">

        <li><a href="{!! langRoute('admin.payment-confirmation.index') !!}"><i class="fa fa-bookmark"></i> {!! trans('app.payment_confirmation') !!}</a></li>
        <li class="active">{!! trans('app.delete') !!}</li>
    </ol>
</section>
<div class="content">
    <div class="box">  
        {!! Form::open( array(  'route' => array(getLang(). '.admin.payment-confirmation.destroy', $paymentConfirmation->id ) ) ) !!}
        {!! Form::hidden('_method', 'DELETE') !!}
        <div class="box-header">
            <h3 class="box-title">{!! $attr['box_title'] !!}</h3>
        </div>
        <div class="box-footer">
            {!! Form::submit(trans('app.yes'), array('class' => 'btn btn-danger')) !!}
            {!! link_to( langRoute('admin.payment-confirmation.index'), trans('app.no'), array( 'class' => 'btn btn-primary' ) ) !!}
        </div>
        {!! Form::close() !!}
    </div>
</div>
@stop
