@extends('backend.layout.layout')
@section('content')
<section class="content-header">
    <h1> {!! $user->name !!}  </h1>
    <ol class="breadcrumb">
        <li><a href="{!! langRoute('admin.user.index') !!}"><i class="fa fa-bookmark"></i> {{{ trans('app.user') }}}</a></li>
        <li class="active">{{{ trans('app.show') }}}</li>
    </ol>
</section>
<div class="content">
    <div class="box">  
        <div class="box-header with-border">
            <h3 class="box-title">{!! $history_title !!}</h3>
        </div>
        <div class="box-body">
            <div class="row">
                <div class="col-lg-2">
                    @include('backend.user.history-versions') 
                </div>
                <div class="col-lg-10 border-left">
                    @yield('history_data')
                </div>
            </div>
        </div>
        <div class="box-footer">
            {!! link_to( langRoute('admin.cfp.client.index'), trans('app.back'), array( 'class' => 'btn btn-primary' ) ) !!}
        </div>
    </div>
</div>
@stop
