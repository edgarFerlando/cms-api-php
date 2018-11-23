@extends('backend.layout.layout')
@section('content')
<section class="content-header">
  <h1>
    {!! trans('app.portofolio_detail') !!} 
    <ul class="action list-inline">
      <li>{!! HTML::decode( HTML::link(langRoute('admin.detail.portofolio.create'), '<i class="fa fa-plus-square"></i>'. trans('app.add_portofolio_detail')) ) !!}</li>
    </ul>
  </h1>
  <ol class="breadcrumb">
    <li><a href="{!! URL::route('admin.dashboard') !!}">{!! trans('app.dashboard') !!}</a></li>
    <li class="active">{!! trans('app.portofolio_detail') !!}</li>
  </ol>
</section>
<div class="content">
  {!! Notification::showAll() !!}
  <div class="box">  
    <div class="box-body">
      @if($portofolioDetails->count())
      <div class="table-responsive">
        <table class="table table-striped">
          <thead>
            <tr>
              <th>{!! trans('app.name') !!}</th>
              <th>{!! trans('app.portofolio_name') !!}</th>
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
            @foreach( $portofolioDetails as $portofolioDetail )
            {{-- {!! dd($testimonial) !!} --}}
            {{-- {!! dd($testimonial) !!} --}}
            <tr>
              <td>{!! link_to_route(getLang(). '.admin.detail.portofolio.show', $portofolioDetail->detail_name, $portofolioDetail->id) !!}</td>
              <td>{!! $portofolioDetail->portofolioName !!}</td>
              <td>{!! $portofolioDetail->keterangan !!}</td>
              <td>{!! $portofolioDetail->userCreate !!}</td>
              <td>{!! $portofolioDetail->created_on !!}</td>
              <td>{!! $portofolioDetail->userUpdate !!}</td>
              <td>{!! $portofolioDetail->updated_on !!}</td>
              <td>{!! $portofolioDetail->record_flag !!}</td>
              <td class="action">
                {!! HTML::decode( HTML::link(langRoute('admin.detail.portofolio.edit', array($portofolioDetail->id)), '<i class="fa fa-pencil"></i>') ) !!}
                {!! HTML::decode( HTML::link(URL::route('admin.detail.portofolio.delete', array($portofolioDetail->id)), '<i class="fa fa-trash-o"></i>') ) !!}
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
      {!! $portofolioDetails->render() !!}
    </div>
  </div>
  @stop