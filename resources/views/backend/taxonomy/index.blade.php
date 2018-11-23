@extends('backend.layout.layout')
@section('content')
    <link href="{!! url('backend/css/menu-management.css') !!}" rel="stylesheet" type="text/css" />
    <script src="{!! url('backend/js/jquery.nestable.js') !!}"></script>
    <meta name="_token" content="{!! csrf_token() !!}"/>
    <section class="content-header">
      <h1>
        {!! trans('app.'.$post_type.'_taxonomy') !!} 
        <ul class="action list-inline">
          <li>{!! HTML::decode( HTML::link(URL::route('admin.taxonomy.create', [ 'post_type' => $post_type ] ), '<i class="fa fa-plus-square"></i>'. trans('app.add_'.$post_type.'_taxonomy')) ) !!}</li>
        </ul>
      </h1>
      <ol class="breadcrumb">
        <li><a href="{!! URL::route('admin.dashboard') !!}">{!! trans('app.dashboard') !!}</a></li>
        <li class="active">{!! trans('app.'.$post_type.'_taxonomy') !!}</li>
      </ol>
    </section>
    <div class="content">
        {!! Notification::showAll() !!}
        <div id="msg"></div>
        <div class="box">  
            <div class="box-body">
                @if($taxonomies !== null)
                    <div class="dd" id="nestable">
                        {!! $taxonomies !!}
                    </div>
                @else
                    <div class="alert alert-danger">{{{ trans('app.no_results_found') }}}</div>
                @endif
            </div>
        </div>
    </div>
    <script type="text/javascript">
        $(document).ready(function () {
            var updateOutput = function (e) {
                var list = e.length ? e : $(e.target),
                        output = list.data('output');
                if (window.JSON) {

                    var jsonData = window.JSON.stringify(list.nestable('serialize'));
                    $.ajax({
                        type: "POST",
                        url: "{!! URL::route('admin.taxonomy.save') !!}",
                        data: {'json': jsonData},
                        headers: {
                            'X-CSRF-Token': $('meta[name="_token"]').attr('content')
                        },
                        success: function (response) {
                            $("#msg").append('<div class="msg-save alert alert-success" role="alert">{{{ trans("app.".$post_type."_taxonomy_sorted") }}}</div>');
                            $('.msg-save').delay(4000).fadeOut(700);
                        },
                        error: function () {
                            alert("error");
                        }
                    });

                } else {
                    alert('error');
                }
            };

            $('#nestable').nestable({
                group: 1
            }).on('change', updateOutput);
        });
    </script>
@stop
