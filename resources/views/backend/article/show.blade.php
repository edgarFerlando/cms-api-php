@extends('backend.layout.layout')
@section('content')
<section class="content-header">
    <h1> {{{ trans('app.article') }}}  </h1>
    <ol class="breadcrumb">
        <li><a href="{!! langRoute('admin.article.index') !!}"><i class="fa fa-bookmark"></i> {{{ trans('app.article') }}}</a></li>
        <li class="active">{{{ trans('app.show') }}}</li>
    </ol>
</section>
<div class="content">
    <div class="box">  
        <div class="box-header with-border">
            <h3 class="box-title">{!! $article->title !!}</h3>
        </div>
        <div class="box-body">
            <div class="form-group">
                {!! Form::label('title', trans('app.title')) !!}
                <div>{!! $article->title !!}</div>
            </div>
            <div class="form-group">
                {!! Form::label('slug', trans('app.slug')) !!}
                <div>{!! $article->slug !!}</div>
            </div>
            <div class="form-group">
                {!! Form::label('category', trans('app.category')) !!}
                <div>{!! $article->category->title !!}</div>
            </div>
            <div class="form-group">
                {!! Form::label('body', trans('app.content')) !!}
                <div>{!! $article->body !!}</div>
            </div>
            <div class="form-group">
                <div>{!! Form::label('is_published', trans('app.publish'). ' ?') !!}
                <div>{!! $article->is_published == 1?trans('app.yes'):trans('app.no') !!}</div>
            </div>
            <div class="form-group">
                {!! Form::label('created_date', trans('app.created_date')) !!}
                <div>{!! $article->created_at !!}</div>
            </div>
            <div class="form-group">
                {!! Form::label('updated_date', trans('app.updated_date')) !!}
                <div>{!! $article->updated_at !!}</div>
            </div>
        </div>
        <div class="box-footer">
            {!! link_to( langRoute('admin.article.index'), trans('app.back'), array( 'class' => 'btn btn-primary' ) ) !!}
        </div>
    </div>
</div>
@stop