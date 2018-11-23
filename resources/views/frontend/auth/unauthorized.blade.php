@extends('frontend.layout')
@section('body')
<div class="content-wrap detail collapse">
	<div class="banner">
		<img src="{{ asset('img/banner/spec-banner.jpg') }}">
	</div>
	<div class="row">
		<div class="large-12 columns">
			<div class="content-wrapper">
				{!! Notification::showAll() !!}
				<div class="row">
					<div class="small-12 columns">
						<div class="small-12 columns">
						<div class="panel shadow box">
							<div class="box-content">
								@if(isset($attr['box_title']))
							    	<div class="box-header with-border">
							            <h3 class="box-title">{!! $attr['box_title'] !!}</h3>
							        </div>
						        @endif
						        <div class="box-body">
						            {!! $attr['unauthorized_message'] !!}
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