@extends('backend.layout.layout')
@section('content')
<section class="content-header">
    <h1> {{{ trans('app.category_code') }}}</h1>
    <ol class="breadcrumb">
        <li><a href="{!! url(getLang() . '/admin/category/code') !!}"><i class="fa fa-bookmark"></i> {{{ trans('app.category_code') }}}</a></li>
        <li class="active">{{{ trans('app.edit') }}}</li>
    </ol>
</section>
<div class="content">
    <div class="box">
        {!! Form::open( array( 'route' => array( getLang() . '.admin.category.code.update', $categoryCode->category_code), 'method' => 'PATCH', 'files'=>true)) !!}
        <div class="box-header with-border">
            <h3 class="box-title">{!! trans('app.edit') !!}</h3>
        </div>
        <div class="box-body">
            <div class="form-group {!! $errors->has('category_name') ? 'has-error' : '' !!}">
                {!! Form::label('category_name', trans('app.category_name').' *') !!}
                {!! Form::text('category_name', $categoryCode->category_name, [ 'class' => 'form-control', 'autocomplete' => 'off' ]) !!}
                @if ($errors->first('category_name'))
                    <span class="help-block">{!! $errors->first('category_name') !!}</span>
                @endif
            </div>
            <div class="form-group {!! $errors->has('keterangan') ? 'has-error' : '' !!}">
                {!! Form::label('keterangan', trans('app.keterangan')) !!}
                {!! Form::text('keterangan', $categoryCode->keterangan, [ 'class' => 'form-control', 'autocomplete' => 'off' ]) !!}
                @if ($errors->first('keterangan'))
                    <span class="help-block">{!! $errors->first('keterangan') !!}</span>
                @endif
            </div>
        </div>
          <div class="box-footer">
            {!! Form::submit( trans('app.update') , array('class' => 'btn btn-success')) !!}
            {!! link_to( langRoute('admin.testimoni.index'), trans('app.cancel'), array( 'class' => 'btn btn-primary' ) ) !!}
        </div>
    {!! Form::close() !!}
</div>
</div>
@stop
