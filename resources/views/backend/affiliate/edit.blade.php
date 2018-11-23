@extends('backend.layout.layout')
@section('content')
<section class="content-header">
    <h1>{!! trans('app.payment_confirmation') !!}</h1>
    <ol class="breadcrumb">
        <li><a href="{!! url(getLang() . '/admin/payment-confirmations') !!}"><i class="fa fa-bookmark"></i> {!! trans('app.payment_confirmation') !!}</a></li>
        <li class="active">{!! trans('app.edit') !!}</li>
    </ol>
</section>
<div class="content">
    <div class="box">  
        {!! Form::open( array( 'route' => array( getLang() . '.admin.payment-confirmation.update', $paymentConfirmation->id), 'method' => 'PATCH')) !!}
        <div class="box-header with-border">
            <h3 class="box-title">{!! trans('app.edit') !!}</h3>
        </div>
        <div class="box-body">
            <div class="form-group {!! $errors->has('email') ? 'has-error' : '' !!}">
                {!! Form::label('email', trans('app.email').' *') !!}
                {!! Form::text('email',  val($paymentConfirmation, 'email'), [ 'class' => 'form-control', 'autocomplete' => 'off', 'readonly' => true ]) !!}
                @if ($errors->first('email'))
                    <span class="help-block">{!! $errors->first('email') !!}</span>
                @endif
            </div>
            <div class="form-group {!! $errors->has('booking_no') ? 'has-error' : '' !!}">
                {!! Form::label('booking_no', trans('app.booking_no').' *') !!}
                {!! Form::text('booking_no',  val($paymentConfirmation->booking, 'booking_no'), [ 'class' => 'form-control', 'readonly' => true ]) !!}
                @if ($errors->first('booking_no'))
                    <span class="help-block">{!! $errors->first('booking_no') !!}</span>
                @endif
            </div>
            <div class="form-group {!! $errors->has('paid_date') ? 'has-error' : '' !!}">
                {!! Form::label('paid_date', trans('app.paid_date').' *') !!}
                {!! Form::text('paid_date', carbon_format_view(val($paymentConfirmation, 'paid_date')), [ 'class' => 'form-control fdatepicker', 'readonly' => true ]) !!}
                @if ($errors->first('paid_date'))
                    <span class="help-block">{!! $errors->first('paid_date') !!}</span>
                @endif
            </div>
            <div class="form-group {!! $errors->has('amount') ? 'has-error' : '' !!}">
                {!! Form::label('amount', trans('app.amount').' *') !!}
                {!! Form::text('amount',  val($paymentConfirmation, 'amount'), [ 'class' => 'form-control', 'readonly' => true ]) !!}
                @if ($errors->first('amount'))
                    <span class="help-block">{!! $errors->first('amount') !!}</span>
                @endif
            </div>
            <div class="form-group {!! $errors->has('bank_account') ? 'has-error' : '' !!}">
                {!! Form::label('bank_account', trans('app.bank_account').' *') !!}
                {!! Form::text('bank_account', val($paymentConfirmation, 'account_name'), [ 'class' => 'form-control', 'readonly' => true ]) !!}
                @if ($errors->first('bank_account'))
                    <span class="help-block">{!! $errors->first('bank_account') !!}</span>
                @endif
            </div>
            <div class="form-group {!! $errors->has('customer_bank_account') ? 'has-error' : '' !!}">
                {!! Form::label('customer_bank_account', trans('app.customer_bank_account').' *') !!}
                {!! Form::text('customer_bank_account', val($paymentConfirmation->customer_bank_account, 'name'), [ 'class' => 'form-control number', 'readonly' => true ]) !!}
                @if ($errors->first('customer_bank_account'))
                    <span class="help-block">{!! $errors->first('customer_bank_account') !!}</span>
                @endif
            </div>
            <div class="form-group {!! $errors->has('account_name') ? 'has-error' : '' !!}">
                {!! Form::label('account_name', trans('app.account_name').' *') !!}
                {!! Form::text('account_name', val($paymentConfirmation, 'account_name','account_name'), [ 'class' => 'form-control number', 'readonly' => true ]) !!}
                @if ($errors->first('account_name'))
                    <span class="help-block">{!! $errors->first('account_name') !!}</span>
                @endif
            </div>
            <div class="form-group {!! $errors->has('account_no') ? 'has-error' : '' !!}">
                {!! Form::label('account_no', trans('app.account_no').' *') !!}
                {!! Form::text('account_no', val($paymentConfirmation, 'amount','account_no'), [ 'class' => 'form-control number', 'readonly' => true ]) !!}
                @if ($errors->first('account_no'))
                    <span class="help-block">{!! $errors->first('account_no') !!}</span>
                @endif
            </div>
            <div class="form-group {!! $errors->has('note') ? 'has-error' : '' !!}">
                {!! Form::label('note', trans('app.note').' *') !!}
                {!! Form::textarea('note', val($paymentConfirmation, 'note'), [ 'class' => 'form-control', 'autocomplete' => 'off', 'rows' => 4 ]) !!}
                @if ($errors->first('note'))
                    <span class="help-block">{!! $errors->first('note') !!}</span>
                @endif
            </div>
            <div class="form-group">
                {!! Form::label('status', trans('app.status')) !!}
                {!!  Form::select('status', [ 'waiting_for_confirmation' => trans('app.waiting_for_confirmation'), 'confirmed' => trans('app.confirmed') ], val($paymentConfirmation, 'status','top_up_status'), [ 'class' => 'form-control' ]) !!}
                @if ($errors->first('group'))
                    <span class="help-block">{!! $errors->first('group') !!}</span>
                @endif
            </div>
        </div>
        <div class="box-footer">
            {!! Form::submit(trans('app.save'), array('class' => 'btn btn-success')) !!}
            {!! link_to( langRoute('admin.payment-confirmation.index'), trans('app.cancel'), array( 'class' => 'btn btn-primary' ) ) !!}
        </div>
        {!! Form::close() !!}
        </div>
</div>
@stop
