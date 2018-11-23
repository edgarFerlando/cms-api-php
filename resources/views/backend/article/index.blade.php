@extends('backend/layout/layout')
@section('content')
<script type="text/javascript">
$(document).ready(function () {
    // publish settings
    $(".publish").bind("click", function (e) {
      var id = $(this).attr('id');
      e.preventDefault();
      $.ajax({
        type: "POST",
        url: "{!! url(getLang() . '/admin/article/" + id + "/toggle-publish/') !!}",
        headers: {
          'X-CSRF-Token': $('meta[name="_token"]').attr('content')
        },
        success: function (response) {
          if (response['result'] == 'success') {
            var imagePath = (response['changed'] == 1) ? 'fa fa-eye' : 'fa fa-eye-slash';
            $("#publish-image-" + id).attr('class', imagePath);
          }
        },
        error: function () {
          alert("error");
        }
      })
    });
  });
</script>
<section class="content-header">
  <h1>
    {!! trans('app.article') !!} 
    <ul class="action list-inline">
      <li>{!! HTML::decode( HTML::link(langRoute('admin.article.create'), '<i class="fa fa-plus-square"></i>'. trans('app.add_article')) ) !!}</li>
      <li>{!! HTML::decode( HTML::link(URL::route('admin.taxonomy.create', [ 'post_type' => 'article' ] ), '<i class="fa fa-plus-square"></i>'. trans('app.add_category') ) ) !!}</li>
    </ul>
  </h1>
  <ol class="breadcrumb">
    <li><a href="{!! URL::route('admin.dashboard') !!}">{!! trans('app.dashboard') !!}</a></li>
    <li class="active">{!! trans('app.article') !!}</li>
  </ol>
</section>
<div class="content">
  <div class="box">  
    <div class="box-body">
    <div class="row filter-form">
        {!! Form::open([ 'route' => [ 'admin.article.filter'] ]) !!}
        <div class="col-xs-2 clear-right">
          <div class="form-group">
            {!! Form::label('title', trans('app.title')) !!}
            {!! Form::text('title', Input::get('title'), [ 'class'  => 'form-control', 'autocomplete'   => 'off']) !!}
          </div>
        </div>
        <div class="col-xs-2 clear-right">
          <div class="form-group">
            {!! Form::label('category', trans('app.category')) !!}
            {!!  Form::select('category', $cat_options, Input::get('category'), [ 'class' => 'selectize' ]) !!}
          </div>
        </div>  
        <div class="col-xs-2 clear-right">
          <div class="form-group action-tool">
            {!! Form::submit(trans('app.filter'), array('class' => 'btn btn-success')) !!}
            {!! HTML::link(langurl('admin/article'),trans('app.reset'), array('class' => 'btn btn-default')) !!}
          </div>
        </div>
        {!! Form::close() !!}
      </div>
    {!! Notification::showAll() !!}
    @if($articles->count())
    <div class="table-responsive">
      <table class="table table-striped">
        <thead>
          <tr>
            <th>{{{ trans('app.title') }}}</th>
            <th>{{{ trans('app.category') }}}</th>
            <th>{{{ trans('app.created_date') }}}</th>
            <th>{{{ trans('app.updated_date') }}}</th>
            <th>{{{ trans('app.action') }}}</th>
            <th>{{{ trans('app.publish') }}} ?</th>
          </tr>
        </thead>
        <tbody>
          @foreach( $articles as $article )
            <tr>
              <td>{!! link_to_route(getLang(). '.admin.article.show', $article->title, $article->id) !!}</td>
              <td>{!! $article->category->title !!}</td>
              <td>{!! $article->created_at !!}</td>
              <td>{!! $article->updated_at !!}</td>
              <td class="action">
                {!! HTML::decode( HTML::link(langRoute('admin.article.edit', array($article->id)), '<i class="fa fa-pencil"></i>') ) !!}
                {!! HTML::decode( HTML::link(URL::route('admin.article.delete', array($article->id)), '<i class="fa fa-trash-o"></i>') ) !!}
              </td>
              <td>
                {!! HTML::decode( 
                  HTML::link( '#', '<i id="publish-image-'.$article->id.'" class="fa fa-'.($article->is_published ? 'eye' : 'eye-slash').'"></i>', [ 'id' => $article->id, 'class' => 'publish' ]) 
                  ) !!}
              </td>
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
    {!! $articles->render() !!}
  </div>

</div>
@stop