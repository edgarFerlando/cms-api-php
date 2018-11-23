<div class="box shadow">
	<div class="box-content">
		{!! Form::open() !!}
		<div class="row">
			<div class="small-6 columns">
				<label>Check-in
				{!! Form::text( 'checkin', '', [ 'class' => 'radius checkin', 'autocomplete' => 'off' ] ) !!}
				</label>
			</div>
			<div class="small-6 columns">
				<label>Night(s)
				{!! Form::select( 'where', [ '1' => '1' ], '', [ 'class' => 'radius', 'autocomplete_source' => url('lists-city')] ) !!}</label>
			</div>
			<div class="small-6 columns end">
				<label>Check-out
				{!! Form::text( 'checkout', '', [ 'class' => 'radius checkout', 'autocomplete' => 'off' ] ) !!}</label>
			</div>
		</div>

		<div class="row action-panel">
			<div class="large-12 columns">
				{!! Form::submit( 'BOOK NOW', [ 'class' => 'button tiny alert radius expand bold' ] ) !!}
			</div>
			<div class="large-12 columns">
				{!! Form::submit( 'CHANGE', [ 'class' => 'button tiny warning radius expand bold' ] ) !!}
			</div>
		</div>
		{!! Form::close() !!}
	</div>
</div>

@include('partial.side_bar_filterby')    	