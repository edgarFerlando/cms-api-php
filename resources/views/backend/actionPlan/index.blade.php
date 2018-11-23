@extends('backend.layout.layout')
@section('content')
<section class="content-header">
  <h1>
    {!! trans('app.triangle') !!} 
    <ul class="action list-inline">
      <li>{!! HTML::decode( HTML::link(langRoute('admin.settings.plan-analysis.triangle.create'), '<i class="fa fa-plus-square"></i>'. trans('app.add_mapping')) ) !!}</li>
    </ul>
  </h1>
  <ol class="breadcrumb">
    <li><a href="{!! URL::route('admin.dashboard') !!}">{!! trans('app.dashboard') !!}</a></li>
    <li class="active">{!! trans('app.triangle') !!}</li>
  </ol>
</section>
<div class="content">
  {!! Notification::showAll() !!}
  <div class="box">
    <div class="box-body">
      @if($items->count())
      <div class="table-responsive">
        <table class="table table-striped">
          <thead>
            <tr>
              <th>{!! trans('app.triangle_layer') !!}</th>
              <th>{!! trans('app.step_1') !!}</th>
              <th>{!! trans('app.step_2') !!}</th>
              <th>{!! trans('app.step_3') !!}</th>
              <th>{!! trans('app.action') !!}</th>
            </tr>
          </thead>
          <tbody>
            @foreach( $items as $item )
            <?php
              $step_1 = $item->step_1 == ''? '':( $item->step_1 == 'plan_type'? 'Plan' : 'Goal' );
              $step_3 = $item->step_3 == ''?'':($item->step_3 == 'plan_a'? 'Safe Retirement' : 'Comfort Plan');
              if($item->step_1 == 'taxo_wallet_asset'){
                $taxo = \App\Taxonomy::where('id', $item->step_2)->first();
                $step_2 = $taxo->title;
              }else{
                $step_2 = $item->step_2 == ''?'':($item->step_2 == 'plan_a'? 'Safe Retirement' : 'Comfort Plan');
              }
            ?>
            <tr>
              <td>{!! $item->layer->title !!}</td>
              <td>{!! $step_1 !!}</td>
              <td>{!! $step_2 !!}</td>
              <td>{!! $step_3 !!}</td>
              <td class="action">
                {!! HTML::decode( HTML::link(langRoute('admin.settings.plan-analysis.triangle.edit', array($item->id)), '<i class="fa fa-pencil"></i>') ) !!}
                {!! HTML::decode( HTML::link(URL::route('admin.settings.plan-analysis.triangle.delete', array($item->id)), '<i class="fa fa-trash-o"></i>') ) !!}
              </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        @else
          <div class="alert alert-danger">{{{ trans('app.no_results_found') }}}</div>
        @endif
      </div>
    </div>
    <div class="text-center">
      {!! $items->render() !!}
    </div>
  </div>
  @stop