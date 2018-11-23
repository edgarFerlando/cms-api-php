@extends('backend.layout.layout')
@section('content')
<section class="content-header">
    <h1> {!! trans('app.cfp_client') !!}</h1>
    <ol class="breadcrumb">
        <li><a href="{!! URL::route('admin.wallet.index') !!}"><i class="fa fa-bookmark"></i> {!! trans('app.wallet') !!}</a></li>
        <li class="active">{!! trans('app.delete') !!}</li>
    </ol>
</section>
<div class="content">
    <div class="box">  
        {!! Form::open( array(  'route' => array('admin.wallet.destroy', $data->id ) ) ) !!}
        {!! Form::hidden('_method', 'DELETE') !!}
        <div class="box-header with-border">
            <h3 class="box-title">{!! HTML::decode(trans('app.delete_confirm')) !!} <b>Transaction with date transaction {!! date_trans($data->transaction_date) !!} </b> ?</h3>
        </div>
        <div class="box-footer">
            {!! Form::submit(trans('app.yes'), array('class' => 'btn btn-danger')) !!}
            {!! link_to( URL::route('admin.wallet.index'), trans('app.no'), array( 'class' => 'btn btn-primary' ) ) !!}
        </div>
            
        {!! Form::close() !!}
    </div>
</div>
@stop
