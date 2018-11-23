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
                                    <div class="content-wrapper">
                                        <div class="search-result horizontal-items">
                                            <div class="row">
                                                @if(isset($current_user[0]))
                                                    <a href="{!! URL::route('messages.create') !!}" class="button">Add message</a>
                                                @endif

                                                @if (Session::has('error_message'))
                                                    <div class="alert alert-danger" role="alert">
                                                        {!! Session::get('error_message') !!}
                                                    </div>
                                                @endif
                                                @if($threads->count() > 0)
                                                    @foreach($threads as $thread)
                                                    <?php 
                                                        $class = $thread->isUnread($currentUserId) ? 'alert-info' : ''; 
                                                        $user_id = $thread->participants[1]->user_id;

                                                        $user = $user::with(['userMetas'])->find($user_id);
                                                        $userMeta = userMeta($user->userMetas);
                                                        //dd($current_user);
                                                    ?>
                                                    <div class="small-12 columns item alert {!!$class!!}">
                                                        <div class="panel shadow box">
                                                            @if(isset($userMeta->user_image) == '' || !File::exists(public_path($userMeta->user_image)))
                                                                <img data-src="holder.js/130x130?text=&font=FontAwesome&size=14&bg=#fff&fg=#868686&fontweight=normal" />
                                                            @else
                                                                <img class="squareClip" src="{{ asset($userMeta->user_image) }}"/>
                                                            @endif

                                                            <ul class="no-bullet">
                                                                            
                                                                <li class="title"><h4>{!! link_to('messages/' . $thread->id, $thread->subject) !!}</h4></li>
                                                                <li class="title"><strong>Creator:</strong> {!! $thread->creator()->name !!}</li>
                                                                <li class="title"><strong>Participants: </strong> {!! $thread->participantsString(Auth::id()) !!}</li>
                                                                <li class="description">{!! $thread->latestMessage->body !!}</li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                    @endforeach
                                                @else
                                                    <p>Sorry, no threads.</p>
                                                @endif
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
    </div>
</div>
@stop
