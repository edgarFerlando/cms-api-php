@extends('backend.layout.layout')
@section('content')
<section class="content-header">
  <h1>
    {!! trans('app.cfp_schedule') !!} 
    <ul class="action list-inline">
      <li>{!! HTML::decode( HTML::link(langRoute('admin.cfp.schedule.create'), '<i class="fa fa-plus-square"></i>'. trans('app.add_schedule_cfp')) ) !!}</li>
    </ul>
  </h1>
  <ol class="breadcrumb">
    <li><a href="{!! URL::route('admin.dashboard') !!}">{!! trans('app.dashboard') !!}</a></li>
    <li class="active">{!! trans('app.cfp_schedule') !!}</li>
  </ol>
</section>
<div class="content">
{!! Notification::showAll() !!}
  <div class="box">
    @if(Route::currentRouteName() == 'admin.cfp.schedule.filter')
      <div class="box-header with-border">
        <h3 class="box-title text-green">Result &nbsp;: &nbsp;<strong>{!! $totalItems.'</strong> item'.($totalItems > 1?'s':'') !!}</h3>
      </div>
    @endif
    <div class="box-body">
      <div class="row filter-form">
        {!! Form::open([ 'route' => [ 'admin.cfp.schedule.filter'] ]) !!}
        <div class="col-xs-2 clear-right">
          <div class="form-group">
            {!! Form::label('schedule_type', trans('app.schedule_type')) !!}
            {!!  Form::select('schedule_type', $cfp_schedule_types, Input::get('schedule_type'), [ 'class' => 'selectize' ]) !!}
          </div>
        </div>
        <div class="col-xs-2 clear-right">
          <div class="form-group">
            {!! Form::label('client_name', trans('app.client_name')) !!}
            {!! Form::text('client_name', Input::get('client_name'), [ 'class'  => 'form-control', 'autocomplete'   => 'off'], $errors) !!}
          </div>
        </div>
        <div class="col-xs-2 clear-right">
          <div class="form-group">
            {!! Form::label('cfp_name', trans('app.cfp_name')) !!}
            {!! Form::text('cfp_name', Input::get('cfp_name'), [ 'class'  => 'form-control', 'autocomplete'   => 'off'], $errors) !!}
          </div>
        </div>
        
        <div class="col-xs-2 clear-right">
          <div class="form-group action-tool">
            {!! Form::submit(trans('app.filter'), array('class' => 'btn btn-success')) !!}
            {!! HTML::link(langurl('admin/cfp/schedule'),trans('app.reset'), array('class' => 'btn btn-default')) !!}
          </div>
        </div>
        {!! Form::close() !!}
      </div>

      @if($cfpSchedules->count())
      <div class="table-responsive">
        <table class="table table-striped">
          <thead>
            <tr>
              <th>{!! trans('app.schedule_type') !!}</th>
              <th>{!! trans('app.your_concern') !!}</th>
              <th>{!! trans('app.client_name') !!}</th>
              <th>{!! trans('app.cfp_name') !!}</th>
              <th>{!! trans('app.schedule_start_date') !!}</th>
              <th>{!! trans('app.schedule_end_date') !!}</th>
              <th>{!! trans('app.notes') !!}</th>
              <!-- <th>{!! trans('app.created_by') !!}</th>
              <th>{!! trans('app.created_date') !!}</th>
              <th>{!! trans('app.updated_by') !!}</th>
              <th>{!! trans('app.updated_date') !!}</th>
              <th>{!! trans('app.flag') !!}</th>-->
              <th>{!! trans('app.action') !!}</th>
            </tr>
          </thead>
          <tbody>
            @foreach( $cfpSchedules as $cfpSchedule )
            <tr>
              <td>{!! $cfpSchedule->type_display_name !!}</td>
              <td>{!! link_to_route(getLang(). '.admin.cfp.schedule.show', $cfpSchedule->title, $cfpSchedule->internalid) !!}</td>
              <td>{!! link_to_route(getLang(). '.admin.user.show', $cfpSchedule->client->name, $cfpSchedule->client_id) !!}</td>
              <td>{!! link_to_route(getLang(). '.admin.user.show', $cfpSchedule->cfp->name, $cfpSchedule->cfp_id) !!}</td>
              <td>{!! fulldate_trans($cfpSchedule->schedule_start_date) !!}</td>
              <td>{!! fulldate_trans($cfpSchedule->schedule_end_date) !!}</td>
              <td>{!! $cfpSchedule->notes !!}</td>
              <!--<td>{!! $cfpSchedule->created_by_name !!}</td>
              <td>{!! fulldate_trans($cfpSchedule->created_at) !!}</td>
              <td>{!! $cfpSchedule->updated_by_name !!}</td>
              <td>{!! fulldate_trans($cfpSchedule->updated_at) !!}</td>
              <td>{!! $cfpSchedule->record_flag !!}</td>-->
              <td class="action">
                {!! HTML::decode( HTML::link(langRoute('admin.cfp.schedule.edit', array($cfpSchedule->internalid)), '<i class="fa fa-pencil"></i>') ) !!}
                {!! HTML::decode( HTML::link(URL::route('admin.cfp.schedule.delete', array($cfpSchedule->internalid)), '<i class="fa fa-trash-o"></i>') ) !!}
              </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        @else
          <div class="alert alert-danger">{{{ trans('app.no_results_found') }}}</div>
        @endif
      </div>
    </div>
    <div class="text-center">
      {!! $cfpSchedules->render() !!}
    </div>
  </div>
  @stop