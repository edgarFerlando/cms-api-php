@extends('backend.layout.layout')
@section('content')
<section class="content-header">
	<h1>{!! trans('app.wallet_settings') !!}</h1>
	<ol class="breadcrumb">
		<li><a href="{!! url(getLang() . '/admin/settings') !!}"><i class="fa fa-bookmark"></i> {!! trans('app.settings') !!}</a></li>
		<li class="active">{!! trans('app.wallet') !!}</li>
	</ol>
</section>
<div class="content">
	<div class="box">  
		{!! Form::open(array('action' => '\App\Http\Controllers\Backend\SettingController@walletStore')) !!}
		<div class="box-header with-border">
            <h3 class="box-title">{!! trans('app.wallet') !!}</h3>
        </div>
		<div class="box-body">
			<div class="form-group {!! $errors->has('default_taxo_wallet_id_on_reminder_action') ? 'has-error' : '' !!}">
				{!! Form::label('default_taxo_wallet_id_on_reminder_action', trans('app.default_taxo_wallet_id_on_reminder_action').' *') !!}
				{!!  Form::select('default_taxo_wallet_id_on_reminder_action', $wallet_category_options, config_db_cached('settings::default_taxo_wallet_id_on_reminder_action'), [ 'class' => 'selectize' ]) !!}
				<span class="help-block info">Taxo wallet id on reminder action. Especially for predefined reminder such us breakfast, lunch and dinner.</span>
				@if ($errors->first('default_taxo_wallet_id_on_reminder_action'))
					<span class="help-block">{!! $errors->first('default_taxo_wallet_id_on_reminder_action') !!}</span>
				@endif
			</div>
		</div>
		<div class="box-footer">
			{!! Form::submit(trans('app.save'), array('class' => 'btn btn-success')) !!}
		</div>
		{!! Form::close() !!}
		</div>
</div>
@stop
