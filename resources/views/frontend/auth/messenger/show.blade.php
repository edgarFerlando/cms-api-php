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
                                    <div class="col-md-6">
                                        <h1>{!! $thread->subject !!}</h1>
                                        
                                        @foreach($thread->messages as $message)
                                            @if($message->user->id == Auth::user()->id)
                                                <div class="media text-right">
                                                    <a class="pull-right" href="#">
                                                        <img src="//www.gravatar.com/avatar/{!! md5($message->user->email) !!}?s=64" alt="{!! $message->user->name !!}" class="img-circle">
                                                    </a>
                                                    <div class="media-body">
                                                        <h5 class="media-heading">{!! $message->user->name !!}</h5>
                                                        <p>{!! $message->body !!}</p>
                                                        <div class="text-muted"><small>Posted {!! $message->created_at->diffForHumans() !!}</small></div>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="media text-left">
                                                    <a class="pull-left" href="#">
                                                        <img src="//www.gravatar.com/avatar/{!! md5($message->user->email) !!}?s=64" alt="{!! $message->user->name !!}" class="img-circle">
                                                    </a>
                                                    <div class="media-body">
                                                        <h5 class="media-heading">{!! $message->user->name !!}</h5>
                                                        <p>{!! $message->body !!}</p>
                                                        <div class="text-muted"><small>Posted {!! $message->created_at->diffForHumans() !!}</small></div>
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach

                                        <h2>Add a new message</h2>
                                        {!! Form::open(['route' => ['messages.update', $thread->id], 'method' => 'PUT']) !!}
                                        <!-- Message Form Input -->
                                        <div class="form-group {!! $errors->has('message') ? 'error' : '' !!}">
                                            {!! Form::textarea('message', null, ['class' => 'form-control']) !!}
                                            @if ($errors->first('message'))
                                                <span class="help-block error">{!! $errors->first('message') !!}</span>
                                            @endif
                                        </div>

                                        {{-- @if($users->count() > 0)
                                        <div class="checkbox">
                                            @foreach($users as $user)
                                                <label title="{!! $user->name !!}"><input type="checkbox" name="recipients[]" value="{!! $user->id !!}">{!! $user->name !!}</label>
                                            @endforeach
                                        </div>
                                        @endif --}}

                                        <!-- Submit Form Input -->
                                        <div class="form-group">
                                            {!! Form::submit('Submit', ['class' => 'btn btn-primary form-control']) !!}
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
</div>
@stop
