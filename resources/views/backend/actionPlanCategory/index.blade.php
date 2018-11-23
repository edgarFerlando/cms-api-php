@extends('backend.layout.layout')
@section('content')
<section class="content-header">
  <h1>
    {!! trans('app.action_plan_category') !!} 
    <ul class="action list-inline">
      <li>{!! HTML::decode( HTML::link(langRoute('admin.settings.plan-analysis.action-plan-category.create'), '<i class="fa fa-plus-square"></i>'. trans('app.add')) ) !!}</li>
    </ul>
  </h1>
  <ol class="breadcrumb">
    <li><a href="{!! URL::route('admin.dashboard') !!}">{!! trans('app.dashboard') !!}</a></li>
    <li class="active">{!! trans('app.action_plan_category') !!}</li>
  </ol>
</section>
<div class="content">
  {!! Notification::showAll() !!}
  <div class="box">
    <div class="box-body">
      @if($items->count())
      <div class="table-responsive">
        <table class="table table-striped">
          <thead>
            <tr>
              <th>{!! trans('app.title') !!}</th>
              <th>{!! trans('app.action') !!}</th>
            </tr>
          </thead>
          <tbody>
            @foreach( $items as $item )
            <tr>
              <td>{!! $item->title !!}</td>
              <td class="action">
                {!! HTML::decode( HTML::link(langRoute('admin.settings.plan-analysis.action-plan-category.edit', array($item->id)), '<i class="fa fa-pencil"></i>') ) !!}
                {!! HTML::decode( HTML::link(URL::route('admin.settings.plan-analysis.action-plan-category.delete', array($item->id)), '<i class="fa fa-trash-o"></i>') ) !!}
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
      {!! $items->render() !!}
    </div>
  </div>
  @stop