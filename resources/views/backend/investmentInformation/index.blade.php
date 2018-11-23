@extends('backend/layout/layout')
@section('content')
<script type="text/javascript">
$(document).ready(function () {
    // publish settings
    $(".publish").bind("click", function (e) {
      var id = $(this).attr('id');
      e.preventDefault();
      $.ajax({
        type: "POST",
        url: "{!! url(getLang() . '/admin/investment-information/" + id + "/toggle-publish/') !!}",
        headers: {
          'X-CSRF-Token': $('meta[name="_token"]').attr('content')
        },
        success: function (response) {
          if (response['result'] == 'success') {
            var imagePath = (response['changed'] == 1) ? 'fa fa-check' : 'fa fa-times';
            $("#publish-image-" + id).attr('class', imagePath);
          }
        },
        error: function () {
          alert("error");
        }
      })
    });
  });
</script>
<section class="content-header">
  <h1>
    {!! trans('app.investment_information') !!} 
    <ul class="action list-inline">
      <li>{!! HTML::decode( HTML::link(langRoute('admin.investment-information.create'), '<i class="fa fa-plus-square"></i>'. trans('app.add_new')) ) !!}</li>
    </ul>
  </h1>
  <ol class="breadcrumb">
    <li><a href="{!! URL::route('admin.dashboard') !!}">{!! trans('app.dashboard') !!}</a></li>
    <li class="active">{!! trans('app.investment_information') !!}</li>
  </ol>
</section>
<div class="content">
  <div class="box">  
    <div class="box-body">
      {!! Notification::showAll() !!}
      @if($items->count())
      <div class="table-responsive">
        <table class="table table-striped">
          <thead>
            <tr>
              <th>{{{ trans('app.product_name') }}}</th>
              <th>{{{ trans('app.description') }}}</th>
              <th>{{{ trans('app.created_date') }}}</th>
              <th>{{{ trans('app.updated_date') }}}</th>
              <th>{{{ trans('app.action') }}}</th>
            </tr>
          </thead>
          <tbody>
            @foreach( $items as $item ) 
              <tr>
                <td>{!! $item->product_name !!}</td>
                <td>{!! $item->description !!}</td>
                <td>{!! fulldate_trans($item->created_at) !!}</td>
                <td>{!! fulldate_trans($item->updated_at) !!}</td>
                <td class="action">
                  {!! HTML::decode( HTML::link(langRoute('admin.investment-information.edit', array($item->id)), '<i class="fa fa-pencil"></i>') ) !!}
                  {!! HTML::decode( HTML::link(URL::route('admin.investment-information.delete', array($item->id)), '<i class="fa fa-trash-o"></i>') ) !!}
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