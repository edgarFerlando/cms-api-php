@extends('backend.layout.layout')
@section('content')
<section class="content-header">
    <h1> {{{ trans('app.profile') }}}</h1>
    <ol class="breadcrumb">
        <li><a href="{!! url(getLang() . '/admin/profile') !!}"><i class="fa fa-bookmark"></i> {{{ trans('app.profile') }}}</a></li>
    </ol>
</section>
{!! Notification::showAll() !!}
<div class="content">
    <div class="box">
    	{!! Form::open([ 'url' => LangUrl('admin/profile/update') ]) !!}
        <div class="box-header with-border">
            <h3 class="box-title">{!! trans('app.my_profile') !!}</h3>
        </div>
        <div class="box-body">
            <div class="form-group {!! $errors->has('first_name') ? 'has-error' : '' !!}">
                {!! Form::label('first_name', trans('app.first_name').' *') !!}
                {!! Form::text('first_name', val($user, 'name'), [ 'class' => 'form-control', 'autocomplete' => 'off' ]) !!}
                @if ($errors->first('first_name'))
                    <span class="help-block">{!! $errors->first('first_name') !!}</span>
                @endif
            </div>
            <div class="form-group {!! $errors->has('last_name') ? 'has-error' : '' !!}">
                {!! Form::label('last_name', trans('app.last_name').' *') !!}
                {!! Form::text('last_name', val($userMeta, 'last_name'), [ 'class' => 'form-control', 'autocomplete' => 'off' ]) !!}
                @if ($errors->first('last_name'))
                    <span class="help-block">{!! $errors->first('last_name') !!}</span>
                @endif
            </div>
            {!! Form::hidden('full_name', '', [ 'class' => '' ]) !!}
            <?php
				$male = trans('app.male');
				$female = trans('app.female');
			?>
            <div class="form-group {!! $errors->has('gender') ? 'has-error' : '' !!}">
                {!! Form::label('gender', trans('app.gender').' *') !!}
                {!! Form::select('gender', ['M'=> $male,'F'=> $female ], val($userMeta, 'gender'), [ 'class' => 'selectize', 'autocomplete' => 'off' ]) !!}
                @if ($errors->first('gender'))
                    <span class="help-block">{!! $errors->first('gender') !!}</span>
                @endif
            </div> 
            <div class="form-group {!! $errors->has('date_of_birth') ? 'has-error' : '' !!}">
                {!! Form::label('date_of_birth', trans('app.date_of_birth')) !!}
                {!! Form::text( 'date_of_birth', val($userMeta, 'date_of_birth'), [ 'class' => 'form-control bdatepicker'] ) !!}
                @if ($errors->first('date_of_birth'))
                    <span class="help-block">{!! $errors->first('date_of_birth') !!}</span>
                @endif
            </div>
            <div class="form-group {!! $errors->has('phone') ? 'has-error' : '' !!}">
                {!! Form::label('phone', trans('app.phone')) !!}
                {!! Form::text( 'phone', val($userMeta, 'phone'), ['class' => 'form-control', 'autocomplete' => 'off'] ) !!}
                @if ($errors->first('phone'))
                    <span class="help-block">{!! $errors->first('phone') !!}</span>
                @endif
            </div>
            <div class="form-group {!! $errors->has('address') ? 'has-error' : '' !!}">
                {!! Form::label('address', trans('app.address')) !!}
                {!! Form::ckeditor('address', val($userMeta, 'address'), [ 'class'=>'form-control', 'height' => '200'], $errors) !!}
                @if ($errors->first('address'))
                    <span class="address">{!! $errors->first('address') !!}</span>
                @endif
            </div>
        </div>

        <div class="box-footer">
            {!! Form::submit( trans('app.update') , array('class' => 'btn btn-success')) !!}
            {!! link_to( getLang().'/admin/dashboard', trans('app.cancel'), array( 'class' => 'btn btn-primary' ) ) !!}
        </div>
    	{!! Form::close() !!}
	</div>
</div>
@stop