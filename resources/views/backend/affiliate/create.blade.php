@extends('backend.layout.layout')
@section('content')
<section class="content-header">
	<h1>{!! trans('app.top_up_balance') !!}</h1>
	<ol class="breadcrumb">
		<li><a href="{!! url(getLang() . '/balance-history') !!}"><i class="fa fa-bookmark"></i> {!! trans('app.balance_history') !!}</a></li>
		<li class="active">{!! trans('app.add_new') !!}</li>
	</ol>
</section>
<div class="content">
	<div class="box">  
		{!! Form::open(array('action' => '\App\Http\Controllers\Backend\BalanceHistoryController@store')) !!}
		<div class="box-header with-border">
            <h3 class="box-title">{!! trans('app.add_new') !!}</h3>
        </div>
		<div class="box-body">
			<div class="form-group {!! $errors->has('top_up_reseller') ? 'has-error' : '' !!}">
				{!! Form::label('top_up_reseller', trans('app.reseller_name').' *') !!}
				<input type="text" name = "top_up_reseller" value= "" class="browse-users" autocomplete="off">
				<input type="hidden" name = "top_up_reseller_init_val" value= "{!! Input::old('top_up_reseller') !!}" >
				@if ($errors->first('top_up_reseller'))
					<span class="help-block">{!! $errors->first('top_up_reseller') !!}</span>
				@endif
			</div>
			<div class="form-group {!! $errors->has('top_up_transfer_date') ? 'has-error' : '' !!}">
				{!! Form::label('top_up_transfer_date', trans('app.transfer_date').' *') !!}
				{!! Form::text('top_up_transfer_date', Input::old('top_up_transfer_date'), [ 'class' => 'form-control fdatepicker', 'autocomplete' => 'off' ]) !!}
				@if ($errors->first('top_up_transfer_date'))
					<span class="help-block">{!! $errors->first('top_up_transfer_date') !!}</span>
				@endif
			</div>
			<div class="form-group {!! $errors->has('top_up_bank_acc_name') ? 'has-error' : '' !!}">
				{!! Form::label('top_up_bank_acc_name', trans('app.bank_acc_name').' *') !!}
				{!! Form::text('top_up_bank_acc_name', Input::old('top_up_bank_acc_name'), [ 'class' => 'form-control', 'autocomplete' => 'off' ]) !!}
				@if ($errors->first('top_up_bank_acc_name'))
					<span class="help-block">{!! $errors->first('top_up_bank_acc_name') !!}</span>
				@endif
			</div>
			<div class="form-group {!! $errors->has('top_up_amount') ? 'has-error' : '' !!}">
				{!! Form::label('top_up_amount', trans('app.amount').' *') !!}
				{!! Form::text('top_up_amount', Input::old('top_up_amount'), [ 'class' => 'form-control number', 'autocomplete' => 'off' ]) !!}
				@if ($errors->first('top_up_amount'))
					<span class="help-block">{!! $errors->first('top_up_amount') !!}</span>
				@endif
			</div>
			<div class="form-group {!! $errors->has('top_up_note') ? 'has-error' : '' !!}">
				{!! Form::label('top_up_note', trans('app.note').' *') !!}
				{!! Form::textarea('top_up_note', Input::old('top_up_note'), [ 'class' => 'form-control', 'autocomplete' => 'off', 'rows' => 4 ]) !!}
				@if ($errors->first('top_up_note'))
					<span class="help-block">{!! $errors->first('top_up_note') !!}</span>
				@endif
			</div>
			<div class="form-group">
				{!! Form::label('top_up_status', trans('app.status')) !!}
				{!!  Form::select('top_up_status', [ 'waiting_for_confirmation' => trans('app.waiting_for_confirmation'), 'confirmed' => trans('app.confirmed') ], Input::old('top_up_status'), [ 'class' => 'form-control' ]) !!}
				@if ($errors->first('group'))
					<span class="help-block">{!! $errors->first('group') !!}</span>
				@endif
			</div>
		</div>
		<div class="box-footer">
			{!! Form::submit(trans('app.save'), array('class' => 'btn btn-success')) !!}
		</div>
		{!! Form::close() !!}
		</div>
</div>
@stop
