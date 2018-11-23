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
                {!! Form::label('user_code', trans('app.code')) !!}
                <div>{!! $user_code !!}</div>
            </div>
            <div class="form-group">
                {!! Form::label('branch', trans('app.branch')) !!}
                <div>{!! $branch_name !!}</div>
            </div>
            <div class="form-group">
                {!! Form::label('name', trans('app.name')) !!}
                <div>{!! $user->name !!}</div>
            </div>
            <div class="form-group">
                {!! Form::label('last_name', trans('app.last_name')) !!}
                <div>{!! $userMeta->last_name !!}</div>
            </div>
            <div class="form-group">
                {!! Form::label('email', trans('app.email')) !!}
                <div>{!! $user->email !!}</div>
            </div>
            <div class="form-group">
                {!! Form::label('role', trans('app.role')) !!}
                <div>{{ $role_name }}</div>
            </div>
            <div class="form-group">
                {!! Form::label('phone', trans('app.phone')) !!}
                <div>{!! $userMeta->last_name !!}</div>
            </div>
            <div class="form-group">
                {!! Form::label('gender', trans('app.gender')) !!}
                <div>{!! $gender_name !!}</div>
            </div>
            <div class="form-group">
                {!! Form::label('date_of_birth', trans('app.date_of_birth')) !!}
                <div>{!! $date_of_birth !!}</div>
            </div>
            <div class="form-group">
                {!! Form::label('address', trans('app.address')) !!}
                <div>{!! $address !!}</div>
            </div>
            <div class="form-group">
                {!! Form::label('reference_code', trans('app.reference_code')) !!}
                <div>{!! $reference_code !!}</div>
            </div>
            <div class="form-group">
                {!! Form::label('activation_code', trans('app.activation_code')) !!}
                <div>{!! $activation_code !!}</div>
            </div>
            <div class="form-group">
                {!! Form::label('created_by', trans('app.created_by')) !!}
                <div>{!! $user->created_by_name !!}</div>
            </div>
            <div class="form-group">
                {!! Form::label('created_date', trans('app.created_date')) !!}
                <div>{!! fulldate_trans($user->created_at) !!}</div>
            </div>
            <div class="form-group">
                {!! Form::label('updated_by', trans('app.updated_by')) !!}
                <div>{!! $user->updated_by_name !!}</div>
            </div>
            <div class="form-group">
                {!! Form::label('updated_date', trans('app.updated_date')) !!}
                <div>{!! fulldate_trans($user->updated_at) !!}</div>
            </div>
            <div class="form-group">
                {!! Form::label('deleted_by', trans('app.deleted_by')) !!}
                <div>{!! $user->deleted_by_name !!}</div>
            </div>
            <div class="form-group">
                {!! Form::label('deleted_at', trans('app.deleted_at')) !!}
                <div>{!! fulldate_trans($user->deleted_at) !!}</div>
            </div>
        </div>
        <div class="box-footer">
            {!! link_to( langRoute('admin.user.index'), trans('app.back'), array( 'class' => 'btn btn-primary' ) ) !!}
        </div>
    </div>
</div>
@stop
