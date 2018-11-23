@extends('backend/layout/layout')
@section('content')
<section class="content-header">
  <h1>
    {!! trans('app.interest_rate') !!} 
    <ul class="action list-inline">
      <li>{!! HTML::decode( HTML::link(langRoute('admin.settings.finance.interest-rate.create'), '<i class="fa fa-plus-square"></i>'. trans('app.add_interest_rate')) ) !!}</li>
    </ul>
  </h1>
  <ol class="breadcrumb">
    <li><a href="{!! URL::route('admin.dashboard') !!}">{!! trans('app.dashboard') !!}</a></li>
    <li class="active">{!! trans('app.interest_rate') !!}</li>
  </ol>
</section>
<div class="content">
  <div class="box">  
    <div class="box-body">
    {!! Notification::showAll() !!}
    @if($interestRates->count())
    <div class="table-responsive">
      <table class="table table-striped">
        <thead>
          <tr>
            <th>{{{ trans('app.product') }}}</th>
            <th>% {{{ trans('app.rate') }}}</th>
            <th>{{{ trans('app.background_color') }}}</th>
            <!--<th>{{{ trans('app.created_by') }}}</th>
            <th>{{{ trans('app.created_date') }}}</th>-->
            <th>{{{ trans('app.updated_by') }}}</th>
            <th>{{{ trans('app.updated_date') }}}</th>
            <th>{{{ trans('app.action') }}}</th>
          </tr>
        </thead>
        <tbody>
          @foreach( $interestRates as $interestRate )
            <tr>
              <td>{!! $interestRate->product->title !!}</td>
              <td>{!! link_to_route(getLang(). '.admin.settings.finance.interest-rate.show', money($interestRate->rate, 2), $interestRate->id) !!}</td>
              <td style="text-align:center;color:#fff;background: linear-gradient({!! $interestRate->bgcolor !!}, {!! $interestRate->bgcolor2 !!});">{!! $interestRate->bgcolor.' &nbsp;| &nbsp;'.$interestRate->bgcolor2 !!}</td>
              <!--<td>{!! $interestRate->created_by_name !!}</td>
              <td>{!! fulldate_trans($interestRate->created_at) !!}</td>-->
              <td>{!! $interestRate->updated_by_name !!}</td>
              <td>{!! fulldate_trans($interestRate->updated_at) !!}</td>
              <td class="action">
                {!! HTML::decode( HTML::link(langRoute('admin.settings.finance.interest-rate.edit', array($interestRate->id)), '<i class="fa fa-pencil"></i>') ) !!}
                {!! HTML::decode( HTML::link(URL::route('admin.settings.finance.interest-rate.delete', array($interestRate->id)), '<i class="fa fa-trash-o"></i>') ) !!}
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
    {!! $interestRates->render() !!}
  </div>

</div>
@stop