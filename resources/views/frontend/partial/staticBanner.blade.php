@if( isset($staticBanner) && count($staticBanner) )
<div class="banner">
	@if($staticBanner->url != '')
		<a href="{{ langUrl($staticBanner->url) }}"><img src="{!! asset($staticBanner->image) !!}" /></a>
	@else
		<img src="{!! asset($staticBanner->image) !!}" />
	@endif
</div>
@endif