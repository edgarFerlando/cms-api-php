@extends('backend.layout.layout')
@section('content')
<section class="content-header">
    <h1>{!! trans('app.role') !!}</h1>
    <ol class="breadcrumb">
        <li><a href="{!! url(getLang() . '/admin/user') !!}"><i class="fa fa-bookmark"></i> {!! trans('app.role') !!}</a></li>
        <li class="active">{!! trans('app.edit') !!}</li>
    </ol>
</section>
<div class="content">
    <div class="box">  
        {!! Form::open( array( 'route' => array( getLang() . '.admin.user.role.update', $role->id), 'method' => 'PATCH')) !!}
        <div class="box-header with-border">
            <h3 class="box-title">{!! trans('app.add_new') !!}</h3>
        </div>
        <div class="box-body">
            <div class="form-group {!! $errors->has('name') ? 'has-error' : '' !!}">
                {!! Form::label('name', trans('app.name').' *') !!}
                {!! Form::text('name', val($role, 'name'), [ 'class' => 'form-control', 'autocomplete' => 'off']) !!}
                @if ($errors->first('name'))
                    <span class="help-block">{!! $errors->first('name') !!}</span>
                @endif
            </div>
            <div class="form-group {!! $errors->has('display_name') ? 'has-error' : '' !!}">
                {!! Form::label('display_name', trans('app.display_name').' *') !!}
                {!! Form::text('display_name', val($role, 'display_name'), [ 'class' => 'form-control', 'autocomplete' => 'off' ]) !!}
                @if ($errors->first('display_name'))
                    <span class="help-block">{!! $errors->first('display_name') !!}</span>
                @endif
            </div>
            <div class="form-group {!! $errors->has('description') ? 'has-error' : '' !!}">
                {!! Form::label('description', trans('app.description')) !!}
                {!! Form::text('description', val($role, 'description'), [ 'class' => 'form-control', 'autocomplete' => 'off' ]) !!}
                @if ($errors->first('desciption'))
                    <span class="help-block">{!! $errors->first('desciption') !!}</span>
                @endif
            </div>
            <div class="form-group">
                <label for="">Permissions</label>
                <dl>
                    
                    <?php
                        $perm_groups = [];
                        foreach($permissions as $permission){
                            $checked = in_array($permission->id, $rolePerms->lists('id'));
                            $perm_groups[$permission->module][] = '<div class="checkbox">
                                <label>
                                    '.Form::checkbox('perms[]', $permission->id, $checked).' '.$permission->display_name.'
                                </label>
                            </div>';
                        }
                    ?>
                    @foreach($perm_groups as $module => $perms)
                        <dt>{{{ $module }}}</dt>
                        <dd>
                            @foreach($perms as $perm)
                                {!! $perm !!}
                            @endforeach
                        </dd>
                    @endforeach
                </dl>
            </div>
        </div>
        <div class="box-footer">
            {!! Form::submit(trans('app.save'), array('class' => 'btn btn-success')) !!}
            {!! link_to( langRoute('admin.user.role.index'), trans('app.cancel'), array( 'class' => 'btn btn-primary' ) ) !!}
        </div>
        {!! Form::close() !!}
        </div>
</div>
@stop
