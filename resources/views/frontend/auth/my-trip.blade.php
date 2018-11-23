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
				<div class="row">
					<div class="small-12 columns">
						<div class="small-12 columns">
							<div class="panel shadow box">
								<div class="box-content" id="my-trip-content">
									<div class="content-wrapper">
										<div class="search-result horizontal-items">
											<div class="row">
											@if(count($bookings))
										            @foreach ($bookings as $trip)
										            	<?php
										                	$productMeta = productMeta($trip->productVariation->product->productMetas);

										                	$start_date = date('Y-m-d', strtotime($productMeta->start_date));
										                	$now_date = date('Y-m-d');

										                	$mainImage = '';
															if(isset($trip->productVariation->product->productImages) && count($trip->productVariation->product->productImages))
										                	$mainImage = $trip->productVariation->product->productImages[0]->image_path;
										                	//dd($booking);
										                ?>
										                @if($trip->post_type == 'trip')
														<div class="small-6 columns item">
															<div class="panel shadow box">
																@if($mainImage == '' || !File::exists(public_path($mainImage)))
																	<img data-src="holder.js/130x130?text=&font=FontAwesome&size=14&bg=#fff&fg=#868686&fontweight=normal" />
																@else
																	<img class="squareClip" src="{{ asset($mainImage) }}" />
																@endif
																<ul class="no-bullet">
																
																	<li class="title">{!! trans('app.trip_name').' : '.$trip->productVariation->product->title !!}<h4></h4></li>
																	<li class="title">{!! trans('app.start_trip').' : '.date('d M Y', strtotime($productMeta->start_date)) !!}</li>
																	<li class="title">{!! trans('app.qty').' : '.$trip->qty !!}</li>

																</ul>
																{{-- @if($start_date > $now_date) --}}
																<div class="action-panel">{!! HTML::link(route(getLang().'.dashboard.my.trip.show', array('id' => $trip->id, 'slug' => $trip->productVariation->product->slug) ), trans('app.view_detail'), [ 'class' => 'button tiny radius' ] ) !!}</div>
																{{-- @endif --}}
															</div>
														</div>
														@endif
										           	@endforeach
											@else
												<div class="small-6 columns item">
													<div class="panel shadow box">
														Not yet trip
													</div>
												</div>
											@endif
											
											</div>
											<div class="pagination-centered">
												{!! (new App\Pagination($bookings))->render() !!}
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