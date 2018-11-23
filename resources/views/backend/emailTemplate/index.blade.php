@extends('backend/layout/layout')
@section('content')
<section class="content-header">
  <h1>
    {!! trans('app.email_template') !!} 
    <ul class="action list-inline">
      <li>{!! HTML::decode( HTML::link(langRoute('admin.settings.email-template.create'), '<i class="fa fa-plus-square"></i>'. trans('app.add_email_template')) ) !!}</li>
    </ul>
  </h1>
  <ol class="breadcrumb">
    <li><a href="{!! URL::route('admin.dashboard') !!}">{!! trans('app.dashboard') !!}</a></li>
    <li class="active">{!! trans('app.emailTemplate') !!}</li>
  </ol>
</section>
<div class="content">
  <div class="box">  
    <div class="box-body">
    {!! Notification::showAll() !!}
    @if($emailTemplates->count())
    <div class="table-responsive">
      <table class="table table-striped">
        <thead>
          <tr>
            <th>{{{ trans('app.module') }}}</th>
            <th>{{{ trans('app.subject') }}}</th>
            <th>{{{ trans('app.created_date') }}}</th>
            <th>{{{ trans('app.updated_date') }}}</th>
            <th>{{{ trans('app.action') }}}</th>
          </tr>
        </thead>
        <tbody>
          @foreach( $emailTemplates as $emailTemplate )
            <tr>
              <td>{!! $emailTemplate->emailTemplateModule->name !!}</td>
              <td>{!! link_to_route(getLang(). '.admin.settings.email-template.show', $emailTemplate->subject, $emailTemplate->id) !!}</td>
              
              <td>{!! $emailTemplate->created_at !!}</td>
              <td>{!! $emailTemplate->updated_at !!}</td>
              <td class="action">
                {!! HTML::decode( HTML::link(langRoute('admin.settings.email-template.edit', array($emailTemplate->id)), '<i class="fa fa-pencil"></i>') ) !!}
                {!! HTML::decode( HTML::link(URL::route('admin.settings.email-template.delete', array($emailTemplate->id)), '<i class="fa fa-trash-o"></i>') ) !!}
            </tr>
          @endforeach
      </tbody>
    </table>
  </div>
  @else
    <div class="alert alert-danger">{{{ trans('app.no_results_found') }}}</div>
  @endif
</div>
</div>
 <div class="text-center">
    {!! $emailTemplates->render() !!}
  </div>

</div>
@stop