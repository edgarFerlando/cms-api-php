@extends('backend.layout.layout')
@section('content')
<section class="content-header">
    <h1>{!! $attr['title'] !!}</h1>
    <ol class="breadcrumb">
        <li><a href="{!! langRoute('admin.user.role.index') !!}"><i class="fa fa-bookmark"></i> {{{ trans('app.role') }}}</a></li>
        <li class="active">{{{ trans('app.show') }}}</li>
    </ol>
</section>
<div class="content">
    <div class="box">  
        <div class="box-header with-border">
            <h3 class="box-title">{!! $attr['box_title'] !!}</h3>
        </div>
        <div class="box-body">
            <div class="form-group">
                {!! Form::label('reseller_name', trans('app.reseller_name')) !!}
                <div>{!! $balanceHistory->user->name !!}</div>
            </div>
            <div class="form-group">
                {!! Form::label('transfer_date', trans('app.transfer_date')) !!}
                <div>{!! date_trans($balanceHistory->transferred_at) !!}</div>
            </div>
            <div class="form-group">
                {!! Form::label('bank_account_name', trans('app.bank_acc_name')) !!}
                <div>{!! $balanceHistory->bank_acc_name !!}</div>
            </div>
            <div class="form-group">
                {!! Form::label('amount', trans('app.amount')) !!}
                <div>Rp {!! money($balanceHistory->amount) !!}</div>
            </div>
            <div class="form-group">
                {!! Form::label('prev_balance', trans('app.prev_balance')) !!}
                <div>Rp {!! money($balanceHistory->prev_balance) !!}</div>
            </div>
            <div class="form-group">
                {!! Form::label('current_balance', trans('app.current_balance')) !!}
                <div>Rp {!! money($balanceHistory->current_balance) !!}</div>
            </div>
            <div class="form-group">
                {!! Form::label('note', trans('app.note')) !!}
                <div>{!! $balanceHistory->note !!}</div>
            </div>
            <div class="form-group">
                {!! Form::label('status', trans('app.status')) !!}
                <div>{!! trans('app.'.$balanceHistory->status) !!}</div>
            </div>
            <div class="form-group">
                {!! Form::label('created_date', trans('app.created_date')) !!}
                <div>{!! fulldate_trans($balanceHistory->created_at) !!}</div>
            </div>
            <div class="form-group">
                {!! Form::label('updated_date', trans('app.updated_date')) !!}
                <div>{!! fulldate_trans($balanceHistory->updated_at) !!}</div>
            </div>
        </div>
        <div class="box-footer">
            {!! link_to( langRoute('admin.balance-history.index'), trans('app.back'), array( 'class' => 'btn btn-primary' ) ) !!}
        </div>
    </div>
</div>
@stop
