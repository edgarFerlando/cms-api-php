@extends('backend.layout.layout')
@section('content')
<section class="content-header">
  <h1>
    {!! trans('app.referral') !!} 
  </h1>
  <ol class="breadcrumb">
    <li><a href="{!! URL::route('admin.dashboard') !!}">{!! trans('app.dashboard') !!}</a></li>
    <li class="active">{!! trans('app.referral') !!}</li>
  </ol>
</section>
<div class="content">
  {!! Notification::showAll() !!}
  <div class="box">  
    <div class="box-body">
      <div class="row filter-form">
        {!! Form::open([ 'route' => [ 'admin.referrals.filter'] ]) !!}
        <div class="col-xs-3 clear-right">
          <div class="form-group">
            {!! Form::label('affiliate_name', trans('app.affiliate_name')) !!}
            {!!  Form::select('affiliate_name', $affiliate_name_options, Input::get('affiliate_name'), [ 'class' => 'selectize' ]) !!}
          </div>
        </div>
        <div class="col-xs-3 clear-right">
          <div class="form-group">
            {!! Form::label('periode', trans('app.periode')) !!}
            {!! Form::text('periode', Input::get('periode'), [ 'class'  => 'form-control bdatepicker-month', 'autocomplete' => 'off'], $errors) !!}
          </div>
        </div>
        <div class="col-xs-3 clear-right">
          <div class="form-group">
            {!! Form::label('status', trans('app.status')) !!}
            {!!  Form::select('status', $status_options, Input::get('status'), [ 'class' => 'selectize' ]) !!}
          </div>
        </div>
        <div class="col-xs-2 clear-right">
          <div class="form-group action-tool">
            {!! Form::submit(trans('app.filter'), array('class' => 'btn btn-success')) !!}
            {!! HTML::link(langurl('admin/referrals'),trans('app.reset'), array('class' => 'btn btn-default')) !!}
          </div>
        </div>
        {!! Form::close() !!}
      </div>

      <div class="table-responsive">
        <table class="table table-striped">
          <thead>
            <tr>
              <th>{!! trans('app.affiliate_name') !!}</th>
              <th>{!! trans('app.booking_no') !!}</th>
              <th>{!! trans('app.total') !!}</th>
              <th>{!! trans('app.booking_status') !!}</th>
              <th>{!! trans('app.booking_date') !!}</th>
              <!-- <th>{!! trans('app.action') !!}</th> -->
            </tr>
          </thead>
          <tbody>
            <?php
              $grand_total_all = 0;
            ?>
            @if($referrals->count()) 
              @foreach( $referrals as $referral )
                <?php
                  $grand_total = 0;
                  $bookingDetails = [];
                  if(count($referral->booking->allBookingConfirmedDetails)){
                    $bookingDetails = $referral->booking->allBookingConfirmedDetails;
                  }elseif(count($referral->booking->allBookingDetails)){
                    $bookingDetails = $referral->booking->allBookingDetails;
                  }

                  foreach($bookingDetails as $detail){
                    switch ($detail->post_type) {
                      case 'hotel':
                        $price_n_total_lbl = price_n_total_lbl([
                            'checkin' => $detail->check_in,
                            'checkout' => $detail->check_out,
                            'price' => $detail->price,
                            'weekend_price' => $detail->weekend_price,
                            'quantity' => $detail->no_of_rooms
                          ]);
                          $grand_total += $price_n_total_lbl['total'];
                        break;
                      case 'playground':
                          $price_n_total_lbl = playground_price_n_total_lbl([
                            'playground_visit_date' => $detail->check_in,
                            'price' => $detail->price,
                            'weekend_price' => $detail->weekend_price,
                            'quantity' => $detail->qty
                          ]);
                          $grand_total += $price_n_total_lbl['total']; 
                        break;
                    }
                  }
                  $grand_total_all += $grand_total;
                ?>
                <tr>
                  <td>{!! $referral->affiliate->name !!}</td>
                  <td>{!! link_to_route(getLang(). '.admin.order.hotel.show', $referral->booking->booking_no!=''?$referral->booking->booking_no:trans('app.show'), $referral->booking_id, [ 'target' => '_blank' ]) !!}</td>
                  <td class="text-right">{!! money($grand_total) !!}</td>
                  <td>{!! ucfirst($referral->booking->bookingStatus->name) !!}</td>
                  <td>{!! fulldate_trans($referral->booking->created_at) !!}</td>
                  <!-- <td class="action">
                    @if( $referral->status != 'confirmed' )
                      {-- HTML::decode( HTML::link(langRoute('admin.referrals.edit', array($referral->id)), '<i class="fa fa-pencil"></i>') ) --}
                      {-- HTML::decode( HTML::link(URL::route('admin.referrals.delete', array($referral->id)), '<i class="fa fa-trash-o"></i>') ) --}
                    @endif
                  </td>-->
                </tr>
              @endforeach
            @else
              <td colspan="5">{!! trans('app.no_results_found') !!}</td>
            @endif
          </tbody>
          <tfoot>
            <td colspan="2">&nbsp;</td>
            <td class="text-right">{!! money($grand_total_all) !!}</td>
            <td colspan="2">&nbsp;</td>
          </tfoot>
        </table>
      </div>
    </div>
  </div>
  <div class="text-center">
    {!! $referrals->render() !!}
  </div>
</div>
@stop