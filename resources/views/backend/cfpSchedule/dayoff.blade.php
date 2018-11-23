@extends('backend.layout.layout')
@section('content')
<section class="content-header">
  <h1>
    {!! trans('app.cfp_schedule_dayoff') !!} 
    <ul class="action list-inline">
      <li>{!! HTML::decode( HTML::link(URL::route('admin.cfp.schedule.dayoff.create'), '<i class="fa fa-plus-square"></i>'. trans('app.add_schedule_cfp_dayoff')) ) !!}</li>
    </ul>
  </h1>
  <ol class="breadcrumb">
    <li><a href="{!! URL::route('admin.dashboard') !!}">{!! trans('app.dashboard') !!}</a></li>
    <li class="active">{!! trans('app.cfp_schedule_dayoff') !!}</li>
  </ol>
</section>
<div class="content">
        {!! Notification::showAll() !!}
        <div class="box">  
          <div class="box-body">
            @if($cfp_schedule_day_off->count())
            <div class="table-responsive">
              <table class="table table-striped">
                <thead>
                  <tr>
                    <th>{!! trans('app.cfp_name') !!}</th>
                    <th>{!! trans('app.cfp_schedule_day_off_start_date') !!}</th>
                    <th>{!! trans('app.cfp_schedule_day_off_end_date') !!}</th>
                    <th>{!! trans('app.is_approval') !!}</th>
                    <th>{!! trans('app.action') !!}</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach( $cfp_schedule_day_off as $day_off )
                  <tr>
                    <td><a href="{{ action('Backend\CfpScheduleController@cfpScheduleDayOffShow',[$day_off->id]) }}">{!! $day_off->name !!}</a></td>
                    <td>{!! \Carbon\Carbon::parse($day_off->cfp_schedule_day_off_start_date)->format('d M Y') !!}</td>
                    <td>{!! \Carbon\Carbon::parse($day_off->cfp_schedule_day_off_end_date)->format('d M Y')  !!}</td>
                    <td>
                      @if($day_off->is_approval == 1)
                        Cuti Disetujui
                      @elseif($day_off->is_approval == 2)
                        Cuti Ditolak
                      @else
                        Cuti Belum Disetujui
                      @endif
                    </td>
                    <td class="action">
                      @if( $day_off->id != 1 || Auth::user()->hasRole('admin'))
                        {!! HTML::decode( HTML::link(URL::route('admin.cfp.schedule.dayoff.edit', array($day_off->id)), '<i class="fa fa-pencil"></i>') ) !!}
                        {!! HTML::decode( HTML::link(URL::route('admin.cfp.schedule.dayoff.delete', array($day_off->id)), '<i class="fa fa-trash-o"></i>') ) !!}
                      @endif
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
            {!! $cfp_schedule_day_off->render() !!}
          </div>
      </div>
@stop