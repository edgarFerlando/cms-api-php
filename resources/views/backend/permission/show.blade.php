@extends('backend.layout.layout')
@section('content')
<section class="content-header">
    <h1> {{{ trans('app.permission') }}}  </h1>
    <ol class="breadcrumb">
        <li><a href="{!! langRoute('admin.user.permission.index') !!}"><i class="fa fa-bookmark"></i> {{{ trans('app.user_permission') }}}</a></li>
        <li class="active">{{{ trans('app.show') }}}</li>
    </ol>
</section>
<div class="content">
    <div class="box">  
        <div class="box-header with-border">
            <h3 class="box-title">{!! $userPermission->name !!}</h3>
        </div>
        <div class="box-body">
            <div class="form-group">
                {!! Form::label('name', trans('app.name')) !!}
                <div>{!! $userPermission->name !!}</div>
            </div>
            <div class="form-group">
                {!! Form::label('slug', trans('app.slug')) !!}
                @if($userPermission->slug)
                    <?php
                        $td_attribute = '<tr>';
                        foreach($userPermission->slug as $capability => $is_able){
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
                {!! Form::label('created_date', trans('app.created_date')) !!}
                <div>{!! $userPermission->created_at !!}</div>
            </div>
            <div class="form-group">
                {!! Form::label('updated_date', trans('app.updated_date')) !!}
                <div>{!! $userPermission->updated_at !!}</div>
            </div>
        </div>
        <div class="box-footer">
            {!! link_to( langRoute('admin.user.permission.index'), trans('app.back'), array( 'class' => 'btn btn-primary' ) ) !!}
        </div>
    </div>
</div>
@stop
