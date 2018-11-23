@extends('backend.layout.layout')
@section('content')
<section class="content-header">
    <h1> {{{ trans('app.email_template') }}}  </h1>
    <ol class="breadcrumb">
        <li><a href="{!! langRoute('admin.settings.email-template.index') !!}"><i class="fa fa-bookmark"></i> {{{ trans('app.email_template') }}}</a></li>
        <li class="active">{{{ trans('app.show') }}}</li>
    </ol>
</section>
<div class="content">
    <div class="box">  
        <div class="box-header with-border">
            <h3 class="box-title">{!! $emailTemplate->subject !!}</h3>
        </div>
        <div class="box-body">
            <div class="form-group">
                {!! Form::label('subject', trans('app.subject')) !!}
                <div>
                    {!! $emailTemplate->subject !!}
                </div>
            </div>
            <div class="form-group">
                {!! Form::label('body', trans('app.body')) !!}
                <div>
                    <pre>
                    {!! $emailTemplate->body !!}
                    </pre>
                </div>
            </div>
            <div class="form-group">
                {!! Form::label('created_date', trans('app.created_date')) !!}
                <div>{!! $emailTemplate->created_at !!}</div>
            </div>
            <div class="form-group">
                {!! Form::label('updated_date', trans('app.updated_date')) !!}
                <div>{!! $emailTemplate->updated_at !!}</div>
            </div>
        </div>
        <div class="box-footer">
            {!! link_to( langRoute('admin.settings.email-template.index'), trans('app.back'), array( 'class' => 'btn btn-primary' ) ) !!}
        </div>
    </div>
</div>
@stop