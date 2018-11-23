								
								<p>
									<h2>{{ trans('app.my_profile') }}</h2>
									
									{!! Form::open( [ 'url' => LangUrl('my-profile') ] ) !!}
									<div class="row">
										<div class="small-12 columns">
											<label>E-Mail
												{!! Form::text( 'email', $user->email, [ 'class' => 'radius autocomplete', 'readonly' => 'readonly'] ) !!}
											</label>
										</div>
									</div>
									<div class="row">
										<div class="small-12 columns">
											<label>{!! trans('app.full_name') !!}
												{!! Form::text( 'full_name', val($userMeta, 'full_name'), [ 'class' => 'radius autocomplete'] ) !!}
											</label>
										</div>
									</div>
									<?php
										$male = trans('app.male');
										$female = trans('app.female');
									?>
									<div class="row">
										<div class="small-12 columns">
											<label>{!! trans('app.gender') !!}
												{!! Form::select('gender', ['M'=> $male,'F'=> $female ], val($userMeta, 'gender'), [ 'class' => 'selectize', 'autocomplete' => 'off' ]) !!}
												<!-- {!! Form::text( 'gender', val($userMeta, 'gender'), [ 'class' => 'radius autocomplete'] ) !!} -->
											</label>
										</div>
									</div>
									<div class="row">
										<div class="small-12 columns">
											<label>{!! trans('app.date_of_birth') !!}
												{!! Form::text( 'date_of_birth', val($userMeta, 'date_of_birth'), [ 'class' => 'radius fdatepickerDOB'] ) !!}
											</label>
										</div>
									</div>
									<div class="row">
										<div class="small-12 columns">
											<label>{!! trans('app.phone') !!}
												{!! Form::text( 'phone', val($userMeta, 'phone'), [ 'class' => 'radius autocomplete'] ) !!}
											</label>
										</div>
									</div>
									<div class="row">
										<div class="small-12 columns">
											<label>{!! trans('app.country') !!}
												{!! Form::text( 'country', val($userMeta, 'country'), [ 'class' => 'radius autocomplete'] ) !!}
											</label>
										</div>
									</div>
									<div class="row">
										<div class="small-12 columns">
											<label>{!! trans('app.city') !!}
												{!! Form::text( 'city', val($userMeta, 'city'), [ 'class' => 'radius autocomplete'] ) !!}
											</label>
										</div>
									</div>
									<div class="row">
										<div class="small-12 columns">
											<label>{!! trans('app.post_code') !!}
												{!! Form::text( 'post_code', val($userMeta, 'post_code'), [ 'class' => 'radius autocomplete'] ) !!}
											</label>
										</div>
									</div>
									<div class="row">
										<div class="small-12 columns">
											<label>{!! trans('app.address') !!}
												{!! Form::ckeditor('address', val($userMeta, 'address'), [ 'class'=>'form-control', 'height' => '200'], $errors) !!}
											</label>
										</div>
									</div>
									<div class="row action-panel">
										<div class="small-12 columns">
											{!! Form::hidden( 'store_type', 'general-info', [ 'class' => 'radius autocomplete'] ) !!}
											{!! Form::submit( strtoupper(trans('app.save')), [ 'class' => 'button thirdary tiny radius expand' ] ) !!}
										</div>
									</div>
									{!! Form::close() !!}
								</p>
								