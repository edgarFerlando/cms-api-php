@extends('backend.layout.layout')
@section('content')
<section class="content-header">
  <h1>
    {!! trans('app.triangle_layer') !!} 
    <ul class="action list-inline">
      <li>{!! HTML::decode( HTML::link(langRoute('admin.settings.plan-analysis.triangle-layer.create'), '<i class="fa fa-plus-square"></i>'. trans('app.add')) ) !!}</li>
    </ul>
  </h1>
  <ol class="breadcrumb">
    <li><a href="{!! URL::route('admin.dashboard') !!}">{!! trans('app.dashboard') !!}</a></li>
    <li class="active">{!! trans('app.triangle_layer') !!}</li>
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
              <th>{!! trans('app.description') !!}</th>
              <th>{!! trans('app.stack_number') !!}</th>
              <th>{!! trans('app.action') !!}</th>
            </tr>
          </thead>
          <tbody>
            @foreach( $items as $item )
            <tr>
              <td>{!! $item->title !!}</td>
              <td>{!! str_limit($item->description, 100, '...') !!}</td>
              <td>{!! $item->stack_number !!}</td>
              <td class="action">
                {!! HTML::decode( HTML::link(langRoute('admin.settings.plan-analysis.triangle-layer.edit', array($item->id)), '<i class="fa fa-pencil"></i>') ) !!}
                {!! HTML::decode( HTML::link(URL::route('admin.settings.plan-analysis.triangle-layer.delete', array($item->id)), '<i class="fa fa-trash-o"></i>') ) !!}
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