@extends('backend.layout.layout')
@section('content')
    <link href="{!! url('backend/css/menu-management.css') !!}" rel="stylesheet" type="text/css" />
    <script src="{!! url('backend/js/jquery.nestable.js') !!}"></script>
    <meta name="_token" content="{!! csrf_token() !!}"/>
    <script type="text/javascript">
        $(document).ready(function () {

            // publish settings
            $(".publish").bind("click", function (e) {
                var id = $(this).attr('id');
                e.preventDefault();

                $.ajax({
                    type: "POST",
                    url: "{!! url(getLang() . '/admin/menu/" + id + "/toggle-publish/') !!}",
                    headers: {
                        'X-CSRF-Token': $('meta[name="_token"]').attr('content')
                    },
                    success: function (response) {
                        if (response['result'] == 'success') {
                            var imagePath = (response['changed'] == 1) ? 'fa fa-eye' : 'fa fa-eye-slash';
                            $('#publish-image-' + id ).attr('class', imagePath);
                        }
                    },
                    error: function () {
                        alert("error");
                    }
                });
            });
        });
    </script>
    <section class="content-header">
      <h1>
        {!! trans('app.menu_group') !!}
        <ul class="action list-inline">
          <li>{!! HTML::decode( HTML::link(langRoute('admin.menu.create', [ 'menu_group' => $menu_group_id ]), '<i class="fa fa-plus-square"></i>'. trans('app.add_menu')) ) !!}</li>
        </ul>
      </h1>
      <ol class="breadcrumb">
        <li><a href="{!! URL::route('admin.dashboard') !!}">{!! trans('app.dashboard') !!}</a></li>
        <li class="active">{!! trans('app.menu_group') !!} : {!! $menu_group->title !!}</li>
      </ol>
    </section>
    <div class="content">
        {!! Notification::showAll() !!}
        <div id="msg"></div>
        <div class="box"> 
            <div class="box-header with-border">
                <h3 class="box-title">{!! $menu_group->title !!}</h3>
            </div>
            <div class="box-body">
                @if($menus !== null)
                    <div class="dd" id="nestable">
                        {!! $menus !!}
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
                        url: "{!! URL::route('admin.menu.save') !!}",
                        data: {'json': jsonData},
                        headers: {
                            'X-CSRF-Token': $('meta[name="_token"]').attr('content')
                        },
                        success: function (response) {

                            //$("#msg").append('<div class="alert alert-success msg-save">Saved!</div>');
                            $("#msg").append('<div class="msg-save alert alert-success" role="alert">{{{ trans("app.menu_sorted") }}}</div>');
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
