@extends('backend.layout.layout')
@section('content')
<section class="content-header">
    <h1> {!! trans('app.'.$term->post_type.'_taxonomy') !!}</h1>
    <ol class="breadcrumb">
        <li><a href="{!! URL::route('admin.taxonomy.index', $term->post_type) !!}"><i class="fa fa-bookmark"></i> {!! trans('app.dashboard') !!}</a></li>
        <li class="active">{!! trans('app.delete') !!}</li>
    </ol>
</section>
<div class="content">
    <div class="box">  
        {!! Form::open( array(  'url' => URL::route('admin.taxonomy.destroy', [ $term->post_type, $term->id ] ) ) ) !!}
        {!! Form::hidden('_method', 'DELETE') !!}
        <div class="box-header with-border">
            <h3 class="box-title">{!! HTML::decode(trans('app.delete_confirm')) !!} <b>{!! $term->title !!} </b> ?</h3>
        </div>
        <div class="box-footer">
            {!! Form::submit(trans('app.yes'), array('class' => 'btn btn-danger')) !!}
            {!! link_to( URL::route('admin.taxonomy.index', $term->post_type), trans('app.no'), array( 'class' => 'btn btn-primary' ) ) !!}
        </div>
        {!! Form::close() !!}
    </div>
</div>
@stop
