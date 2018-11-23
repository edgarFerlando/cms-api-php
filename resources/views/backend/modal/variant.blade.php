<div class="modal fade variant-modal" tabindex="-1" role="dialog" aria-labelledby="variantModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-sm">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="variantModalLabel">{{{ trans('app.add_variant') }}}</h4>
			</div>
			{!! Form::open() !!}
			<div class="modal-body content">
				<div class="form-group">
					{!! Form::label('variant', trans('app.variant').' *') !!}
					{!! Form::lang_text('new_variant', '', [ 'class'=>'form-control slug', 'autocomplete' => 'off' ]) !!}
				</div>
			</div>
			<div class="modal-footer">
				{!! Form::button(trans('app.cancel'), [ 'class' => 'btn btn-default', 'data-dismiss' => 'modal' ]) !!}
				{!! Form::button(trans('app.save'), [ 'class' => 'btn btn-primary save-variant-modal' ]) !!}
			</div>
			{!! Form::close() !!}
		</div>
	</div>
</div>