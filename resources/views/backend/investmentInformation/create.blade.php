@extends('backend.layout.layout')
@section('content')
<section class="content-header">
	<h1>{!! trans('app.investment_information') !!}</h1>
	<ol class="breadcrumb">
		<li><a href="{!! url(getLang() . '/admin/investment-information') !!}"><i class="fa fa-bookmark"></i> {!! trans('app.investment_information') !!}</a></li>
		<li class="active">{!! trans('app.add_new') !!}</li>
	</ol>
</section>
<div class="content">
	<div class="box">  
		{!! Form::open(array('action' => '\App\Http\Controllers\Backend\InvestmentInformationController@store')) !!}
		<div class="box-header with-border">
			<h3 class="box-title">{!! trans('app.add_new') !!}</h3>
		</div>
		<div class="box-body">
			<div class="form-group {!! $errors->has('product_name') ? 'has-error' : '' !!}">
				{!! Form::label('product_name', trans('app.product_name').' *') !!}
				{!! 
					Form::text('product_name', Input::old('product_name'), [
						'class'			=> 'form-control', 
						'autocomplete' 	=> 'off', 
					]) 
				!!}
				@if ($errors->first('product_name'))
					<span class="help-block">{!! $errors->first('product_name') !!}</span>
				@endif
			</div>
			<div class="form-group {!! $errors->has('description') ? 'has-error' : '' !!}">
				{!! Form::label('description', trans('app.description').' *') !!}
				{!! 
					Form::textarea('description', Input::old('description'), [
						'class'=>'form-control',
						'rows'=> 3
					]) 
				!!}
				@if ($errors->first('description'))
					<span class="help-block">{!! $errors->first('description') !!}</span>
				@endif
			</div>
			<div class="form-group">
				<div class="row">
					<div class="col-lg-3">
						<div class="form-group {!! $errors->has('nab') ? 'has-error' : '' !!}">
						
							{!! Form::label('nab', trans('app.nab').' *') !!}
							{!! 
								Form::text('nab', Input::old('nab'), [
									'class'			=> 'form-control number', 
									'autocomplete' 	=> 'off', 
								]) 
							!!}
							@if ($errors->first('nab'))
								<span class="help-block">{!! $errors->first('nab') !!}</span>
							@endif
						</div>
					</div>
					<div class="col-lg-3">
						<div class="form-group {!! $errors->has('scoring_3_thn') ? 'has-error' : '' !!}">
							{!! Form::label('scoring_3_thn', trans('app.scoring_3_thn').' *') !!}
							{!! 
								Form::select('scoring_3_thn', $stars_options, Input::old('scoring_3_thn'), [
									'class'			=> 'form-control', 
									'autocomplete' 	=> 'off', 
								]) 
							!!}
								<span class="help-block">{!! $errors->first('scoring_3_thn') !!}</span>
						</div>
					</div>
					<div class="col-lg-3">
						<div class="form-group {!! $errors->has('inv_1_hr') ? 'has-error' : '' !!}">
							{!! Form::label('inv_1_hr', trans('app.1_hr').' *') !!}
							{!! 
								Form::text('inv_1_hr', Input::old('inv_1_hr'), [
									'class'			=> 'form-control number', 
									'autocomplete' 	=> 'off', 
								]) 
							!!}
							@if ($errors->first('inv_1_hr'))
								<span class="help-block">{!! $errors->first('inv_1_hr') !!}</span>
							@endif
						</div>
					</div>
					<div class="col-lg-3">
						<div class="form-group {!! $errors->has('inv_1_bln') ? 'has-error' : '' !!}">
							{!! Form::label('inv_1_bln', trans('app.1_bln').' *') !!}
							{!! 
								Form::text('inv_1_bln', Input::old('inv_1_bln'), [
									'class'			=> 'form-control number', 
									'autocomplete' 	=> 'off', 
								]) 
							!!}
							@if ($errors->first('inv_1_bln'))
								<span class="help-block">{!! $errors->first('inv_1_bln') !!}</span>
							@endif
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-lg-3">
						<div class="form-group {!! $errors->has('inv_1_thn') ? 'has-error' : '' !!}">
							{!! Form::label('inv_1_thn', trans('app.1_thn').' *') !!}
							{!! 	
								Form::text('inv_1_thn', Input::old('inv_1_thn'), [
									'class'			=> 'form-control number', 
									'autocomplete' 	=> 'off', 
								]) 
							!!}
							@if ($errors->first('inv_1_thn'))
								<span class="help-block">{!! $errors->first('inv_1_thn') !!}</span>
							@endif
						</div>
					</div>
					<div class="col-lg-3">
						<div class="form-group {!! $errors->has('inv_3_thn') ? 'has-error' : '' !!}">
							{!! Form::label('inv_3_thn', trans('app.3_thn').' *') !!}
							{!! 
								Form::text('inv_3_thn', Input::old('inv_3_thn'), [
									'class'			=> 'form-control number', 
									'autocomplete' 	=> 'off', 
								]) 
							!!}
							@if ($errors->first('inv_3_thn'))
								<span class="help-block">{!! $errors->first('inv_3_thn') !!}</span>
							@endif
						</div>
					</div>
					<div class="col-lg-3">
						<div class="form-group {!! $errors->has('since_launched') ? 'has-error' : '' !!}">
							{!! Form::label('since_launched', trans('app.since_launched').' *') !!}
							{!! 
								Form::text('since_launched', Input::old('since_launched'), [
									'class'			=> 'form-control number', 
									'autocomplete' 	=> 'off', 
								]) 
							!!}
							@if ($errors->first('since_launched'))
								<span class="help-block">{!! $errors->first('since_launched') !!}</span>
							@endif
						</div>
					</div>
					<div class="col-lg-3">
						<div class="form-group {!! $errors->has('fluctuation') ? 'has-error' : '' !!}">
							{!! Form::label('fluctuation', trans('app.fluctuation').' *') !!}
							{!! 
								Form::text('fluctuation', Input::old('fluctuation'), [
									'class'			=> 'form-control number', 
									'autocomplete' 	=> 'off', 
								]) 
							!!}
							@if ($errors->first('fluctuation'))
								<span class="help-block">{!! $errors->first('fluctuation') !!}</span>
							@endif
						</div>
					</div>
				</div>
			</div>
			
			<h3>Providers</h3>
			<div class="form-group">
				<div class="conrows-scope provider-wrap" id="add-provider-form">
					<div class="form-only">
						<?php
							$ff_providers = [];
						?>
						{!! Form::hidden('ff_providers', rawurlencode(json_encode($ff_providers))) !!}
						<div class="row">
							<div class="col-lg-2">
								<div class="form-group">
									{!! Form::label('provider_name', trans('app.provider_name').' *') !!}
									{!! Form::text('provider_name', '', [ 'class'=>'form-control', 'autocomplete' => 'off']) !!}
									{!! Form::hidden('provider_id') !!}
									<span class="help-block"></span>
								</div>
							</div>
							<div class="col-lg-2">
								<div class="form-group">
									{!! Form::label('pic_name', trans('app.pic_name').' *') !!}
									{!! Form::text('pic_name', '', [ 'class'=>'form-control', 'autocomplete' => 'off']) !!}
									<span class="help-block"></span>
								</div>
							</div>
							<div class="col-lg-2">
								<div class="form-group">
									{!! Form::label('pic_email', trans('app.pic_email').' *') !!}
									{!! Form::text('pic_email', Input::old('pic_email'), [ 'class'=>'form-control', 'autocomplete' => 'off']) !!}
									<span class="help-block"></span>
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-lg-12">
								<div class="form-group">
									{!! HTML::link('#', trans('app.add'), [ 'class' => 'btn btn-primary btn-addto_conrows' ]) !!}
									{!! HTML::link('#', trans('app.cancel'), [ 'class' => 'btn btn-default btn-addto_conrows-reset' ]) !!}
								</div>
							</div>
						</div>
					</div>
					<br />
					<div class="row">
						<div class="col-lg-12">
							<table class="table table-bordered table-condensed table-white table-striped conrows">
								<thead>
									<tr>
										<th>{{{ trans('app.provider_name') }}}</th>
										<th>{{{ trans('app.pic_name') }}}</th>
										<th>{{{ trans('app.pic_email') }}}</th>
										<th>?</th>
									</tr>
								</thead>
								<tbody>
									<?php
									$provider_datarow = Input::old('provider_datarow');
									if (!is_null($provider_datarow)) {
										$provider_datarow = buildPOST_fromJS($provider_datarow); 
														//dd($variant_datarow);
										foreach ($provider_datarow as $idx => $provider) { 
											$hidden_data = mapping_fieldType('provider', $provider);
											echo '<tr>';
											echo '<td>' . $provider['provider_name']['val'] . '</td>';
											echo '<td>' . $provider['pic_name']['val'] . '</td>';
											echo '<td>' . $provider['pic_email']['val'] . '</td>';
											echo '<td width="65">
											<input type="hidden" value="' . $hidden_data . '" name="provider_datarow[' . $idx . ']">
											<button type="button" class="btn btn-danger btn-xs edit_conrow">
											<span class="fa fa-pencil"></span>
											</button>&nbsp;
											<button type="button" class="btn btn-danger btn-xs clear_conrow">
											<span class="fa fa-times"></span>
											</button>
											</td>';
											echo '</tr>';
										}
									}
									?>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="box-footer">
			{!! Form::submit(trans('app.save'), array('class' => 'btn btn-success')) !!}
			{!! link_to( langRoute('admin.investment-information.index'), trans('app.cancel'), array( 'class' => 'btn btn-primary  form-controll' ) ) !!}
		</div>
		{!! Form::close() !!}
	</div>
</div>
{!! Form::hidden('variant_data') !!}
@include('backend.modal.variant')
@include('backend.modal.variant_option')
@stop