@extends('backend.layout.layout')
@section('content')
<section class="content-header">
    <h1> {{{ trans('app.role') }}}  </h1>
    <ol class="breadcrumb">
        <li><a href="{!! langRoute('admin.user.role.index') !!}"><i class="fa fa-bookmark"></i> {{{ trans('app.role') }}}</a></li>
        <li class="active">{{{ trans('app.show') }}}</li>
    </ol>
</section>
<div class="content">
    <div class="box">  
        <div class="box-header with-border">
            <h3 class="box-title">{!! $role->name !!}</h3>
        </div>
        <div class="box-body">
            <div class="form-group">
                {!! Form::label('name', trans('app.name')) !!}
                <div>{!! $role->name !!}</div>
            </div>
            <div class="form-group">
                @if($role->slug)
                    <?php
                        $td_attribute = '<tr>';
                        foreach($role->slug as $capability => $is_able){
                            if($is_able)
                                $td_attribute .= '<td>'.$capability.'</td></tr>';
                        }
                    ?>
                    <table class="table table-bordered table-condensed table-white table-striped conrows">
                        <thead>
                            <tr>
                                <th>{{{ trans('app.capability') }}}</th>
                            </tr>
                        </thead>
                        <tbody>
                            {!! $td_attribute !!}
                        </tbody>
                    </table>
                @endif
            </div>
            <div class="form-group">
                {!! Form::label('permission', trans('app.permissions')) !!}
              <div>
                <dl class="permissions-wrapper">
                    
                    <?php
                        $perm_groups = [];
                        foreach($role->perms as $permission){
                            $perm_groups[$permission->module][] = $permission->display_name;
                        }
                    ?>
                    @foreach($perm_groups as $module => $perms)
                        <dt>{{{ $module }}}</dt>
                        
                            @foreach($perms as $perm)
                                <dd>&nbsp;&nbsp;<i class="fa fa-unlock-alt" aria-hidden="true"></i>&nbsp;&nbsp;{!! $perm !!}</dd>
                            @endforeach
                    @endforeach
                </dl>

                </div>
            </div>
            <div class="form-group">
                {!! Form::label('created_date', trans('app.created_date')) !!}
                <div>{!! $role->created_at !!}</div>
            </div>
            <div class="form-group">
                {!! Form::label('updated_date', trans('app.updated_date')) !!}
                <div>{!! $role->updated_at !!}</div>
            </div>
        </div>
        <div class="box-footer">
            {!! link_to( langRoute('admin.user.role.index'), trans('app.back'), array( 'class' => 'btn btn-primary' ) ) !!}
        </div>
    </div>
</div>
@stop
