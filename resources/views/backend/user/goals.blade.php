@extends('backend.layout.layout')
@section('content')
<?php
$grade = '';
$goals = '';
//$goal->createdBy->name
//fulldate_trans($goal->created_at
$created_by = '';
$created_at = '';
$last_updated_by = '';
$last_updated_at = '';
foreach($user->goalGrade as $goalGrade){
    $grade = $goalGrade->grade->grade_name;
    $goals .= '<dd><i class="fa fa-check" aria-hidden="true"></i>&nbsp;&nbsp;'.$goalGrade->goal->goal_name.'</dd>';

    $created_by = $goalGrade->createdBy->name;
    $created_at = fulldate_trans($goalGrade->created_at);
    $last_updated_by = $goalGrade->updatedBy->name;
    $last_updated_at = fulldate_trans($goalGrade->updated_at);
}
if($goals != '')
    $goals = '<dl>'.$goals.'</dl>';
?>
<section class="content-header">
    <h1> {{{ trans('app.user_goal') }}}  </h1>
    <ol class="breadcrumb">
        <li><a href="{!! langRoute('admin.user.index') !!}"><i class="fa fa-bookmark"></i> {{{ trans('app.user') }}}</a></li>
        <li class="active">{{{ trans('app.show') }}}</li>
    </ol>
</section>
<div class="content">
    <div class="box">  
        <div class="box-header with-border">
            <h3 class="box-title">{!! $user->name !!}</h3>
        </div>
        <div class="box-body">
            <div class="form-group">
                {!! Form::label('grade', trans('app.grade')) !!}
                <div>{!! $grade !!}</div>
            </div>
            <div class="form-group">
                {!! Form::label('goals', trans('app.goals')) !!}
                {!! $goals !!}
            </div>
            <div class="form-group">
                {!! Form::label('created_by', trans('app.created_by')) !!}
                <div>{!! $created_by !!}</div>
            </div>
            <div class="form-group">
                {!! Form::label('created_at', trans('app.created_date')) !!}
                <div>{!! $created_at !!}</div>
            </div>
            <div class="form-group">
                {!! Form::label('updated_by', trans('app.updated_by')) !!}
                <div>{!! $last_updated_by !!}</div>
            </div>
            <div class="form-group">
                {!! Form::label('updated_at', trans('app.updated_date')) !!}
                <div>{!! $last_updated_at !!}</div>
            </div>
        </div>
        <div class="box-footer">
            {!! link_to( langRoute('admin.user.index'), trans('app.back'), array( 'class' => 'btn btn-primary' ) ) !!}
        </div>
    </div>
</div>
@stop
