@extends('backend.layout.layout')
@section('content')
<section class="content-header">
    <h1> {{{ trans('app.menu_group') }}}</h1>
    <ol class="breadcrumb">
        <li><a href="{!! url(getLang() . '/admin/menu-group') !!}"><i class="fa fa-bookmark"></i> {{{ trans('app.menu_group') }}}</a></li>
        <li class="active">{{{ trans('app.edit') }}}</li>
    </ol>
</section>
<div class="content">
    <div class="box">
        {!! Form::open( array( 'route' => array( getLang() . '.admin.menu-group.update', $menuGroup->id), 'method' => 'PATCH', 'files'=>true)) !!}
        <div class="box-header with-border">
            <h3 class="box-title">{!! trans('app.edit') !!}</h3>
        </div>
        <div class="box-body">
            <div class="form-group {!! $errors->has('title') ? 'has-error' : '' !!}">
                {!! Form::label('title', trans('app.title').' *') !!}
                {!! Form::text('title', $menuGroup->title, [ 'class' => 'form-control slug-source', 'autocomplete'    => 'off' ]) !!}
                @if ($errors->first('title'))
                    <span class="help-block">{!! $errors->first('title') !!}</span>
                @endif
            </div>
            <div class="form-group">
                {!! Form::label('description', trans('app.description')) !!}
                {!! Form::textarea('description', $menuGroup->description, [ 'class'=>'form-control', ])  !!}
            </div>
        </div>
          <div class="box-footer">
            {!! Form::submit( trans('app.update') , array('class' => 'btn btn-success')) !!}
            {!! link_to( langRoute('admin.menu-group.index'), trans('app.cancel'), array( 'class' => 'btn btn-primary' ) ) !!}
        </div>
    {!! Form::close() !!}
</div>
</div>
@stop
