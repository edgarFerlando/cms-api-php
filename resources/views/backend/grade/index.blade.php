@extends('backend.layout.layout')
@section('content')
<section class="content-header">
  <h1>
    {!! trans('app.grade') !!} 
    <ul class="action list-inline">
      <li>{!! HTML::decode( HTML::link(langRoute('admin.grade.create'), '<i class="fa fa-plus-square"></i>'. trans('app.add_grade')) ) !!}</li>
    </ul>
  </h1>
  <ol class="breadcrumb">
    <li><a href="{!! URL::route('admin.dashboard') !!}">{!! trans('app.dashboard') !!}</a></li>
    <li class="active">{!! trans('app.grade') !!}</li>
  </ol>
</section>
<div class="content">
  {!! Notification::showAll() !!}
  <div class="box">  
    <div class="box-body">
      @if($grades->count())
      <div class="table-responsive">
        <table class="table table-striped">
          <thead>
            <tr>
              <th>{!! trans('app.grade_name') !!}</th>
              <th>{!! trans('app.created_by') !!}</th>
              <th>{!! trans('app.created_date') !!}</th>
              <th>{!! trans('app.updated_by') !!}</th>
              <th>{!! trans('app.updated_date') !!}</th>
              <th>{!! trans('app.flag') !!}</th>
              <th>{!! trans('app.action') !!}</th>
            </tr>
          </thead>
          <tbody>
            @foreach( $grades as $grade )
            {{-- {!! dd($testimonial) !!} --}}
            {{-- {!! dd($testimonial) !!} --}}
            <tr>
              <td>{!! link_to_route(getLang(). '.admin.grade.show', $grade->grade_name, $grade->id) !!}</td>
              <td>{!! $grade->createdBy->name !!}</td>
              <td>{!! fulldate_trans($grade->created_at) !!}</td>
              <td>{!! $grade->updatedBy->name !!}</td>
              <td>{!! fulldate_trans($grade->updated_at) !!}</td>
              <td>{!! $grade->record_flag !!}</td>
              <td class="action">
                {!! HTML::decode( HTML::link(langRoute('admin.grade.edit', array($grade->id)), '<i class="fa fa-pencil"></i>') ) !!}
                {!! HTML::decode( HTML::link(URL::route('admin.grade.delete', array($grade->id)), '<i class="fa fa-trash-o"></i>') ) !!}
                {{-- {!! HTML::decode( HTML::link(URL::route(getLang().'.admin.testimoni.destroy', array($testimonial->id)), '<i class="fa fa-trash-o"></i>') ) !!} --}}
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
      {!! $grades->render() !!}
    </div>
  </div>
  @stop