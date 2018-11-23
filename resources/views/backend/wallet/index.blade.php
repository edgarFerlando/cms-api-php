@extends('backend.layout.layout')
@section('content')
<section class="content-header">
  <h1>
    {!! trans('app.wallet') !!} 
    <ul class="action list-inline">
      <li>{!! HTML::decode( HTML::link(URL::route('admin.wallet.create' ), '<i class="fa fa-plus-square"></i>'. trans('app.add_transaction')) ) !!}</li>
    </ul>
  </h1>
  <ol class="breadcrumb">
    <li><a href="{!! URL::route('admin.dashboard') !!}">{!! trans('app.dashboard') !!}</a></li>
    <li class="active">{!! trans('app.wallet') !!}</li>
  </ol>
</section>
<div class="content">
  {!! Notification::showAll() !!}
  <div class="box">  
    <div class="box-body">
      @if($transactions->count())
      <div class="table-responsive">
        <table class="table table-striped">
          <thead>
            <tr>
              <th>{!! trans('app.user') !!}</th>
              <th>{!! trans('app.transaction_type') !!}</th>
              <th>{!! trans('app.category_type') !!}</th>
              <th>{!! trans('app.category') !!}</th>
              <th>{!! trans('app.amount') !!}</th>
              
              
              <!--<th>{!! trans('app.notes') !!}</th>-->
              <th>{!! trans('app.transaction_date') !!}</th>
              <!--<th>{!! trans('app.created_by') !!}</th>
              <th>{!! trans('app.created_date') !!}</th>-->
              <th>{!! trans('app.updated_by') !!}</th>
              <th>{!! trans('app.updated_date') !!}</th>
              <th>{!! trans('app.flag') !!}</th>
              <th>{!! trans('app.action') !!}</th>
            </tr>
          </thead>
          <tbody>
            @foreach( $transactions as $transaction )
            {{-- {!! dd($testimonial) !!} --}}
            {{-- {!! dd($testimonial) !!} --}}
            {{-- $transaction->transaction_type?'null nih':dd($transaction->transaction_type) --}}
        {{-- $transaction->category_type?'null nih':dd($transaction->category_type) --}}
            <tr>
              <td>{!! ucfirst($transaction->created_by_name) !!}</td>
              <td>{!! $transaction->transaction_type?$transaction->transaction_type->title:'' !!}</td>
              <td>{!! $transaction->category_type?$transaction->category_type->title:'' !!}</td>
              <td>{!! $transaction->category?$transaction->category->title:'' !!}</td>
              <td>{!! $transaction->amount !!}</td>
              
              
              <!--<td>{!! $transaction->notes !!}</td>-->
              <td>{!! date_trans($transaction->transaction_date) !!}</td>
              <!-- <td>{!! $transaction->created_by_name !!}</td>
              <td>{!! fulldate_trans($transaction->created_at) !!}</td>-->
              <td>{!! $transaction->updated_by_name !!}</td>
              <td>{!! fulldate_trans($transaction->updated_at) !!}</td>
              <td>{!! $transaction->record_flag !!}</td>
              <td class="action">
                {!! HTML::decode( HTML::link(URL::route('admin.wallet.edit', array($transaction->id)), '<i class="fa fa-pencil"></i>') ) !!}
                {!! HTML::decode( HTML::link(URL::route('admin.wallet.delete', array($transaction->id)), '<i class="fa fa-trash-o"></i>') ) !!}
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
      {!! $transactions->render() !!}
    </div>
  </div>
  @stop