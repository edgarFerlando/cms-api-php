@extends('backend.layout.layout')
@section('content')
<section class="content-header">
	<h1>{!! trans('app.user') !!}</h1>
	<ol class="breadcrumb">
		<li><a href="{!! url(getLang() . '/admin/user') !!}"><i class="fa fa-bookmark"></i> {!! trans('app.user') !!}</a></li>
		<li class="active">{!! trans('app.add_new') !!}</li>
	</ol>
</section>
<div class="content">
	<div class="box">  
		{!! Form::open(array('action' => '\App\Http\Controllers\Backend\UserController@store')) !!}
		<div class="box-header with-border">
            <h3 class="box-title">{!! trans('app.add_new') !!}</h3>
        </div>
		<div class="box-body">
			{{-- {!! dd($errors)!!} --}}
			<div class="form-group {!! $errors->has('name') ? 'has-error' : '' !!}">
                {!! Form::label('name', trans('app.first_name').' *') !!}
                {!! Form::text('name', Input::old('name'), [ 'class' => 'form-control', 'autocomplete' => 'off' ]) !!}
                @if ($errors->first('name'))
                    <span class="help-block">{!! $errors->first('name') !!}</span>
                @endif
            </div>
            <div class="form-group {!! $errors->has('last_name') ? 'has-error' : '' !!}">
                {!! Form::label('last_name', trans('app.last_name')) !!}
                {!! Form::text('last_name', Input::old('last_name'), [ 'class' => 'form-control', 'autocomplete' => 'off' ]) !!}
                @if ($errors->first('last_name'))
                    <span class="help-block">{!! $errors->first('last_name') !!}</span>
                @endif
            </div>
            <div class="form-group {!! $errors->has('branch') ? 'has-error' : '' !!}">
				{!! Form::label('branch', trans('app.branch')) !!}
				{!! Form::select('branch', $branch_options, Input::old('branch'), [ 'class' => 'selectize', 'autocomplete' => 'off' ]) !!}
				@if ($errors->first('branch'))
					<span class="help-block">{!! $errors->first('branch') !!}</span>
				@endif
			</div>
            {!! Form::hidden('full_name', '', [ 'class' => '' ]) !!}
			<div class="form-group {!! $errors->has('email') ? 'has-error' : '' !!}">
				{!! Form::label('email', trans('app.email').' *') !!}
				{!! Form::text('email', Input::old('email'), [ 'class' => 'form-control', 'autocomplete' => 'off' ]) !!}
				@if ($errors->first('email'))
					<span class="help-block">{!! $errors->first('email') !!}</span>
				@endif
			</div>
			<div class="form-group {!! $errors->has('password') ? 'has-error' : '' !!}">
				{!! Form::label('password', trans('app.password').' *') !!}
				{!! Form::password('password', [ 'class' => 'form-control', 'autocomplete' => 'off' ]) !!}
				@if ($errors->first('password'))
					<span class="help-block">{!! $errors->first('password') !!}</span>
				@endif
			</div>
			<div class="form-group {!! $errors->has('password_confirmation') ? 'has-error' : '' !!}">
				{!! Form::label('password_confirmation', trans('app.password_confirmation').' *') !!}
				{!! Form::password('password_confirmation', [ 'class' => 'form-control', 'autocomplete' => 'off' ]) !!}
				@if ($errors->first('password_confirmation'))
					<span class="help-block">{!! $errors->first('password_confirmation') !!}</span>
				@endif
			</div>
			<div class="form-group {!! $errors->has('role') ? 'has-error' : '' !!}">
				{!! Form::label('role', trans('app.role').' *') !!}
				{!! Form::select('role', $roles, Input::old('role'), [ 'class' => 'selectize', 'autocomplete' => 'off' ]) !!}
				@if ($errors->first('role'))
					<span class="help-block">{!! $errors->first('role') !!}</span>
				@endif
			</div>
			<?php
                $male = trans('app.male');
                $female = trans('app.female');
            ?>
            <div class="form-group {!! $errors->has('gender') ? 'has-error' : '' !!}">
                {!! Form::label('gender', trans('app.gender').' *') !!}
                {!! Form::select('gender', ['M'=> $male,'F'=> $female ], Input::old('gender'), [ 'class' => 'selectize', 'autocomplete' => 'off' ]) !!}
                @if ($errors->first('gender'))
                    <span class="help-block">{!! $errors->first('gender') !!}</span>
                @endif
            </div> 
            <div class="form-group {!! $errors->has('date_of_birth') ? 'has-error' : '' !!}">
                {!! Form::label('date_of_birth', trans('app.date_of_birth')) !!}
                {!! Form::text( 'date_of_birth', Input::old('date_of_birth'), [ 'class' => 'form-control dob_bdatepicker'] ) !!}
                @if ($errors->first('date_of_birth'))
                    <span class="help-block">{!! $errors->first('date_of_birth') !!}</span>
                @endif
            </div>
            <div class="form-group {!! $errors->has('phone') ? 'has-error' : '' !!}">
                {!! Form::label('phone', trans('app.phone')) !!}
                {!! Form::text( 'phone', Input::old('phone'), ['class' => 'form-control', 'autocomplete' => 'off'] ) !!}
                @if ($errors->first('phone'))
                    <span class="help-block">{!! $errors->first('phone') !!}</span>
                @endif
			</div>
			<div class="form-group {!! $errors->has('certificate_no') ? 'has-error' : '' !!}">
                {!! Form::label('certificate_no', trans('app.certificate_no')) !!}
                {!! Form::text( 'certificate_no', Input::old('certificate_no'), ['class' => 'form-control', 'autocomplete' => 'off'] ) !!}
                @if ($errors->first('certificate_no'))
                    <span class="help-block">{!! $errors->first('certificate_no') !!}</span>
                @endif
			</div>
			<div class="form-group {!! $errors->has('description') ? 'has-error' : '' !!}">
                {!! Form::label('description', trans('app.description')) !!}
                {!! Form::text( 'description', Input::old('description'), ['class' => 'form-control', 'autocomplete' => 'off'] ) !!}
                @if ($errors->first('description'))
                    <span class="help-block">{!! $errors->first('description') !!}</span>
                @endif
            </div>
			<div class="form-group {!! $errors->has('photo') ? 'has-error' : '' !!}">
				{!! Form::label('photo', trans('app.photo')) !!}
				{!! 
					Form::cke_image('photo', Input::old('photo'), [ 'class'=>'form-control', 'height' => 200 ])
				!!}
				@if ($errors->first('photo'))
					<span class="help-block">{!! $errors->first('photo') !!}</span>
				@endif
			</div>
            <div class="form-group {!! $errors->has('address') ? 'has-error' : '' !!}">
                {!! Form::label('address', trans('app.address')) !!}
                {!! Form::ckeditor('address', Input::old('address'), [ 'class'=>'form-control', 'height' => '200'], $errors) !!}
                @if ($errors->first('address'))
                    <span class="address">{!! $errors->first('address') !!}</span>
                @endif
            </div>
            <div class="form-group {!! $errors->has('reference_code') ? 'has-error' : '' !!}">
                {!! Form::label('reference_code', trans('app.reference_code')) !!}
                {!! Form::text( 'reference_code', Input::old('reference_code'), ['class' => 'form-control', 'autocomplete' => 'off'] ) !!}
                @if ($errors->first('reference_code'))
                    <span class="help-block">{!! $errors->first('reference_code') !!}</span>
                @endif
            </div>
			<div class="form-group {!! $errors->has('cutoff_date') ? 'has-error' : '' !!}">
                {!! Form::label('cutoff_date', trans('app.cutoff_date')) !!}
                {!! Form::select( 'cutoff_date', $cutoff_date_options, Input::old('cutoff_date'), ['class' => 'form-control', 'autocomplete' => 'off'] ) !!}
                <span class="help-block info">Akan berpengaruh pada End of month balance dan scheduler End of month balance</span>
				@if ($errors->first('cutoff_date'))
                    <span class="help-block">{!! $errors->first('cutoff_date') !!}</span>
                @endif
            </div>
			<div class="form-group {!! $errors->has('is_active') ? 'has-error' : '' !!}">
			    <label>
			      {!! Form::checkbox('is_active', 'is_active').' '.trans('app.active') !!} ?
			    </label>
			    @if ($errors->first('is_active'))
					<span class="help-block">{!! $errors->first('is_active') !!}</span>
				@endif
			</div>
			<h4>Bank accounts</h4><hr />
			<div class="form-group {!! $errors->has('bca_acc') ? 'has-error' : '' !!}">
                {!! Form::label('bca_acc', 'BCA') !!}
                {!! Form::text('bca_acc', Input::old('bca_acc'), [ 'class' => 'form-control', 'autocomplete' => 'off' ]) !!}
                @if ($errors->first('bca_acc'))
                    <span class="help-block">{!! $errors->first('bca_acc') !!}</span>
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
