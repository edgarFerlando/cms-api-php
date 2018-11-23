@extends('backend.layout.layout')
@section('content')
<section class="content-header">
  <h1>
    {!! trans('app.bank') !!} 
    <ul class="action list-inline">
      <li><a href="{!! url(getLang() . '/admin/bank/create') !!}"><i class="fa fa-plus-square"></i> {!! trans('app.bank_add') !!}</a></li>
    </ul>
  </h1>
  <ol class="breadcrumb">
    <li><a href="{!! URL::route('admin.dashboard') !!}">{!! trans('app.dashboard') !!}</a></li>
    <li class="active">{!! trans('app.bank') !!}</li>
  </ol>
</section>

<div class="content">
    {!! Notification::showAll() !!}
    <div class="box">  
      <div class="box-body">
        @if($banks->count())
        <div class="table-responsive">
          <table class="table table-striped">
            <thead>
              <tr>
                <th>{!! trans('app.title') !!}</th>
                <th>{!! trans('app.is_status') !!}</th>
                <th>{!! trans('app.action') !!}</th>
              </tr>
            </thead>
            <tbody>
              @foreach( $banks as $bank )
              <tr>
                <td><a href="{{ action('Backend\BankController@show',[$bank->id]) }}">{!! $bank->title !!}</a></td>
                <td>
                  @if($bank->is_status == 1)
                    Ditampilkan
                  @else
                    Belum Ditampilkan
                  @endif
                </td>
                <td class="action">
                  {{-- @if( $bank->id != 1 || Auth::user()->hasRole('admin')) --}}
                    <a href="{!! url(getLang() . '/admin/bank/' . $bank->id . '/edit') !!}"><i class="fa fa-pencil"></i></a>
                    <a href="{!! url(getLang() . '/admin/bank/' . $bank->id . '/confirm_delete') !!}"><i class="fa fa-trash-o"></i></a>
                  {{-- @endif --}}
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
        {!! $banks->render() !!}
      </div>
  </div>

@stop