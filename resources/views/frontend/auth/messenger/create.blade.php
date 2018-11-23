@extends('frontend.layout')
@section('body')
<div class="content-wrap detail collapse">
    <div class="banner">
        <img src="{{ asset('img/banner/spec-banner.jpg') }}">
    </div>
    <div class="row">
        <div class="large-12 columns">
            <div class="content-wrapper">
                <dl class="sub-nav">
                  <dd><a href="{!! URL::route('messages') !!}">Massages</a></dd>
                  <dd><a href="{!! URL::route(getLang().'.mytrip') !!}">Your Trips</a></dd>
                  <dd><a href="#">Your Favorites</a></dd>
                  <dd><a href="{!! URL::route(getLang().'.myprofile') !!}">Profile</a></dd>
                  <dd><a href="#">Account</a></dd>
                </dl>
                {!! Notification::showAll() !!}
                <div class="row">
                    <div class="small-12 columns">
                        <div class="small-12 columns">
                            <div class="panel shadow box">
                                <div class="box-content">
                                    <h1>Create a new message</h1>
                                    {!! Form::open(['route' => 'messages.store']) !!}
                                    <div class="col-md-6">
                                        <!-- Subject Form Input -->
                                        <div class="form-group {!! $errors->has('subject') ? 'error' : '' !!}">
                                            {!! Form::label('subject', 'Subject', ['class' => 'control-label']) !!}
                                            {!! Form::text('subject', null, ['class' => 'form-control']) !!}
                                            @if ($errors->first('subject'))
                                                <span class="help-block error">{!! $errors->first('subject') !!}</span>
                                            @endif
                                        </div>

                                        <!-- Message Form Input -->
                                        <div class="form-group {!! $errors->has('message') ? 'error' : '' !!}">
                                            {!! Form::label('message', 'Message', ['class' => 'control-label']) !!}
                                            {!! Form::textarea('message', null, ['class' => 'form-control']) !!}
                                            @if ($errors->first('message'))
                                                <span class="help-block error">{!! $errors->first('message') !!}</span>
                                            @endif
                                        </div>

                                        @if($users->count() > 0)
                                        <div class="form-group {!! $errors->has('recipients') ? 'error' : '' !!}">
                                            @if ($errors->first('recipients'))
                                                <span class="help-block error">{!! $errors->first('recipients') !!}</span>
                                            @endif
                                            @foreach($users as $user)
                                                <label title="{!!$user->name!!}"><input type="radio" name="recipients" value="{!!$user->id!!}">{!!$user->name!!}</label>
                                            @endforeach
                                        </div>
                                        @else
                                            @if ($errors->first('recipients'))
                                                <span class="help-block error">{!! $errors->first('recipients') !!}</span>
                                            @endif
                                        @endif
                                        
                                        <!-- Submit Form Input -->
                                        <div class="form-group">
                                            {!! Form::submit('Submit', ['class' => 'btn btn-primary form-control']) !!}
                                        </div>
                                    </div>
                                    {!! Form::close() !!}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop
