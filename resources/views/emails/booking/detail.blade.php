@if(isset($details) && count($details))
<h3>{{ trans('app.hotel') }}</h3>
<table style="border: solid 1px #797979; border-spacing:0;margin:10px 0; font-size : 11px;" width="100%">
  <tr>
	<thead>
		<tr style="background-color:#868686; color:#ffffff;">
			<th style="padding:5px;border-right: solid 1px #797979;">{!! trans('app.hotel_name') !!}</th>
			<th style="padding:5px;border-right: solid 1px #797979;">{!! trans('app.room_type') !!}</th>
			<th style="padding:5px;border-right: solid 1px #797979;">{!! trans('app.check_in') !!}</th>
			<th style="padding:5px;border-right: solid 1px #797979;">{!! trans('app.check_out') !!}</th>
			<th style="padding:5px;border-right: solid 1px #797979;">{!! trans('app.nights') !!}</th>
			<th width="120" style="padding:5px;border-right: solid 1px #797979;">{!! trans('app.no_of_rooms') !!}</th>
			<th style="padding:5px;border-right: solid 1px #797979;">{!! trans('app.price') !!}</th>
			<th style="padding:5px;">{!! trans('app.total') !!}</th>
		</tr>
	</thead>
	<tbody>
		@if($details)
			<?php
			$grand_total = 0;
			?>
			@foreach($details as $detail)
				<?php
					$price_n_total_lbl = price_n_total_lbl([
                        'checkin' => $detail->attributes->checkin,
                        'checkout' => $detail->attributes->checkout,
                        'price' => $detail->price,
                        'weekend_price' => $detail->attributes->weekend_price,
                        'quantity' => $detail->quantity
                    ]);
					$grand_total += $price_n_total_lbl['total'];	
				?>

				<tr>
					<td style="vertical-align:top;padding:5px;border-bottom: solid 1px #797979;">{!! $detail->name !!}</td>
					<td style="vertical-align:top;padding:5px;border-bottom: solid 1px #797979;">{{{ $detail->attributes->room_type }}}</td>
					<td style="vertical-align:top;padding:5px;border-bottom: solid 1px #797979;">{{{ $detail->attributes->checkin != '0000-00-00'?carbon_format($detail->attributes->checkin,'d M Y'):'' }}}</td>
					<td style="vertical-align:top;padding:5px;border-bottom: solid 1px #797979;">{{{ $detail->attributes->checkout != '0000-00-00'?carbon_format($detail->attributes->checkout,'d M Y'):'' }}}</td>
					<td style="vertical-align:top;text-align:right;padding:5px;border-bottom: solid 1px #797979;">{!! $price_n_total_lbl['nights'] !!}</td>
					<td style="vertical-align:top;text-align:right;padding:5px;border-bottom: solid 1px #797979;">{!! $detail->quantity !!}</td>
					<td style="vertical-align:top;text-align:right;padding:5px;border-bottom: solid 1px #797979;">{!! $price_n_total_lbl['price_info_lbl_html'] !!}</td>
					<td style="vertical-align:top;text-align:right;padding:5px;border-bottom: solid 1px #797979;">{!! $price_n_total_lbl['total_info_lbl_html'] !!}</td>
				</tr>
			@endforeach
		@endif
	</tbody>
	<tfoot>
		<tr>
			<td colspan="7" style="vertical-align:top;text-align:right;">{!! trans('app.grand_total') !!}</td>
			<td style="vertical-align:top;text-align:right;padding:5px;">Rp. {{{ money($grand_total) }}}</td>
		</tr>
	</tfoot>
</table>
@endif

@if(isset($playgroundDetails) && count($playgroundDetails))
<h3>{{ trans('app.playground') }}</h3>
<table style="border: solid 1px #797979; border-spacing:0;margin:10px 0; font-size : 11px;" width="100%">
  <tr>
	<thead>
		<tr style="background-color:#868686; color:#ffffff;">
			<th style="padding:5px;border-right: solid 1px #797979;">{!! trans('app.name') !!}</th>
			<th style="padding:5px;border-right: solid 1px #797979;">{!! trans('app.ages') !!}</th>
			<th style="padding:5px;border-right: solid 1px #797979;">{!! trans('app.date') !!}</th>
			<th width="120" style="padding:5px;border-right: solid 1px #797979;">{!! trans('app.no_of_people') !!}</th>
			<th style="padding:5px;border-right: solid 1px #797979;">Price</th>
			<th style="padding:5px;">Total</th>
		</tr>
	</thead>
	<tbody>
		@if($playgroundDetails)
			<?php
			$grand_total = 0;
			?>
			@foreach($playgroundDetails as $detail)
				<?php
					$price_n_total_lbl = playground_price_n_total_lbl([
                        'playground_visit_date' => $detail->attributes->playground_visit_date,
                        'price' => $detail->price,
                        'weekend_price' => $detail->attributes->weekend_price,
                        'quantity' => $detail->quantity
                    ]);
					$grand_total += $price_n_total_lbl['total'];	
				?>

				<tr>
					<td style="vertical-align:top;padding:5px;border-bottom: solid 1px #797979;">{!! $detail->name !!}</td>
					<td style="vertical-align:top;padding:5px;border-bottom: solid 1px #797979;">{{{ $detail->attributes->ages }}}</td>
					<td style="vertical-align:top;padding:5px;border-bottom: solid 1px #797979;">{{{ $detail->attributes->playground_visit_date != '0000-00-00'?carbon_format($detail->attributes->playground_visit_date,'d M Y'):'' }}}</td>
					<td style="vertical-align:top;text-align:right;padding:5px;border-bottom: solid 1px #797979;">{!! $detail->quantity !!}</td>
					<td style="vertical-align:top;text-align:right;padding:5px;border-bottom: solid 1px #797979;">{!! $price_n_total_lbl['price_info_lbl_html'] !!}</td>
					<td style="vertical-align:top;text-align:right;padding:5px;border-bottom: solid 1px #797979;">{!! $price_n_total_lbl['total_info_lbl_html'] !!}</td>
				</tr>
			@endforeach
		@endif
	</tbody>
	<tfoot>
		<tr>
			<td colspan="5" style="vertical-align:top;text-align:right;">Grand Total</td>
			<td style="vertical-align:top;text-align:right;padding:5px;">Rp. {{{ money($grand_total) }}}</td>
		</tr>
	</tfoot>
</table>
@endif

@if(isset($tripDetails) && count($tripDetails))
<h3>{{ trans('app.trip') }}</h3>
<table style="border: solid 1px #797979; border-spacing:0;margin:10px 0; font-size : 11px;" width="100%">
  <tr>
	<thead>
		<tr style="background-color:#868686; color:#ffffff;">
			<th style="padding:5px;border-right: solid 1px #797979;">{!! trans('app.name') !!}</th>
			<th style="padding:5px;border-right: solid 1px #797979;">{!! trans('app.variants') !!}</th>
			<th style="padding:5px;border-right: solid 1px #797979;">{!! trans('app.date') !!}</th>
			<th width="120" style="padding:5px;border-right: solid 1px #797979;">{!! trans('app.no_of_people') !!}</th>
			<th style="padding:5px;border-right: solid 1px #797979;">Price</th>
			<th style="padding:5px;">Total</th>
		</tr>
	</thead>
	<tbody>
		@if($tripDetails)
			<?php
			$grand_total = 0;
			?>
			@foreach($tripDetails as $detail)
				<?php
					$price_n_total_lbl = trip_price_n_total_lbl([
                        'trip_visit_date' => $detail->attributes->trip_visit_date,
                        'price' => $detail->price,
                        'weekend_price' => $detail->attributes->weekend_price,
                        'quantity' => $detail->quantity
                    ]);
					$grand_total += $price_n_total_lbl['total'];	
					$visit_date = date('m/d/Y', strtotime($detail->attributes->trip_visit_date));
				?>

				<tr>
					<td style="vertical-align:top;padding:5px;border-bottom: solid 1px #797979;">{!! $detail->name !!}</td>
					<td style="vertical-align:top;padding:5px;border-bottom: solid 1px #797979;">{{{ $detail->attributes->variants }}}</td>
					<td style="vertical-align:top;padding:5px;border-bottom: solid 1px #797979;">{{{ $detail->attributes->trip_visit_date != '0000-00-00'?carbon_format($visit_date,'d M Y'):'' }}}</td>
					<td style="vertical-align:top;text-align:right;padding:5px;border-bottom: solid 1px #797979;">{!! $detail->quantity !!}</td>
					<td style="vertical-align:top;text-align:right;padding:5px;border-bottom: solid 1px #797979;">{!! $price_n_total_lbl['price_info_lbl_html'] !!}</td>
					<td style="vertical-align:top;text-align:right;padding:5px;border-bottom: solid 1px #797979;">{!! $price_n_total_lbl['total_info_lbl_html'] !!}</td>
				</tr>
			@endforeach
		@endif
	</tbody>
	<tfoot>
		<tr>
			<td colspan="5" style="vertical-align:top;text-align:right;">Grand Total</td>
			<td style="vertical-align:top;text-align:right;padding:5px;">Rp. {{{ money($grand_total) }}}</td>
		</tr>
	</tfoot>
</table>
@endif

@if(isset($merchantDetails) && count($merchantDetails))
<h3>{{ trans('app.merchant') }}</h3>
<table style="border: solid 1px #797979; border-spacing:0;margin:10px 0; font-size : 11px;" width="100%">
  <tr>
	<thead>
		<tr style="background-color:#868686; color:#ffffff;">
			<th style="padding:5px;border-right: solid 1px #797979;">{!! trans('app.name') !!}</th>
			<th style="padding:5px;border-right: solid 1px #797979;">{!! trans('app.ages') !!}</th>
			<th style="padding:5px;border-right: solid 1px #797979;">{!! trans('app.date') !!}</th>
			<th width="120" style="padding:5px;border-right: solid 1px #797979;">{!! trans('app.no_of_people') !!}</th>
			<th style="padding:5px;border-right: solid 1px #797979;">Price</th>
			<th style="padding:5px;">Total</th>
		</tr>
	</thead>
	<tbody>
		@if($merchantDetails)
			<?php
			$grand_total = 0;
			?>
			@foreach($merchantDetails as $detail)
				<?php
					$price_n_total_lbl = merchant_price_n_total_lbl([
                        'merchant_visit_date' => $detail->attributes->merchant_visit_date,
                        'price' => $detail->price,
                        'weekend_price' => $detail->attributes->weekend_price,
                        'quantity' => $detail->quantity
                    ]);
					$grand_total += $price_n_total_lbl['total'];	
				?>

				<tr>
					<td style="vertical-align:top;padding:5px;border-bottom: solid 1px #797979;">{!! $detail->name !!}</td>
					<td style="vertical-align:top;padding:5px;border-bottom: solid 1px #797979;">{{{ $detail->attributes->variants }}}</td>
					<td style="vertical-align:top;padding:5px;border-bottom: solid 1px #797979;">{{{ $detail->attributes->merchant_visit_date != '0000-00-00'?carbon_format($detail->attributes->merchant_visit_date,'d M Y'):'' }}}</td>
					<td style="vertical-align:top;text-align:right;padding:5px;border-bottom: solid 1px #797979;">{!! $detail->quantity !!}</td>
					<td style="vertical-align:top;text-align:right;padding:5px;border-bottom: solid 1px #797979;">{!! $price_n_total_lbl['price_info_lbl_html'] !!}</td>
					<td style="vertical-align:top;text-align:right;padding:5px;border-bottom: solid 1px #797979;">{!! $price_n_total_lbl['total_info_lbl_html'] !!}</td>
				</tr>
			@endforeach
		@endif
	</tbody>
	<tfoot>
		<tr>
			<td colspan="5" style="vertical-align:top;text-align:right;">Grand Total</td>
			<td style="vertical-align:top;text-align:right;padding:5px;">Rp. {{{ money($grand_total) }}}</td>
		</tr>
	</tfoot>
</table>
@endif