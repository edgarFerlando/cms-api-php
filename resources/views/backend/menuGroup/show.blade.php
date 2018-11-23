@extends('backend.layout.layout')
@section('content')
<section class="content-header">
    <h1> {{{ trans('app.menu_group') }}}  </h1>
    <ol class="breadcrumb">
        <li><a href="{!! langRoute('admin.menu-group.index') !!}"><i class="fa fa-bookmark"></i> {{{ trans('app.menu_group') }}}</a></li>
        <li class="active">{{{ trans('app.show') }}}</li>
    </ol>
</section>
<div class="content">
    <div class="box">  
        <div class="box-header with-border">
            <h3 class="box-title">{!! $menuGroup->title !!}</h3>
        </div>
        <div class="box-body">
            <div class="form-group">
                {!! Form::label('title', trans('app.title')) !!}
                <div>{!! $menuGroup->title !!}</div>
            </div>
            <div class="form-group">
                {!! Form::label('description', trans('app.description')) !!}
                <div>{!! $menuGroup->description !!}</div>
            </div>
            <div class="form-group">
                {!! Form::label('created_date', trans('app.created_date')) !!}
                <div>{!! $menuGroup->created_at !!}</div>
            </div>
            <div class="form-group">
                {!! Form::label('updated_date', trans('app.updated_date')) !!}
                <div>{!! $menuGroup->updated_at !!}</div>
            </div>
        </div>
        <div class="box-footer">
            {!! link_to( langRoute('admin.menu-group.index'), trans('app.back'), array( 'class' => 'btn btn-primary' ) ) !!}
        </div>
    </div>
</div>
@stop
