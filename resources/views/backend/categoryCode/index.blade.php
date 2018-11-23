@extends('backend.layout.layout')
@section('content')
<section class="content-header">
  <h1>
    {!! trans('app.category_code') !!} 
    <ul class="action list-inline">
      <li>{!! HTML::decode( HTML::link(langRoute('admin.category.code.create'), '<i class="fa fa-plus-square"></i>'. trans('app.add_category_code')) ) !!}</li>
    </ul>
  </h1>
  <ol class="breadcrumb">
    <li><a href="{!! URL::route('admin.dashboard') !!}">{!! trans('app.dashboard') !!}</a></li>
    <li class="active">{!! trans('app.category_code') !!}</li>
  </ol>
</section>
<div class="content">
  {!! Notification::showAll() !!}
  <div class="box">  
    <div class="box-body">
      @if($categoryCodes->count())
      <div class="table-responsive">
        <table class="table table-striped">
          <thead>
            <tr>
              <th>{!! trans('app.name') !!}</th>
              <th>{!! trans('app.keterangan') !!}</th>
              <th>{!! trans('app.created_by') !!}</th>
              <th>{!! trans('app.created_date') !!}</th>
              <th>{!! trans('app.updated_by') !!}</th>
              <th>{!! trans('app.updated_date') !!}</th>
              <th>{!! trans('app.flag') !!}</th>
              <th>{!! trans('app.action') !!}</th>
            </tr>
          </thead>
          <tbody>
            @foreach( $categoryCodes as $categoryCode )
            {{-- {!! dd($testimonial) !!} --}}
            {{-- {!! dd($testimonial) !!} --}}
            <tr>
              <td>{!! link_to_route(getLang(). '.admin.category.code.show', $categoryCode->category_name, $categoryCode->category_code) !!}</td>
              <td>{!! $categoryCode->keterangan !!}</td>
              <td>{!! $categoryCode->userCreate !!}</td>
              <td>{!! $categoryCode->created_on !!}</td>
              <td>{!! $categoryCode->userUpdate !!}</td>
              <td>{!! $categoryCode->updated_on !!}</td>
              <td>{!! $categoryCode->record_flag !!}</td>
              <td class="action">
                {!! HTML::decode( HTML::link(langRoute('admin.category.code.edit', array($categoryCode->category_code)), '<i class="fa fa-pencil"></i>') ) !!}
                {!! HTML::decode( HTML::link(URL::route('admin.category.code.delete', array($categoryCode->category_code)), '<i class="fa fa-trash-o"></i>') ) !!}
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
      {!! $categoryCodes->render() !!}
    </div>
  </div>
  @stop