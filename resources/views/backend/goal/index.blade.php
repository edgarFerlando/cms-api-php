@extends('backend.layout.layout')
@section('content')
<section class="content-header">
  <h1>
    {!! trans('app.goal') !!} 
    <ul class="action list-inline">
      <li>{!! HTML::decode( HTML::link(langRoute('admin.goal.create'), '<i class="fa fa-plus-square"></i>'. trans('app.add_goal')) ) !!}</li>
    </ul>
  </h1>
  <ol class="breadcrumb">
    <li><a href="{!! URL::route('admin.dashboard') !!}">{!! trans('app.dashboard') !!}</a></li>
    <li class="active">{!! trans('app.goal') !!}</li>
  </ol>
</section>
<div class="content">
  {!! Notification::showAll() !!}
  <div class="box">  
    <div class="box-body">
      @if($goals->count())
      <div class="table-responsive">
        <table class="table table-striped">
          <thead>
            <tr>
              <th>{!! trans('app.goal_name') !!}</th>
              <th>{!! trans('app.created_by') !!}</th>
              <th>{!! trans('app.created_date') !!}</th>
              <th>{!! trans('app.updated_by') !!}</th>
              <th>{!! trans('app.updated_date') !!}</th>
              <th>{!! trans('app.flag') !!}</th>
              <th>{!! trans('app.action') !!}</th>
            </tr>
          </thead>
          <tbody>
            @foreach( $goals as $goal )
            {{-- {!! dd($testimonial) !!} --}}
            {{-- {!! dd($testimonial) !!} --}}
            <tr>
              <td>{!! link_to_route(getLang(). '.admin.goal.show', $goal->goal_name, $goal->id) !!}</td>
              <td>{!! $goal->createdBy->name !!}</td>
              <td>{!! fulldate_trans($goal->created_at) !!}</td>
              <td>{!! $goal->updatedBy->name !!}</td>
              <td>{!! fulldate_trans($goal->updated_at) !!}</td>
              <td>{!! $goal->record_flag !!}</td>
              <td class="action">
                {!! HTML::decode( HTML::link(langRoute('admin.goal.edit', array($goal->id)), '<i class="fa fa-pencil"></i>') ) !!}
                {!! HTML::decode( HTML::link(URL::route('admin.goal.delete', array($goal->id)), '<i class="fa fa-trash-o"></i>') ) !!}
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
      {!! $goals->render() !!}
    </div>
  </div>
  @stop