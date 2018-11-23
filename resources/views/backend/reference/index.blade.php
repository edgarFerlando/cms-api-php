@extends('backend.layout.layout')
@section('content')
<section class="content-header">
  <h1>
    {!! trans('app.reference') !!} 
    <ul class="action list-inline">
      <li>{!! HTML::decode( HTML::link(langRoute('admin.reference.create'), '<i class="fa fa-plus-square"></i>'. trans('app.add_reference')) ) !!}</li>
    </ul>
  </h1>
  <ol class="breadcrumb">
    <li><a href="{!! URL::route('admin.dashboard') !!}">{!! trans('app.dashboard') !!}</a></li>
    <li class="active">{!! trans('app.reference') !!}</li>
  </ol>
</section>
<div class="content">
  {!! Notification::showAll() !!}
  <div class="box">  
    <div class="box-body">
      @if($references->count())
      <div class="table-responsive">
        <table class="table table-striped">
          <thead>
            <tr>
              <th>{!! trans('app.code') !!}</th>
              <th>{!! trans('app.name') !!}</th>
              <th>{!! trans('app.company') !!}</th>
              <th>{!! trans('app.email') !!}</th>
              <th>{!! trans('app.phone') !!}</th>
              <th>{!! trans('app.updated_by') !!}</th>
              <th>{!! trans('app.updated_date') !!}</th>
              <th>{!! trans('app.action') !!}</th>
            </tr>
          </thead>
          <tbody>
            @foreach( $references as $reference )
            <tr>
              <td>{!! link_to_route(getLang(). '.admin.reference.show', $reference->code, $reference->id) !!}</td>
              <td>{!! link_to_route(getLang(). '.admin.reference.show', $reference->name, $reference->id) !!}</td>
              <td>{!! $reference->company !!}</td>
              <td>{!! $reference->email !!}</td>
              <td>{!! $reference->phone !!}</td>
              <td>{!! $reference->updatedBy->name !!}</td>
              <td>{!! fulldate_trans($reference->updated_at) !!}</td>
              <td class="action">
                {!! HTML::decode( HTML::link(langRoute('admin.reference.edit', array($reference->id)), '<i class="fa fa-pencil"></i>') ) !!}
                {!! HTML::decode( HTML::link(URL::route('admin.reference.delete', array($reference->id)), '<i class="fa fa-trash-o"></i>') ) !!}
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
      {!! $references->render() !!}
    </div>
  </div>
  @stop