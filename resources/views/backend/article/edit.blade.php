@extends('backend/layout/layout')
@section('content')
<section class="content-header">
    <h1> {!! trans('app.article') !!}</h1>
    <ol class="breadcrumb">
        <li><a href="{!! url(getLang() . '/admin/article') !!}"><i class="fa fa-bookmark"></i> {!! trans('app.article') !!}</a></li>
        <li class="active">{!! trans('app.edit') !!}</li>
    </ol>
</section>
<div class="content">
    <div class="box">
        {!! Form::open( array( 'route' => array( getLang() . '.admin.article.update', $article->id), 'method' => 'PATCH', 'files'=>true)) !!}
        <div class="box-header with-border">
            <h3 class="box-title">{!! trans('app.edit') !!}</h3>
        </div>
        <div class="box-body">
            <div class="form-group {!! $errors->has('title') ? 'has-error' : '' !!}">
                {!! Form::label('title', trans('app.title').' *') !!}
                {!! 
                    Form::text('title', val($article, 'title'), [
                        'class'         => 'form-control', 
                        'autocomplete'  => 'off', 
                    ]) 
                !!}
                @if ($errors->first('title'))
					<span class="help-block">{!! $errors->first('title') !!}</span>
				@endif
            </div>
            <div class="form-group {!! $errors->has('slug') ? 'has-error' : '' !!}">
                {!! Form::label('slug', trans('app.slug').' *') !!}
                {!! 
                    Form::text('slug', val($article, 'slug'), [
                            'class'=>'form-control slug', 
                            'autocomplete' => 'off',
                        ]) 
                !!}
                @if ($errors->first('slug'))
					<span class="help-block">{!! $errors->first('slug') !!}</span>
				@endif
            </div>
            <div class="form-group {!! $errors->has('category') ? 'has-error' : '' !!}">
                {!! Form::label('category', trans('app.category').' *') !!}
                {!!  Form::select('category', $category_options, $article->category_id, [ 'class' => 'form-control' ]) !!}
                @if ($errors->first('category'))
                    <span class="help-block">{!! $errors->first('category') !!}</span>
                @endif
            </div>
            <div class="form-group {!! $errors->has('source_name') ? 'has-error' : '' !!}">
				{!! Form::label('source_name', trans('app.source_name').' *') !!}
                {!! Form::text('source_name', val($article, 'source_name'), [ 'class' => 'form-control' ]) !!}
				@if ($errors->first('source_name'))
					<span class="help-block">{!! $errors->first('source_name') !!}</span>
				@endif
            </div>
            <div class="form-group {!! $errors->has('source_url') ? 'has-error' : '' !!}">
				{!! Form::label('source_url', trans('app.source_url').' *') !!}
                {!! Form::text('source_url', val($article, 'source_url'), [ 'class' => 'form-control', 'placeholder' => 'example: https://example.com' ]) !!}
				@if ($errors->first('source_url'))
					<span class="help-block">{!! $errors->first('source_url') !!}</span>
				@endif
            </div>
            <div class="form-group {!! $errors->has('body') ? 'has-error' : '' !!}">
                {!! Form::label('body', trans('app.content').' *') !!}
                {!! 
                    Form::ckeditor('body', val( $article, 'body' ), [
                            'class' => 'form-control', 
                        ]) 
                !!}
                @if ($errors->first('body'))
					<span class="help-block">{!! $errors->first('body') !!}</span>
				@endif
            </div>
            <div class="form-group {!! $errors->has('featured_image') ? 'has-error' : '' !!}">
				{!! Form::label('featured_image', trans('app.featured_image')) !!}
				{!! 
					Form::cke_image('featured_image', val($article, 'featured_image'), [ 'class'=>'form-control', 'height' => 200 ])
				!!}
				@if ($errors->first('featured_image'))
					<span class="help-block">{!! $errors->first('featured_image') !!}</span>
				@endif
			</div>
            <div class="form-group {!! $errors->has('is_published') ? 'has-error' : '' !!}">
            <label>
              {!! Form::checkbox('is_published', 'is_published', val( $article, 'is_published' ) ).' '.trans('app.publish') !!} ?
            </label>
            @if ($errors->first('is_published'))
                <span class="help-block">{!! $errors->first('is_published') !!}</span>
            @endif
            </div>
        </div>
          <div class="box-footer">
            {!! Form::submit( trans('app.update') , array('class' => 'btn btn-success')) !!}
            {!! link_to( langRoute('admin.article.index'), trans('app.cancel'), array( 'class' => 'btn btn-primary  form-controll' ) ) !!}
        </div>
        {!! Form::close() !!}
    </div>
</div>
@stop