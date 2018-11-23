@extends('backend.layout.layout')
@section('content')
<section class="content-header">    
    <ol class="breadcrumb">
		<li><a href="{!! url(getLang() . '/admin/bank') !!}"><i class="fa fa-bookmark"></i> {!! trans('app.bank') !!}</a></li>
		<li class="active">{!! trans('app.show') !!}</li>
	</ol>
</section>
<br>

<div class="content">
    <div class="box"> 
        <div class="box-header with-border">
            <h3 class="box-title">Data Bank</h3>
        </div>
        <div class="box-body">
            <div class="form-group">
                {!! Form::label('title', trans('app.title')) !!}
                <div>{!! $data->title !!}</div>
            </div>
        </div>
        <div class="box-body">
            <div class="form-group">
                {!! Form::label('slug', trans('app.slug')) !!}
                <div>{!! $data->slug !!}</div>
            </div>
        </div>
        <div class="box-body">
            <div class="form-group">
                {!! Form::label('featured_image', trans('app.featured_image')) !!}
                <div><img src="{!! $data->featured_image !!}"></div>
            </div>
        </div>
        <div class="box-body">
            <div class="form-group">
                {!! Form::label('is_status', trans('app.is_status')) !!}
                <div>
                    @if($data->is_status == 1)
                        Ditampilkan
                      @else
                        Belum Ditampilkan
                      @endif
                </div>
            </div>
        </div>
        <div class="box-footer">
            {!! link_to( url(getLang() . '/admin/bank'), trans('app.back'), array( 'class' => 'btn btn-primary' ) ) !!}
        </div>
    </div>
</div>

@stop