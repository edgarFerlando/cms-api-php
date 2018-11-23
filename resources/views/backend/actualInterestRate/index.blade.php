@extends('backend/layout/layout')
@section('content')
<section class="content-header">
  <h1>
    {!! trans('app.actual_interest_rate') !!} 
    <ul class="action list-inline">
      <li>{!! HTML::decode( HTML::link(langRoute('admin.settings.finance.actual-interest-rate.create'), '<i class="fa fa-plus-square"></i>'. trans('app.add_actual_interest_rate')) ) !!}</li>
    </ul>
  </h1>
  <ol class="breadcrumb">
    <li><a href="{!! URL::route('admin.dashboard') !!}">{!! trans('app.dashboard') !!}</a></li>
    <li class="active">{!! trans('app.actual_interest_rate') !!}</li>
  </ol>
</section>
<div class="content">
  <div class="box">  
    <div class="box-body">
    {!! Notification::showAll() !!}
    @if($actualInterestRates->count())
    <div class="table-responsive">
      <table class="table table-striped">
        <thead>
          <tr>
            <th>{{{ trans('app.product') }}}</th>
            <th>% {{{ trans('app.rate') }}}</th>
            <th>{{{ trans('app.period') }}}</th>
            <th>% {{{ trans('app.actual_rate') }}}</th>
            <th>{{{ trans('app.updated_by') }}}</th>
            <th>{{{ trans('app.updated_date') }}}</th>
            <th>{{{ trans('app.action') }}}</th>
          </tr>
        </thead>
        <tbody>
          @foreach( $actualInterestRates as $actualInterestRate )
          <?php
            $rate = $actualInterestRate->interest_rate->rate;
            $actual_rate = $actualInterestRate->rate;
            $rate_vw = money($rate, 2);
            $actual_rate_vw = money($actual_rate, 2);
            $actual_rate_color = ($actual_rate<$rate)?'#DD4B39':(($actual_rate>$rate)?'#00A65A':'#CFB900');
          ?>
            <tr>
              <td>{!! $actualInterestRate->interest_rate->product->title !!}</td>
              <td class="text-right">{!! $rate_vw !!}</td>
              <td>{!! \Carbon\Carbon::parse($actualInterestRate->period)->format('M Y') !!}</td>
              <td class="text-right" style="color:{{ $actual_rate_color }}">{!!  $actual_rate_vw !!}</td>
              <td>{!! $actualInterestRate->updated_by_name !!}</td>
              <td>{!! fulldate_trans($actualInterestRate->updated_at) !!}</td>
              <td class="action">
                {!! HTML::decode( HTML::link(langRoute('admin.settings.finance.actual-interest-rate.edit', array($actualInterestRate->id)), '<i class="fa fa-pencil"></i>') ) !!}
                {!! HTML::decode( HTML::link(URL::route('admin.settings.finance.actual-interest-rate.delete', array($actualInterestRate->id)), '<i class="fa fa-trash-o"></i>') ) !!}
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
    {!! $actualInterestRates->render() !!}
  </div>

</div>
@stop