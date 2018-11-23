@extends('backend.layout.layout')
@section('content')
<section class="content-header">
    <h1> {{{ trans('app.user') }}}</h1>
    <ol class="breadcrumb">
        <li><a href="{!! url(getLang() . '/admin/dashboard') !!}"><i class="fa fa-bookmark"></i> {{{ trans('app.dashboard') }}}</a></li>
    </ol>
</section>
<div class="content">
    <div class="box">
        {!! Form::open( [ 'url' => LangUrl('admin/user/'.Auth::user()->id.'/edit/password/update') ] ) !!}
        <div class="box-header with-border">
            <h3 class="box-title">{!! trans('app.edit') !!}</h3>
        </div>
        <div class="box-body">
            <div class="form-group {!! $errors->has('password') ? 'has-error' : '' !!}">
                {!! Form::label('password', trans('app.password')) !!}
                {!! Form::password('password', [ 'class' => 'form-control', 'autocomplete' => 'off' ]) !!}
                @if ($errors->first('password'))
                    <span class="help-block">{!! $errors->first('password') !!}</span>
                @endif
            </div>
            <div class="form-group {!! $errors->has('password_confirmation') ? 'has-error' : '' !!}">
                {!! Form::label('password_confirmation', trans('app.password_confirmation')) !!}
                {!! Form::password('password_confirmation', [ 'class' => 'form-control', 'autocomplete' => 'off' ]) !!}
                @if ($errors->first('password_confirmation'))
                    <span class="help-block">{!! $errors->first('password_confirmation') !!}</span>
                @endif
            </div>
        </div>
          <div class="box-footer">
            {!! Form::hidden('name', val($user, 'name'), [ 'class' => 'form-control', 'autocomplete' => 'off' ]) !!}
            {!! Form::hidden('role', $userRoles->first()->id, [ 'class' => 'form-control', 'autocomplete' => 'off' ]) !!}
            {!! Form::hidden('post_type','change_password', [ 'class' => 'form-control', 'autocomplete' => 'off' ]) !!}
            {!! Form::submit( trans('app.update') , array('class' => 'btn btn-success')) !!}
            {!! link_to( langRoute('admin.user.index'), trans('app.cancel'), array( 'class' => 'btn btn-primary' ) ) !!}
        </div>
    {!! Form::close() !!}
</div>
</div>
@stop
