@extends('backend.layout.layout')
@section('content')
<section class="content-header">
  <h1>
    {!! trans('app.role') !!} 
    <ul class="action list-inline">
      <li>{!! HTML::decode( HTML::link(langRoute('admin.user.role.create'), '<i class="fa fa-plus-square"></i>'. trans('app.add_role')) ) !!}</li>
    </ul>
  </h1>
  <ol class="breadcrumb">
    <li><a href="{!! URL::route('admin.dashboard') !!}">{!! trans('app.dashboard') !!}</a></li>
    <li class="active">{!! trans('app.role') !!}</li>
  </ol>
</section>
<div class="content">
  {!! Notification::showAll() !!}
  <div class="box">  
    <div class="box-body">
      @if($roles->count())
      <div class="table-responsive">
        <table class="table table-striped">
          <thead>
            <tr>
              <th>{!! trans('app.display_name') !!}</th>
              <th>{!! trans('app.name') !!}</th>
              <th>{!! trans('app.created_date') !!}</th>
              <th>{!! trans('app.updated_date') !!}</th>
              <th>{!! trans('app.action') !!}</th>
            </tr>
          </thead>
          <tbody>
            @foreach( $roles as $role )
            <tr>
              <td>{!! link_to_route(getLang(). '.admin.user.role.show', $role->display_name, $role->id) !!}</td>
              <td>{!! $role->name !!}</td>
              <td>{!! $role->created_at !!}</td>
              <td>{!! $role->updated_at !!}</td>
              <td class="action">
                @if( $role->id != 1 || Auth::user()->hasRole('admin'))
                  {!! HTML::decode( HTML::link(langRoute('admin.user.role.edit', array($role->id)), '<i class="fa fa-pencil"></i>') ) !!}
                  {!! HTML::decode( HTML::link(URL::route('admin.user.role.delete', array($role->id)), '<i class="fa fa-trash-o"></i>') ) !!}
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
    {!! $roles->render() !!}
  </div>
</div>
@stop