									
									<p>
										{!! Form::open( [ 'url' => LangUrl('my-profile') ] ) !!}
										<h2>{{ trans('app.id_info') }}</h2>
										<div class="row">
											<div class="small-6 columns">
												<label>{!! trans('app.ktp_image') !!}
													{!! Form::cke_image('ktp_image', '', [ 'class'=>'is_cke', 'height' => '100']) !!}
			                						{!! Form::hidden('old_ktp_image', val($userMeta, 'ktp_image')) !!}
												</label>
											</div>
											<div class="small-6 columns">
												@if(isset($userMeta->ktp_image) && $userMeta->ktp_image != '')
													<img src="{!!val($userMeta, 'ktp_image')!!}" width="150px" height="100px"> 
												@endif
												
											</div>
										</div>
										<div class="row action-panel">
											<div class="small-12 columns">
												{!! Form::hidden( 'store_type', 'id-info', [ 'class' => 'radius autocomplete'] ) !!}
												{!! Form::submit( strtoupper(trans('app.save')), [ 'class' => 'button thirdary tiny radius expand' ] ) !!}
											</div>
										</div>
										{!! Form::close() !!}
									</p>
									