@extends('backend.layout.layout')
@section('content')
<script type="text/javascript">
    $(document).ready(function () {
        $('.type').change(function () {
                var selected = $('input[class="type"]:checked').val();
                if (selected == "custom") {
                    $('.modules').css('display', 'none');
                    $('.url').css('display', 'block');
                }
                else {
                    $('.modules').css('display', 'block');
                    $('.url').css('display', 'none');
                }
            }
        );

        $(".type").trigger("change");
    });
</script>
<section class="content-header">
    <h1>{!! trans('app.menu') !!}</h1>
    <ol class="breadcrumb">
        <li><a href="{!! url(getLang() . '/admin/menu') !!}"><i class="fa fa-bookmark"></i> {!! trans('app.menu') !!}</a></li>
        <li class="active">{!! trans('app.add_new') !!}</li>
    </ol>
</section>
<div class="content">
  <div class="box">
    {!! Form::open(array('action' => '\App\Http\Controllers\Backend\MenuController@store') ) !!}
    {!! Form::hidden('menu_group', Input::get('menu_group')) !!}
    <div class="box-header with-border">
      <h3 class="box-title">{!! trans('app.add_new') !!}</h3>
    </div>
    <div class="box-body">
        <div class="form-group {!! lang_errors_has($errors, 'title') ? 'has-error' : '' !!}">
            {!! Form::label('title', trans('app.title').' *') !!}
            {!! Form::lang_text('title', Input::old('title'), [
                    'class'         => 'form-control', 
                    'autocomplete'  => 'off', 
                ], $errors) !!}
        </div>
        <div class="form-group">
            {!! Form::label('type', trans('app.type')) !!}
            <div class="radio">
                <label>
                    {!! Form::radio('type', 'module', false, array('id'=>'module', 'class'=>'type')) !!}
                    <span>{{{ trans('app.module') }}}</span>
                </label>
            </div>
            <div class="radio">
                <label>
                    {!! Form::radio('type', 'custom', true, array('id'=>'custom', 'class'=>'type')) !!}
                    <span>{{{ trans('app.custom') }}}</span>
                </label>
            </div>
        </div>
        <!-- Modules -->
        <div class="form-group {!! $errors->has('options') ? 'has-error' : '' !!} modules">
            {!! Form::label('options', trans('app.options').' *') !!}
            <div class="controls">
                {!! Form::select('option', $options, Input::old('options'), [ 'class'=>'form-control', 'id' => 'options' ] ) !!}
                @if ($errors->first('options'))
                    <span class="help-block">{!! $errors->first('options') !!}</span>
                @endif
            </div>
        </div>

        <!-- URL -->
        <div style="display:none" class="form-group {!! lang_errors_has($errors, 'url') ? 'has-error' : '' !!} url">
            {!! Form::label('url', trans('app.url').' *') !!}
            {!! 
                Form::lang_text('url', Input::old('url'), [
                    'class'         => 'form-control', 
                    'autocomplete'  => 'off', 
                ], $errors) 
            !!}
        </div>
        <div class="form-group">
            {!! Form::label('parent', trans('app.hierarchy')) !!}
            {!!  Form::select('parent', $parent_options, '', [ 'class' => 'form-control' ]) !!}
        </div>
        <div class="form-group {!! $errors->has('is_published') ? 'has-error' : '' !!}">
            <label>
              {!! Form::checkbox('is_published', 'is_published').' '.trans('app.publish') !!} ?
            </label>
            @if ($errors->first('is_published'))
                <span class="help-block">{!! $errors->first('is_published') !!}</span>
            @endif
        </div>
    </div>
    <div class="box-footer">
        {!! Form::submit( trans('app.save') , [ 'class' => 'btn btn-success' ] ) !!}
        {!! link_to( URL::route('admin.menu_group.items', [ 'menu_group' => Input::get('menu_group') ]), trans('app.cancel'), [ 'class' => 'btn btn-default' ] ) !!}

    </div>
    {!! Form::close() !!}
    </div>
</div>
@stop
