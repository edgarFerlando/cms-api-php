@extends('backend.layout.layout')
@section('content')
<section class="content-header">
    <h1> {{{ trans('app.wallet') }}}</h1>
    <ol class="breadcrumb">
        <li><a href="{!! url(getLang() . '/admin/wallet') !!}"><i class="fa fa-bookmark"></i> {{{ trans('app.wallet') }}}</a></li>
        <li class="active">{{{ trans('app.edit') }}}</li>
    </ol>
</section>
<div class="content">
    <div class="box">
        {!! Form::open( array( 'route' => array( 'admin.wallet.update', $data->id), 'method' => 'PATCH', 'files'=>true)) !!}
        <div class="box-header with-border">
            <h3 class="box-title">{!! trans('app.edit') !!}</h3>
        </div>
        <div class="box-body">
            <div class="form-group">
                <div class="row">
                    <div class="col-lg-12">
                        {!! Form::label('type', trans('app.transaction_type')) !!}
                    </div>
                    @foreach($transaction_types as $transaction_type)
                    <div class="col-lg-1">
                        <div class="form-group {!! $errors->has('transaction_type') ? 'has-error' : '' !!}">
                            <div class="radio">
                                <label>
                                    <?php
                                        $is_checked = $transaction_type->id == val($data, 'transaction_type_id', 'transaction_type')?true:false;
                                    ?>
                                    {!! Form::radio('transaction_type', $transaction_type->id, $is_checked) !!}
                            <span>{{{ $transaction_type->title }}}</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            <div class="form-group {!! $errors->has('amount') ? 'has-error' : '' !!}">
                {!! Form::label('amount', trans('app.amount').' *') !!}
                {!! Form::text('amount', val($data, 'amount'), [ 'class' => 'form-control number', 'autocomplete' => 'off' ]) !!}
                @if ($errors->first('amount'))
                    <span class="help-block">{!! $errors->first('amount') !!}</span>
                @endif
            </div>
            <div class="form-group {!! $errors->has('wallet_category') ? 'has-error' : '' !!}">
                {!! Form::label('wallet_category', trans('app.category').' *') !!}
                {!! Form::select('wallet_category', [], val($data, 'wallet_category_id', 'wallet_category'), [ 'class' => 'selectize_wallet_category', 'autocomplete' => 'off', 'data_options' => $dataOptions, 'disableOptions' => $disableOptions, 'old' => val($data, 'category_id', 'wallet_category') ]) !!}
                @if ($errors->first('wallet_category'))
                    <span class="help-block">{!! $errors->first('wallet_category') !!}</span>
                @endif
            </div>
            <div class="form-group {!! $errors->has('notes') ? 'has-error' : '' !!}">
                {!! Form::label('notes', trans('app.notes')) !!}
                {!! Form::textarea('notes', val($data, 'notes'), [ 'class' => 'form-control', 'autocomplete' => 'off', 'rows' => 4 ]) !!}
                @if ($errors->first('notes'))
                    <span class="help-block">{!! $errors->first('notes') !!}</span>
                @endif
            </div>
            <div class="form-group {!! $errors->has('transaction_date') ? 'has-error' : '' !!}">
                {!! Form::label('transaction_date', trans('app.date').' *') !!}
                {!! 
                    Form::text('transaction_date', val($data, 'transaction_date'), [ 'class'=>'form-control bdatepicker'])
                !!}
                @if ($errors->first('transaction_date'))
                    <span class="help-block">{!! $errors->first('transaction_date') !!}</span>
                @endif
            </div>



        </div>
          <div class="box-footer">
            {!! Form::submit( trans('app.update') , array('class' => 'btn btn-success')) !!}
            {!! link_to( URL::route('admin.wallet.index'), trans('app.cancel'), array( 'class' => 'btn btn-primary' ) ) !!}
        </div>
    {!! Form::close() !!}
</div>
</div>
@stop
