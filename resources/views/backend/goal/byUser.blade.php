@extends('backend.layout.layout')
@section('content')
<section class="content-header">
    <h1> {{{ trans('app.user') }}}  </h1>
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
                {!! Form::label('name', trans('app.name')) !!}
                <div>{!! $user->name !!}</div>
            </div>
            <div class="form-group">
                {!! Form::label('email', trans('app.email')) !!}
                <div>{!! $user->email !!}</div>
            </div>
            <div class="form-group">
                {!! Form::label('role', trans('app.role')) !!}
                <div>
                    @foreach($user->roles as $role)
                       {{ $role->name }}
                    @endforeach
                </div>
            </div>
            
            <div class="form-group">
                {!! Form::label('created_date', trans('app.created_date')) !!}
                <div>{!! $user->created_at !!}</div>
            </div>
            <div class="form-group">
                {!! Form::label('updated_date', trans('app.updated_date')) !!}
                <div>{!! $user->updated_at !!}</div>
            </div>
        </div>
        <div class="box-footer">
            {!! link_to( langRoute('admin.user.index'), trans('app.back'), array( 'class' => 'btn btn-primary' ) ) !!}
        </div>
    </div>
</div>
@stop
