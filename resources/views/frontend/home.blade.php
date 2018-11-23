@extends('frontend.layout')
@section('body')
<div class="content-wrap home collapse">
	<?php

	//dd(session()->all());
	//$promo = (object)[
	//(object)[
	//	'picture' => '<img src="'.asset('img/banner/main-banner.jpg').'">'
	//	],
	/*(object)[
		'picture' => '<img src="'.asset('img/banner/main-banner-2.jpg').'">'
		]*/
	//];
	?>
	@if( isset($mainBanners) && count($mainBanners) )
	<div class="banner">
		<ul id="banner-slider" data-orbit data-options="animation:fade; timer_speed:5000; bullets:false;">
			@foreach( $mainBanners as $item )
				@if($item->image)
					<li>
						@if($item->url != '')
							<a href="{{ langUrl($item->url) }}"><img src="{!! asset($item->image) !!}" /></a>
						@else
							<img src="{!! asset($item->image) !!}" />
						@endif
					</li>
				@endif
			@endforeach
		</ul>
	</div>
	@endif

	<!--
	<div class="row has-carousel">
		<div class="large-12 columns">
			<h3>{{ trans('app.special_offers') }} <a href="#">{{ trans('app.view_all') }} <i class="fa fa-caret-right"></i></a><small>Lorem ipsum dolor sit amet.</small></h3>
		</div>
		<?php 
		$items = [
		[
		'title' => 'Carlton Hotel',
		'img' => 'item-sm-1.jpg',
		'stars' => 5,
		'location' => 'Orchard, Singapore',
		'latest-booking' => 'Latest booking : 30 minutes ago',
		'price' => 'Rp 1.950.000',
		'sale-price' => 'Rp 1.900.000'
		],
		[
		'title' => 'Mandarin Orchard',
		'img' => 'item-sm-2.jpg',
		'stars' => 4,
		'location' => 'Orchard, Singapore',
		'latest-booking' => 'Latest booking : 30 minutes ago',
		'price' => 'Rp 1.950.000'
		],
		[
		'title' => 'Pan Pasific',
		'img' => 'item-sm-3.jpg',
		'stars' => 3,
		'location' => 'Orchard, Singapore',
		'latest-booking' => 'Latest booking : 30 minutes ago',
		'price' => 'Rp 1.950.000',
		'sale-price' => 'Rp 1.900.000'
		],
		[
		'title' => 'Holiday Inn',
		'img' => 'item-sm-4.jpg',
		'stars' => 2,
		'location' => 'Orchard, Singapore',
		'latest-booking' => 'Latest booking : 30 minutes ago',
		'price' => 'Rp 1.950.000'
		],
		[
		'title' => 'Mandarin Orchard',
		'img' => 'item-sm-1.jpg',
		'stars' => 1,
		'location' => 'Orchard, Singapore',
		'latest-booking' => 'Latest booking : 30 minutes ago',
		'price' => 'Rp 1.950.000'
		],
		[
		'title' => 'Pan Pasific',
		'img' => 'item-sm-2.jpg',
		'stars' => 5,
		'location' => 'Orchard, Singapore',
		'latest-booking' => 'Latest booking : 30 minutes ago',
		'price' => 'Rp 1.950.000'
		],
		[
		'title' => 'Carlton Hotel',
		'img' => 'item-sm-3.jpg',
		'stars' => 4,
		'location' => 'Orchard, Singapore',
		'latest-booking' => 'Latest booking : 30 minutes ago',
		'price' => 'Rp 1.950.000'
		],
		[
		'title' => 'Holiday Inn',
		'img' => 'item-sm-4.jpg',
		'stars' => 3,
		'location' => 'Orchard, Singapore',
		'latest-booking' => 'Latest booking : 30 minutes ago',
		'price' => 'Rp 1.950.000'
		]
		];
		?>
		<div class="large-12 columns">
			<div class="special-offers-carousel">
				@foreach($specialOffers as $item)
				<?php
					$product = $item->product;
					$productImages = isset($product->productImages) && count($product->productImages)?$product->productImages[0]:'';
					$productSpecialOffers = $product->productSpecialOffers;
					$productMeta = productMeta($product->productMetas); 
					$location = $product->productCategory;
					//get lowest price
					$normal_prices = [];
					$special_prices = [];
					$special_offer_percent = [];
					//$special_prices = [];
					//$prices[5] = "1";
					foreach($product->productSkus as $sku){
						$normal_prices[$sku->id] = $sku->price;
						//$special_prices[$sku->id] = $sku->price * 
					}
					foreach($productSpecialOffers as $productSpecialOffer){
						$special_prices[$sku->id] = $normal_prices[$productSpecialOffer->product_sku_id] - ( $normal_prices[$productSpecialOffer->product_sku_id] * ( $productSpecialOffer->special_offer / 100) ) ;
						$special_offer_percent[$sku->id] = $productSpecialOffer->special_offer;
						//$special_prices[$sku->id] = $sku->price * 
					}
					//dd($special_prices);
					asort($special_prices);
					$smallest_specialPrice = current($special_prices); 
					$skuID_of_smallest_specialPrice = array_keys($special_prices)[0];
					$normalPrice_of_smallest_specialPrice = $normal_prices[$skuID_of_smallest_specialPrice];
					$specialPercent = $special_offer_percent[$skuID_of_smallest_specialPrice];


					//asort($prices);
					//dd(current($prices));//mendapatkan value pertama
					//dd(array_keys($prices)[0]);//smalest array keys
					//$lowest_price = $prices[0];

				?>
					<div class="item">
						@if($productImages != '')
							<img src="{{ asset(thumb_path($productImages->image_path, 'thumb225x153')) }}">
						@else
							<img data-src="holder.js/227x153/#564F8A:#8C86B9">
						@endif
						<div class="sale-tag">SAVE {!! $specialPercent !!}%</div>
						<div class="panel shadow">
							<ul class="no-bullet">
								<li class="title"><h4>{!! HTML::link( route(getLang().'.dashboard.hotel.show', [ $product->slug ]) , $product->title ) !!}</h4></li>
								<li class="stars">{!! stars($productMeta->hotel_star) !!}</li>
								<li class="location">{!! HTML::link(route(getLang().'.dashboard.hotel.region', [ $location->slug ]), $location->title) !!}</li>
								@if(isset($smallest_specialPrice))
									<li class="price strike bottom-collapse">Rp. {!! money($normalPrice_of_smallest_specialPrice) !!}</li>
									<li class="price sale top-collapse">Rp. {!! money($smallest_specialPrice) !!}</li>
								@else
									<li class="price normal">Rp. {!! money($smallest_specialPrice) !!}</li>
								@endif
								<li class="action-panel">{!! HTML::link(LangUrl('hotel/'.$product->slug), 'VIEW DETAIL', [ 'class' => 'button tiny radius expand' ] ) !!}</li>
							</ul>
						</div>
					</div>
				@endforeach
			</div>
		</div>
	</div>
	-->
	<!--
	<div id="youtube">
		<div class="row has-carousel">
			<div class="large-6 columns">
				<iframe src="http://www.youtube.com/embed/{{ $video }}"
   				width="100%" height="315" frameborder="0" allowfullscreen></iframe>			
			</div>
			<div class="large-6 columns">
				i'm here
			</div>
		</div>
	</div>
	-->
		<div class="large-12">
			<div class="large-8 columns">	
					<div class="small-12 columns">							
						<iframe src="http://www.youtube.com/embed/{{ $video }}" width="100%" height="307px" frameborder="0" allowfullscreen></iframe>
					</div>		
			</div>
			<div class="large-4 columns">
					<div class="small-6 columns">
						<div class="row">													
							<span>icon</span>
						</div>
					</div>
					<div class="small-6 columns">
						<div class="row">													
							<span>icon</span>	
						</div>
					</div>
					<div class="small-6 columns">
						<div class="row">													
							<span>icon</span>
						</div>
					</div>
					<div class="small-6 columns">
						<div class="row">													
							<span>icon</span>
						</div>
					</div>
			</div>
		</div>

	<div class="large-12">
		<!--
	  	<div class="large-6 columns">
	  		<div class="small-12 columns">
	  			<div id="gmap" style="width: 100%; height: 400px;"></div>
	  		</div>
	  	</div>
	  	-->
	  	<div class="large-12 columns">
			<h3>{{ trans('app.city') }}</h3>
		</div>
	  	<div class="large-12 columns">
	  		<ul class="small-block-grid-3">
	  		@foreach($locations as $location)
				@if(count($location->children))
					@foreach($location->children as $city)
						<li class="image-hover-wrapper">
						  <span class="image-hover-wrapper-banner">{!! $city->title !!}</span>
						  <a href="{!! route(getLang().'.dashboard.trip.city',$city->slug) !!}"><img src="{{ $city->image?asset($city->image):asset('img/products/item-sm-1.jpg') }}">
						    <span class="image-hover-wrapper-reveal">
						      <p>Check it<br><i class="fa fa-link" aria-hidden="true"></i></p>
						    </span>
						  </a>
						</li>
					@endforeach
				@endif
			@endforeach
			</ul>
	  	</div>
	</div>
	<div class="large-12">
	  	<div class="large-12 columns">
	  	<div class="row has-carousel">
			<?php 
				$no = 0;
			?>
			<div class="large-12 columns">
				<div class="large-12 columns">
					<h3>{{ trans('app.tour_guide') }}</h3>
				</div>
				<div class="large-12 columns">
					<div class="special-offers-carousel">
						@foreach($tourguides as $tourguide)
						<div class="item">
							<?php
					  			$userMeta = userMeta($tourguide->userMetas); 

					  			//$lang_skill = $userMeta->language_skill;
					  			
					  			//dd($userMeta->about_me);
					  		?>
					  		<a href="{!! getLang().'/tourguide/detail/'.$tourguide->id !!}">@if(isset($userMeta->user_image) && $userMeta->user_image != '')
									<img src="{{ asset($userMeta->user_image) }}" />			
								@else
									<img data-src="holder.js/130x130?text=&font=FontAwesome&size=14&bg=#fff&fg=#868686&fontweight=normal" />
								@endif
							</a>
							
							<div class="panel shadow">
								<ul class="no-bullet">
									<li class="title"><h4>{!! $tourguide->name !!}</h4></li>
									<!--<li>
										<i class="fa fa-star"></i>
										<i class="fa fa-star"></i>
										<i class="fa fa-star"></i>
										<i class="fa fa-star"></i>
									</li>-->
									@if(strpos($userMeta->language_skill, ',') !== false)						  			
						  				<?php
						  					$lang_skill = explode(',', $userMeta->language_skill);
						  				?>
						  				<li>
							  				<ul class="small-block-grid-3">
							  				@foreach($lang_skill as $skill)					  					
							  					<li class="text-center">{!! $skill !!}</li>
							  				@endforeach
							  				</ul>
						  				</li>
									@else
					  					<li>{!! $userMeta->language_skill !!}</li>
									@endif								
								</ul>
							</div>
						</div>
						@endforeach
					</div>
					<div class="pagination-centered">
						<div class="large-12 columns"><a href="{!! getLang().'/tourguide' !!}">More See</a></li>
					</div>
				</div>
			</div>
		</div>
		</div>
	</div>

	@if( isset($mainBanners) && count($mainBanners) )
	<div class="banner">
		<ul id="banner-slider" data-orbit data-options="animation:fade; timer_speed:5000; bullets:false;">
			@foreach( $mainBanners as $item )
				@if($item->image)
					<li>
						@if($item->url != '')
							<a href="{{ langUrl($item->url) }}"><img src="{!! asset($item->image) !!}" /></a>
						@else
							<img src="{!! asset($item->image) !!}" />
						@endif
					</li>
				@endif
			@endforeach
		</ul>
	</div>
	@endif
	<div class="row has-carousel">
			<div class="large-12 columns">
					<h3>{{ trans('app.testimonial') }}</h3>
				</div>
			<div class="large-12 columns">
			<div class="special-offers-carousel">
			@if($guestBooks)
				@foreach($guestBooks as $guestBook)
					<?php 
						//dd($guestBook);
						$userMeta = userMeta($guestBook->user->userMetas);

						$mainImage = '';

						$mainImage = $userMeta->user_thumbnail;
					?>
				  	<div class="item">
						
					  		@if($mainImage == '' || !File::exists(public_path($mainImage)))
								<img data-src="holder.js/130x130?text=&font=FontAwesome&size=14&bg=#fff&fg=#868686&fontweight=normal"/>
							@else
								<img src="{{ asset($mainImage) }}"/>
							@endif
						<div class="panel shadow">
							<ul class="no-bullet">
								<li class="title">{!! $guestBook->name !!}</li>
							</ul>

						</div>
					</div>
					
					
				@endforeach			
			@endif
			</div>
			</div>
			<div><a href="{!! route(getLang().'.testimonial') !!}">More See</a></div>
	</div>
</div>
<div class="row"></div>
@stop