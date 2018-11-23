@extends('backend.layout.layout')
@section('content')
<section class="content-header">
    <h1> {{{ trans('app.interest_rate') }}}  </h1>
    <ol class="breadcrumb">
        <li><a href="{!! langRoute('admin.settings.finance.interest-rate.index') !!}"><i class="fa fa-bookmark"></i> {{{ trans('app.interest_rate') }}}</a></li>
        <li class="active">{{{ trans('app.show') }}}</li>
    </ol>
</section>
<div class="content">
    <div class="box">  
        <div class="box-header with-border">
            <h3 class="box-title">{!! $interestRate->product->title.' '.$interestRate->rate !!}%</h3>
        </div>
        <div class="box-body">
            <div class="form-group">
                {!! Form::label('product', trans('app.product')) !!}
                <div>{!! $interestRate->product->title !!}</div>
            </div>
            <div class="form-group">
                {!! Form::label('rate', '% '.trans('app.rate')) !!}
                <div>{!! $interestRate->rate !!}</div>
            </div>
            <div class="form-group">
                {!! Form::label('created_date', trans('app.created_date')) !!}
                <div>{!! $interestRate->created_at !!}</div>
            </div>
            <div class="form-group">
                {!! Form::label('updated_date', trans('app.updated_date')) !!}
                <div>{!! $interestRate->updated_at !!}</div>
            </div>
        </div>
        <div class="box-footer">
            {!! link_to( langRoute('admin.settings.finance.interest-rate.index'), trans('app.back'), array( 'class' => 'btn btn-primary' ) ) !!}
        </div>
    </div>
</div>
@stop