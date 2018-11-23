@extends('backend.layout.layout')
@section('content')
<section class="content-header">
    <h1> {{{ trans('app.code') }}}  </h1>
    <ol class="breadcrumb">
        <li><a href="{!! langRoute('admin.code.index') !!}"><i class="fa fa-bookmark"></i> {{{ trans('app.code') }}}</a></li>
        <li class="active">{{{ trans('app.show') }}}</li>
    </ol>
</section>
<div class="content">
    <div class="box">  
        <div class="box-header with-border">
            <h3 class="box-title">{!! $code->code_name !!}</h3>
        </div>
        <div class="box-body">
            <div class="form-group">
                {!! Form::label('name', trans('app.name')) !!}
                <div>{!! $code->code_name !!}</div>
            </div>
            <div class="form-group">
                {!! Form::label('category_name', trans('app.category_name')) !!}
                <div>{!! $code->category_name !!}</div>
            </div>
            <div class="form-group">
                {!! Form::label('keterangan', trans('app.keterangan')) !!}
                <div>{!! $code->keterangan !!}</div>
            </div>
            <div class="form-group">
                {!! Form::label('created_date', trans('app.created_date')) !!}
                <div>{!! $code->created_on !!}</div>
            </div>
            <div class="form-group">
                {!! Form::label('updated_date', trans('app.updated_date')) !!}
                <div>{!! $code->updated_on !!}</div>
            </div>
            <div class="form-group">
                {!! Form::label('flag', trans('app.flag')) !!}
                <div>{!! $code->record_flag !!}</div>
            </div>
        </div>
        <div class="box-footer">
            {!! link_to( langRoute('admin.code.index'), trans('app.back'), array( 'class' => 'btn btn-primary' ) ) !!}
        </div>
    </div>
</div>
@stop
