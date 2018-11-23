@extends('backend.layout.layout')
@section('content')
<section class="content-header">
    <h1> {{{ trans('app.menu') }}}  </h1>
    <ol class="breadcrumb">
        <li><a href="{!! langRoute('admin.menu.index') !!}"><i class="fa fa-bookmark"></i> {{{ trans('app.menu') }}}</a></li>
        <li class="active">{{{ trans('app.menu') }}}</li>
    </ol>
</section>
<div class="content">
    <div class="box">  
        <div class="box-header with-border">
            <h3 class="box-title">{!! $menu->title !!}</h3>
        </div>
        <div class="box-body">
            <div class="form-group">
                {!! Form::label('title', trans('app.title')) !!}
                {!! lang_show($menu, 'title') !!}
            </div>
            <div class="form-group">
                {!! Form::label('type', trans('app.type')) !!}
                {!! lang_show($menu, 'type') !!}
            </div>
            <div class="form-group">
                {!! Form::label('url', trans('app.url')) !!}
                {!! lang_show($menu, 'url') !!}
            </div>
            <div class="form-group">
                {!! Form::label('hierarchy', trans('app.hierarchy')) !!}
                <div>
                    <?php
                        $parent = App\Menu::find($menu->parent_id);
                    ?>
                    {!! $parent?$parent->title: '-' !!}
                </div>
            </div>
            <div class="form-group">
                {!! Form::label('is_published', trans('app.publish'). ' ?') !!}
                <div>{!! $menu->is_published == 1?trans('app.yes'):trans('app.no') !!}</div>
            </div>
            <div class="form-group">
                {!! Form::label('created_date', trans('app.created_date')) !!}
                <div>{!! $menu->created_at !!}</div>
            </div>
            <div class="form-group">
                {!! Form::label('updated_date', trans('app.updated_date')) !!}
                <div>{!! $menu->updated_at !!}</div>
            </div>
        </div>
        <div class="box-footer">
            {!! link_to( URL::route('admin.menu_group.items', [ 'menu_group' => $menu->menu_group_id ]), trans('app.back'), array( 'class' => 'btn btn-primary' ) ) !!}
        </div>
    </div>
</div>
@stop
