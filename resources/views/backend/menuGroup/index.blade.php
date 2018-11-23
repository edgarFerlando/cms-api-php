@extends('backend.layout.layout')
@section('content')
<section class="content-header">
  <h1>
    {!! trans('app.menu_group') !!} 
    <ul class="action list-inline">
      <li>{!! HTML::decode( HTML::link(langRoute('admin.menu-group.create'), '<i class="fa fa-plus-square"></i>'. trans('app.add_menu_group')) ) !!}</li>
    </ul>
  </h1>
  <ol class="breadcrumb">
    <li><a href="{!! URL::route('admin.dashboard') !!}">{!! trans('app.dashboard') !!}</a></li>
    <li class="active">{!! trans('app.menu_group') !!}</li>
  </ol>
</section>
<div class="content">
  {!! Notification::showAll() !!}
  <div class="box">  
    <div class="box-body">
      @if($menuGroups->count())
      <div class="table-responsive">
        <table class="table table-striped">
          <thead>
            <tr>
              <th>{!! trans('app.title') !!}</th>
              <th>{!! trans('app.description') !!}</th>
              <th>{!! trans('app.created_date') !!}</th>
              <th>{!! trans('app.updated_date') !!}</th>
              <th>{!! trans('app.action') !!}</th>
            </tr>
          </thead>
          <tbody>
            @foreach( $menuGroups as $menuGroup )
            <tr>
              <td>{!! link_to_route(getLang(). '.admin.menu-group.show', $menuGroup->title, $menuGroup->id) !!}</td>
              <td>{!! $menuGroup->description !!}</td>
              <td>{!! $menuGroup->created_at !!}</td>
              <td>{!! $menuGroup->updated_at !!}</td>
              <td class="action">
                {!! HTML::decode( HTML::link(URL::route('admin.menu_group.items', array($menuGroup->id)), '<i class="fa fa-bars"></i>') ) !!}
                {!! HTML::decode( HTML::link(langRoute('admin.menu-group.edit', array($menuGroup->id)), '<i class="fa fa-pencil"></i>') ) !!}
                {!! HTML::decode( HTML::link(URL::route('admin.menu_group.delete', array($menuGroup->id)), '<i class="fa fa-trash-o"></i>') ) !!}
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
    {!! $menuGroups->render() !!}
  </div>
</div>
@stop