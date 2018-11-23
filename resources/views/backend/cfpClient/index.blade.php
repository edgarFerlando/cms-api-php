@extends('backend.layout.layout')
@section('content')
<section class="content-header">
  <h1>
    {!! trans('app.cfp_client') !!} 
    <ul class="action list-inline">
      <li>{!! HTML::decode( HTML::link(langRoute('admin.cfp.client.create'), '<i class="fa fa-plus-square"></i>'. trans('app.add_cfp_client')) ) !!}</li>
    </ul>
  </h1>
  <ol class="breadcrumb">
    <li><a href="{!! URL::route('admin.dashboard') !!}">{!! trans('app.dashboard') !!}</a></li>
    <li class="active">{!! trans('app.cfp_client') !!}</li>
  </ol>
</section>
<div class="content">
  {!! Notification::showAll() !!}
  <div class="box">  
    @if(Route::currentRouteName() == 'admin.cfp.client.filter')
      <div class="box-header with-border">
        <h3 class="box-title text-green">Result &nbsp;: &nbsp;<strong>{!! $totalItems.'</strong> item'.($totalItems > 1?'s':'') !!}</h3>
      </div>
    @endif
    <div class="box-body">
      <div class="row filter-form">
        {!! Form::open([ 'route' => [ 'admin.cfp.client.filter'] ]) !!}
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
            {!! HTML::link(langurl('admin/cfp/client'),trans('app.reset'), array('class' => 'btn btn-default')) !!}
          </div>
        </div>
        {!! Form::close() !!}
      </div>
      @if($cfpClients->count())
      <div class="table-responsive">
        <table class="table table-striped">
          <thead>
            <tr>
              <th>{!! trans('app.client_name') !!}</th>
              <th>{!! trans('app.cfp_name') !!}</th>
              <th>{!! trans('app.notes') !!}</th>
              <th>History F.Check</th>
              <th>{!! trans('app.updated_by') !!}</th>
              <th>{!! trans('app.updated_date') !!}</th>
              <th>{!! trans('app.action') !!}</th>
            </tr>
          </thead>
          <tbody>
            @foreach( $cfpClients as $cfpclient )
            <tr>
              <td>{!! link_to_route(getLang(). '.admin.user.show', $cfpclient->client_name, $cfpclient->client_id) !!}</td>
              <td>{!! link_to_route(getLang(). '.admin.user.show', $cfpclient->cfp_name, $cfpclient->cfp_id) !!}</td>
              <td>{!! $cfpclient->notes !!}</td>
              <td class="text-center"><a href="{{ Route('admin.user.cashflow-analysis.show', [ 'id' => $cfpclient->client_id, 'version' => 0 ]) }}"><i class="fa fa-file-text-o" aria-hidden="true"></i></a></td>
              <td>{!! $cfpclient->updated_by_name !!}</td>
              <td>{!! fulldate_trans($cfpclient->updated_at) !!}</td>
              <td class="action">
                {!! HTML::decode( HTML::link(langRoute('admin.cfp.client.edit', array($cfpclient->internalid)), '<i class="fa fa-pencil"></i>') ) !!}
                {!! HTML::decode( HTML::link(URL::route('admin.cfp.client.delete', array($cfpclient->internalid)), '<i class="fa fa-trash-o"></i>') ) !!}
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
      {!! $cfpClients->render() !!}
    </div>
  </div>
  @stop