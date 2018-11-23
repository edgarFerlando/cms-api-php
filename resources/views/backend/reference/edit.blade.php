@extends('backend.layout.layout')
@section('content')
<section class="content-header">
    <h1> {{{ trans('app.reference') }}}</h1>
    <ol class="breadcrumb">
        <li><a href="{!! url(getLang() . '/admin/reference') !!}"><i class="fa fa-bookmark"></i> {{{ trans('app.reference') }}}</a></li>
        <li class="active">{{{ trans('app.edit') }}}</li>
    </ol>
</section>
<div class="content">
    <div class="box">
        {!! Form::open( array( 'route' => array( getLang() . '.admin.reference.update', $reference->id), 'method' => 'PATCH', 'files'=>true)) !!}
        <div class="box-header with-border">
            <h3 class="box-title">{!! trans('app.edit') !!}</h3>
        </div>
        <div class="box-body">
            <div class="form-group {!! $errors->has('code') ? 'has-error' : '' !!}">
                {!! Form::label('code', trans('app.code').' *') !!}
                {!! Form::text('code', val($reference, 'code'), [ 'class' => 'form-control', 'autocomplete' => 'off' ]) !!}
                @if ($errors->first('code'))
                    <span class="help-block">{!! $errors->first('code') !!}</span>
                @endif
            </div>
            <div class="form-group {!! $errors->has('name') ? 'has-error' : '' !!}">
                {!! Form::label('name', trans('app.name').' *') !!}
                {!! Form::text('name', val($reference, 'name'), [ 'class' => 'form-control', 'autocomplete' => 'off' ]) !!}
                @if ($errors->first('name'))
                    <span class="help-block">{!! $errors->first('name') !!}</span>
                @endif
            </div>
            <div class="form-group {!! $errors->has('company') ? 'has-error' : '' !!}">
                {!! Form::label('company', trans('app.company')) !!}
                {!! Form::text('company', val($reference, 'company'), [ 'class' => 'form-control', 'autocomplete' => 'off' ]) !!}
                @if ($errors->first('company'))
                    <span class="help-block">{!! $errors->first('company') !!}</span>
                @endif
            </div>
            <div class="form-group {!! $errors->has('email') ? 'has-error' : '' !!}">
                {!! Form::label('email', trans('app.email').' *') !!}
                {!! Form::text('email', val($reference, 'email'), [ 'class' => 'form-control', 'autocomplete' => 'off' ]) !!}
                @if ($errors->first('email'))
                    <span class="help-block">{!! $errors->first('email') !!}</span>
                @endif
            </div>
            <div class="form-group {!! $errors->has('phone') ? 'has-error' : '' !!}">
                {!! Form::label('phone', trans('app.phone')) !!}
            {!! Form::text('phone', val($reference, 'phone'), [ 'class' => 'form-control', 'autocomplete' => 'off' ]) !!}
                @if ($errors->first('phone'))
                    <span class="help-block">{!! $errors->first('phone') !!}</span>
                @endif
            </div>
        </div>
        <div class="box-footer">
            {!! Form::submit( trans('app.update') , array('class' => 'btn btn-success')) !!}
            {!! link_to( langRoute('admin.reference.index'), trans('app.cancel'), array( 'class' => 'btn btn-primary' ) ) !!}
        </div>
    {!! Form::close() !!}
</div>
</div>
@stop
