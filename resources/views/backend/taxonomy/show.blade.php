@extends('backend.layout.layout')
@section('content')
<section class="content-header">
    <h1> {{{ trans('app.'.$term->post_type.'_taxonomy') }}}</h1>
    <ol class="breadcrumb">
        <li><a href="{!! URL::route('admin.taxonomy.index', $term->post_type) !!}"><i class="fa fa-bookmark"></i> {{{ trans('app.'.$term->post_type.'_taxonomy') }}}</a></li>
        <li class="active">{{{ trans('app.show') }}}</li>
    </ol>
</section>
<div class="content">
    <div class="box">  
        <div class="box-header with-border">
            <h3 class="box-title">{!! $term->title !!}</h3>
        </div>
        <div class="box-body">
            <div class="form-group">
                {!! Form::label('title', trans('app.title')) !!}
                <div>{!! val($term, 'title') !!}</div>
            </div>
            <div class="form-group">
                {!! Form::label('slug', trans('app.slug')) !!}
                <div>{!! val($term, 'slug') !!}</div>
            </div>
            <!--<div class="form-group">
                {!! Form::label('product_attribute', trans('app.product_attribute')) !!}
                <div>
                    @if($term->productAttributes)
                        {{{ implode($term->productAttributes, ', ') }}}
                    @else
                        {{{ trans('app.product_attribute_not_set') }}}
                    @endif
                </div>
            </div>-->
            <div class="form-group">
                {!! Form::label('created_date', trans('app.created_date')) !!}
                <div>{!! $term->created_at !!}</div>
            </div>
            <div class="form-group">
                {!! Form::label('updated_date', trans('app.updated_date')) !!}
                <div>{!! $term->updated_at !!}</div>
            </div>
        </div>
        <div class="box-footer">
            {!! link_to( URL::route('admin.taxonomy.index', $term->post_type), trans('app.back'), array( 'class' => 'btn btn-primary  form-controll' ) ) !!}
        </div>
    </div>
</div>
@stop