@extends('backend.layout.layout')
@section('content')
<section class="content-header">
  <h1>
    {!! trans('app.permission') !!} 
    <ul class="action list-inline">
      <li>{!! HTML::decode( HTML::link(langRoute('admin.user.permission.create'), '<i class="fa fa-plus-square"></i>'. trans('app.add_permission')) ) !!}</li>
    </ul>
  </h1>
  <ol class="breadcrumb">
    <li><a href="{!! URL::route('admin.dashboard') !!}">{!! trans('app.dashboard') !!}</a></li>
    <li class="active">{!! trans('app.permission') !!}</li>
  </ol>
</section>
<div class="content">
  {!! Notification::showAll() !!}
  <div class="box">  
    <div class="box-body">
      @if($permissions->count())
      <div class="table-responsive">
        <table class="table table-striped">
          <thead>
            <tr>
              <th>{!! trans('app.module') !!}</th>
              <th>{!! trans('app.ability') !!}</th>
              <th>{!! trans('app.created_date') !!}</th>
              <th>{!! trans('app.updated_date') !!}</th>
              <th>{!! trans('app.action') !!}</th>
            </tr>
          </thead>
          <tbody>
            @foreach( $permissions as $permission )
            <tr>
              <td>{!! $permission->module !!}</td>
              <td>{!! link_to_route(getLang(). '.admin.user.permission.show', $permission->display_name, $permission->id) !!}</td>
              <td>{!! $permission->created_at !!}</td>
              <td>{!! $permission->updated_at !!}</td>
              <td class="action">
                {!! HTML::decode( HTML::link(langRoute('admin.user.permission.edit', array($permission->id)), '<i class="fa fa-pencil"></i>') ) !!}
                {!! HTML::decode( HTML::link(URL::route('admin.user.permission.delete', array($permission->id)), '<i class="fa fa-trash-o"></i>') ) !!}
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
    {!! $permissions->render() !!}
  </div>
</div>
@stop