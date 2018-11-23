@extends('backend.layout.layout')
@section('content')
<section class="content-header">
    <h1> {!! trans('app.cfp_schedule_dayoff') !!}</h1>
    <ol class="breadcrumb">
        <li><a href="{!! URL::Route('admin.cfp.schedule.dayoff') !!}"><i class="fa fa-bookmark"></i> {!! trans('app.cfp_schedule_cutoff') !!}</a></li>
        <li class="active">{!! trans('app.delete') !!}</li>
    </ol>
</section>
<div class="content">

    <div class="box">
        {!! Form::open( array(  'url' => URL::route('admin.cfp.schedule.dayoff.destroy', $cfpScheduleDayOff->id ) ) ) !!}
        {!! Form::hidden('_method', 'DELETE') !!}
        <div class="box-header with-border">
            <h3 class="box-title">{!! HTML::decode(trans('app.delete_confirm')) !!} For Name CFP Cut Off Day <b>{!! $cfpScheduleDayOff->name !!} </b> ?</h3>
        </div>
        <div class="box-footer">
            {!! Form::submit(trans('app.yes'), array('class' => 'btn btn-danger')) !!}
            {!! link_to( URL::route('admin.cfp.schedule.dayoff'), trans('app.no'), array( 'class' => 'btn btn-primary' ) ) !!}
        </div>
            
        {!! Form::close() !!}
    </div>
</div>
@stop
