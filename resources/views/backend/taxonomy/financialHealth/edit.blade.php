@extends('backend.layout.layout')
@section('content')
<section class="content-header">
    <h1> {{{ trans('app.'.$post_type.'_taxonomy') }}}</h1>
    <ol class="breadcrumb">
        <li><a href="{!! url(getLang() . '/admin/article/category') !!}"><i class="fa fa-bookmark"></i> {{{ trans('app.category') }}}</a></li>
        <li class="active">{{{ trans('app.edit') }}}</li>
    </ol>
</section>
<div class="content">
    <div class="box">
        {!! Form::open(['url' => URL::route('admin.taxonomy.update', $term->id ), 'method' => 'PATCH', 'files'=>true ]) !!}
        {!! Form::hidden('post_type', $post_type) !!}
        <div class="box-header with-border">
            <h3 class="box-title">{!! trans('app.edit') !!}</h3>
        </div>
        <div class="box-body">
            <div class="form-group {!! $errors->has('title') ? 'has-error' : '' !!}">
                {!! Form::label('title', trans('app.title').' *') !!}
                {!! Form::text('title', val($term, 'title'), [ 'class' => 'form-control slug-source', 'autocomplete' => 'off' ]) !!}
                @if ($errors->first('title'))
                    <span class="help-block">{!! $errors->first('title') !!}</span>
                @endif
            </div>
            <div class="form-group {!! $errors->has('slug') ? 'has-error' : '' !!}">
                {!! Form::label('slug', trans('app.slug').' *') !!}
                {!! Form::text('slug', val($term, 'slug'), [ 'class' => 'form-control slug', 'autocomplete' => 'off' ]) !!}
                @if ($errors->first('slug'))
                    <span class="help-block">{!! $errors->first('slug') !!}</span>
                @endif
            </div>
            <div class="form-group {!! $errors->has('input_name') ? 'has-error' : '' !!}">
                {!! Form::label('input_name', trans('app.input_name').' *') !!}
                {!! Form::text('input_name', val($taxoMeta, 'input_name'), [ 'class' => 'form-control', 'autocomplete' => 'off' ]) !!}
                @if ($errors->first('input_name'))
                    <span class="help-block">{!! $errors->first('input_name') !!}</span>
                @endif
            </div>
            <div class="form-group {!! $errors->has('description') ? 'has-error' : '' !!}">
                {!! Form::label('description', trans('app.description').' *') !!}
                {!! Form::text('description', val($taxoMeta, 'description'), [ 'class' => 'form-control', 'autocomplete' => 'off' ]) !!}
                @if ($errors->first('description'))
                    <span class="help-block">{!! $errors->first('description') !!}</span>
                @endif
            </div>
            <div class="form-group">
                {!! Form::label('parent', trans('app.hierarchy')) !!}
                {!!  Form::select('parent', $parent_options, val($term, 'parent_id', 'parent'), [ 'class' => 'selectize' ]) !!}
            </div>
        </div>
          <div class="box-footer">
            {!! Form::submit( trans('app.update') , array('class' => 'btn btn-success')) !!}
            {!! link_to( URL::route('admin.taxonomy.index', $post_type), trans('app.cancel'), array( 'class' => 'btn btn-primary' ) ) !!}
        </div>
    {!! Form::close() !!}
</div>
</div>
@stop
