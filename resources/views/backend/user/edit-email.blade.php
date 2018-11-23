@extends('backend.layout.layout')
@section('content')
<section class="content-header">
    <h1> {{{ trans('app.user') }}}</h1>
    <ol class="breadcrumb">
        <li><a href="{!! url(getLang() . '/admin/user') !!}"><i class="fa fa-bookmark"></i> {{{ trans('app.user') }}}</a></li>
        <li class="active">{{{ trans('app.edit') }}}</li>
    </ol>
</section>
<div class="content">
    <div class="box">
        {!! Form::open( array( 'route' => array( getLang() . '.admin.user.update', $user->id), 'method' => 'PATCH', 'files'=>true)) !!}
        <div class="box-header with-border">
            <h3 class="box-title">{!! trans('app.edit') !!}</h3>
        </div>
        <div class="box-body">
            <div class="form-group {!! $errors->has('name') ? 'has-error' : '' !!}">
                {!! Form::label('name', trans('app.name').' *') !!}
                {!! Form::text('name', val($user, 'name'), [ 'class' => 'form-control', 'autocomplete' => 'off' ]) !!}
                @if ($errors->first('name'))
                    <span class="help-block">{!! $errors->first('name') !!}</span>
                @endif
            </div>
            <div class="form-group {!! $errors->has('password') ? 'has-error' : '' !!}">
                {!! Form::label('password', trans('app.password').' *') !!}
                {!! Form::password('password', [ 'class' => 'form-control', 'autocomplete' => 'off' ]) !!}
                @if ($errors->first('password'))
                    <span class="help-block">{!! $errors->first('password') !!}</span>
                @endif
            </div>
            <div class="form-group {!! $errors->has('password_confirmation') ? 'has-error' : '' !!}">
                {!! Form::label('password_confirmation', trans('app.password_confirmation').' *') !!}
                {!! Form::password('password_confirmation', [ 'class' => 'form-control', 'autocomplete' => 'off' ]) !!}
                @if ($errors->first('password_confirmation'))
                    <span class="help-block">{!! $errors->first('password_confirmation') !!}</span>
                @endif
            </div>
            <div class="form-group {!! $errors->has('role') ? 'has-error' : '' !!}">
                {!! Form::label('role', trans('app.role').' *') !!}
                {!! Form::select('role', $roles, $userRoles->first()->id, [ 'class' => 'selectize', 'autocomplete' => 'off' ]) !!}
                @if ($errors->first('role'))
                    <span class="help-block">{!! $errors->first('role') !!}</span>
                @endif
            </div>
        </div>
          <div class="box-footer">
            {!! Form::submit( trans('app.update') , array('class' => 'btn btn-success')) !!}
            {!! link_to( langRoute('admin.user.index'), trans('app.cancel'), array( 'class' => 'btn btn-primary' ) ) !!}
        </div>
    {!! Form::close() !!}
</div>
</div>
@stop
