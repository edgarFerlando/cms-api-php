@extends('backend.layout.layout')
@section('content')
<section class="content-header">
    <h1> {{{ trans('app.goal') }}}</h1>
    <ol class="breadcrumb">
        <li><a href="{!! url(getLang() . '/admin/goal') !!}"><i class="fa fa-bookmark"></i> {{{ trans('app.goal') }}}</a></li>
        <li class="active">{{{ trans('app.edit') }}}</li>
    </ol>
</section>
<div class="content">
    <div class="box">
        @if(!$goal['id_data'])
        {!! Form::open( array( 'route' => array( getLang() . '.admin.goal.update', $goal->id), 'method' => 'PATCH', 'files'=>true)) !!}
        <div class="box-header with-border">
            <h3 class="box-title">{!! trans('app.edit') !!}</h3>
        </div>
        <div class="box-body">
            <div class="form-group {!! $errors->has('goal_name') ? 'has-error' : '' !!}">
                {!! Form::label('goal_name', trans('app.goal_name').' *') !!}
                {!! Form::text('goal_name', val($goal, 'goal_name'), [ 'class' => 'form-control', 'autocomplete' => 'off' ]) !!}
                @if ($errors->first('goal_name'))
                    <span class="help-block">{!! $errors->first('goal_name') !!}</span>
                @endif
            </div>
            <div class="form-group {!! $errors->has('goal_icon') ? 'has-error' : '' !!}">
                {!! Form::label('goal_icon', trans('app.goal_icon').' *') !!}
                {!! Form::cke_image('goal_icon', val($goal, 'icon_path'), [ 'class'=>'is_cke']) !!}
                @if ($errors->first('goal_icon'))
                    <span class="help-block">{!! $errors->first('goal_icon') !!}</span>
                @endif
            </div>
            <div class="form-group {!! $errors->has('goal_thumb') ? 'has-error' : '' !!}">
                {!! Form::label('goal_thumb', trans('app.goal_thumb').' *') !!}
                {!! Form::cke_image('goal_thumb', val($goal, 'thumb_path') , [ 'class'=>'is_cke']) !!}
                @if ($errors->first('goal_thumb'))
                    <span class="help-block">{!! $errors->first('goal_thumb') !!}</span>
                @endif
            </div>
            <div class="form-group {!! $errors->has('goal_position_under_grade') ? 'has-error' : '' !!}">
                {!! Form::label('goal_position_under_grade', trans('app.goal_position_under_grade').' *') !!}
                {!!  Form::select('goal_position_under_grade', $grade_options, val($goal, 'position_under_grade_id'), [ 'class' => 'form-control' ]) !!}
                @if ($errors->first('goal_position_under_grade'))
                    <span class="help-block">{!! $errors->first('goal_position_under_grade') !!}</span>
                @endif
            </div>
        </div>
          <div class="box-footer">
            {!! Form::submit( trans('app.update') , array('class' => 'btn btn-success')) !!}
            {!! link_to( langRoute('admin.goal.index'), trans('app.cancel'), array( 'class' => 'btn btn-primary' ) ) !!}
        </div>
    {!! Form::close() !!}
    @else
        <div class="box-header with-border">
            <h3 class="box-title">{!! trans('app.edit') !!}</h3>
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
