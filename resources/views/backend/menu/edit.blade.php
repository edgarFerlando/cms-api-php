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
    {!! Form::open( [ 'route' => [ getLang() . '.admin.menu.update', $menu->id ], 'method' => 'PATCH'] ) !!}
    <div class="box-header with-border">
      <h3 class="box-title">{!! trans('app.add_new') !!}</h3>
    </div>
    <div class="box-body">
      <div class="form-group {!! lang_errors_has($errors, 'title') ? 'has-error' : '' !!}">
        {!! Form::label('title', trans('app.title').' *') !!}
        {!! Form::lang_text('title', lang_val($menu, 'title'), [
          'class'         => 'form-control', 
          'autocomplete'  => 'off', 
          ], $errors) 
          !!}
        </div>
        <div class="form-group">
          {!! Form::label('type', trans('app.type')) !!}
          <div class="radio">
            <label>
              {!! Form::radio('type', 'module', val($menu, 'type') == 'module' ? true : false , array('id'=>'module', 'class'=>'type')) !!}
              <span>{{{ trans('app.module') }}}</span>
            </label>
          </div>
          <div class="radio">
            <label>
              {!! Form::radio('type', 'custom', val($menu, 'type') == 'custom' ? true : false , array('id'=>'custom', 'class'=>'type')) !!}
              <span>{{{ trans('app.custom') }}}</span>
            </label>
          </div>
        </div>
        <div class="form-group {!! $errors->has('options') ? 'has-error' : '' !!} modules">
          {!! Form::label('options', trans('app.options').' *') !!}
          <div class="controls">
            {!! Form::select('option', $options, val($menu, 'option'), [ 'class'=>'form-control', 'id' => 'options' ] ) !!}
            @if ($errors->first('options'))
            <span class="help-block">{!! $errors->first('options') !!}</span>
            @endif
          </div>
        </div>

        <!-- URL -->
        <div style="display:none" class="form-group {!! lang_errors_has($errors, 'url') ? 'has-error' : '' !!} url">
          {!! Form::label('url', trans('app.url').' *') !!}
          {!! Form::lang_text('url', lang_val($menu, 'url'), [
            'class'         => 'form-control', 
            'autocomplete'  => 'off', 
            ], $errors) 
            !!}
          </div>
          <div class="form-group">
            {!! Form::label('parent', trans('app.hierarchy')) !!}
            {!!  Form::select('parent', $parent_options, lang_val($menu, 'parent', 'parent_id'), [ 'class' => 'form-control' ]) !!}
          </div>
          <div class="form-group {!! $errors->has('is_published') ? 'has-error' : '' !!}">
            <label>
              {!! Form::checkbox('is_published', 'is_published', val( $menu, 'is_published' )).' '.trans('app.publish') !!} ?
            </label>
            @if ($errors->first('is_published'))
            <span class="help-block">{!! $errors->first('is_published') !!}</span>
            @endif
          </div>
        </div>
        <div class="box-footer">
          {!! Form::submit( trans('app.update') , array('class' => 'btn btn-success')) !!}
          {!! link_to( URL::route('admin.menu_group.items', [ 'menu_group' => $menu->menu_group_id ]), trans('app.cancel'), array( 'class' => 'btn btn-primary' ) ) !!}
        </div>
        {!! Form::close() !!}
      </div>
    </div>
    @stop