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
				  <dd><a href="{!! URL::route('messages') !!}">Messages</a></dd>
				  <dd><a href="{!! URL::route(getLang().'.mytrip') !!}">Your Trips</a></dd>
				  <dd><a href="#">Your Favorites</a></dd>
				  <dd><a href="{!! URL::route(getLang().'.myprofile') !!}">Profile</a></dd>
				  <dd><a href="#">Account</a></dd>
				</dl>
				{!! Notification::showAll() !!}
				<div class="row">
					<div class="small-12 columns">
						<div class="small-3 columns">
							<ul class="side-nav">
							  <li><a href="#" id="general-info-trig">General Information</a></li>
							  <li><a href="#" id="profile-pic-trig">Profile Picture</a></li>
							  <li><a href="#" id="id-info-trig">ID Information</a></li>
							</ul>
						</div>
						<div class="small-9 columns">
						<div class="panel shadow box">
							<div class="box-content" id="profile-content">
								<div id="general-info-view">
									@include('frontend.auth.profile.general-info')
								</div>
								<div id="profile-pic-view">
									@include('frontend.auth.profile.profile-pic')
								</div>
								<div id="id-info-view">
									@include('frontend.auth.profile.id-info')
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