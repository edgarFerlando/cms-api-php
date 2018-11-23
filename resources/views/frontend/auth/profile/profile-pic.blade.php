								
								<p>
									{!! Form::open( [ 'url' => LangUrl('my-profile') ] ) !!}
									<h2>{{ trans('app.profile_picture') }}</h2>
									<div class="row">
										<div class="small-6 columns">
											<label>{!! trans('app.user_thumbnail') !!}
												{!! Form::cke_image('user_thumbnail', '', [ 'class'=>'is_cke', 'height' => '100']) !!}
				        						{!! Form::hidden('old_user_thumbnail', val($userMeta, 'user_thumbnail')) !!}
											</label>
										</div>
										<div class="small-6 columns">
											@if(isset($userMeta->user_thumbnail) && $userMeta->user_thumbnail != '')
												<img src="{!!val($userMeta, 'user_thumbnail')!!}" width="150px" height="100px"> 
											@endif
											
										</div>
									</div>
									<div class="row">
										<div class="small-6 columns">
											<label>{!! trans('app.user_image') !!}
												{!! Form::cke_image('user_image', '', [ 'class'=>'is_cke', 'height' => '100']) !!}
				        						{!! Form::hidden('old_user_image', val($userMeta, 'user_image')) !!}
											</label>
										</div>
										<div class="small-6 columns">
											@if(isset($userMeta->user_image) && $userMeta->user_image != '')
												<img src="{!!val($userMeta, 'user_image')!!}" width="150px" height="100px"> 
											@endif
										</div>
									</div>
									
									<div class="row action-panel">
										<div class="small-12 columns">
											{!! Form::hidden( 'store_type', 'profile-pic', [ 'class' => 'radius autocomplete'] ) !!}
											{!! Form::submit( strtoupper(trans('app.save')), [ 'class' => 'button thirdary tiny radius expand' ] ) !!}
										</div>
									</div>
									{!! Form::close() !!}
								</p>
								