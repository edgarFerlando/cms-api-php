@extends('backend.layout.layout')
@section('content')
<section class="content-header">
    <h1> {{{ trans('app.goal') }}}  </h1>
    <ol class="breadcrumb">
        <li><a href="{!! langRoute('admin.goal.index') !!}"><i class="fa fa-bookmark"></i> {{{ trans('app.goal') }}}</a></li>
        <li class="active">{{{ trans('app.show') }}}</li>
    </ol>
</section>
<div class="content">
    <div class="box"> 
        @if(!$goal['id_data']) 
            <div class="box-header with-border">
                <h3 class="box-title">{!! $goal->goal_name !!}</h3>
            </div>
            <div class="box-body">
                <div class="form-group">
                    {!! Form::label('goal_name', trans('app.goal_name')) !!}
                    <div>{!! $goal->goal_name !!}</div>
                </div>
                <div class="form-group">
                    {!! Form::label('created_date', trans('app.created_date')) !!}
                    <div>{!! $goal->created_on !!}</div>
                </div>
                <div class="form-group">
                    {!! Form::label('updated_date', trans('app.updated_date')) !!}
                    <div>{!! $goal->updated_on !!}</div>
                </div>
                <div class="form-group">
                    {!! Form::label('flag', trans('app.flag')) !!}
                    <div>{!! $goal->record_flag !!}</div>
                </div>
            </div>
            <div class="box-footer">
                {!! link_to( langRoute('admin.goal.index'), trans('app.back'), array( 'class' => 'btn btn-primary' ) ) !!}
            </div>
        @else
            <div class="box-header with-border">
                <h3 class="box-title">{!! trans('app.display') !!}</h3>
            </div>
            <div class="box-body">
                <div class="alert alert-danger">{{{ trans('app.no_results_found') }}}</div>
            </div>
            <div class="box-footer">
                {!! link_to( langRoute('admin.goal.index'), trans('app.cancel'), array( 'class' => 'btn btn-primary' ) ) !!}
            </div>
        @endif
    </div>
</div>
@stop
