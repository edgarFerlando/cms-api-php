@extends('backend.layout.layout')
@section('content')
<section class="content-header">
	<h1>{!! trans('app.bank') !!}</h1>
	<ol class="breadcrumb">
		<li><a href="{!! url(getLang() . '/admin/bank') !!}"><i class="fa fa-bookmark"></i> {!! trans('app.bank') !!}</a></li>
		<li class="active">{!! trans('app.edit') !!}</li>
	</ol>
</section>


<div class="content">
        <div class="box">
            {!! Form::model($data, ['method' => 'PATCH', 'action' => ['Backend\BankController@update',$data->id]]) !!}
            <div class="box-header with-border">
                <h3 class="box-title">{!! trans('app.edit') !!}</h3>
            </div>
            <div class="box-body">

                <div class="form-group {!! $errors->has('title') ? 'has-error' : '' !!}">
                    {!! Form::label('title', trans('app.title').' *') !!}
                    {!! Form::text('title', val($data, 'title'), [ 'class' => 'form-control slug-source', 'autocomplete' => 'off' ]) !!}
                    @if ($errors->first('title'))
                        <span class="help-block">{!! $errors->first('title') !!}</span>
                    @endif
                </div>
                <div class="form-group {!! $errors->has('slug') ? 'has-error' : '' !!}">
                    {!! Form::label('slug', trans('app.slug').' *') !!}
                    {!! Form::text('slug', val($data, 'slug'), [ 'class' => 'form-control slug', 'autocomplete' => 'off' ]) !!}
                    @if ($errors->first('slug'))
                        <span class="help-block">{!! $errors->first('slug') !!}</span>
                    @endif
                </div>

                <div class="form-group {!! $errors->has('is_status') ? 'has-error' : '' !!}">
                    {!! Form::label('is_status', trans('app.is_status').' *') !!}
                    {!! Form::select('is_status', ['Belum Ditampilkan', 'Ditampilkan'], val($data, 'is_status'), ['class' => 'selectize_clients']) !!}
                    @if ($errors->first('is_status'))
                        <span class="help-block">{!! $errors->first('is_status') !!}</span>
                    @endif
                </div>
    
                <div class="form-group {!! $errors->has('featured_image') ? 'has-error' : '' !!}">
                    {!! Form::label('featured_image', trans('app.featured_image').' *') !!}
                    {!! 
                        Form::cke_image('featured_image', $data->featured_image, [ 'class'=>'form-control' ])
                    !!}
                    @if ($errors->first('featured_image'))
                        <span class="help-block">{!! $errors->first('featured_image') !!}</span>
                    @endif
                </div>

                <div class="form-group {!! $errors->has('color') ? 'has-error' : '' !!}">
                    {!! Form::label('color', trans('app.color').' *') !!}
                    {!! Form::input('color','color[0]',null, array('class' => 'form-control color','placeholder' => 'Enter Color','id' => 'color')) !!}
                    @if ($errors->first('color'))
                        <span class="help-block">{!! $errors->first('color') !!}</span>
                    @endif
                </div>

            </div>
                
    
              <div class="box-footer">
                {!! Form::submit( trans('app.update') , array('class' => 'btn btn-success')) !!}
                {!! link_to( URL::route('admin.cfp.schedule.dayoff'), trans('app.cancel'), array( 'class' => 'btn btn-primary' ) ) !!}
            </div>
        {!! Form::close() !!}
    </div>
    </div>

@stop