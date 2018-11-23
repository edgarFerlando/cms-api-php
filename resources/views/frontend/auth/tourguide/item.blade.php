@foreach($trips as $item)
<?php
	
	$args = [
		'slug' => $item->slug
	];

	$productMeta = productMeta($item->productMetas);
	$location = $item->productCategory->title;
	//$productImages = count($item->productImages) > 0?$item->productImages[0]:'';

	$skuImage = '';
	$skuImages = [];

	//get lowest price
	$prices = [];
	$lowest_price_args = [];
	$lowest_price = 0;
	if(count($item->productSkus)){
		foreach($item->productSkus as $sku){
			$prices[] = $sku->price;
			foreach($sku->productVariations as $variant){
				//$product_attribute_id = $variant->productAttribute->product_attribute_key == 'room_type'?$sku->id:$variant->productAttributeOption->id;
				$product_attribute_id = $variant->productAttributeOption->id;
				$lowest_price_args[$sku->price][$variant->productAttribute->product_attribute_key] = $product_attribute_id;
				$skuImages[$sku->price] = $sku->room_image;
			}
			 
		}
		sort($prices);
		
		$lowest_price = count($prices)?$prices[0]:0;

		if(count($lowest_price_args))
			$args += $lowest_price_args[$lowest_price];

		$skuImage = $skuImages[$lowest_price];
	}


/*
	foreach($item->productSkus as $sku){
		$prices[] = $sku->price;
	}
	sort($prices);
	
	$lowest_price = count($prices)?$prices[0]:0;*/
	//thumb129x129
	$mainImage = '';
	if(isset($item->productImages) && count($item->productImages))
		$mainImage = $item->productImages[0]->image_path;

?>

	<div class="small-6 columns item">
		<div class="panel shadow box">
				@if($mainImage == '' || !File::exists(public_path($mainImage)))
					<img data-src="holder.js/130x130?text=&font=FontAwesome&size=14&bg=#fff&fg=#868686&fontweight=normal" />
				@else
					<img class="squareClip" src="{{ asset($mainImage) }}" />
				@endif
			
			 {{-- @if($productImages == '')
				<img data-src="holder.js/129x129" />
			@else
				<img src="{{ asset(thumb_path($productImages->image_path, 'thumb129x129')) }}" />
			@endif --}}
			<ul class="no-bullet">
				<?php
				$url = route(getLang().'.dashboard.trip.show', [ $item->slug, 'checkin='.Input::get('checkin'), 'checkout='.Input::get('checkout' )]);
				?>
				<li class="title"><h4>{!! HTML::link($url, $item->title ) !!}</h4></li>
				<li class="location">{!! HTML::link('#', $location) !!}</li>
				@if(isset($item['sale-price']))
					<li class="price strike bottom-collapse">{!! $item['price'] !!}</li>
					<li class="price sale top-collapse">{!! $item['price'] !!}</li>
				@else
					<li class="price normal">Rp. {!! money($lowest_price) !!}</li>
				@endif
			</ul>
			
			<div class="action-panel">{!! HTML::link(route(getLang().'.dashboard.trip.show', $item->slug ), trans('app.view_detail'), [ 'class' => 'button tiny radius' ] ) !!}</div>
		</div>
	</div>
@endforeach