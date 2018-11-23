@extends('backend.layout.layout')
@section('content')
<section class="content-header">
    <h1> {!! $attr['title'] !!}</h1>
</section>
<div class="content">
    <div class="box">  
    	@if(isset($attr['box_title']))
    	<div class="box-header with-border">
            <h3 class="box-title">{!! $attr['box_title'] !!}</h3>
        </div>
        @endif
        <div class="box-body">
            {!! $attr['unauthorized_message'] !!}
        </div>
    </div>
</div>
@stop
