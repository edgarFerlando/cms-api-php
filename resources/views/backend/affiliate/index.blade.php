@extends('backend.layout.layout')
@section('content')
<section class="content-header">
  <h1>
    {!! trans('app.affiliate') !!} 
  </h1>
  <ol class="breadcrumb">
    <li><a href="{!! URL::route('admin.dashboard') !!}">{!! trans('app.dashboard') !!}</a></li>
    <li class="active">{!! trans('app.affiliate') !!}</li>
  </ol>
</section>
<div class="content">
  {!! Notification::showAll() !!}
  <div class="box">  
    <div class="box-body">
      <div class="row filter-form">
        {!! Form::open([ 'route' => [ 'admin.affiliates.filter'] ]) !!}
        <div class="col-xs-3 clear-right">
          <div class="form-group">
            {!! Form::label('code', trans('app.code')) !!}
            {!! Form::text('code', Input::get('code'), [ 'class'  => 'form-control', 'autocomplete'   => 'off'], $errors) !!}
          </div>
        </div>
        <div class="col-xs-3 clear-right">
          <div class="form-group">
            {!! Form::label('name', trans('app.name')) !!}
            {!! Form::text('name', Input::get('name'), [ 'class'  => 'form-control', 'autocomplete'   => 'off'], $errors) !!}
          </div>
        </div>
        <div class="col-xs-2 clear-right">
          <div class="form-group action-tool">
            {!! Form::submit(trans('app.filter'), array('class' => 'btn btn-success')) !!}
            {!! HTML::link(langurl('admin/affiliates'),trans('app.reset'), array('class' => 'btn btn-default')) !!}
          </div>
        </div>
        {!! Form::close() !!}
      </div>

      <div class="table-responsive">
        <table class="table table-striped">
          <thead>
            <tr>
              <th>{!! trans('app.code') !!}</th>
              <th>{!! trans('app.name') !!}</th>
              <!-- <th>{!! trans('app.action') !!}</th> -->
            </tr>
          </thead>
          <tbody>
            @if($affiliates->count())
              @foreach( $affiliates as $affiliate )
                <tr>
                  <td>{!! $affiliate->code !!}</td>
                  <td>{!! $affiliate->name !!}</td>
                  <!-- <td class="action">
                    @if( $affiliate->status != 'confirmed' )
                      {!! HTML::decode( HTML::link(langRoute('admin.affiliates.edit', array($affiliate->id)), '<i class="fa fa-pencil"></i>') ) !!}
                      {!! HTML::decode( HTML::link(URL::route('admin.affiliates.delete', array($affiliate->id)), '<i class="fa fa-trash-o"></i>') ) !!}
                    @endif
                  </td>-->
                </tr>
              @endforeach
            @else
              <td colspan="5">{!! trans('app.no_results_found') !!}</td>
            @endif
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <div class="text-center">
    {!! $affiliates->render() !!}
  </div>
</div>
@stop