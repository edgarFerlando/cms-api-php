@extends('backend.layout.layout')
@section('content')
<section class="content-header">
    <h1> {{{ trans('app.cfp_client') }}}</h1>
    <ol class="breadcrumb">
        <li><a href="{!! url(getLang() . '/admin/cfp/client') !!}"><i class="fa fa-bookmark"></i> {{{ trans('app.cfp_client') }}}</a></li>
        <li class="active">{{{ trans('app.edit') }}}</li>
    </ol>
</section>
<div class="content">
    <div class="box">
        {!! Form::open( array( 'route' => array( getLang() . '.admin.cfp.client.update', $data->internalid), 'method' => 'PATCH', 'files'=>true)) !!}
        <div class="box-header with-border">
            <h3 class="box-title">{!! trans('app.edit') !!}</h3>
        </div>
        <div class="box-body">
            <div class="form-group {!! $errors->has('client_id') ? 'has-error' : '' !!}">
                {!! Form::label('client_id', trans('app.client_name').' *') !!}
                {!! Form::select('client_id', [], '', [ 'class' => 'selectize_clients', 'autocomplete' => 'off', 'old' => val($data, 'client_id') ]) !!}
                @if ($errors->first('client_id'))
                    <span class="help-block">{!! $errors->first('client_id') !!}</span>
                @endif
            </div>
            <div class="form-group {!! $errors->has('cfp_id') ? 'has-error' : '' !!}">
                {!! Form::label('cfp_id', trans('app.cfp_name').' *') !!}
                {!! Form::select('cfp_id', [], '', [ 'class' => 'selectize_cfps', 'autocomplete' => 'off', 'old' => val($data, 'cfp_id') ]) !!}
                @if ($errors->first('cfp_id'))
                    <span class="help-block">{!! $errors->first('cfp_id') !!}</span>
                @endif
            </div>
            <div class="form-group {!! $errors->has('notes') ? 'has-error' : '' !!}">
                {!! Form::label('notes', trans('app.notes')) !!}
                {!! Form::textarea('notes', val($data, 'notes'), [ 'class' => 'form-control', 'autocomplete' => 'off', 'rows' => 4 ]) !!}
                @if ($errors->first('notes'))
                    <span class="help-block">{!! $errors->first('notes') !!}</span>
                @endif
            </div>
        </div>
          <div class="box-footer">
            {!! Form::submit( trans('app.update') , array('class' => 'btn btn-success')) !!}
            {!! link_to( langRoute('admin.cfp.client.index'), trans('app.cancel'), array( 'class' => 'btn btn-primary' ) ) !!}
        </div>
    {!! Form::close() !!}
</div>
</div>
@stop
