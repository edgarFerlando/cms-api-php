@extends('backend.layout.layout')
@section('content')
<section class="content-header">
  <h1>
    {!! trans('app.portofolio') !!} 
    <ul class="action list-inline">
      <li>{!! HTML::decode( HTML::link(langRoute('admin.portofolio.create'), '<i class="fa fa-plus-square"></i>'. trans('app.add_portofolio')) ) !!}</li>
    </ul>
  </h1>
  <ol class="breadcrumb">
    <li><a href="{!! URL::route('admin.dashboard') !!}">{!! trans('app.dashboard') !!}</a></li>
    <li class="active">{!! trans('app.portofolio') !!}</li>
  </ol>
</section>
<div class="content">
  {!! Notification::showAll() !!}
  <div class="box">  
    <div class="box-body">
      @if($portofolios->count())
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
            @foreach( $portofolios as $portofolio )
            {{-- {!! dd($testimonial) !!} --}}
            {{-- {!! dd($testimonial) !!} --}}
            <tr>
              <td>{!! link_to_route(getLang(). '.admin.portofolio.show', $portofolio->portofolio_name, $portofolio->id) !!}</td>
              <td>{!! $portofolio->keterangan !!}</td>
              <td>{!! $portofolio->userCreate !!}</td>
              <td>{!! $portofolio->created_on !!}</td>
              <td>{!! $portofolio->userUpdate !!}</td>
              <td>{!! $portofolio->updated_on !!}</td>
              <td>{!! $portofolio->record_flag !!}</td>
              <td class="action">
                {!! HTML::decode( HTML::link(langRoute('admin.portofolio.edit', array($portofolio->id)), '<i class="fa fa-pencil"></i>') ) !!}
                {!! HTML::decode( HTML::link(URL::route('admin.portofolio.delete', array($portofolio->id)), '<i class="fa fa-trash-o"></i>') ) !!}
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
      {!! $portofolios->render() !!}
    </div>
  </div>
  @stop