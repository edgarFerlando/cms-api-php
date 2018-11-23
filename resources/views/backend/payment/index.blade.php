@extends('backend.layout.layout')
@section('content')
<section class="content-header">
  <h1>
    Payments
    <ul class="action list-inline">
      <li>{!! HTML::decode( HTML::link(langRoute('admin.user.create'), '<i class="fa fa-plus-square"></i>'. trans('app.add_user')) ) !!}</li>
    </ul>
  </h1>
  <ol class="breadcrumb">
    <li><a href="{!! URL::route('admin.dashboard') !!}">{!! trans('app.dashboard') !!}</a></li>
    <li class="active">{!! trans('app.user') !!}</li>
  </ol>
</section>
<div class="content">
  {!! Notification::showAll() !!}
  <div class="box">  
    @if(Route::currentRouteName() == 'admin.user.filter')
      <div class="box-header with-border">
        <h3 class="box-title text-green">Result &nbsp;: &nbsp;<strong>{!! $totalItems.'</strong> item'.($totalItems > 1?'s':'') !!}</h3>
      </div>
    @endif
    <div class="box-body">

      
      <div class="table-responsive">
        <table class="table table-striped">
          <thead>

            <tr>
              <th>No</th>
              <th>Status</th>
              <th>Id Transaksi</th>
              <th>Name</th>
              <th>Transaction Date</th>
              <th>Transaction Expire</th>
              <th>Customer Account</th>
              <th>Insert Status</th>
              <th>Insert Message</th>
              <th>Insert Id</th>
              <th>Status</th>
              <th>Payment Status</th>
              <th>Payment Message</th>
              <th>Flag Type</th>
              <th>Payment Reff Id</th>
            </tr>
          </thead>
          <tbody>
            <?php
              $no = 1;
            ?>
            @foreach( $payments as $payment )
            <?php

            $status = '<span class="badge bg-red">Gagal Insert</span>';
            if($payment->insert_status == '00') {

              $status = '<span class="label label-danger">Gagal Bayar</span>';
              if($payment->payment_status == '00') {
                $status = '<span class="label label-success">Sudah Bayar</span>';
              } elseif($payment->payment_status == '') {
                $status = '<span class="label label-warning">Belum Bayar</span>';
              }

            } elseif($payment->insert_status == '') {
              $status = '<span class="badge bg-yellow">Belum Insert</span>';
            }
        

            ?>
            <tr>
              <td>{!! $no !!}</td>
              <td>{!! $status !!}</td>
              <td>{!! $payment->id !!}</td>
              <td><b>{!! $payment->name !!}</b><br/><i>{!! $payment->email !!}</i></td>
              <td>{!! $payment->transaction_date !!}</td>
              <td>{!! $payment->transaction_expire !!}</td>
              <td><b>{!! $payment->customer_account !!}</b></td>
              <td>{!! $payment->insert_status !!}</td>
              <td>{!! $payment->insert_message !!}</td>
              <td>{!! $payment->insert_id !!}</td>
              <td>{!! $payment->status !!}</td>
              <td>{!! $payment->payment_status !!}</td>
              <td>{!! $payment->payment_message !!}</td>
              <td>{!! $payment->flag_type !!}</td>
              <td>{!! $payment->payment_reff_id !!}</td>
            </tr>
            <?php
              $no++;
            ?>
            @endforeach

          </tbody>
        </table>
      </div>
      
    </div>
  </div>
  
</div>
@stop