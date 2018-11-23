@extends('backend.layout.layout')
@section('content')
<section class="content-header">
    <h1> {{{ trans('app.portofolio_detail') }}}</h1>
    <ol class="breadcrumb">
        <li><a href="{!! url(getLang() . '/admin/detail/portofolio') !!}"><i class="fa fa-bookmark"></i> {{{ trans('app.portofolio_detail') }}}</a></li>
        <li class="active">{{{ trans('app.edit') }}}</li>
    </ol>
</section>
<div class="content">
    <div class="box">
        {!! Form::open( array( 'route' => array( getLang() . '.admin.detail.portofolio.update', $portofolioDetail->id), 'method' => 'PATCH', 'files'=>true)) !!}
        <div class="box-header with-border">
            <h3 class="box-title">{!! trans('app.edit') !!}</h3>
        </div>
        <div class="box-body">
            <div class="form-group {!! $errors->has('detail_name') ? 'has-error' : '' !!}">
                {!! Form::label('detail_name', trans('app.detail_name').' *') !!}
                {!! Form::text('detail_name', $portofolioDetail->detail_name, [ 'class' => 'form-control', 'autocomplete' => 'off' ]) !!}
                @if ($errors->first('detail_name'))
                    <span class="help-block">{!! $errors->first('detail_name') !!}</span>
                @endif
            </div>
            <div class="form-group {!! $errors->has('portofolio_id') ? 'has-error' : '' !!}">
                {!! Form::label('portofolio_id', trans('app.portofolio_name').' *') !!}
                {!! Form::select('portofolio_id', $portofolios, $portofolioDetail->portofolio_id, [ 'class' => 'selectize', 'autocomplete' => 'off' ]) !!}
                @if ($errors->first('portofolio_id'))
                    <span class="help-block">{!! $errors->first('portofolio_id') !!}</span>
                @endif
            </div>
            <div class="form-group {!! $errors->has('keterangan') ? 'has-error' : '' !!}">
                {!! Form::label('keterangan', trans('app.keterangan')) !!}
                {!! Form::text('keterangan', $portofolioDetail->keterangan, [ 'class' => 'form-control', 'autocomplete' => 'off' ]) !!}
                @if ($errors->first('keterangan'))
                    <span class="help-block">{!! $errors->first('keterangan') !!}</span>
                @endif
            </div>
        </div>
          <div class="box-footer">
            {!! Form::submit( trans('app.update') , array('class' => 'btn btn-success')) !!}
            {!! link_to( langRoute('admin.detail.portofolio.index'), trans('app.cancel'), array( 'class' => 'btn btn-primary' ) ) !!}
        </div>
    {!! Form::close() !!}
</div>
</div>
@stop
