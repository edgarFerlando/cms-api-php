@extends('backend.layout.layout')
@section('content')
<section class="content-header">
  <h1>
    {!! trans('app.user') !!} 
    <ul class="action list-inline">
      <li>{!! HTML::decode( HTML::link(langRoute('admin.user.create'), '<i class="fa fa-plus-square"></i>'. trans('app.add_user')) ) !!}</li>
    </ul>
  </h1>
  <ol class="breadcrumb">
    <li><a href="{!! URL::route('admin.dashboard') !!}">{!! trans('app.dashboard') !!}</a></li>
    <li class="active">{!! trans('app.user') !!}</li>
  </ol>
</section>
<div class="content">
  {!! Notification::showAll() !!}
  <div class="box">  
    @if(Route::currentRouteName() == 'admin.user.filter')
      <div class="box-header with-border">
        <h3 class="box-title text-green">Result &nbsp;: &nbsp;<strong>{!! $totalItems.'</strong> item'.($totalItems > 1?'s':'') !!}</h3>
      </div>
    @endif
    <div class="box-body">

      <div class="row filter-form">
        {!! Form::open([ 'route' => [ 'admin.user.filter'] ]) !!}
        <div class="col-xs-2 clear-right">
          <div class="form-group">
            {!! Form::label('user_code', trans('app.code')) !!}
            {!! Form::text('user_code', Input::get('user_code'), [ 'class'  => 'form-control', 'autocomplete'   => 'off'], $errors) !!}
          </div>
        </div>
        <div class="col-xs-2 clear-right">
          <div class="form-group">
            {!! Form::label('branch_code', trans('app.branch')) !!}
            {!!  Form::select('branch_code', $branch_options, Input::get('branch_code'), [ 'class' => 'selectize' ]) !!}
          </div>
        </div>
        <div class="col-xs-2 clear-right">
          <div class="form-group">
            {!! Form::label('name', trans('app.name')) !!}
            {!! Form::text('name', Input::get('name'), [ 'class'  => 'form-control', 'autocomplete'   => 'off'], $errors) !!}
          </div>
        </div>
        <div class="col-xs-2 clear-right">
          <div class="form-group">
            {!! Form::label('role', trans('app.role')) !!}
            {!!  Form::select('role', $roles, Input::get('role'), [ 'class' => 'selectize' ]) !!}
          </div>
        </div>
        <div class="col-xs-1 clear-right">
          <div class="form-group">
            {!! Form::label('status', trans('app.status')) !!}
            {!!  Form::select('status', [ '' => '-', 0 => trans('app.not_active'), 1 => trans('app.active') ], Input::get('status'), [ 'class' => 'selectize' ]) !!}
          </div>
        </div>
        
        <div class="col-xs-2 clear-right">
          <div class="form-group action-tool">
            {!! Form::submit(trans('app.filter'), array('class' => 'btn btn-success')) !!}
            {!! HTML::link(langurl('admin/user'),trans('app.reset'), array('class' => 'btn btn-default')) !!}
          </div>
        </div>
        {!! Form::close() !!}
      </div>

      @if($users->count())
      <div class="table-responsive">
        <table class="table table-striped">
          <thead>
            <tr>
              <th>{!! trans('app.code') !!}</th>
              <th>{!! trans('app.branch') !!}</th>
              <th>{!! trans('app.name') !!}</th>
              <th>{!! trans('app.email') !!}</th>
              <th>{!! trans('app.role') !!}</th>
              <th>{!! trans('app.goals') !!}</th>
              <th>{!! trans('app.clients') !!}</th>
              <th>{!! trans('app.status') !!}</th>
              <th>{!! trans('app.updated_by') !!}</th>
              <th width="140">{!! trans('app.updated_date') !!}</th>
              <th>{!! trans('app.action') !!}</th>
            </tr>
          </thead>
          <tbody>
            @foreach( $users as $user )
            <?php
            $branch_name = '';
              $role_name = '';
              $role_id = '';
              foreach($user->roles as $role){
                   $role_name = $role->display_name;
                   $role_id = $role->id;
              } 
             /*if($user->id == 74){
                if(!is_null($user->userMeta_branch))
                  if($user->userMeta_branch->branch == null)
                    dd('kosong');
                  else
                    dd('isi');
             }*/
              
              //jika ada
              //$user->userMeta_branch->branch->title

              //$branch_name = !is_null($user->userMeta_branch->branch)?$user->userMeta_branch->branch->title:'';
                //&& is_null($user->userMeta_branch->branch)
             $branch_name = !is_null($user->userMeta_branch)?($user->userMeta_branch->branch != '' ? $user->userMeta_branch->branch->title : ''):'';

             // if(isset($user->userMeta_branch) && !is_null($user->userMeta_branch))
               // dd($user);
              $userMetas = userMeta($user->userMetas);
              //$branch = isset($user->userMeta_branch)?$user->userMeta_branch->taxonomyMeta:'';
              $default_role_id_client = config_db_cached('settings::default_role_id_client');
              $default_role_id_cfp = config_db_cached('settings::default_role_id_cfp');
              $user_code = isset($userMetas->user_code)?$userMetas->user_code:(($role_id==$default_role_id_client || $role_id==$default_role_id_cfp)?HTML::link(langRoute('admin.user.edit', array($user->id)),trans('app.generate_code')):'');
             
            ?>
            <tr>
              <td>{!! $user_code !!}</td>
              <td>{!! $branch_name !!}</td>
              <td>{!! link_to_route(getLang(). '.admin.user.show', $user->name, $user->id) !!}</td>
              <td>{!! $user->email !!}</td>
              <td>{!! $role_name !!}</td>
              <td>{!! $user->goalGrade->count() > 0 ? HTML::decode( HTML::link(route('admin.user.goals', array($user->id)), '<i class="fa fa-list"></i>') ):'' !!}</td>
              <td class="text-right">{!! !is_null($user->cfp_clients) && $default_role_id_cfp == $role_id?count($user->cfp_clients):'' !!}</td>
              <td>{!! is_active_title($user->is_active) !!}</td>
              <td>{!! $user->updated_by_name !!}</td>
              <td class="text-right">{!! fulldate_trans($user->updated_at) !!}</td>
              <td class="action">
                @if( $user->id != 1)
                  {!! HTML::decode( HTML::link(langRoute('admin.user.edit', array($user->id)), '<i class="fa fa-pencil"></i>') ) !!}
                  {!! HTML::decode( HTML::link(URL::route('admin.user.delete', array($user->id)), '<i class="fa fa-trash-o"></i>') ) !!}
                @endif
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
    {!! $users->render() !!}
  </div>
</div>
@stop