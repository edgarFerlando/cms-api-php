@extends('backend.layout.layout')
@section('content')
<section class="content-header">
    <h1> {{{ trans('app.grade') }}}  </h1>
    <ol class="breadcrumb">
        <li><a href="{!! langRoute('admin.grade.index') !!}"><i class="fa fa-bookmark"></i> {{{ trans('app.grade') }}}</a></li>
        <li class="active">{{{ trans('app.show') }}}</li>
    </ol>
</section>
<div class="content">
    <div class="box"> 
        @if(!$grade['id_data']) 
            <div class="box-header with-border">
                <h3 class="box-title">{!! $grade->grade_name !!}</h3>
            </div>
            <div class="box-body">
                <div class="form-group">
                    {!! Form::label('grade_name', trans('app.grade_name')) !!}
                    <div>{!! $grade->grade_name !!}</div>
                </div>
                <div class="form-group">
                    {!! Form::label('created_date', trans('app.created_date')) !!}
                    <div>{!! $grade->created_on !!}</div>
                </div>
                <div class="form-group">
                    {!! Form::label('updated_date', trans('app.updated_date')) !!}
                    <div>{!! $grade->updated_on !!}</div>
                </div>
                <div class="form-group">
                    {!! Form::label('flag', trans('app.flag')) !!}
                    <div>{!! $grade->record_flag !!}</div>
                </div>
            </div>
            <div class="box-footer">
                {!! link_to( langRoute('admin.grade.index'), trans('app.back'), array( 'class' => 'btn btn-primary' ) ) !!}
            </div>
        @else
            <div class="box-header with-border">
                <h3 class="box-title">{!! trans('app.display') !!}</h3>
            </div>
            <div class="box-body">
                <div class="alert alert-danger">{{{ trans('app.no_results_found') }}}</div>
            </div>
            <div class="box-footer">
                {!! link_to( langRoute('admin.grade.index'), trans('app.cancel'), array( 'class' => 'btn btn-primary' ) ) !!}
            </div>
        @endif
    </div>
</div>
@stop
