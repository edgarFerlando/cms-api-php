@extends('frontend.layout')
@section('body')
<div class="content-wrap detail collapse">
	<div class="banner">
		<img src="{{ asset('img/banner/spec-banner.jpg') }}">
	</div>

	<div class="row">
		<div class="large-12 columns">
			<div class="content-wrapper">
				<div class="row">
					<div class="small-12 columns">
						<div class="small-2 columns item">
							<div class="panel shadow box">
								@if(isset($userMeta->user_thumbnail) && $userMeta->user_thumbnail != '')
									<img src="{!! $userMeta->user_thumbnail !!}" width="150px" height="100px"> 
								@else
									<img data-src="holder.js/130x130?text=&font=FontAwesome&size=14&bg=#fff&fg=#868686&fontweight=normal" />
								@endif
							</div>
						</div>
						<?php
							if($userMeta->full_name == '')
							{
								$name = $user->name;
							}
							else
							{
								$name = $userMeta->full_name;
							}
							//dd($user);

							$registered = date('Y/m', strtotime($user->created_at));
							$male = trans('app.male');
							$female = trans('app.female');
							$gender = '';

							if($userMeta->gender == 'M')
							{
								$gender = $male;
							}
							elseif ($userMeta->gender == 'F') {
								$gender = $female;
							}

						?>
						<div class="small-10 columns item">
							<label>{!! trans('app.name')." : ".$name !!} </label>
						</div>

						@if(strpos($userMeta->language_skill, ',') !== false)						  			
						  	<?php
						  		$lang_skill = explode(',', $userMeta->language_skill);
						  	?>
						  <div class="small-10 columns item">
							  <label>{!! trans('app.language')." : "!!}
												</label>	
							  <div class="small-8 columns item">
									<ul class="small-block-grid-12">
									  	@foreach($lang_skill as $skill)					  					
									  		<li class="text-center">{!! $skill !!}</li>
									  	@endforeach
									 </ul>
							  </div>
						  </div>
						@else
					  		<div class="small-10 columns item">
								<label>{!! trans('app.language')." : ".$userMeta->language_skill !!}
												</label>
							</div>
						@endif	

						<div class="small-10 columns item">
							<label>{!! trans('app.registered')." : ".$registered !!}
											</label>
						</div>

						<div class="small-12 columns item">
							<h3>{!! trans('app.about_me')." : " !!}</h3>
							<label>{!! $userMeta->about_me !!}
							</label>
						</div>

						<div class="small-12 columns item">
							<h3>{!! trans('app.occupation')." : " !!}</h3>
							<label>{!! $userMeta->occupation !!}
							</label>
						</div>

						<div class="small-12 columns item">
							<h3>{!! trans('app.hobbies')." : " !!}</h3>
							<label>{!! $userMeta->hobbies !!}
							</label>
						</div>		

						<div class="small-12 columns item">
							<h3>{!! trans('app.my_trip')." : " !!}</h3>
							<div class="content-wrapper">
								<div class="search-result horizontal-items">
									<div class="row">
										@if(isset($trips) && count($trips))
											@include('frontend.auth.tourguide.item')
										@else
											<div class="small-12 columns"><div class="panel shadow box"><div class="box-content">Data not found</div></div></div>
										@endif
									</div>
								</div>
								<div class="pagination-centered">
									{!! (new App\Pagination($trips))->render() !!}
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