@extends('backend.layout.layout')
@section('content')
<section class="content-header">
    <h1> {{{ trans('app.tour_guide') }}}</h1>
    <ol class="breadcrumb">
        <li><a href="{!! url(getLang() . '/admin/dashboard') !!}"><i class="fa fa-bookmark"></i> {{{ trans('app.dashboard') }}}</a></li>
        <li class="active">{{{ trans('app.tour_guide') }}}</li>
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
            <div class="form-group {!! $errors->has('language_skill') ? 'has-error' : '' !!}">
                {!! Form::label('language_skill', trans('app.language_skill')) !!}
                {!! Form::text('language_skill', val($userMeta, 'language_skill'), [ 'class' => 'form-control', 'autocomplete' => 'off' ]) !!}
                <em>Contoh : ID, EN</em>
                @if ($errors->first('language_skill'))
                    <span class="help-block">{!! $errors->first('language_skill') !!}</span>
                @endif
            </div>
            <div class="form-group {!! $errors->has('about_me') ? 'has-error' : '' !!}">
                {!! Form::label('about_me', trans('app.about_me')) !!}
                {!! Form::text('about_me', val($userMeta, 'about_me'), [ 'class' => 'form-control', 'autocomplete' => 'off' ]) !!}
                @if ($errors->first('about_me'))
                    <span class="help-block">{!! $errors->first('about_me') !!}</span>
                @endif
            </div>
            <div class="form-group {!! $errors->has('occupation') ? 'has-error' : '' !!}">
                {!! Form::label('occupation', trans('app.occupation')) !!}
                {!! Form::text('occupation', val($userMeta, 'occupation'), [ 'class' => 'form-control', 'autocomplete' => 'off' ]) !!}
                @if ($errors->first('occupation'))
                    <span class="help-block">{!! $errors->first('occupation') !!}</span>
                @endif
            </div>
            <div class="form-group {!! $errors->has('hobbies') ? 'has-error' : '' !!}">
                {!! Form::label('hobbies', trans('app.hobbies')) !!}
                {!! Form::text('hobbies', val($userMeta, 'hobbies'), [ 'class' => 'form-control', 'autocomplete' => 'off' ]) !!}
                @if ($errors->first('hobbies'))
                    <span class="help-block">{!! $errors->first('hobbies') !!}</span>
                @endif
            </div>
        </div>
        <div class="box-header with-border">
            <h3 class="box-title">{!! trans('app.tourguide_picture') !!}</h3>
        </div>
        <div class="box-body">
            <div class="row">
            <div class="col-md-12">
                <div class="col-md-6">
                    <div class="form-group {!! $errors->has('user_thumbnail') ? 'has-error' : '' !!}">
                        {!! Form::label('user_thumbnail', trans('app.user_thumbnail')) !!}
                        {!! Form::cke_image('user_thumbnail', '', [ 'class'=>'is_cke', 'height' => '100']) !!}
                        {!! Form::hidden('old_user_thumbnail', val($userMeta, 'user_thumbnail')) !!}
                        @if ($errors->first('user_thumbnail'))
                            <span class="address">{!! $errors->first('user_thumbnail') !!}</span>
                        @endif
                    </div>
                </div>
                <div class="col-md-6">
                    @if(isset($userMeta->user_thumbnail) && $userMeta->user_thumbnail != '')
                        <img src="{!!val($userMeta, 'user_thumbnail')!!}" width="150px" height="150px">      
                    @endif      
                </div>
            </div>
            </div>

            <div class="row">
            <div class="col-md-12">
                <div class="col-md-6">
                    <div class="form-group {!! $errors->has('user_image') ? 'has-error' : '' !!}">
                        {!! Form::label('user_image', trans('app.user_image')) !!}
                        {!! Form::cke_image('user_image', '', [ 'class'=>'is_cke', 'height' => '100']) !!}
                        {!! Form::hidden('old_user_image', val($userMeta, 'user_image')) !!}
                        @if ($errors->first('user_image'))
                            <span class="address">{!! $errors->first('user_image') !!}</span>
                        @endif
                    </div>
                </div>
                <div class="col-md-6">
                    @if(isset($userMeta->user_image) && $userMeta->user_image != '')
                        <img src="{!!val($userMeta, 'user_image')!!}" width="150px" height="150px">      
                    @endif      
                </div>
            </div>
            </div>

            <div class="row">
            <div class="col-md-12">
                <div class="col-md-6">
                    <div class="form-group {!! $errors->has('ktp_image') ? 'has-error' : '' !!}">
                        {!! Form::label('ktp_image', trans('app.ktp_image')) !!}
                        {!! Form::cke_image('ktp_image', '', [ 'class'=>'is_cke', 'height' => '100']) !!}
                        {!! Form::hidden('old_ktp_image', val($userMeta, 'ktp_image')) !!}
                        @if ($errors->first('ktp_image'))
                            <span class="address">{!! $errors->first('ktp_image') !!}</span>
                        @endif
                    </div>
                </div>
                <div class="col-md-6">
                    @if(isset($userMeta->ktp_image) && $userMeta->ktp_image != '')
                        <img src="{!!val($userMeta, 'ktp_image')!!}" width="150px" height="150px">
                    @endif
                </div>
            </div>
            </div>
        </div>
        <div class="box-footer">
            {!! Form::hidden('post_type', 'tour_guide', [ 'class' => 'form-control', 'autocomplete' => 'off' ]) !!}
            {!! Form::hidden('role', $userRoles->first()->id, [ 'class' => 'form-control', 'autocomplete' => 'off' ]) !!}
            {!! Form::submit( trans('app.update') , array('class' => 'btn btn-success')) !!}
            {!! link_to( langRoute('admin.user.index'), trans('app.cancel'), array( 'class' => 'btn btn-primary' ) ) !!}
        </div>
    {!! Form::close() !!}
</div>
</div>
@stop
