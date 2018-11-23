@extends('backend.layout.layout')
@section('content')
<section class="content-header">
    <h1> {{{ trans('app.schedule_cfp') }}}
    @if(isset($is_expired) && $is_expired == 1)
        <ul class="action list-inline">
            <li><span class="label label-danger">This schedule has expired</span></li>
        </ul>
    @endif
    </h1>
    
    <ol class="breadcrumb">
        <li><a href="{!! langRoute('admin.cfp.schedule.index') !!}"><i class="fa fa-bookmark"></i> {{{ trans('app.schedule_cfp') }}}</a></li>
        <li class="active">{{{ trans('app.show') }}}</li>
    </ol>
</section>
<div class="content">
    <div class="box"> 
        @if(!$cfpSchedule['id']) 
            <div class="box-header with-border">
                <h3 class="box-title">{!! $cfpSchedule->title !!}</h3>
            </div>
            <div class="box-body">
                <div class="form-group">
                    {!! Form::label('client_name', trans('app.client_name')) !!}
                    <div>{!! $cfpSchedule->client->name !!}</div>
                </div>
                <div class="form-group">
                    {!! Form::label('cfp_name', trans('app.cfp_name')) !!}
                    <div>{!! $cfpSchedule->cfp->name !!}</div>
                </div>
                <div class="form-group">
                    {!! Form::label('schedule_start_date', trans('app.schedule_start_date')) !!}
                    <div>{!! fulldate_trans($cfpSchedule->schedule_start_date) !!}</div>
                </div>
                <div class="form-group">
                    {!! Form::label('schedule_end_date', trans('app.schedule_end_date')) !!}
                    <div>{!! fulldate_trans($cfpSchedule->schedule_end_date) !!}</div>
                </div>
                <div class="form-group">
                    {!! Form::label('location', trans('app.location')) !!}
                    <div>{!! $cfpSchedule->location !!}</div>
                </div>
                <div class="form-group">
                    {!! Form::label('notes', trans('app.notes')) !!}
                    <div>{!! $cfpSchedule->notes !!}</div>
                </div>
                <div class="form-group">
                    {!! Form::label('created_by', trans('app.created_by')) !!}
                    <div>{!! $cfpSchedule->created_by_name !!}</div>
                </div>
                <div class="form-group">
                    {!! Form::label('created_date', trans('app.created_date')) !!}
                    <div>{!! fulldate_trans($cfpSchedule->created_at) !!}</div>
                </div>
                <div class="form-group">
                    {!! Form::label('updated_by', trans('app.updated_by')) !!}
                    <div>{!! $cfpSchedule->updated_by_name !!}</div>
                </div>
                <div class="form-group">
                    {!! Form::label('updated_date', trans('app.updated_date')) !!}
                    <div>{!! fulldate_trans($cfpSchedule->updated_at) !!}</div>
                </div>
                <div class="form-group">
                    {!! Form::label('flag', trans('app.flag')) !!}
                    <div>{!! $cfpSchedule->record_flag !!}</div>
                </div>
            </div>
            <div class="box-footer">
                {!! link_to( langRoute('admin.cfp.schedule.index'), trans('app.back'), array( 'class' => 'btn btn-primary' ) ) !!}
            </div>
        @else
            <div class="box-header with-border">
                <h3 class="box-title">{!! trans('app.display') !!}</h3>
            </div>
            <div class="box-body">
                <div class="alert alert-danger">{{{ trans('app.no_results_found') }}}</div>
            </div>
            <div class="box-footer">
                {!! link_to( langRoute('admin.cfp.schedule.index'), trans('app.cancel'), array( 'class' => 'btn btn-primary' ) ) !!}
            </div>
        @endif
    </div>
</div>
@stop
