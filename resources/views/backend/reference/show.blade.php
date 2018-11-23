@extends('backend.layout.layout')
@section('content')
<section class="content-header">
    <h1> {{{ trans('app.reference') }}}  </h1>
    <ol class="breadcrumb">
        <li><a href="{!! langRoute('admin.reference.index') !!}"><i class="fa fa-bookmark"></i> {{{ trans('app.reference') }}}</a></li>
        <li class="active">{{{ trans('app.show') }}}</li>
    </ol>
</section>
<div class="content">
    <div class="box"> 
        @if(!$reference['id_data']) 
            <div class="box-header with-border">
                <h3 class="box-title">{!! $reference->code !!}</h3>
            </div>
            <div class="box-body">
                <div class="form-group">
                    {!! Form::label('name', trans('app.name')) !!}
                    <div>{!! $reference->name !!}</div>
                </div>
                <div class="form-group">
                    {!! Form::label('company', trans('app.company')) !!}
                    <div>{!! $reference->company !!}</div>
                </div>
                <div class="form-group">
                    {!! Form::label('email', trans('app.email')) !!}
                    <div>{!! $reference->email !!}</div>
                </div>
                <div class="form-group">
                    {!! Form::label('phone', trans('app.phone')) !!}
                    <div>{!! $reference->phone !!}</div>
                </div>
                <div class="form-group">
                    {!! Form::label('created_by', trans('app.created_by')) !!}
                    <div>{!! $reference->createdBy->name !!}</div>
                </div>
                <div class="form-group">
                    {!! Form::label('created_date', trans('app.created_date')) !!}
                    <div>{!! fulldate_trans($reference->created_at) !!}</div>
                </div>
                <div class="form-group">
                    {!! Form::label('updated_by', trans('app.updated_by')) !!}
                    <div>{!! $reference->updatedBy->name !!}</div>
                </div>
                <div class="form-group">
                    {!! Form::label('updated_date', trans('app.updated_date')) !!}
                    <div>{!! fulldate_trans($reference->updated_at) !!}</div>
                </div>
            </div>
            <div class="box-footer">
                {!! link_to( langRoute('admin.reference.index'), trans('app.back'), array( 'class' => 'btn btn-primary' ) ) !!}
            </div>
        @else
            <div class="box-header with-border">
                <h3 class="box-title">{!! trans('app.display') !!}</h3>
            </div>
            <div class="box-body">
                <div class="alert alert-danger">{{{ trans('app.no_results_found') }}}</div>
            </div>
            <div class="box-footer">
                {!! link_to( langRoute('admin.reference.index'), trans('app.cancel'), array( 'class' => 'btn btn-primary' ) ) !!}
            </div>
        @endif
    </div>
</div>
@stop
